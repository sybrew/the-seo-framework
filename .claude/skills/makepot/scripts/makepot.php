<?php
/**
 * Generates language/autodescription.pot via makepot-tsf.
 *
 * Delegates to makepot-tsf:
 *   - .local/pot/makepot-tsf (php run.php)
 *
 * That engine defines the extraction. This script introduces nothing.
 *
 * Usage: php makepot.php
 */

$root = dirname( __DIR__, 4 );

$engine_dir = getenv( 'MAKEPOT_TSF_DIR' );

if ( false === $engine_dir || '' === $engine_dir ) {
	$engine_dir = $root . DIRECTORY_SEPARATOR
		. '.local' . DIRECTORY_SEPARATOR
		. 'pot' . DIRECTORY_SEPARATOR
		. 'makepot-tsf';
}

$permission_file = $root . DIRECTORY_SEPARATOR
	. '.local' . DIRECTORY_SEPARATOR
	. 'pot' . DIRECTORY_SEPARATOR
	. 'permission.txt';

$run_php = $engine_dir . DIRECTORY_SEPARATOR . 'run.php';
$dest    = $root . DIRECTORY_SEPARATOR
	. 'language' . DIRECTORY_SEPARATOR
	. 'autodescription.pot';
$repo    = 'https://github.com/theseoframework/makepot-tsf';

if ( ! is_file( $permission_file ) ) {
	fwrite(
		STDERR,
		"Missing .local/pot/permission.txt.\n",
	);
	exit( 1 );
}

$text = file_get_contents( $permission_file );

if ( false === $text ) {
	fwrite(
		STDERR,
		"Could not read .local/pot/permission.txt.\n",
	);
	exit( 1 );
}

if ( ! preg_match(
	'/^\s*POT=(True|False)\s*$/m',
	$text,
	$match,
) ) {
	fwrite(
		STDERR,
		"permission.txt must contain POT=True|False on its own line.\n",
	);
	exit( 1 );
}

if ( 'True' !== $match[1] ) {
	echo "POT generation skipped (POT=False).\n";
	exit( 0 );
}

if ( ! is_file( $run_php ) ) {
	fwrite(
		STDERR,
		"makepot-tsf engine missing at $engine_dir. Clone $repo there and run composer install.\n",
	);
	exit( 1 );
}

$cmd = [
	PHP_BINARY,
	$run_php,
	$root,
	$dest,
	'--slug=autodescription',
];

$proc = proc_open(
	$cmd,
	[
		0 => STDIN,
		1 => STDOUT,
		2 => STDERR,
	],
	$pipes,
	$engine_dir,
);

if ( ! is_resource( $proc ) ) {
	fwrite(
		STDERR,
		"Could not start makepot-tsf.\n",
	);
	exit( 1 );
}

$code = proc_close( $proc );

echo $code ? "Finished with errors.\n" : "All done.\n";
exit( $code );
