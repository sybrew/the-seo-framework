<?php

add_action( 'wp_head', 'the_seo_framework_php_benchmark', -1 );
//* Benchmark PHP.
function the_seo_framework_php_benchmark() {

	//* Boolean.
	$b = true;
	$ba = false;

	//* String.
	$s = '';

	//* Compare
	$c1 = 'thing1';
	$c4 = 'thing4';
	$c40 = 'thing40';

	//* Array 5
	$a5 = array( 'thing1', 'thing2', 'thing3', 'thing4', 'thing5' );

	//* Array 50
	$a50 = array( 'thing1','thing2','thing3','thing4','thing5','thing6','thing7','thing8','thing9','thing10','thing11','thing12','thing13','thing14','thing15','thing16','thing17','thing18','thing19','thing20','thing21','thing22','thing23','thing24','thing25','thing26','thing27','thing28','thing29','thing30','thing31','thing32','thing33','thing34','thing35','thing36','thing37','thing38','thing39','thing40','thing41','thing42','thing43','thing44','thing45','thing46','thing47','thing48','thing49','thing50' );

	//* Iterations
	$it = 10000000;

	$int1 = 5;
	$int2 = 6;
	$int3 = PHP_INT_MAX;

	//* Start the engines.
	$i = 0;
	$t = microtime(true);
	while ( $i < 10 ) {
		if ( $b ) {
			$a = $b;
		}
		if ( empty( $b ) ) {
			$a = $b;
		}
		if ( ! $b ) {
			$a = $b;
		}
		if ( isset( $b ) ) {
			$a = $b;
		}
		if ( the_seo_framework_is_empty_string( $b ) ) {
			$a = $b;
		}
		if ( in_array( $c1, $a5 ) ) {
			$a = $b;
		}
		if ( the_seo_framework_in_array( $c1, $a5 ) ) {
			$a = $b;
		}
		$i++;
	}
	$starttime = microtime(true) - $t;

	//* Loose
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( $b ) {
			// valuated
		}
		++$i;
	}
	$loosetime = microtime(true) - $t;

	//* Strict
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( true === $b ) {
			// valuated
		}
		++$i;
	}
	$stricttime = microtime(true) - $t;

	//* Strict Neg
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( true !== $b ) {
			// valuated
		}
		++$i;
	}
	$strictnegtime = microtime(true) - $t;

	//* Empty
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( empty( $b ) ) {
			// valuated
		}
		++$i;
	}
	$emptytime = microtime(true) - $t;

	//* Neg Empty
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( ! empty( $b ) ) {
			// valuated
		}
		++$i;
	}
	$negemptytime = microtime(true) - $t;

	//* False Empty
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( false === empty( $b ) ) {
			// valuated
		}
		++$i;
	}
	$strictemptytime = microtime(true) - $t;

	//* Isset
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( isset( $b ) ) {
			// valuated
		}
		++$i;
	}
	$issettime = microtime(true) - $t;

	//* Isset Strict
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( true === isset( $b ) ) {
			// valuated
		}
		++$i;
	}
	$issetstricttime = microtime(true) - $t;

	//* Loose Empty string
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( $s ) {
			// valuated
		}
		++$i;
	}
	$looseemptystring = microtime(true) - $t;

	//* Loose Neg Empty string
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( ! $s ) {
			// valuated
		}
		++$i;
	}
	$loosenegemptystring = microtime(true) - $t;

	//* Empty string
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( empty( $s ) ) {
			// valuated
		}
		++$i;
	}
	$emptystring = microtime(true) - $t;

	//* Empty string strict
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( '' === $s ) {
			// valuated
		}
		++$i;
	}
	$emptystrictstring = microtime(true) - $t;

	//* Empty string strict
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( the_seo_framework_is_empty_string( $s ) ) {
			// valuated
		}
		++$i;
	}
	$emptystrictfunctionstring = microtime(true) - $t;

	//* In array begin 5
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( in_array( $c1, $a5 ) ) {
			// valuated
		}
		++$i;
	}
	$inarraybegin5 = microtime(true) - $t;

	//* In array begin 5 function
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( the_seo_framework_in_array( $c1, $a5 ) ) {
			// valuated
		}
		++$i;
	}
	$inarraybegin5function = microtime(true) - $t;


	//* In array end 5
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( in_array( $c4, $a5 ) ) {
			// valuated
		}
		++$i;
	}
	$inarrayend5 = microtime(true) - $t;

	//* In array end 5 function
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( the_seo_framework_in_array( $c4, $a5 ) ) {
			// valuated
		}
		++$i;
	}
	$inarrayend5function = microtime(true) - $t;

	//* In array begin 50
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( in_array( $c1, $a50 ) ) {
			// valuated
		}
		++$i;
	}
	$inarraybegin50 = microtime(true) - $t;

	//* In array begin 50 function
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( the_seo_framework_in_array( $c1, $a50 ) ) {
			// valuated
		}
		++$i;
	}
	$inarraybegin50function = microtime(true) - $t;


	//* In array end 50
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( in_array( $c40, $a50 ) ) {
			// valuated
		}
		++$i;
	}
	$inarrayend50 = microtime(true) - $t;

	//* In array end 50 function
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( the_seo_framework_in_array( $c40, $a5 ) ) {
			// valuated
		}
		++$i;
	}
	$inarrayend50function = microtime(true) - $t;

	//* Strict false
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( false === $ba ) {
			// valuated
		}
		++$i;
	}
	$strictfalsetime = microtime(true) - $t;

	//* Loose flip false
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( ! $ba ) {
			// valuated
		}
		++$i;
	}
	$falsefliptime = microtime(true) - $t;

	//* Loose flip false
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( ! $ba ) {
			// valuated
		}
		++$i;
	}
	$falsefliptime = microtime(true) - $t;

	//* Bigger than
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( $int2 > $int1 ) {
			// valuated
		}
		++$i;
	}
	$biggerthan = microtime(true) - $t;

	//* Bigger than or equal to
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( $int2 >= $int1 ) {
			// valuated
		}
		++$i;
	}
	$biggerthanor = microtime(true) - $t;

	//* Bigger than or equal to max int
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( $int2 >= $int3 ) {
			// valuated
		}
		++$i;
	}
	$biggerthanormax = microtime(true) - $t;

	//* PHP 7 FCGI results @ 10,000,000 iterations.
	echo 'Loose time: ' . $loosetime . " seconds\r\n"; 					// 0.1115360260009765625000 seconds
	echo 'Strict time: ' . $stricttime . " seconds\r\n";				// 0.1202042102813720703125 seconds
	echo 'Strict Neg time: ' . $strictnegtime . " seconds\r\n";			// 0.1270349025726318359375 seconds
	echo 'Empty time: ' . $emptytime . " seconds\r\n";					// 0.1297409534454345703125 seconds
	echo 'Neg Empty time: ' . $negemptytime . " seconds\r\n";			// 0.2008590698242187500000 seconds <- Triple check
	echo 'Strict Neg Empty time: ' . $strictemptytime . " seconds\r\n"; // 0.1864080429077148437500 seconds <- Double check
	echo 'Isset time: ' . $issettime . " seconds\r\n"; 					// 0.1153779029846191406250 seconds
	echo 'Strict Isset time: ' . $issetstricttime . " seconds\r\n"; 	// 0.1703500747680664062500 seconds <- Double check

	echo 'Strict False time: ' . $strictfalsetime . " seconds\r\n"; 	// 0.1211879253387451171875  seconds
	echo 'Loose Flip time: ' . $falsefliptime . " seconds\r\n";			// 0.1306369304656982421875 seconds

	echo "\r\n";

	echo 'Loose Empty String time: ' . $looseemptystring . " seconds\r\n";						// 0.1340930461883544921875 seconds
	echo 'Loose Neg Empty String time: ' . $loosenegemptystring . " seconds\r\n";				// 0.1588211059570312500000 seconds
	echo 'Empty String time: ' . $emptystring . " seconds\r\n"; 								// 0.1351380348205566406250 seconds
	echo 'Strict Empty String time: ' . $emptystrictstring . " seconds\r\n"; 					// 0.1573431491851806640625 seconds
	echo 'Strict Empty Function String time: ' . $emptystrictfunctionstring . " seconds\r\n"; 	// 0.3850169181823730468750 seconds <- Triple check.

	echo "\r\n";

	echo 'In array begin 5: ' . $inarraybegin5 . " seconds\r\n"; 						// 0.3640620708465576171875 seconds
	echo 'In array begin function 5: ' . $inarraybegin5function . " seconds\r\n"; 		// 2.0675928592681884765625 seconds <- VERY bad (1. function call, 2. array sorting. 3. Triple check)
	echo 'In array end 5: ' . $inarrayend5 . " seconds\r\n"; 							// 0.5424749851226806640625 seconds
	echo 'In array end function 5: ' . $inarrayend5function . " seconds\r\n"; 			// 2.0651528835296630859375 seconds <- VERY bad

	echo "\r\n";

	echo 'In array begin 50: ' . $inarraybegin50 . " seconds\r\n"; 						// 0.3695099353790283203125 seconds
	echo 'In array begin function 50: ' . $inarraybegin50function . " seconds\r\n"; 	// 8.4975330829620361328125 seconds <- VERY bad.
	echo 'In array end 50: ' . $inarrayend50 . " seconds\r\n"; 							// 2.3926529884338378906250 seconds
	echo 'In array end function 50: ' . $inarrayend50function . " seconds\r\n"; 		// 2.0044331550598144531250 seconds <- miniscule benefit.

	echo 'Bigger than: ' . $biggerthan . " seconds\r\n"; 								// 0.1194138526916503906250 seconds
	echo 'Bigger than or equal to: ' . $biggerthanor . " seconds\r\n"; 					// 0.1157648563385009765625 seconds
	echo 'Bigger than or equal to max int: ' . $biggerthanormax . " seconds\r\n"; 		// 0.1121711730957031250000 seconds

}

function the_seo_framework_is_empty_string( $string ) {
	if ( '' === $string ) return true;
	return false;
}

function the_seo_framework_in_array( $needle, $array ) {

	$array = array_flip( $array );

	if ( is_string( $needle ) ) {
		if ( isset( $array[$needle] ) )
			return true;
	} elseif ( is_array( $needle ) ) {
		foreach ( $needle as $str ) {
			if ( isset( $array[$str] ) )
				return true;
		}
	}

	return false;
}
