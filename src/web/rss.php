<?php
$img_dir = './imgs';

function byte_format( $bytesIn ) {
	$mod = 1024;
	$units = explode( ' ', 'Bytes KibiBytes MebiBytes GibiBytes TebiBytes' );
	for( $i=0; $bytesIn > $mod; $i++ ) {
		$bytesIn /= $mod;
	}
	return sprintf( "%0.03f %s", $bytesIn, $units[$i] );
}

function scanFolder( $dirIn ) {
	# @parameter    $dirIn - directory to scan
	# @return       array  - ["fileList"] and ["totalSize"]
	$all = scandir( $dirIn );
	$fileList = array();
	$totalSize = 0;
	foreach( $all as $file ) {
		$fullPath = "$dirIn/$file";
		if( !is_dir( $fullPath ) ) {
			$fileList[] = $fullPath;
			$totalSize += filesize( $fullPath );
		}
	}
	return( array( "fileList" => $fileList, "totalSize" => $totalSize ) );
}

$afiles = scanFolder( $img_dir );
$c = count($afiles["fileList"]);
$totalSize = $afiles["totalSize"];

$afiles = scanFolder( "$img_dir/thumbs" );
$totalSize += $afiles["totalSize"];

$formattedSize = byte_format( $totalSize );

header("Content-type: application/xml");

print <<<END
<?xml version='1.0' encoding='UTF-8'?>
<?xml-stylesheet title='XSL_formatting' type='text/xsl' href='/include/xsl/rss.xsl'?>
<rss version="2.0">
<channel>
<title>WowShots</title>
<link>http://www.zz9-za.com/~opus/wowshots/</link>
<description>Screen Shot count</description>
<generator>php</generator>
<ttl>30</ttl>

END;

$itemData=array();

$item=array();
$item["title"] = "Screen shot count: $c";
$item["pubDate"] = date( "r" );
$item["link"] = "http://www.zz9-za.com/~opus/wowshots/";
$item["guid"] = $item["title"];
$itemData[] = $item;

$item=array();
$item["title"] = "using $formattedSize";
$item["pubDate"] = date( "r" );
$item["link"] = "http://www.zz9-za.com/~opus/wowshots/";
$item["guid"] = $item["title"];
$itemData[] = $item;

foreach( $itemData as $item ){
	print("<item>\n");
	foreach( $item as $key=>$value) {
		print("\t<$key>$value</$key>\n");
	}
	print("</item>\n");
}
?>
</channel>
</rss>
