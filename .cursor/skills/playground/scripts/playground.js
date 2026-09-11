/**
 * Runs wp-plugin-regression against this repo.
 *
 * Delegates to wp-plugin-regression:
 *   - .local/playground/wp-plugin-regression (node run.js)
 *
 * That engine boots Playground, captures, and compares. This script introduces nothing.
 *
 * Usage: node playground.js <command> [flags]
 */

const { spawnSync } = require( 'child_process' );
const fs   = require( 'fs' );
const path = require( 'path' );

const ROOT = path.resolve( __dirname, '..', '..', '..', '..' );

const ENGINE_DIR =
	   process.env.WP_PLUGIN_REGRESSION_DIR
	|| path.resolve(
		ROOT,
		'.local',
		'playground',
		'wp-plugin-regression',
	);

const PERMISSION_FILE = path.resolve( ROOT, '.local', 'playground', 'permission.txt' );
const PLUGIN_JSON     = path.resolve( __dirname, '..', 'plugin.json' );
const REPO            = 'https://github.com/theseoframework/wp-plugin-regression';

/**
 * Reads the Playground permission flag.
 *
 * @return {Boolean}
 */
function readPermission() {

	if ( ! fs.existsSync( PERMISSION_FILE ) )
		throw new Error( 'Missing .local/playground/permission.txt.' );

	const text  = fs.readFileSync( PERMISSION_FILE, 'utf8' );
	const match = /^\s*PLAYGROUND=(True|False)\s*$/m.exec( text );

	if ( ! match )
		throw new Error(
			'permission.txt must contain PLAYGROUND=True|False on its own line.',
		);

	return 'True' === match[1];
}

/**
 * @param {string[]} argv
 * @param {string}   name
 * @return {Boolean}
 */
function hasFlag( argv, name ) {
	return argv.some(
		a => a === `--${name}` || a.startsWith( `--${name}=` ),
	);
}

/**
 * Runs a wp-plugin-regression command.
 */
function main() {

	if ( ! readPermission() ) {
		console.log( 'Playground skipped (PLAYGROUND=False).' );

		return;
	}

	const runJs = path.join( ENGINE_DIR, 'run.js' );

	if ( ! fs.existsSync( runJs ) )
		throw new Error(
			`wp-plugin-regression engine missing at ${ENGINE_DIR}. Clone ${REPO} there and run npm install.`,
		);

	const extra = process.argv.slice( 2 );

	if ( ! extra.length )
		throw new Error( 'Usage: node playground.js <launch|stop|capture|compare|harness> [flags]' );

	if ( ! hasFlag( extra, 'root' ) )
		extra.push( '--root', ROOT );

	if ( ! hasFlag( extra, 'plugin-json' ) )
		extra.push( '--plugin-json', PLUGIN_JSON );

	const result = spawnSync(
		process.execPath,
		[ runJs, ...extra ],
		{
			cwd:   ENGINE_DIR,
			stdio: 'inherit',
		},
	);

	if ( null !== result.status && result.status )
		process.exit( result.status );
}

try {
	main();
} catch ( err ) {
	console.error( `\nError: ${err.message}\n` );
	process.exit( 1 );
}
