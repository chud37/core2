<?
//
// --------------------
// Image Functions
// --------------------
//
function imageType($file) 
{
	if(is_file($file)) {
		$imageInfo = getimagesize($file);
		switch($imageInfo['mime'])
		{
			//create the image according to the content type
			case "image/jpg":
			case "image/jpeg":
			case "image/pjpeg": //for IE
				$imageCreated = imagecreatefromjpeg("$file");
				break;
			case "image/gif":
				$imageCreated = imagecreatefromgif("$file");
				break;
			case "image/png":
			case "image/x-png": //for IE
				$imageCreated = imagecreatefrompng("$file");
				break;
		}
		return $imageCreated;
	} else {
		echo "<b>$file</b> is not valid file. <i>(function: imageType)</i><br>";	
		return false;
	}
}
function genThumbFile($f,$t,$new_w = 100, $new_h = 100) 
{
	if(is_file($f)) {
		$source = imageType($f); 
		$orig_w  = imagesx($source); 
		$orig_h = imagesy($source);
		
		$w_ratio = ($new_w / $orig_w);
		$h_ratio = ($new_h / $orig_h);
		
		if ($orig_w > $orig_h ) {//landscape
		$crop_w = round($orig_w * $h_ratio);
		$crop_h = $new_h;
		$src_x = ceil( ( $orig_w - $orig_h ) / 2 );
		$src_y = 0;
		} elseif ($orig_w < $orig_h ) {//portrait
			$crop_h = round($orig_h * $w_ratio);
			$crop_w = $new_w;
			$src_x = 0;
			$src_y = ceil( ( $orig_h - $orig_w ) / 2 );
		} else {	//square
			$crop_w = $new_w;
			$crop_h = $new_h;
			$src_x = 0;
			$src_y = 0;	
		}
		$target = imagecreatetruecolor($new_w,$new_h);
		imagecopyresampled($target, $source, 0 , 0 , $src_x, $src_y, $crop_w, $crop_h, $orig_w, $orig_h);
		imagejpeg($target, $t);	
		
		return true;
	} else {
		echo "<b>$f</b> is not valid file. <i>(function: genThumbFile)</i><br>";
		return false;
	}
}

function resizeImage($source,$target,$extension,$w=500) 
{
	if(is_file($source)) {
		$gdExtensions = array (
			'jpg'=>	'JPEG',
			'jpeg'=>'JPEG',
			'gif'=>	'GIF',
			'bmp'=>	'WBMP',
			'png'=>	'PNG'
		);
		$gdExtension = $gdExtensions[$extension];
		$function_to_read = "ImageCreateFrom".$gdExtension;
		$function_to_write = "Image".$gdExtension;
		$size = GetimageSize($source);
		$ratio = $size[0]/$size[1]; // width/height
		if($ratio > 1) {
			$width = $w;
			$height = $w / $ratio;
		} else {	
			$width = $w * $ratio;
			$height = $w;
		}
		$original = $function_to_read($source);
		$finished = ImageCreateTrueColor($width, $height);
		ImageCopyResampled($finished, $original, 0, 0, 0, 0, $width+1, $height+1, $size[0], $size[1]);
		$function_to_write($finished, $target);
		ImageDestroy($original);
		ImageDestroy($finished);
		return true;
	} else {
		echo "<b>$source</b> is not valid file. <i>(function: resizeImage)</i><br>";
		return false;
	}
}

function sortFiles($debug = false) {
	//	Sort $_FILES into a easily managed array.
	//	Usage: $files = sortFiles();
	$array = array();
	if((isset($_FILES['files']))) {
		for($x = 0; $x < count($_FILES['files']['name']); $x++) {
			$tmp = array();
			if(isset($_FILES['files']['name'][$x])) 	$tmp['name'] = $_FILES['files']['name'][$x];
			if(isset($_FILES['files']['type'][$x])) 	$tmp['type'] = $_FILES['files']['type'][$x];
			if(isset($_FILES['files']['tmp_name'][$x])) $tmp['tmp_name'] = $_FILES['files']['tmp_name'][$x];
			if(isset($_FILES['files']['error'][$x])) 	$tmp['error'] = $_FILES['files']['error'][$x];
			if(isset($_FILES['files']['size'][$x])) 	$tmp['size'] = $_FILES['files']['size'][$x];
			$array[] = $tmp;
		}             
		return $array;
	} else {
		if($debug) echo "No \$_FILES array found.";
		return false;	
	}
}


?>
