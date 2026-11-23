/**
 * WooCommerce HTTP regressions on an isolated, already running Playground site.
 *
 * Usage: node .claude/skills/pre-release/scripts/woocommerce.js --port=9001
 * Select a named run with --site=pre-release-woocommerce instead of --port.
 * The standard Playground seed and real WooCommerce must already be loaded.
 */

const crypto = require( 'node:crypto' );
const fs     = require( 'node:fs' );
const path   = require( 'node:path' );
const assert = require( 'node:assert/strict' );

const root = path.resolve(
	__dirname,
	'..',
	'..',
	'..',
	'..',
);
const flags = Object.fromEntries( process.argv.slice( 2 )
	.map( argument => {

		const split = argument.indexOf( '=' );

		if ( ! argument.startsWith( '--' ) || split < 3 )
			throw new Error(
				'Use --port=<port>, --site=<site>, or --output=<report>.',
			);

		return [ argument.slice( 2, split ), argument.slice( split + 1 ) ];
	} ) );
const engine = process.env.WP_PLUGIN_REGRESSION_DIR || path.join(
	root,
	'.local',
	'playground',
	'wp-plugin-regression',
);
const permission = path.join(
	root,
	'.local',
	'playground',
	'permission.txt',
);

if (
	   ! fs.existsSync( permission )
	|| fs.readFileSync( permission, 'utf8' )
		.trim() !== 'PLAYGROUND=True'
) {
	throw new Error(
		'Playground permission must be recorded as PLAYGROUND=True.',
	);
}

const state = require( path.join(
	engine,
	'lib',
	'state.js',
) );
const runs = state.readRuns()
	.filter(
		run => path.resolve( run.root ) === root && state.pidAlive( run.pid ),
	);
const matches = runs.filter(
	run => flags.port ? String( run.port ) === flags.port : run.site === ( flags.site || 'pre-release-woocommerce' ),
);

if ( matches.length !== 1 )
	throw new Error(
		'Select exactly one isolated Playground run with --port or --site.',
	);

const run    = matches[0];
const origin = new URL( run.url );

if (
	   origin.protocol !== 'http:'
	|| ! [ '127.0.0.1', 'localhost', '[::1]' ].includes( origin.hostname )
) {
	throw new Error(
		'Only an owned loopback Playground run may be mutated.',
	);
}

const persist = state.siteDir(
	run.slug,
	run.wpVersion,
	run.site,
);
const fixture = path.join(
	persist,
	'mu-plugins',
	'wpr-woocommerce-fixture.php',
);

if ( fs.existsSync( fixture ) )
	throw new Error(
		'A WooCommerce fixture is already installed. Use a fresh Playground site.',
	);

const key = fs.readFileSync(
	state.harnessKeyPath( run.slug ),
	'utf8',
)
	.trim();
const token      = crypto.randomUUID();
const reportPath = path.resolve(
	root,
	flags.output || '.local/playground/captures/woocommerce.json',
);
const debugLog   = path.join( persist, 'debug.log' );
const debugStart = fs.existsSync( debugLog ) ? fs.statSync( debugLog ).size : 0;
const timeout    = 30000; // Allow up to 30 seconds per Playground request.
const cases      = [];
const presets    = [
	[ 'plain', '' ],
	[ 'day-name', '/%year%/%monthnum%/%day%/%postname%/' ],
	[ 'month-name', '/%year%/%monthnum%/%postname%/' ],
	[ 'numeric-archives', '/archives/%post_id%' ],
	[ 'post-name', '/%postname%/' ],
	[ 'blog-front', '/blog/%postname%/' ],
	[ 'index-name', '/index.php/%postname%/' ],
	[ 'index-blog', '/index.php/blog/%postname%/' ],
	[ 'category-name', '/%category%/%postname%/' ],
	[ 'author-name', '/%author%/%postname%/' ],
	[ 'post-id', '/%post_id%/' ],
	[ 'post-name-no-slash', '/%postname%' ],
	[ 'blog-no-slash', '/blog/%postname%' ],
	[ 'day-no-slash', '/%year%/%monthnum%/%day%/%postname%' ],
];

/**
 * Records one complete route or feature assertion.
 *
 * @param {string} name Assertion label.
 * @param {*} actual Observed value.
 * @param {*} expected Required value.
 */
function check( name, actual, expected ) {

	try {
		assert.deepStrictEqual( actual, expected );
		cases.push( {
			name,
			passed: true,
		} );
	} catch {
		console.error( `FAIL: ${name}` );
		cases.push( {
			name,
			passed: false,
			actual,
			expected,
		} );
	}
}

/**
 * Calls the key-authenticated fixture after WordPress initialization.
 *
 * @param {Object} body Fixture action.
 * @return {Promise<Object>}
 */
async function control( body ) {

	const response = await fetch(
		new URL( '/?wpr-woocommerce=1', origin ),
		{
			method:   'POST',
			headers:  {
				'Content-Type': 'application/json',
				'X-WPR-Key':    key,
			},
			body:     JSON.stringify( {
				...body,
				token,
			} ),
			redirect: 'manual',
			signal:   AbortSignal.timeout( timeout ),
		},
	);
	const text = await response.text();
	let data;

	try {
		data = JSON.parse( text );
	} catch {
		throw new Error(
			`Fixture HTTP ${response.status}: ${text.slice( 0, 500 )}`,
		);
	}

	if ( ! response.ok || data.error )
		throw new Error(
			data.error || `Fixture HTTP ${response.status}`,
		);

	return data;
}

/**
 * Decodes URL character references from HTML attributes and XML elements.
 *
 * @param {string} value Encoded URL.
 * @return {string}
 */
function decode( value ) {
	return value.replace(
		/&(?:amp|quot|apos|lt|gt|#(\d+)|#x([0-9a-f]+));/gi,
		( entity, decimal, hexadecimal ) => {

			if ( decimal || hexadecimal )
				return String.fromCodePoint(
					parseInt(
						decimal || hexadecimal,
						decimal ? 10 : 16,
					),
				);

			return {
				'&amp;':  '&',
				'&quot;': '"',
				'&apos;': "'",
				'&lt;':   '<',
				'&gt;':   '>',
			}[ entity.toLowerCase() ];
		},
	);
}

/**
 * Reads one HTTP response without following redirects.
 *
 * @param {string} url Absolute or relative test URL.
 * @return {Promise<Object>}
 */
async function request( url ) {

	const target = new URL( url, origin );

	if ( target.origin !== origin.origin )
		throw new Error(
			`Fixture returned a foreign origin: ${target.origin}`,
		);

	const response = await fetch(
		target,
		{
			redirect: 'manual',
			signal:   AbortSignal.timeout( timeout ),
		},
	);
	const text    = await response.text();
	const headers = response.headers;

	return {
		status:           response.status,
		contentType:      headers.get( 'content-type' ) || '',
		location:         headers.get( 'location' ) || '',
		category:         headers.get( 'x-wpr-category' ) || '0',
		singular:         headers.get( 'x-wpr-singular' ) || '0',
		shop:             headers.get( 'x-wpr-shop' ) || '0',
		taxonomy:         headers.get( 'x-wpr-taxonomy' ) || '0',
		archive:          headers.get( 'x-wpr-archive' ) || '',
		product:          headers.get( 'x-wpr-product' ) || '0',
		product_category: headers.get( 'x-wpr-product-category' ) || '0',
		productListing:   headers.get( 'x-wpr-product-listing' ) ? JSON.parse( headers.get( 'x-wpr-product-listing' ) ) : null,
		product_tag:      headers.get( 'x-wpr-product-tag' ) || '0',
		matchedQuery:     headers.get( 'x-wpr-matched-query' ) || '',
		canonical:        decode( text.match(
			/<link\b[^>]*rel="canonical"[^>]*href="([^"]+)"/,
		)?.[1] || '' ),
		og:               decode( text.match(
			/<meta\b[^>]*property="og:url"[^>]*content="([^"]+)"/,
		)?.[1] || '' ),
		robots:           text.match(
			/<meta\b[^>]*name="robots"[^>]*content="([^"]+)"/,
		)?.[1] || '',
		breadcrumbs:      headers.get( 'x-wpr-breadcrumb' ) ? JSON.parse( decodeURIComponent( headers.get( 'x-wpr-breadcrumb' ) ) ) : [],
		locations:        [ ...text.matchAll( /<loc>([^<]+)<\/loc>/g ) ]
			.map( match => decode( match[1] ) ),
	};
}

/**
 * Checks real sitemap output for the fixture's current public URLs.
 *
 * @param {string} name Layout and feature state.
 * @param {Object} info Fixture URLs.
 * @return {Promise<void>}
 */
async function sitemap( name, info ) {

	const response = await request( info.sitemap );
	check(
		`${name}: sitemap is XML`,
		( response.status === 200 && /\bxml\b/.test( response.contentType ) ),
		true,
	);

	for ( const label of [ 'product_one', 'product_two', 'product_three', 'product_four', 'post' ] )
		check(
			`${name}: sitemap includes ${label}`,
			response.locations.includes( info.links[ label ] ),
			true,
		);
}

/**
 * Adds an archive suffix with the site's trailing-slash convention.
 *
 * @param {string} url Archive URL.
 * @param {string} suffix Added archive path.
 * @param {Boolean} slash Whether to append a slash.
 * @return {string}
 */
function append( url, suffix, slash ) {

	const target = new URL( url );
	target.pathname = target.pathname.replace( /\/$/, '' ) + '/' + suffix + ( slash ? '/' : '' );

	return target.href;
}

/**
 * Exercises one permalink layout with commerce, CPTs, and category toggling.
 *
 * @param {string} name Layout label.
 * @param {string} permalink WordPress permalink structure.
 * @param {Object} woocommerce WooCommerce permalink options.
 * @param {Boolean} feature Whether this TSF version supports taxonomy base removal.
 */
async function layout( name, permalink, woocommerce, feature ) {

	console.log( `WooCommerce: ${name}` );
	await control( {
		action:  'config',
		permalink,
		woocommerce,
		enabled: false,
	} );
	await request( '/' );
	const before = await control( { action: 'info' } );
	const urls   = { ...before.links };
	const slash  = permalink.endsWith( '/' );

	if ( permalink ) {
		urls.shop_paged             = append( urls.shop, 'page/2', slash );
		urls.product_category_paged = append( urls.product_cat, 'page/2', slash );
		urls.product_category_feed  = append( urls.product_cat, 'feed', slash );
		urls.account_lost_password  = append( urls.myaccount, 'lost-password', slash );

		before.owners.shop_paged             = before.owners.shop;
		before.owners.product_category_paged = before.owners.product_cat;
		before.owners.product_category_feed  = before.owners.product_cat;
		before.owners.account_lost_password  = before.owners.myaccount;
	}
	const snapshots = {};

	for ( const [ label, url ] of Object.entries( urls ) ) {
		if ( ! url )
			throw new Error(
				`Required baseline fixture missing: ${label}`,
			);

		const response         = await request( url );
		const checkoutRedirect = label === 'checkout' && response.status === 302 && response.location === urls.cart;
		snapshots[ label ]     = response;

		check(
			`${name}: ${label} baseline resolves`,
			( response.status === 200 || checkoutRedirect ),
			true,
		);
		const owner = before.owners[ label ];

		if ( owner && response.status === 200 )
			check(
				`${name}: ${label} query owner`,
				Object.fromEntries( Object.keys( owner )
					.map(
						field => [ field, response[ field ] ],
					) ),
				owner,
			);

		if (
			   owner
			&& ! [ 'cart', 'checkout', 'myaccount', 'account_lost_password', 'product_category_feed' ].includes( label )
		) {
			const canonical = label === 'shop' ? before.shop_canonical : url;
			check(
				`${name}: ${label} canonical and Open Graph URL`,
				{
					canonical: response.canonical,
					og:        response.og,
				},
				{
					canonical,
					og: canonical,
				},
			);
		}

		if ( label.startsWith( 'product_' ) && owner )
			check(
				`${name}: ${label} WooCommerce breadcrumb is present`,
				response.breadcrumbs.length > 0,
				true,
			);

		if ( label === 'product_category_feed' )
			check(
				`${name}: product category feed is XML`,
				/\bxml\b/.test( response.contentType ),
				true,
			);

		if (
			   [ 'cart', 'checkout', 'myaccount' ].includes( label )
			&& response.status === 200
		) {
			check(
				`${name}: ${label} noindex`,
				/\bnoindex\b/.test( response.robots ),
				true,
			);
		}
	}
	await sitemap( `${name}: disabled`, before );

	if ( ! feature ) return;

	await control( {
		action:  'config',
		permalink,
		woocommerce,
		enabled: true,
	} );
	await request( '/' );
	const after = await control( { action: 'info' } );
	check(
		`${name}: generated commerce and CPT URLs preserved`,
		after.links,
		before.links,
	);
	await sitemap( `${name}: enabled`, after );

	// With post-name permalinks, no native rule matches this three-level archive.
	if ( name === 'post-name' ) {
		const term = after.categories.grandchild;

		for ( const [ argument, suffix ] of [ [ 'paged=2', 'page/2' ], [ 'feed=rss2', 'feed' ] ] ) {
			const pretty = append( term, suffix, slash );

			for ( const url of [ pretty, `${term}?${argument}` ] ) {
				const response = await request( url );
				const feed     = suffix === 'feed';
				check(
					`${name}: grandchild archive at ${url}`,
					[ response.status, response.category, feed ? /\bxml\b/.test( response.contentType ) : response.canonical ],
					[ 200, String( after.ids.grandchild ), feed ? true : pretty ],
				);
			}
		}
	}

	for ( const [ label, url ] of Object.entries( urls ) ) {
		const expected = structuredClone( snapshots[ label ] );

		if ( label === 'post' ) for ( const crumb of expected.breadcrumbs )
			for ( const [ category, oldUrl ] of Object.entries( before.categories ) )
				if ( crumb[1] === oldUrl )
					crumb[1] = after.categories[ category ];

		check(
			`${name}: ${label} response preserved`,
			await request( url ),
			expected,
		);
	}

	if ( permalink ) {
		for ( const category of [ 'category', 'child' ] ) {
			const target = before.categories[ category ].replace( '/category/', '/' );
			check(
				`${name}: ${category} shortened link`,
				after.categories[ category ],
				target,
			);
			for ( const url of [ target, before.categories[ category ] ] ) {
				const response = await request( url );
				const id       = category === 'category' ? after.ids.category : after.ids.child;
				check(
					`${name}: ${category} query and canonical at ${url}`,
					{
						status:    response.status,
						location:  response.location,
						category:  response.category,
						canonical: response.canonical,
						og:        response.og,
					},
					{
						status:    200,
						location:  '',
						category:  String( id ),
						canonical: target,
						og:        target,
					},
				);
			}
		}

		for ( const suffix of [ 'page/2', 'feed' ] ) {
			const oldUrl = append(
				before.categories.category,
				suffix,
				slash,
			);
			const target = append(
				after.categories.category,
				suffix,
				slash,
			);

			for ( const url of [ target, oldUrl ] ) {
				const response = await request( url );
				check(
					`${name}: category ${suffix} query at ${url}`,
					{
						status:   response.status,
						location: response.location,
						category: response.category,
					},
					{
						status:   200,
						location: '',
						category: String( after.ids.category ),
					},
				);

				if ( suffix === 'feed' ) {
					check(
						`${name}: category feed is XML at ${url}`,
						/\bxml\b/.test( response.contentType ),
						true,
					);
				} else {
					check(
						`${name}: category ${suffix} canonical and Open Graph URL at ${url}`,
						{
							canonical: response.canonical,
							og:        response.og,
						},
						{
							canonical: target,
							og:        target,
						},
					);
				}
			}
		}
	} else {
		check(
			`${name}: plain category links preserved`,
			after.categories,
			before.categories,
		);

		for ( const category of [ 'category', 'child' ] ) {
			const target   = after.categories[ category ];
			const response = await request( target );
			const id       = category === 'category' ? after.ids.category : after.ids.child;
			check(
				`${name}: retained ${category} query and canonical`,
				{
					status:    response.status,
					category:  response.category,
					canonical: response.canonical,
				},
				{
					status:    200,
					category:  String( id ),
					canonical: target,
				},
			);
		}
	}

	for ( const slug of Object.keys( after.ids.collisions ) ) {
		const target = permalink ? before.categories[ slug ].replace( '/category/', '/' ) : before.categories[ slug ];
		check(
			`${name}: ${slug} category link remains independent of other routes`,
			after.categories[ slug ],
			target,
		);
	}

	await control( {
		action:     'config',
		permalink,
		woocommerce,
		enabled:    true,
		taxonomies: [ 'product_cat' ],
	} );
	const commerce      = await control( { action: 'info' } );
	const expectedLinks = { ...before.links };

	for ( const label of [ 'product_cat', 'product_child' ] ) {
		const target = permalink ? before.links[ label ].replace( `/${before.product_category_base}/`, '/' ) : before.links[ label ];
		expectedLinks[ label ] = target;
		check(
			`${name}: ${label} shortened link`,
			commerce.links[ label ],
			target,
		);

		for ( const url of new Set( [
			target,
			before.links[ label ],
		] ) ) {
			const response = await request( url );
			check(
				`${name}: ${label} archive query and canonical at ${url}`,
				{
					status:    response.status,
					category:  response.product_category,
					canonical: response.canonical,
					og:        response.og,
				},
				{
					status:    200,
					category:  String( commerce.ids[ label ] ),
					canonical: target,
					og:        target,
				},
			);
		}
	}
	check(
		`${name}: product category selection preserves all other URLs`,
		commerce.links,
		expectedLinks,
	);
	check(
		`${name}: product category selection leaves category bases intact`,
		commerce.categories,
		before.categories,
	);

	// No native rewrite matches the shortened archive outside the blog front.
	if ( name === 'blog-front' ) {
		const ids = commerce.ids;

		for ( const [ key, value, products ] of [
			[ 'orderby', 'price', [ ids.product_two, ids.product_four ] ],
			[ 'orderby', 'price-desc', [ ids.product_three, ids.product_one ] ],
			[ 'order', 'asc' ],
			[ 'order', 'desc' ],
		] ) for ( const archive of [ before.links.product_cat, commerce.links.product_cat ] ) {
			const url      = `${archive}?${key}=${value}`;
			const response = await request( url );
			check(
				`${name}: product archive retains its sorting argument at ${url}`,
				[ response.status, response.product_category, response.productListing?.[ key ] ],
				[ 200, String( ids.product_cat ), value ],
			);

			if ( products )
				check(
					`${name}: products in price order at ${url}`,
					response.productListing?.ids,
					products,
				);
		}
	}

	if ( permalink ) {
		for ( const suffix of [ 'page/2', 'feed' ] ) {
			const target   = append(
				commerce.links.product_cat,
				suffix,
				slash,
			);
			const original = append( before.links.product_cat, suffix, slash );

			for ( const url of [ target, original ] ) {
				const response = await request( url );
				check(
					`${name}: product category ${suffix} query at ${url}`,
					{
						status:   response.status,
						category: response.product_category,
					},
					{
						status:   200,
						category: String( commerce.ids.product_cat ),
					},
				);
				check(
					`${name}: product category ${suffix} output at ${url}`,
					suffix === 'feed' ? /\bxml\b/.test( response.contentType ) : response.canonical,
					suffix === 'feed' ? true : target,
				);
			}
		}
	}

	await control( {
		action:  'config',
		permalink,
		woocommerce,
		enabled: false,
	} );
	await request( '/' );
	const restored = await control( { action: 'info' } );
	check(
		`${name}: disabling restores category links`,
		restored.categories,
		before.categories,
	);
	check(
		`${name}: disabling restores product category links`,
		restored.links,
		before.links,
	);
}

/**
 * Reads the rendered Categories rows with their batched SEO Bar assessments.
 *
 * @return {Promise<Map>} Row HTML indexed by term ID.
 */
async function categoryRows() {

	const rows = new Map();
	let pages  = 1;

	for ( let page = 1; page <= pages; ++page ) {
		const url  = new URL( '/wp-admin/edit-tags.php', origin );
		url.search = new URLSearchParams( {
			taxonomy: 'category',
			orderby:  'id',
			order:    'desc',
			paged:    page,
		} );
		const response = await fetch(
			url,
			{
				headers:  { 'X-TSF-Playground-Admin': '1' },
				redirect: 'manual',
				signal:   AbortSignal.timeout( timeout ),
			},
		);

		if ( ! response.ok )
			throw new Error(
				`Categories page ${page} returned HTTP ${response.status}.`,
			);

		const html = await response.text();

		if (
			   ! /<body\b[^>]*["\s]taxonomy-category["\s]/.test( html )
			|| ! html.includes( 'id="posts-filter"' )
		) {
			throw new Error(
				`Expected the Categories list table on page ${page}.`,
			);
		}

		pages = Number( html.match( /class="total-pages">([0-9]+)</ )?.[1] || 1 );

		for ( const match of html.matchAll(
			/<tr id="tag-([0-9]+)"[^>]*>([\s\S]*?)<\/tr>/g,
		) ) {
			rows.set( Number( match[1] ), match[2] );
		}
	}

	return rows;
}

/**
 * Compares the Categories screen against the post, product, or taxonomy that serves each URL.
 *
 * @param {Object} ids Seeded commerce and post IDs.
 * @return {Promise<void>}
 */
async function termConflicts( ids ) {

	const states    = [];
	const endpoints = {};

	for ( const enabled of [ false, true ] ) {
		await control( {
			action:      'config',
			permalink:   '/%category%/%postname%/',
			woocommerce: { product_base: '/shop/%product_cat%' },
			enabled,
		} );
		// The first front-end request stores the complete native rules used by the SEO Bar.
		await request( '/' );
		const terms = await control( { action: 'conflicts' } );
		states.push( terms );

		// Core can also interpret feed and page/2 as endpoints at the original category URL.
		if ( ! enabled )
			for ( const label of [ 'feed', 'paged' ] )
				endpoints[ label ] = await request( terms[ label ].url );
	}
	const [ before, after ] = states;
	const rows              = await categoryRows();

	for ( const [ label, field, id ] of [
		[ 'child', 'category', after.child.id ],
		[ 'absent_product', 'category', after.absent_product.id ],
		[ 'post', 'singular', ids.post_one ],
		[ 'product', 'product', ids.product_one ],
		[ 'feed', 'product_tag', ids.product_tag ],
		[ 'paged', 'product_tag', ids.product_tag ],
	] ) {
		const term     = after[ label ];
		const original = before[ label ].url;
		const row      = rows.get( term.id );

		if ( ! row )
			throw new Error(
				`Missing fixture term ${label} (ID ${term.id}) from the Categories list table.`,
			);

		const sections = row.match( /class="tsf-seo-bar-section-wrap\b/g ) || [];
		const response = await request( term.url );
		const native   = await request( original );
		const occupied = field !== 'category';
		const endpoint = endpoints[ label ];

		check(
			`${label}: shortened category URL`,
			term.url,
			original.replace( '/category/', '/' ),
		);
		check(
			`${label}: native query owner at the shortened URL`,
			[ response.status, response[ field ] ],
			[ 200, String( id ) ],
		);
		check(
			`${label}: original category URL and canonical`,
			[ native.status, native.category, native.canonical ],
			endpoint
				? [ endpoint.status, endpoint.category, endpoint.canonical.replace( '/category/', '/' ) ]
				: [ 200, String( term.id ), term.url ],
		);
		check(
			`${label}: rendered SEO Bar identifies the conflicting owner`,
			occupied
				? (
					   sections.length === 1
					&& row.includes( 'tsf-seo-bar-bad"' )
					&& row.includes( '>!!!<' )
					&& row.includes( `ID ${id}` )
				)
				: ( sections.length > 1 && ! row.includes( '>!!!<' ) ),
			true,
		);

		if ( ! endpoint ) {
			const vars        = new URLSearchParams( response.matchedQuery );
			const product     = [ 'absent_product', 'product' ].includes( label );
			const taxonomyVar = product ? 'product_cat' : 'category_name';
			check(
				`${label}: native rewrite matches both taxonomy and singular selectors`,
				Boolean(
					   vars.get( taxonomyVar )
					&& vars.get( product ? 'product' : 'name' ),
				),
				true,
			);

			if ( product )
				check(
					`${label}: shortened category exercises the shop product path`,
					new URL( term.url ).pathname.startsWith( '/shop/' ),
					true,
				);
		}
		check(
			`${label}: shortened URL canonical or native XML feed`,
			label === 'feed' ? /\bxml\b/.test( response.contentType ) : response.canonical,
			label === 'feed' ? true : term.url,
		);
	}
}

/**
 * Runs the matrix and restores only this runner's fixture state.
 *
 * @return {Promise<void>}
 */
async function main() {

	const report  = {
		site:   run.site,
		origin: origin.origin,
		date:   new Date().toISOString(),
		cases,
	};
	let installed = false;
	let cleaned   = false;

	try {
		fs.copyFileSync(
			path.join(
				__dirname,
				'..',
				'fixtures',
				'woocommerce.php',
			),
			fixture,
			fs.constants.COPYFILE_EXCL,
		);
		installed = true;
		await control( { action: 'setup' } );
		await request( '/' );
		const info = await control( { action: 'info' } );

		report.versions            = {
			wordpress:   info.wp_version,
			php:         info.php_version,
			woocommerce: info.wc_version,
			tsf:         info.tsf_version,
		};
		report.pluginSource        = run.plugin;
		report.taxonomyBaseFeature = info.taxonomy_base_feature;

		for ( const [ name, permalink ] of presets )
			await layout(
				name,
				permalink,
				{},
				info.taxonomy_base_feature,
			);

		for ( const [ name, productBase ] of [ [ 'shop-base', '/shop' ], [ 'shop-category', '/shop/%product_cat%' ], [ 'custom-category', '/catalog/%product_cat%' ] ] )
			await layout(
				`woocommerce-${name}`,
				'/%postname%/',
				{ product_base: productBase },
				info.taxonomy_base_feature,
			);

		if ( info.taxonomy_base_feature )
			await termConflicts( info.ids );
	} catch ( error ) {
		check(
			'runner completed',
			error.message,
			null,
		);
	} finally {
		if ( installed ) {
			try {
				cleaned = ( await control( { action: 'cleanup' } ) ).cleaned === true;
				check(
					'fixture objects and settings restored',
					cleaned,
					true,
				);
			} catch ( error ) {
				check(
					'fixture cleanup',
					error.message,
					null,
				);
			}

			if ( cleaned ) {
				fs.unlinkSync( fixture );
			} else {
				report.fixtureRecovery = {
					fixture,
					token,
				};
			}
		}

		if ( fs.existsSync( debugLog ) ) {
			const log = fs.readFileSync( debugLog )
				.subarray( debugStart )
				.toString( 'utf8' );
			const diagnostics = log.split( /\r?\n/ )
				.filter(
					line => /PHP (?:Warning|Notice|Deprecated|Fatal error|Parse error)/.test( line ),
				);
			check(
				'no new PHP diagnostics',
				diagnostics,
				[],
			);
		}
		fs.mkdirSync(
			path.dirname( reportPath ),
			{ recursive: true },
		);
		report.checks = cases.length;
		report.failed = cases.filter( item => ! item.passed ).length;
		fs.writeFileSync(
			reportPath,
			JSON.stringify( report, null, 2 ) + '\n',
		);
		console.log(
			`${report.checks - report.failed}/${report.checks} assertions passed. ${reportPath}`,
		);
		process.exitCode = report.failed ? 1 : 0;
	}
}

main()
	.catch( error => {

		console.error( error );
		process.exitCode = 1;
	} );
