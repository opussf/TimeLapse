<?php
# build the list of slides
$img_dir = "imgs";
$date_parse_format = '\i\m\g\s\/\W\o\W\S\c\r\n\S\h\o\t\_mdy_Gis*';

# get all the files
$all = scandir("./$img_dir");
$afiles = array();
foreach( $all as $file) {
	if (!is_dir("./$img_dir/$file")) {
		$afiles[] = "$img_dir/$file";
	}
}

# pre-process the files
$outFiles = array();
$outFilterList = array();
foreach( $afiles as $file ) {
	$date = date_create_from_format( $date_parse_format, $file );
	if (!$date) {
		$date = date_create( '@'.filemtime( $file ) );
	}
	$working = array();
	$working["path"] = $file;
	$working["ts"] = $date->getTimestamp();
	array_push( $outFiles, $working );

	$dateKey = $date->format("j M Y (D) A");
	if (array_key_exists( $dateKey, $outFilterList )) {
		$outFilterList[$dateKey]["count"] ++;
		$outFilterList[$dateKey]["maxTS"] = $date->getTimestamp();
	} else {
		$outFilterList[$dateKey] = array( 
				"count" => 1, 
				"str" => $dateKey, 
				"minTS" => $date->getTimestamp() );
	}
		
}

#var_dump( $outFilterList );
#var_dump( array_values( $outFilterList ) );

print( "{\"wowshots\":" );
print( json_encode( $outFiles ) );
print( ", \"filterData\":" );
print( json_encode( array_values( $outFilterList ) ) );

print( "}" );
?>
