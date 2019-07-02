<?php

$all = scandir("./imgs");

$afiles = array();
foreach ( $all as $file ) {
	if (!is_dir("./imgs/$file")) {
		$afiles[] = "$img_dir/$file";
	}
}
$c = count($afiles);

print <<<END
<?xml version='1.0' encoding='UTF-8'?>
<?xml-stylesheet title='XSL_formatting' type='text/xsl' href='/includes/rss.xsl'?>
<rss version="2.0">
<channel>
<title>WowShots</title>
<link>http://www.zz9-za.com/~opus/wowshots</link>
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
