<?php
	include ( '../SearchableFile.phpclass' ) ;
	$file 		=  'verybigfile.rtf' ;
	$re 		=  '/\\\\pict/' ;

	$t1		=  microtime ( true ) ;
	
	$sf 		=  new SearchableFile ( $file ) ;
	$offset 	=  0 ;
	$status1 	= $sf -> pcre_match ( $re, $matches1, PREG_OFFSET_CAPTURE, $offset ) ;
	$count1 	=  count ( $matches1 ) ;
	
	$t2 		=  microtime ( true ) ;
	
	$contents 	=  file_get_contents ( $file ) ;
	$offset 	=  0 ;
	$status2 	=  preg_match ( $re, $contents, $matches2, PREG_OFFSET_CAPTURE, $offset ) ;
	$count2 	=  count ( $matches1 ) ;

	$t3 		=  microtime ( true ) ;
	
	echo "Elapsed (SearchableFile) : " . round ( $t2 - $t1, 3 ) . " (count = $count1)\n" ;
	echo "Elapsed (preg_match)     : " . round ( $t3 - $t2, 3 ) . " (count = $count2)\n" ;
