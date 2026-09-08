<?php
/**
 * Reports multiline PHP calls/arrays missing a trailing comma.
 *
 * Usage (Windows, macOS, Linux):
 *   php find-missing.php path [path ...]
 * Recurses directories. PHP 7.4+. Does not rewrite files.
 */

$args  = array_slice( $argv, 1 );
$files = [];

if ( ! $args ) {
	fwrite( STDERR, "Usage: php find-missing.php path [path ...]\n" );
	exit( 1 );
}

$skip_dir = [
	'.git'         => true,
	'.local'       => true,
	'vendor'       => true,
	'node_modules' => true,
];

foreach ( $args as $path ) {
	if ( is_dir( $path ) ) {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS )
		);
		foreach ( $it as $fileinfo ) {
			$parts = explode( DIRECTORY_SEPARATOR, $fileinfo->getPathname() );
			$skip  = false;
			foreach ( $parts as $part ) {
				if ( isset( $skip_dir[ $part ] ) ) {
					$skip = true;
					break;
				}
			}
			if ( $skip || 'php' !== $fileinfo->getExtension() ) {
				continue;
			}
			$files[] = $fileinfo->getPathname();
		}
	} elseif ( is_readable( $path ) ) {
		$files[] = $path;
	} else {
		fwrite( STDERR, "Unreadable: $path\n" );
		exit( 1 );
	}
}

$ignore_paren_prev = [
	T_IF       => true,
	T_ELSEIF   => true,
	T_ELSE     => true,
	T_FOR      => true,
	T_FOREACH  => true,
	T_WHILE    => true,
	T_SWITCH   => true,
	T_CATCH    => true,
	T_DECLARE  => true,
	T_FUNCTION => true,
	T_FN       => true,
	T_USE      => true,
];

if ( \defined( 'T_MATCH' ) ) {
	$ignore_paren_prev[ T_MATCH ] = true;
}

$call_prev = [
	T_STRING       => true,
	T_VARIABLE     => true,
	T_ISSET        => true,
	T_UNSET        => true,
	T_EMPTY        => true,
	T_EVAL         => true,
	T_EXIT         => true,
	T_INCLUDE      => true,
	T_INCLUDE_ONCE => true,
	T_REQUIRE      => true,
	T_REQUIRE_ONCE => true,
	T_PRINT        => true,
	T_ECHO         => true,
	T_LIST         => true,
	T_ARRAY        => true,
	']'            => true,
	')'            => true,
];

$index_prev = [
	T_VARIABLE                 => true,
	T_STRING                   => true,
	T_CONSTANT_ENCAPSED_STRING => true,
	T_ENCAPSED_AND_WHITESPACE  => true,
	T_NUM_STRING               => true,
	T_LNUMBER                  => true,
	T_DNUMBER                  => true,
	']'                        => true,
	')'                        => true,
];

foreach ( [ 'T_NAME_FULLY_QUALIFIED', 'T_NAME_QUALIFIED', 'T_NAME_RELATIVE' ] as $tsf_tok ) {
	if ( \defined( $tsf_tok ) ) {
		$call_prev[ \constant( $tsf_tok ) ]  = true;
		$index_prev[ \constant( $tsf_tok ) ] = true;
	}
}

/**
 * @param array $tok Token row.
 * @return bool
 */
function tsf_is_trivia( $tok ) {
	return T_WHITESPACE === $tok['id']
		|| T_COMMENT === $tok['id']
		|| T_DOC_COMMENT === $tok['id'];
}

/**
 * @param array[] $tokens
 * @param int     $i
 * @return array{0:int,1:array}|null
 */
function tsf_prev_sig( $tokens, $i ) {

	for ( $j = $i - 1; $j >= 0; $j-- ) {
		if ( tsf_is_trivia( $tokens[ $j ] ) ) {
			continue;
		}

		return [ $j, $tokens[ $j ] ];
	}

	return null;
}

/**
 * @param string $code
 * @return array[]
 */
function tsf_tokenize( $code ) {

	$line = 1;
	$out  = [];

	foreach ( token_get_all( $code ) as $t ) {
		if ( \is_array( $t ) ) {
			$out[] = [
				'id'   => $t[0],
				'text' => $t[1],
				'line' => $t[2],
			];
			$line = $t[2] + substr_count( $t[1], "\n" );
		} else {
			$out[] = [
				'id'   => $t,
				'text' => $t,
				'line' => $line,
			];
			$line += substr_count( $t, "\n" );
		}
	}

	return $out;
}

/**
 * @param array[] $tokens
 * @param int     $open
 * @param string  $close_ch
 * @return array{multiline:bool,last:?array,close:?array}
 */
function tsf_scan_group( $tokens, $open, $close_ch ) {

	$n         = \count( $tokens );
	$depth     = 0;
	$saw_nl    = false;
	$last      = null;
	$close_tok = null;

	for ( $j = $open; $j < $n; $j++ ) {
		$tok  = $tokens[ $j ];
		$text = $tok['text'];
		$id   = $tok['id'];

		if ( '(' === $text || '[' === $text ) {
			$depth++;
		} elseif ( ')' === $text || ']' === $text ) {
			if ( 1 === $depth && $close_ch === $text ) {
				$close_tok = $tok;
				break;
			}
			$depth--;
		}

		if ( 1 === $depth && T_WHITESPACE === $id && false !== strpos( $text, "\n" ) ) {
			$saw_nl = true;
		}

		if ( 1 === $depth && $j !== $open && ! tsf_is_trivia( $tok ) ) {
			$last = $tok;
		}
	}

	return [
		'multiline' => $saw_nl,
		'last'      => $last,
		'close'     => $close_tok,
	];
}

$had_hit = false;

foreach ( $files as $file ) {
	$code    = file_get_contents( $file );
	$tokens  = tsf_tokenize( $code );
	$n       = \count( $tokens );
	$display = str_replace( '\\', '/', $file );

	for ( $i = 0; $i < $n; $i++ ) {
		$ch = $tokens[ $i ]['text'];

		if ( '(' !== $ch && '[' !== $ch ) {
			continue;
		}

		$prev = tsf_prev_sig( $tokens, $i );

		if ( '(' === $ch ) {
			if ( $prev && isset( $ignore_paren_prev[ $prev[1]['id'] ] ) ) {
				continue;
			}
			if ( ! $prev || ! isset( $call_prev[ $prev[1]['id'] ] ) ) {
				continue;
			}
			$close_ch = ')';
		} else {
			if ( $prev && isset( $index_prev[ $prev[1]['id'] ] ) ) {
				continue;
			}
			$close_ch = ']';
		}

		$scan = tsf_scan_group( $tokens, $i, $close_ch );

		if ( ! $scan['multiline'] || ! $scan['last'] || ! $scan['close'] ) {
			continue;
		}

		if ( ',' === $scan['last']['text'] || ';' === $scan['last']['text'] ) {
			continue;
		}

		$had_hit = true;
		printf(
			"%s:%d: missing trailing comma before %s\n",
			$display,
			$scan['last']['line'],
			$close_ch
		);
	}
}

exit( $had_hit ? 1 : 0 );
