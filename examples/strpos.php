<?php
	include ( '../SearchableFile.phpclass' ) ;
	$file 		=  'verybigfile.rtf' ;
	$t1 		=  microtime ( true ) ;

	$sf 		=  new SearchableFile ( ) ;
	$sf -> Open ( $file ) ;
	$pos 		=  0 ;
	$search		=  '\\pict' ;
	$length 	=  strlen ( $search ) ;
	$pos1 		=  [] ;
	$pos2 		=  [] ;

	while  ( ( $pos  = $sf -> strpos ( $search, $pos ) )  !==  false )
	{
		//echo "POS1 = $pos\n" ;
		$pos += $length ;
		$pos1 []	=  $pos ;
	}

	$t2 		=  microtime ( true ) ;
	$contents 	=  file_get_contents ( $file ) ;
	$pos 		=  0 ;

	while  ( ( $pos  = strpos ( $contents, $search, $pos ) )  !==  false )
	{
		//echo "POS2 = $pos\n" ;
		$pos += $length ;
		$pos2 []	=  $pos ;
	}
	$t3 		=  microtime ( true ) ;

	echo "Using SearchableFile    : " . round ( $t2 - $t1, 3 ) . "\n" ;
	echo "Using file_get_contents : " . round ( $t3 - $t2, 3 ) . "\n" ;

	if  ( count ( $pos1 )  !=  count ( $pos2 ) )
		echo "Result count mismatch : " . count ( $pos1 ) . " (SearchableFile), " . count ( $pos2 ) . " (file_get_contents)\n" ;
	else
	{
		for  ( $i = 0 ; $i  <  count ( $pos1 ) ; $i ++ )
		{
			if  ( $pos1 [$i]  !=  $pos2 [$i] )
				echo "Result mismatch at index #$i : {$pos1 [$i]} (SearchableFile), {$pos2 [$i]} (file_get_contents)" ;
		}
	}