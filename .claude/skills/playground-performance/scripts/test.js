/**
 * Tests this consumer integration with a synthetic engine, never WordPress.
 * Optionally pass an installed performance engine to validate both plan schemas.
 */

const assert = require( 'assert/strict' );
const fs     = require( 'fs' );
const os     = require( 'os' );
const path   = require( 'path' );
const { spawnSync } = require( 'child_process' );

/**
 * Checks consent, command routing, config precedence and isolated consumer roots.
 *
 * @param {string|undefined} engine Installed engine directory; no acquisition.
 * @return {Object} Synthetic evidence location and plan and linked path validation states.
 */
function main( engine ) {

	const skill = path.resolve( __dirname, '..' );
	const temporary = fs.realpathSync( fs.mkdtempSync(
		path.join( os.tmpdir(), 'tsf-hotspot-integration-' ),
	) );
	const consumer = path.join( temporary, 'consumer' );
	const relative = '.claude/skills/playground-performance';
	const installed = path.join( consumer, relative );
	const script = path.join( installed, 'scripts/performance.js' );
	const permission = path.join( consumer, '.local/playground/permission.txt' );
	const settings = path.join( consumer, '.local/playground/performance/engine.json' );
	const fake = path.join( temporary, 'fake-engine' );
	const record = path.join( fake, 'called.json' );
	for ( const directory of [ path.dirname( script ), path.dirname( settings ), fake ] )
		fs.mkdirSync( directory, { recursive: true } );

	fs.copyFileSync( path.join( __dirname, 'performance.js' ), script );
	for ( const file of [ 'profile-plan.json', 'plan.json' ] )
		fs.copyFileSync( path.join( skill, file ), path.join( installed, file ) );

	fs.writeFileSync( settings, JSON.stringify( { directory: fake } ) );
	// A failing fake engine makes swallowed child exit codes detectable.
	fs.writeFileSync(
		path.join( fake, 'run.js' ),
		"require('fs').writeFileSync(require('path').join(__dirname,'called.json'),JSON.stringify(process.argv.slice(2)));process.exitCode=23;",
	);

	/**
	 * Runs the copied dispatcher with synthetic settings and no real engine.
	 *
	 * @param {string[]} argv Command and flags.
	 * @param {string} entry Optional linked dispatcher path.
	 * @return {Object} Completed child result.
	 */
	function invoke( argv, entry = script ) {

		const result = spawnSync(
			process.execPath,
			[ entry, ...argv ],
			{
				cwd: temporary, encoding: 'utf8', windowsHide: true,
				env: { ...process.env, WP_PLUGIN_PERFORMANCE_DIR: '', WP_PLUGIN_PERFORMANCE_ROOT: '', WP_PLUGIN_REGRESSION_DIR: '' },
			},
		);
		if ( result.error ) throw result.error;

		return result;
	}

	/**
	 * Verifies child failure propagation and the real consumer root argument.
	 *
	 * @param {string[]} argv Command and flags.
	 * @param {string} entry Optional linked dispatcher path.
	 * @return {string[]} Arguments received by the fake engine.
	 */
	function forwarded( argv, entry = script ) {

		assert.equal( invoke( argv, entry ).status, 23 );
		const recorded = JSON.parse( fs.readFileSync( record, 'utf8' ) );
		assert.deepEqual( recorded.slice( -2 ), [ '--root', consumer ] );

		return recorded;
	}

	assert.equal( invoke( [ '--help' ] ).status, 0 );
	assert.equal( fs.existsSync( permission ), false );
	for ( const decision of [ null, 'PLAYGROUND=False', 'PLAYGROUND=invalid' ] ) {
		if ( null !== decision ) fs.writeFileSync( permission, decision + '\n' );

		for ( const command of [ 'profile', 'run', 'prepare', 'resume', 'stop' ] ) {
			if ( fs.existsSync( record ) ) fs.unlinkSync( record );

			const result = invoke( [ command ] );
			assert.equal( result.status, 'PLAYGROUND=False' === decision ? 0 : 1 );
			assert.equal( fs.existsSync( record ), false );
		}
		for ( const command of [ 'hotspots', 'prune', 'report', 'export', 'doctor', 'test' ] ) {
			const recorded = forwarded( [ command ] );
			assert.equal( recorded[0], command );
			assert.equal( recorded.includes( '--config' ), false );
		}
		if ( null === decision ) assert.equal( fs.existsSync( permission ), false );
	}
	fs.writeFileSync( permission, 'PLAYGROUND=True\n' );
	for ( const command of [ 'profile', 'run', 'prepare' ] ) {
		const recorded = forwarded( [ command ] );
		const file = 'profile' === command ? 'profile-plan.json' : 'plan.json';
		assert.equal( recorded[recorded.indexOf( '--config' ) + 1], path.join( installed, file ) );
	}
	for ( const config of [ [ '--config', 'custom.json' ], [ '--config=custom.json' ] ] ) {
		const recorded = forwarded( [ 'profile', ...config ] );
		assert.deepEqual( recorded, [ 'profile', ...config, '--root', consumer ] );
	}
	for ( const command of [ 'resume', 'run' ] ) {
		for ( const session of [ [ '--session', 'saved.json' ], [ '--session=saved.json' ] ] ) {
			const recorded = forwarded( [ command, ...session ] );
			assert.equal( recorded.includes( '--config' ), false );
		}
	}
	const alias = path.join( temporary, 'linked-consumer' );
	let linkedConsumerSkip;
	try {
		fs.symlinkSync( consumer, alias, process.platform === 'win32' ? 'junction' : 'dir' );
	} catch ( error ) {
		if ( ! [ 'EPERM', 'EACCES', 'ENOTSUP' ].includes( error.code ) ) throw error;

		linkedConsumerSkip = error.code;
	}
	if ( ! linkedConsumerSkip ) {
		const linked = forwarded(
			[ 'profile' ],
			path.join( alias, relative, 'scripts/performance.js' ),
		);
		assert.equal( linked[linked.indexOf( '--config' ) + 1], path.join( installed, 'profile-plan.json' ) );
	}
	assert.equal( fs.readFileSync( permission, 'utf8' ), 'PLAYGROUND=True\n' );

	if ( engine ) {
		const directory = path.resolve( engine );
		const profile = require( path.join( directory, 'lib/profile' ) ).configuration(
			JSON.parse( fs.readFileSync( path.join( skill, 'profile-plan.json' ), 'utf8' ) ),
		);
		assert.equal( profile.plugin.slug, 'autodescription' );
		const timing = require( path.join( directory, 'lib/benchmark' ) ).configuration(
			path.join( skill, 'plan.json' ),
			{},
		);
		assert( timing.candidates.every( item => item.slug === profile.plugin.slug ) );
	}
	const result = {
		passed: true,
		temporary,
		plansValidated: !! engine,
		linkedConsumerChecked: ! linkedConsumerSkip,
		linkedConsumerSkip,
		wordpressLaunched: false,
	};
	console.log( JSON.stringify( result ) );

	return result;
}

if ( require.main === module ) main( process.argv[2] );
module.exports = { main };
