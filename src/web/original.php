<?php
if ($_GET["limit"]) {
	$limit = intval( $_GET["limit"] );
}
if (empty($limit)) { $limit = 10; }
if ($_GET["ms"]) {
	$ms = intval( $_GET["ms"] );
}
if (empty($ms)) { $ms = 800; }
if ($_GET["width"]) {
	$width = intval( $_GET["width"]);
}
if (empty($width)) { $width= 640; }
if (array_key_exists( "startOffset", $_GET )) {
	$startOffset = intval( $_GET["startOffset"]);
}
if ($_GET["endOffset"]) {
	$endOffset = intval( $_GET["endOffset"]);
}

$date_parse_format = '\i\m\g\s\/\W\o\W\S\c\r\n\S\h\o\t\_mdy_Gis*';

# build the list of slides
$img_dir = "imgs";

$all = scandir("./$img_dir");
$afiles = array();
foreach( $all as $file) {
	if (!is_dir("./$img_dir/$file")) {
		$afiles[] = "$img_dir/$file";
	}
}

# build the size limit array
$c = count( $afiles );
$alimitsrc = array(1, 2, 5 );
$alimit = array();
$pow = 0;
$lcv = 0;
$v = $alimitsrc[$lcv] * pow( 10, $pow );

while( $v < $c ) {
	$alimit[] = $v;
	$lcv ++;
	if ($lcv > count( $alimitsrc ) - 1) {
		$lcv = 0;
		$pow++;
	}
	$v = $alimitsrc[$lcv] * pow( 10, $pow );
}
$alimit[] = $c;


$ams = array(100, 200, 300, 400, 500, 800, 1000, 2000, 5000);
$widths = array(640, 720, 1000, 1440);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<title>Wow Slideshow</title>
<link type='text/css' href='/css/bootstrap.css' rel='stylesheet'/>
<link type='text/css' href="wowshots.css" rel="stylesheet"/>
<script src='/js/bootstrap.min.js'></script>
<meta name='viewport' content='width=device-width, initial-scale=1'/>
</head>
<body>
<div class='container-fluid'>
<div class='row menu-row-1'>
<div class='col-xs-10 menu-limits'>
<ul><li>Limit to:</li>
<?php
# show the limit menu
foreach( $alimit as $a ) {
	$linkStart = "<strong><mark>"; $linkEnd = "</mark></strong>";
	if ($a != $limit) {
		$linkStart = "<a href='index.php?width=$width&limit=$a&ms=$ms'>";
		$linkEnd = "</a>";
	}
	print("<li>$linkStart$a$linkEnd</li>");
}
?>
</ul>
</div> <!-- menu-limits -->
</div> <!-- menu-row-1 -->
<div class='row menu-row-2'>
<div class='col-xs-5 menu-seconds'>
<ul><li>Seconds:</li>
<?php
foreach ( $ams as $m ) {
	$linkStart = "<strong><mark>"; $linkEnd = "</mark></strong>";
	if ($m != $ms) {
		$linkStart = "<a href='index.php?width=$width&limit=$limit&ms=$m'>";
		$linkEnd = "</a>";
	}
	$m = $m / 1000;
	print("<li>$linkStart$m$linkEnd</li>");
}
?>
</ul>
</div> <!-- menu-seconds -->
<div class='col-xs-5 menu-width'>
<ul><li>Width:</li>
<?
foreach( $widths as $w ) {
	$linkStart = "<strong><mark>"; $linkEnd = "</mark></strong>";
	if ($w != $width) {
		$linkStart = "<a href='index.php?width=$w&limit=$limit&ms=$ms'>";
		$linkEnd = "</a>";
	}
	print("<li>$linkStart$w$linkEnd</li>");
}
?>
</ul>
</div> <!-- width_menu -->
</div> <!-- Menu-row-2 -->
<!--
<div class="display" style="max-width:1300px">
<div class="carousel" style="max-width:1000px">
-->
<div class='row display-row'>
<div class='col-xs-9 carousel-col'>
<?php
# mySlides divs for content
if (isset($startOffset) and isset($endOffset)) {
	$showfiles = array_slice( $afiles, $startOffset, ($endOffset - $startOffset) );
} else {
	$showfiles = array_slice($afiles, -$limit);
}

$lcv = 1; $c = count($showfiles);
$lastDate = date_create();
$now = $lastDate;
foreach( $showfiles as $file) {
	$date = date_create_from_format($date_parse_format, $file);

	if (!$date) {
		$date = date_create( '@'.filemtime( $file ) );
	}

	$interval = date_diff($lastDate, $date);
	$fromNow = date_diff($now, $date);
	
	print("<div class=\"mySlides\">");
	print("<div class=\"img\" style=\"width:1000px\">");
	if ($width < 1440) {
		print("<img src=\"mythumb.php?fname=$file&w=$width\" style=\"width:100%\">\n");
	} else {
		print("<img lowsrc=\"mythumb.php?fname=$file&w=50\" src=\"$file\" style=\"width:100%\">\n");
	}
	print("</div>");  # Img
	$displayName = $date->format( "D j M Y G:i:s" );
	$diffStr = $interval->format("%a Days %H:%I:%S");
	$fromNow = $fromNow->format("%a Days %H:%I:%S");
	$lastDate = $date;
	
	print("<div class=\"caption\"><a href=\"$file\">$displayName  $lcv/$c</a>");
	print(" - $fromNow ($diffStr)");
	print("</div>");
	print("</div>");
	$lcv++;
}

?>
</div> <!-- corousel-col -->
<div class='col-xs-3 dateList'>
<!--
<div class="dateList" style="max-width:200px">
-->
<?php
$dateCount = array();
foreach( $afiles as $file) {
	$date = date_create_from_format( $date_parse_format, $file )->format("D j M Y");
	$dateCount[$date] = isset($dateCount[$date]) ? $dateCount[$date]+1 : 1;
}
$startOffset = 0;
$endOffset = 0;
foreach( $dateCount as $k=>$v ) {
	$endOffset += $v;
	print("<a href='?width=$width&ms=$ms&startOffset=$startOffset&endOffset=$endOffset'>$k</a> -- $v<br/>");
	$startOffset = $endOffset;
	#print("$k -- $v<br/>");
}

?>
</div> <!-- dateList -->
</div> <!-- display-row -->
</div>  <!-- container -->
<!-- carousel script to show the images -->
<!-- from http://www.w3schools.com/w3css/w3css_slideshow.asp  -->
<script>
var myIndex = 0;
carousel();

function carousel() {
    var i;
    var x = document.getElementsByClassName("mySlides");
    for (i = 0; i < x.length; i++) {
       x[i].style.display = "none";  
    }
    myIndex++;
    if (myIndex > x.length) {myIndex = 1}    
    x[myIndex-1].style.display = "block";  
    setTimeout(carousel, <? echo $ms ?>); // Change image every miliseconds
}
</script>
</body>
