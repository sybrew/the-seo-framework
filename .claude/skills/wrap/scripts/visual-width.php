<?php
/**
 * Prints visual column widths (tab = 4).
 *
 * Usage (Windows, macOS, Linux):
 *   php visual-width.php [--line N] [--gt N] [--compact-if] [file ...]
 *   stdin if no files: php visual-width.php
 */

$tab        = 4;
$only_line  = 0;
$min_width  = 0;
$compact_if = false;
$files      = [];

$args = array_slice( $argv, 1 );

for ( $i = 0, $n = \count( $args ); $i < $n; $i++ ) {
	$arg = $args[ $i ];

	if ( '--line' === $arg ) {
		$only_line = (int) ( $args[ ++$i ] ?? 0 );
		continue;
	}

	if ( '--gt' === $arg ) {
		$min_width = (int) ( $args[ ++$i ] ?? 0 );
		continue;
	}

	if ( '--compact-if' === $arg ) {
		$compact_if = true;
		continue;
	}

	if ( $arg && '-' === $arg[0] && '-' !== $arg ) {
		fwrite( STDERR, "Unknown option: $arg\n" );
		exit( 1 );
	}

	$files[] = $arg;
}

if ( ! $files )
	$files[] = '-';

/**
 * @param string $line Raw line.
 * @param int    $tab  Tab width.
 * @return int Visual columns.
 */
function visual_width( $line, $tab ) {

	$line     = rtrim( $line, "\n\r" );
	$expanded = str_replace( "\t", str_repeat( ' ', $tab ), $line );

	return \function_exists( 'mb_strlen' )
		? mb_strlen( $expanded, 'UTF-8' )
		: \strlen( $expanded );
}

/**
 * @param string $line Raw line.
 * @return bool
 */
function is_compact_if_terminator( $line ) {
	return (bool) preg_match(
		'/^\s*(?:else\s+if|elseif|if)\s*\(.*\)\s*(?:return|continue|break)\s*;/',
		rtrim( $line, "\n\r" ),
	);
}

$had_error = false;

foreach ( $files as $file ) {
	if ( '-' === $file ) {
		$lines = file( 'php://stdin' );
		$label = '-';
	} elseif ( is_file( $file ) && is_readable( $file ) ) {
		$lines = file( $file );
		$label = $file;
	} else {
		fwrite( STDERR, "Unreadable: $file\n" );
		$had_error = true;
		continue;
	}

	if ( false === $lines ) {
		fwrite( STDERR, "Unreadable: $file\n" );
		$had_error = true;
		continue;
	}

	foreach ( $lines as $i => $line ) {
		$num = $i + 1;

		if ( $only_line && $num !== $only_line ) continue;

		if ( $compact_if && ! is_compact_if_terminator( $line ) ) continue;

		$width = visual_width( $line, $tab );

		if ( $min_width && $width <= $min_width ) continue;

		printf( "%s:%d:%d\n", $label, $num, $width );
	}
}

exit( $had_error ? 1 : 0 );
