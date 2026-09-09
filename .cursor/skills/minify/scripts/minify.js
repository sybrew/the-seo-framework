/**
 * Minifies all JS and CSS source files in lib/.
 *
 * Delegates to babel-tsf and cleancss-tsf:
 *   - .local/minify/babel-tsf    (port 8000, ?folder= endpoint)
 *   - .local/minify/cleancss-tsf (port 8002, ?folder= endpoint)
 *
 * Those servers define the minification rules. This script introduces nothing.
 *
 * Usage: node minify.js
 *        node minify.js --js    (JS only)
 *        node minify.js --css   (CSS only)
 */

const { spawn } = require( 'child_process' );
const fs   = require( 'fs' );
const http = require( 'http' );
const path = require( 'path' );

const ROOT = path.resolve( __dirname, '..', '..', '..', '..' );

const BABEL_DIR       = process.env.BABEL_TSF_DIR || path.resolve( ROOT, '.local', 'minify', 'babel-tsf' );
const CLEANCSS_DIR    = process.env.CLEANCSS_TSF_DIR || path.resolve( ROOT, '.local', 'minify', 'cleancss-tsf' );
const LIB_DIR         = path.resolve( ROOT, 'lib' );
const PERMISSION_FILE = path.resolve( ROOT, '.local', 'minify', 'permission.txt' );

const BABEL_PORT      = 8000;
const CLEANCSS_PORT   = 8002;
const LISTEN_RETRY_MS = 200;
const SPAWN_WAIT_MS   = 300;

const BABEL_REPO    = 'https://github.com/theseoframework/babel-tsf';
const CLEANCSS_REPO = 'https://github.com/theseoframework/cleancss-tsf';

/**
 * Reads JS/CSS flags from permission.txt.
 *
 * @return {{js: Boolean, css: Boolean}}
 */
function readPermission() {

	if ( ! fs.existsSync( PERMISSION_FILE ) )
		throw new Error( 'Missing .local/minify/permission.txt.' );

	const text = fs.readFileSync( PERMISSION_FILE, 'utf8' );
	const js   = /^\s*JS=(True|False)\s*$/m.exec( text );
	const css  = /^\s*CSS=(True|False)\s*$/m.exec( text );

	if ( ! js || ! css )
		throw new Error(
			'permission.txt must contain JS=True|False and CSS=True|False on their own lines.',
		);

	return {
		js:  'True' === js[1],
		css: 'True' === css[1],
	};
}

/**
 * Throws if the minify engine is not installed at dir.
 *
 * @param {string} label Display name.
 * @param {string} dir   Engine directory.
 * @param {string} repo  Clone URL.
 */
function assertEngine( label, dir, repo ) {

	if ( fs.existsSync( path.join( dir, 'run.js' ) ) ) return;

	throw new Error(
		`${label} engine missing at ${dir}. Clone ${repo} there and run npm install.`,
	);
}

/**
 * Whether the engine reported a failed minify.
 *
 * @param {string} result Engine response body.
 * @return {Boolean}
 */
function folderHasFailures( result ) {

	if ( result.includes( '---ERROR' ) )
		return true;

	const match = /^Failed:\s*(\d+)\s*$/m.exec( result );

	if ( ! match )
		return true;

	return '0' !== match[1];
}

/**
 * Checks whether a server is already listening on the given port.
 *
 * @param {number} port Port to probe.
 * @return {Promise<Boolean>}
 */
function isPortListening( port ) {
	return new Promise( resolve => {

		const req = http.get( `http://localhost:${port}/`, () => {
			req.destroy();
			resolve( true );
		} );

		req.on( 'error', () => resolve( false ) );
		req.end();
	} );
}

/**
 * Starts a server in the given directory and waits until it's listening.
 *
 * @param {string} cwd  Directory containing the server's run.js.
 * @param {number} port Port the server listens on.
 * @return {Promise<import('child_process').ChildProcess>}
 */
function startServer( cwd, port ) {
	return new Promise( ( resolve, reject ) => {

		const child = spawn( 'node', [ 'run.js' ], {
			cwd,
			stdio:      [ 'ignore', 'pipe', 'pipe' ],
			windowsHide: true,
		} );

		let settled = false;

		function tryConnect() {

			const req = http.get( `http://localhost:${port}/`, () => {
				if ( ! settled ) {
					settled = true;
					resolve( child );
				}
				req.destroy();
			} );

			req.on( 'error', () => {
				if ( ! settled )
					setTimeout( tryConnect, LISTEN_RETRY_MS );
			} );
			req.end();
		}

		child.on( 'error', err => {
			if ( ! settled ) {
				settled = true;
				reject( err );
			}
		} );

		child.on( 'exit', code => {
			if ( ! settled ) {
				settled = true;
				reject( new Error( `Server in ${cwd} exited prematurely (code ${code})` ) );
			}
		} );

		setTimeout( tryConnect, SPAWN_WAIT_MS );
	} );
}

/**
 * Ensures a server is running on the given port.
 * Starts one if needed; returns null when the server was already running.
 *
 * @param {string} label Display name for console output.
 * @param {string} cwd   Directory containing the server's run.js.
 * @param {number} port  Port the server listens on.
 * @return {Promise<import('child_process').ChildProcess|null>}
 */
async function ensureServer( label, cwd, port ) {

	if ( await isPortListening( port ) ) {
		console.log( `${label} server already running on port ${port}.` );

		return null;
	}

	console.log( `Starting ${label} server...` );

	return startServer( cwd, port );
}

/**
 * Fetches a URL and returns the full response body as a string.
 *
 * @param {string} reqUrl URL to fetch.
 * @return {Promise<string>}
 */
function getResponse( reqUrl ) {
	return new Promise( ( resolve, reject ) => {

		http.get( reqUrl, res => {
			let body = '';

			res.on( 'data', chunk => { body += chunk; } );
			res.on( 'end', () => resolve( body ) );
		} )
			.on( 'error', reject );
	} );
}

/**
 * Kills a child process.
 *
 * @param {import('child_process').ChildProcess} child
 */
function killServer( child ) {
	try {
		child.kill();
	} catch {}
}

/**
 * Runs JS and CSS minification for lib/.
 */
async function main() {

	const args = process.argv.slice( 2 );
	let doJS  = ! args.length || args.includes( '--js' );
	let doCSS = ! args.length || args.includes( '--css' );

	const spawned = [];
	let failed    = false;

	try {
		const perm = readPermission();

		if ( doJS && ! perm.js ) {
			console.log( 'JS minify skipped (JS=False).' );
			doJS = false;
		}

		if ( doCSS && ! perm.css ) {
			console.log( 'CSS minify skipped (CSS=False).' );
			doCSS = false;
		}

		if ( ! doJS && ! doCSS ) {
			console.log( 'Nothing to minify.' );

			return;
		}

		if ( doJS ) {
			assertEngine( 'babel-tsf', BABEL_DIR, BABEL_REPO );

			const jsFolder = path.join( LIB_DIR, 'js' );
			const child    = await ensureServer( 'babel-tsf', BABEL_DIR, BABEL_PORT );

			if ( child )
				spawned.push( child );

			console.log( `Minifying JS via localhost:${BABEL_PORT}/?folder=${jsFolder}\n` );
			const result = await getResponse( `http://localhost:${BABEL_PORT}/?folder=${encodeURIComponent( jsFolder )}` );
			console.log( result );

			if ( folderHasFailures( result ) )
				failed = true;
		}

		if ( doCSS ) {
			assertEngine( 'cleancss-tsf', CLEANCSS_DIR, CLEANCSS_REPO );

			const cssFolder = path.join( LIB_DIR, 'css' );
			const child     = await ensureServer( 'cleancss-tsf', CLEANCSS_DIR, CLEANCSS_PORT );

			if ( child )
				spawned.push( child );

			console.log( `Minifying CSS via localhost:${CLEANCSS_PORT}/?folder=${cssFolder}\n` );
			const result = await getResponse( `http://localhost:${CLEANCSS_PORT}/?folder=${encodeURIComponent( cssFolder )}` );
			console.log( result );

			if ( folderHasFailures( result ) )
				failed = true;
		}
	} catch ( err ) {
		console.error( `\nError: ${err.message}\n` );
		failed = true;
	} finally {
		spawned.forEach( killServer );
	}

	console.log( failed ? 'Finished with errors.' : 'All done.' );
	process.exit( failed ? 1 : 0 );
}

main();
