<?php
	include ( '../SearchableFile.phpclass' ) ;
	$file 		=  'verybigfile.rtf' ;
	$t1 		=  microtime ( true ) ;

	$sf 		=  new SearchableFile ( ) ;
	$sf -> Open ( $file ) ;
	$pos 		=  0 ;
	$search		=  [ '\\pict', '\\sv', '\\LTRPAR' ] ;
	$pos1 		=  [] ;


	while  ( ( $pos  = $sf -> multistripos ( $search, $pos, $found_index, $found_string ) )  !==  false )
	{
		// echo "POS1 = $pos, STR = $found_string\n" ;
		$pos1 []	=  $pos ;

		$pos 	+=  strlen ( $found_string ) ;
	}

	$t2 		=  microtime ( true ) ;

	echo "Total time : " . round ( $t2 - $t1, 3 ) . "\n" ;
	echo count ( $pos1 ) . " occurrences found\n";