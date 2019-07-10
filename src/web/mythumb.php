<?php
# mythumb.php
# Charles Gordon
# 18 June 2004
#########################################
# Parameters:
# fname = Filename of image to thumbnail
# w = desired width
# h = desired height
#########################################
# Defaults to height = 100 pixels (width calculated on that)
# If a height is given, calculates the width from that.
# If a width is given, calculates the height from that.
# If both, then uses both
#########################################
# Will not create thumb if image is larger than original.
# Thumb file of 0 size is created as place holder for speed.
# (file processed, load original)
#########################################
# Can take a list of sizes.  Sizes will be paired by location in list.
# Will process and return based on the first value, and the second
# values will be secondary results (saved for later use)
#########################################
# Codes used in the log file include:
# L = Load     T = Thumb  O = Original
# C = Created  J = JPG    P = PNG    Z = Zero size
# D = Display


  $p = dirname($_SERVER['PHP_SELF']);
  $pos = strrpos($p,"/");
  $name = substr($p,$pos+1);

  $log_str = "";

  $log_file = "mythumb_".date("md").".log";

  #$new_width=100;
  $new_height=75;

  $image_name=$_GET['fname'];
  $width=$_GET['w'];
  $height=$_GET['h'];
  $quiet=is_null($_GET['q']) ? false : true;

  $awidth =explode(",",$width);
  $aheight=explode(",",$height);

  $thumb_dir = "./imgs/thumbs/";
  if (!is_dir($thumb_dir)) {
    mkdir($thumb_dir, 0777);
    chmod($thumb_dir, 0777);
  }

  #$fhLog = fopen($log_file,"a") or die("Cannot open $log_file");
  $fhLog = @fopen("../".$log_file,"a");		# suppress errors and try this
  if (!$fhLog) {
    $fhLog = fopen($thumb_dir . $log_file,"a");
  }

#  $src_path = array_slice(explode(DIRECTORY_SEPARATOR, $image_name),0,-1);
#  $src_path = $src_path[0];
#  $image_name = array_slice(explode(DIRECTORY_SEPARATOR, $image_name),-1);
#  $image_name = $image_name[0];

  $len = strlen($image_name);
  $pos = strrpos($image_name,".");
  $type = strtoupper(substr($image_name,$pos+1,$len));

  # build this from the first elements
  # (controls the execution of the script)
  $thumb_name = make_thumb_name($image_name,$awidth[0],$aheight[0]);

  $d = date("d M Y H:i:s");
  fwrite($fhLog,"$d\t$image_name\t");

  thumb_image ($image_name);

  function make_thumb_name($fname,$w,$h)
  ## makes and returns a thumb name
  {
    global $thumb_dir;
    $afile = explode(DIRECTORY_SEPARATOR, $fname);
    $fname = implode("_", $afile);

    $thumb_name = $thumb_dir . "tn_".$w."x".$h."_".$fname;
    return $thumb_name;
  }

  function set_w_h($img,$w,$h)
  ## returns true if thumb would be smaller than original
  {
    #global $width;
    #global $height;
    global $new_width;
    global $new_height;
    #global $fhLog;

    $ratio = ImageSX($img) / ImageSY($img);		# calc the image ratio

    if (!empty($w)) {					# width is given
      $new_width=$w;
      $new_height=$w/$ratio;				# calc height
    }

    # set the height if given
    if (!empty($h)) {
      $new_height = $h;
    }

    # calc the width if not set
    #if (!isset($new_width)) $new_width = $new_height * $ratio;
    if (empty($w)) $new_width = $new_height * $ratio;

    #fwrite($fhLog, "Final Size: $new_width x $new_height\n");
    if (($new_width >= ImageSX($img)) or ($new_height >= ImageSY($img))) {
      #print ("Can't resize larger");
      return False;
    }
    #print ("Being happy");
    return True;
  }

  function thumb_image($image_name)
  {
    global $awidth;
    global $aheight;
    global $new_width;
    global $new_height;
    global $thumb_name;
    global $type;
    global $fhLog;
    global $quiet;

    $s = $new_width . "x" . $new_height;
    $s = $awidth[0] . "x" . $aheight[0];

    # check for pre-existing thumb (use the first size given)
    if (file_exists($thumb_name)) {
      if (filesize($thumb_name) > 0) {		# if file larger than 0
        fwrite($fhLog,"LT $s\n");
        if( !$quiet ) { header("Location: $thumb_name"); }	# load the thumb
        exit;
      } else {
        fwrite($fhLog,"LO\n");
        if( !$quiet ) { header("Location: $image_name"); }	#load the original
      }
    } else {					# no thumbnail
      set_time_limit(60);
      switch ($type) {
        case "JPEG":
	case "JPG":
	  #$src = ImageCreateFromJPEG($image_name) or die("Problem in opening source JPEG");
	  $src = ImageCreateFromJPEG($image_name) or die(fwrite($fhLog,"\tProblem in opening source JPEG\n"));
	  break;
	case "PNG":
	  $src = ImageCreateFromPNG($image_name) or die(fwrite($fhLog,"\tProblem in opening source PNG\n"));
	  break;
	default:
	  die("File Type not supported");
      }
      #print ("\nImage is loaded");

      # find the size of the largest of the arrays
      $index = max(count($awidth),count($aheight));

      for($lcv=$index; $lcv; $lcv--) {
        $w = $awidth[$lcv-1];
	$h = $aheight[$lcv-1];
	$s = $w . "x" . $h;
	$thumb_name=make_thumb_name($image_name,$w,$h);
	if (set_w_h($src,$w,$h)) {
	  #print ("Making Thumb");
	  $im = ImageCreatetruecolor($new_width, $new_height) or die("Problem in creating image");
	  ImageCopyResized($im,$src,0,0,0,0,$new_width,$new_height,ImageSX($src),ImageSY($src)) or dir("Problem in resizing");
	  switch ($type) {
	    case "JPEG":
	    case "JPG":
  	      ImageJPEG($im,$thumb_name) or die("Problem in saving JPEG");
	      fwrite($fhLog,"CJ $s ");
	      if ($lcv==1) {			# last one
	        fwrite($fhLog,"DJ $s\n");
		#print ("<br>Display image");
		header("Content-type: image/jpeg");
		ImageJPEG($im);
	      }
	      break;
	    case "PNG":
	      ImagePNG($im,$thumb_name) or die("Problem in saving PNG");
	      fwrite($fhLog,"CP $s ");
	      if ($lcv==1) {			# last one
	        fwrite($fhLog,"DP $s\n");
	        #print ("<br>Display PNG");
		header("Content-type: image/png");
		ImagePNG($im);
	      }
	      break;
	    default:
	      die("Should never reach here");
	  }
	} else {
	  fclose(fopen($thumb_name,"w"));
	  fwrite($fhLog,"CZ $s ");
	  if ($lcv==1) {
	    fwrite($fhLog,"DZ $s\n");
	    header("Location: $image_name");
	  } # if
	} # else
      } #for
    } # else
  } # function
?>
