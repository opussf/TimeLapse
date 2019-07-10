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


class MyThumb {
	public $new_height = 100;
	public $thumb_dir = "./imgs/thumbs";

	function __construct() {
		$this->log_file_name = sprintf( "%s/mythumb_%s.log", $this->thumb_dir, date("md") );
		if( !is_dir( $this->thumb_dir ) ) {
			mkdir( $this->thumb_dir, 0777 );
			chmod( $this->thumb_dir, 0777 );
		}
		if( !$this->fhLog ) {
			fclose( fopen( $this->log_file_name, "a" ) );
			@chmod( $this->log_file_name, 0666 );
			$this->fhLog = fopen( $this->log_file_name, "a" );
		}
	}
	function setSizes( $widths, $heights ) {
		# both are comma seperated string
		$this->awidth =explode( ",", $widths );
		$this->aheight=explode( ",", $heights );
		if( $this->awidth[0] == "" and $this->aheight[0] == "" ) {
			$this->aheight[0] = $this->new_height;
		}
	}
	function setQuiet( $quiet ) {
		$this->quiet = $quiet;
	}
	function log( $str, $override=False ) {
		if( !$this->quiet or $override ) {
			fwrite( $this->fhLog, $str );
		}
	}
	function processImage( $image_name ) {
		$len = strlen( $image_name );
		$pos = strrpos( $image_name, "." );
		$type = strtoupper( substr( $image_name, $pos+1, $len ) );
		$d = date("d M Y H:i:s");
		$this->log( "$d\t$image_name\t" );

		# fid the size of the largest of the arrays
		$index = max( count( $this->awidth ), count( $this->aheight ) );

		for( $lcv=$index; $lcv; $lcv-- ) {
			$w = $this->awidth[$lcv-1];
			$h = $this->aheight[$lcv-1];
			$s = $w."x".$h;
			$thumb_name = $this->make_thumb_name( $image_name, $w, $h );
			# check for pre-existing thumb
			if( !file_exists( $thumb_name ) ) {
				#print( "thumb does not exist" );
				set_time_limit( 60 ); # give some time to work on this
				if( $this->affirmSrc( $image_name ) ) {
					#print( "\nImage is loaded" );
					if( $this->set_w_h( $w, $h ) ) {
						#print( "resize image to ".$this->new_width."x".$this->new_height );
						$im = ImageCreatetruecolor( $this->new_width, $this->new_height) 
								or die( "Problem in creating image" );
						ImageCopyResized( $im, $this->file_src, 
								0, 0, 0, 0, 
								$this->new_width, $this->new_height, 
								ImageSX( $this->file_src ), 
								ImageSY( $this->file_src ) ) 
								or die( "Problem in resizing" );
						switch( $this->file_type ) {
							case "JPEG":
							case "JPG":
								ImageJPEG( $im, $thumb_name ) or die( "Problem in saving JPEG" );
								$this->log( "CJ $s ");
								if( $lcv == 1 and !$this->quiet ) {  # last one
									$this->log( "DJ $s\n" );
									#display jpg
									header( "Content-type: image/jpeg" );
									ImageJPEG( $im );
									exit;
								}
								break;
							case "PNG":
								ImagePNG( $im, $thumb_name ) or die( "Problem in saving PNG" );
								$this->log( "CP $s ");
								if( $lcv == 1 and !$this->quiet ) { # last one
									$this->log( "DP $s\n" );
									# display png
									header( "Content-type: image/png" );
									ImagePNG( $im );
									exit;
								}
								break;
							default:
								die( "unknown file type" );
						}
					} else { # create a 0 size file
						fclose( fopen( $thumb_name, "w" ) );
						$this->log( "CZ $s " );
						if( $lcv == 1 and !$this->quiet ) {
							$this->log( "DZ $s\n" );
							header( "Location: $image_name" );
							exit;
						}
					}
				}
			}
		}
		
		#print( "\n<br/>Return $thumb_name" );
		# this better exist... just spent some time making it....
		if( filesize( $thumb_name ) > 0 ) {
			$this->log( "LT $s\n" );
			if( !$this->quiet ) { header( "Location: $thumb_name" ); }  # load the thumb 
			#exit;
		} else { #file size is 0
			$this->log( "LO\n" );
			if( !$this->quiet ) { header( "Lodation: $image_name" ); } # load the original
			#exit;
		}
	}
	private function make_thumb_name( $fname, $w, $h ) {
		## makes and returns a thumb name
		$afile = explode( DIRECTORY_SEPARATOR, $fname );
		$fname = implode( "_", $afile );
		$thumb_name = sprintf( "%s/tn_%sx%s_%s",
				$this->thumb_dir, $w, $h, $fname );
		return $thumb_name;
	}
	private function affirmSrc( $image_name ) {
		# sets file_type and file_src
		# returns true if file supported, flase otherwise
		$len = strlen( $image_name );
		$pos = strrpos( $image_name, "." );
		$this->file_type = strtoupper( substr( $image_name, $pos+1, $len ) );
		if( ! isset( $this->file_src ) ) {
			switch( $this->file_type ) {
				case "JPEG":
				case "JPG":
					$this->file_src = ImageCreateFromJPEG( $image_name ) or
						 die( $this->log( "\tProblem in opening source JPEG\n" ) );
					break;
				case "PNG":
					$this->file_src = ImageCreateFromPNG( $image_name ) or
						 die( $this->log( "\tProblem in opening source PNG\n" ) );
					break;
				default:
					die( "File Type not supported" );
					return False;
			}
		}
		return True;
	}
	private function set_w_h( $w, $h ) {
		## returns true if thumb would be smaller than original ( make a thumbnail )
		$x = ImageSX( $this->file_src );
		$y = ImageSY( $this->file_src );
		$ratio = $x / $y;

		if( !empty( $w ) ) { # width is given 
			$this->new_width = $w;
			$this->new_height = $w / $ratio;
		}
		if( !empty( $h ) ) { # height is given
			$this->new_height = $h;
		}
		# calc the width if not set
		if( empty($w) ) { $this->new_width = $this->new_height * $ratio; }

		#printf( "New size: %s x %s\n<br/>", $this->new_width, $this->new_height );
		if( ( $this->new_width >= $x ) or ( $this->new_height >= $y ) ) {
			# cannot resize larger than original 
			return False;
		}
		return True;
	}

}


#var_dump( $_SERVER["SCRIPT_FILENAME"] );
#var_dump( __FILE__ );

if( __FILE__ == $_SERVER["SCRIPT_FILENAME"] ) {
$image_name=$_GET['fname'];
$width=$_GET['w'];
$height=$_GET['h'];
$quiet=is_null($_GET['q']) ? false : true; 

$mythumb = new MyThumb();
$mythumb->setQuiet( $quiet );

$mythumb->setSizes( $width, $height );
$mythumb->processImage( $image_name );
} else {
// Turn off all error reporting
error_reporting(0);
}
?>
