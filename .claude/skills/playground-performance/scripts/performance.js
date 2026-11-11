/**
 * Consumer dispatcher for the shared wp-plugin-performance engine.
 * Entry point for the TSF skill under .claude/skills/playground-performance/.
 */

const fs   = require( 'fs' );
const path = require( 'path' );
const { spawnSync } = require( 'child_process' );

const ROOT = path.resolve( __dirname, '../../../..' );

/**
 * Rejects private Cursor paths before checking files or resolving symlinks.
 *
 * @param {string} file
 * @return {string}
 */
function allowedPath( file ) {

	const absolute = path.resolve( file );
	const forbidden = /(?:^|[\\/])\.local[\\/]\.cursor(?:[\\/]|$)/i;

	if ( forbidden.test( absolute ) )
		throw new Error( 'The private Cursor directory is outside this workflow.' );

	let existing = absolute;
	while ( ! fs.existsSync( existing ) && path.dirname( existing ) !== existing )
		existing = path.dirname( existing );

	if ( forbidden.test( fs.realpathSync( existing ) ) )
		throw new Error( 'The engine path resolves into the private Cursor directory.' );

	return absolute;
}

/**
 * Reads explicit consumer permission without creating or inferring a decision.
 *
 * @return {Boolean}
 */
function readPermission() {

	const file = allowedPath( path.join( ROOT, '.local/playground/permission.txt' ) );

	if ( ! fs.existsSync( file ) )
		throw new Error( 'Missing .local/playground/permission.txt. Ask the user for PLAYGROUND=True|False before installing or launching.' );

	const text = fs.readFileSync( file, 'utf8' ).trim();

	if ( ! [ 'PLAYGROUND=True', 'PLAYGROUND=False' ].includes( text ) )
		throw new Error( 'permission.txt must contain exactly PLAYGROUND=True or PLAYGROUND=False.' );

	return 'PLAYGROUND=True' === text;
}

/**
 * Finds a shared engine through a private setting, explicit environment or default.
 *
 * @return {string}
 */
function engineDirectory() {

	const settingsFile = allowedPath( path.join( ROOT, '.local/playground/performance/engine.json' ) );
	const settings = fs.existsSync( settingsFile ) ? JSON.parse( fs.readFileSync( settingsFile, 'utf8' ) ) : {};
	const directory = allowedPath( path.resolve(
		ROOT,
		process.env.WP_PLUGIN_PERFORMANCE_DIR || settings.directory || '.local/playground/wp-plugin-performance',
	) );

	if ( ! fs.existsSync( path.join( directory, 'run.js' ) ) )
		throw new Error( `wp-plugin-performance is missing at ${directory}. Follow the skill's permission and installation steps.` );

	return directory;
}

/**
 * Runs one engine command against this consumer, propagating all failures.
 *
 * @param {string[]} argv
 */
function main( argv = process.argv.slice( 2 ) ) {

	if ( ! argv.length || argv.includes( '--help' ) ) {
		console.log( 'Usage: node performance.js <profile|hotspots|prune|run|prepare|resume|stop|report|export|doctor|test> [flags]\nShared source: https://github.com/theseoframework/wp-plugin-performance' );

		return;
	}
	const offline = [ 'hotspots', 'prune', 'report', 'export', 'doctor', 'test' ].includes( argv[0] );

	if ( ! offline && ! readPermission() ) {
		console.log( 'Performance tests skipped (PLAYGROUND=False).' );

		return;
	}
	const args = [ ...argv ];
	const command = args[0].startsWith( '--' ) ? 'run' : args[0];
	const hasConfig = args.some( item => '--config' === item || item.startsWith( '--config=' ) );
	const hasSession = args.some( item => '--session' === item || item.startsWith( '--session=' ) );

	if ( [ 'profile', 'run', 'prepare' ].includes( command ) && ! hasConfig && ! hasSession ) {
		const plan = 'profile' === command ? '../profile-plan.json' : '../plan.json';
		args.push(
			'--config',
			path.resolve( __dirname, plan ),
		);
	}

	args.push( '--root', ROOT );
	const directory = engineDirectory();
	const child = spawnSync(
		process.execPath,
		[ path.join( directory, 'run.js' ), ...args ],
		{ cwd: directory, stdio: 'inherit', windowsHide: true },
	);

	if ( child.error ) throw child.error;

	if ( child.signal ) throw new Error( `Performance engine terminated by ${child.signal}.` );

	process.exitCode = child.status ?? 1;
}

if ( require.main === module ) {
	try {
		main();
	} catch ( error ) {
		console.error( error.message );
		process.exitCode = 1;
	}
}
