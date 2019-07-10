<?php

require( "mythumb.php" );

$starttime = microtime( true );  # return as float

$makeSizes = array( "1440", "1200", "1000", "720", "640", "320" );

#build the list of slides
$img_dir = "imgs";

#get all the files
$all = scandir( "./$img_dir" );
$afiles = array();
foreach( $all as $file ) {
	if( !is_dir( "./$img_dir/$file" ) ) {
		$afiles[] = "$img_dir/$file";
	}
}

$mythumb = new MyThumb();
$mythumb->setQuiet( True );
$mythumb->setSizes( join( ",",$makeSizes ) );

$urlBase = "http://127.0.0.1/~opus/wowshots/mythumb.php";
$widthStr = "w=".join(",",$makeSizes);
$urlBase = $urlBase."?".$widthStr."&q=true";


foreach( $afiles as $fname ) {
	$mythumb->processImage( $fname );
	# make the URL
	$fname = "fname=$fname";
	$requestURL = $urlBase."&".$fname;

	# request the file
	#fclose( fopen( $requestURL, 'r' ) );
}

$numFiles = count( $afiles );
$endtime = microtime( true );
printf( "%s Processed % 4d files in % 6.2f seconds (%0.2f/s)",
		date( "r", $starttime ), 
		$numFiles, 
		($endtime - $starttime),
		$numFiles / ($endtime - $starttime)
);

?>

