<?php
/**************************************************************************

    Copyright (C) 2018 Iplussoft Technologies

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
**************************************************************************/

set_time_limit(0);
session_start();

function blur($image, $w, $h){

	$size = array('sm'=>array('w'=>intval($w/4), 'h'=>intval($h/4)),
				   'md'=>array('w'=>intval($w/2), 'h'=>intval($h/2))
				  );                       

	$sm = imagecreatetruecolor($size['sm']['w'],$size['sm']['h']);
	imagecopyresampled($sm, $image, 0, 0, 0, 0, $size['sm']['w'], $size['sm']['h'], $w, $h);

	for ($x=1; $x <=70; $x++){
		imagefilter($sm, IMG_FILTER_GAUSSIAN_BLUR, 999);
	} 

	imagefilter($sm, IMG_FILTER_SMOOTH, 999);
	imagefilter($sm, IMG_FILTER_BRIGHTNESS, 70);        

	$md = imagecreatetruecolor($size['md']['w'], $size['md']['h']);
	imagecopyresampled($md, $sm, 0, 0, 0, 0, $size['md']['w'], $size['md']['h'], $size['sm']['w'], $size['sm']['h']);
	imagedestroy($sm);

		for ($x=1; $x <=25; $x++){
			imagefilter($md, IMG_FILTER_GAUSSIAN_BLUR, 999);
		} 

	imagefilter($md, IMG_FILTER_SMOOTH, 99);
	imagefilter($md, IMG_FILTER_BRIGHTNESS, 10);        

	imagecopyresampled($image, $md, 0, 0, 0, 0, $w, $h, $size['md']['w'], $size['md']['h']);
	imagedestroy($md);  

	return $image;
}


$success = '';
$error = '';

if (@$_POST["submitbtn"]) {

	//save last preference to session
	
	try {
	
		$thumbtype = @$_POST['thumbtype'];
		$thumbupload = @$_FILES['thumbupload'];
		
		$_SESSION['thumbtype'] = $thumbtype;

		if ($thumbtype == '1') {
		//for stories
			$vwidth = 1080;
			$vheight = 1920;
			$wpadding = 320;
			$hpadding = 520;
		}elseif($thumbtype == '6'){
		//for stories
			$vwidth = 1080;
			$vheight = 1920;
			$wpadding = 320;
			$hpadding = 520;
		}elseif($thumbtype == '4'){
			$vwidth = 1000;
			$vheight = 560;
			$wpadding = 0;
			$hpadding = 0;
		}elseif($thumbtype == '5'){
			$vwidth = 1000;
			$vheight = 560;
			$wpadding = 0;
			$hpadding = 0;
		}else{
			$vwidth = 1080;
			$vheight = 1080;
			$wpadding = 0;
			$hpadding = 0;
		}
		
		$imagemaxw = $vwidth - $wpadding;
		$imagemaxh = $vheight - $hpadding;

		$check = @getimagesize($thumbupload["tmp_name"]);
		if($check === false) throw new Exception("Invalid image file uploaded.");

		$orig_w = $check[0];
		$orig_h = $check[1];

		$w = $vwidth;
		$h = $vheight;

		$target_file = basename($thumbupload["name"]);
		$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

		switch(strtolower($imageFileType)){
			case 'jpeg':
			case 'jpg':
				$img = imagecreatefromjpeg($thumbupload["tmp_name"]);
			break;
		
			case 'png':
				$img = imagecreatefrompng($thumbupload["tmp_name"]);
			break;

			case 'gif':
				$img = imagecreatefromgif($thumbupload["tmp_name"]);
			break;

			default:
				throw new Exception('File is not valid jpg, png or gif image.');
			break;
		}

		//handle custom upload
		//resize to width and height
		$thumb = imagecreatetruecolor($vwidth, $vheight);
		$white = imagecolorallocate($thumb, 255, 255, 255);
		imagefill($thumb, 0, 0, $white);
	

		$w_ratio = $orig_w / $w;
		$h_ratio = $orig_h / $h;

		$ratio = $w_ratio > $h_ratio ? $h_ratio : $w_ratio;

		$bgdst_w = $orig_w / $ratio;
		$bgdst_h = $orig_h / $ratio;
		$bgdst_x = ($w - $bgdst_w) / 2;
		$bgdst_y = ($h - $bgdst_h) / 2;

		if ($thumbtype != '6' && $thumbtype != '7' && $thumbtype != '8') {
			imagecopyresampled($thumb, $img, $bgdst_x, $bgdst_y,
							   0, 0, $bgdst_w, $bgdst_h, $orig_w, $orig_h);
			
			$thumb = blur($thumb, $vwidth, $vheight);
		}
		

		$w_ratio = $orig_w / $imagemaxw;
		$h_ratio = $orig_h / $imagemaxh;

		$ratio = $w_ratio > $h_ratio ? $w_ratio : $h_ratio;

		$dst_w = ($orig_w / $ratio);
		$dst_h = ($orig_h / $ratio);
		$dst_x = ($w - $dst_w) / 2;
		$dst_y = ($h - $dst_h) / 2;
	
		imagecopyresampled($thumb, $img, $dst_x, $dst_y,
						   0, 0, $dst_w, $dst_h, $orig_w, $orig_h);

		if ($thumbtype == '8' || $thumbtype == '6' || $thumbtype == '1' || $thumbtype == '3' || $thumbtype == '5') {
		//for stories
		header('Content-Disposition: Attachment;filename=socialmediaimg_'.date('dmy_His').'.jpg'); 
		}
		//else direct display
		header('Content-Type: image/jpg');
		imagejpeg($thumb, NULL, 100);
		imagedestroy($thumb);
		imagedestroy($img);
	} catch(Exception $e) {
	  // When validation fails or other local issues
	  $error = 'Script returned an error: ' . $e->getMessage();
	}
}				
?>
<html>
<head>
<title>Social Media Image Resizer</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style><!--
	.go_red {
		color: red;
	}
		
	.go_green {
		color: green;
	}
	
	.loading-modal {
		display:    none;
		position:   fixed;
		z-index:    1000;
		top:        0;
		left:       0;
		height:     100%;
		width:      100%;
		background: rgba( 255, 255, 255, .8 ) 
					url('ajax-loader.gif') 
					50% 50% 
					no-repeat;
	}
	
	.loading {
		overflow: hidden;   
	}

--></style>
</head>
<body style="font-family:Arial;">
<h2 style="color:#8a3ab9;">Social Media Image Resizer</h2>
<form id="frm" method="post" autocomplete="off" enctype="multipart/form-data">
<div style="color:#006400;margin:5px;"><?php if (@$success) echo $success; ?></div>
<div style="color:#ff0000;margin:5px;"><?php if (@$error) echo $error; ?></div>

<div id="thumbdiv">

	<div>
		<label for="thumbtype">Select Style:</label>
		<select id="thumbtype" name="thumbtype" style="margin-bottom:5px;font-size:15px;">
			<option value="6"<?php if ((@$_POST['thumbtype'] == '6') || (@$_SESSION['thumbtype'] == '6' && !@$_POST)) echo ' selected'; ?>>Instagram Story White (Download)</option>
			<option value="1"<?php if ((@$_POST['thumbtype'] == '1') || (@$_SESSION['thumbtype'] == '1' && !@$_POST)) echo ' selected'; ?>>Instagram Story (Download)</option>
			<option value="7"<?php if ((@$_POST['thumbtype'] == '7') || (@$_SESSION['thumbtype'] == '7' && !@$_POST)) echo ' selected'; ?>>Instagram Square Post White (Direct Share)</option>
			<option value="8"<?php if ((@$_POST['thumbtype'] == '8') || (@$_SESSION['thumbtype'] == '8' && !@$_POST)) echo ' selected'; ?>>Instagram Square Post White (Download)</option>
			<option value="2"<?php if ((@$_POST['thumbtype'] == '2') || (@$_SESSION['thumbtype'] == '2' && !@$_POST)) echo ' selected'; ?>>Instagram Square Post (Direct Share)</option>
			<option value="3"<?php if ((@$_POST['thumbtype'] == '3') || (@$_SESSION['thumbtype'] == '3' && !@$_POST)) echo ' selected'; ?>>Instagram Square Post (Download)</option>
			<option value="4"<?php if ((@$_POST['thumbtype'] == '4') || (@$_SESSION['thumbtype'] == '4' && !@$_POST)) echo ' selected'; ?>>Twitter Image Post (Direct Share)</option>
			<option value="5"<?php if ((@$_POST['thumbtype'] == '5') || (@$_SESSION['thumbtype'] == '5' && !@$_POST)) echo ' selected'; ?>>Twitter Image Post (Download)</option>
		</select>
	</div>

	<div id="thumbupload">
		<div><input type="file" name="thumbupload" accept="image/*" style="margin-bottom:5px;padding:10px;width:100%;font-size:15px;"></div>
	</div>

</div>

<br>

<input type="hidden" name="submitbtn" value="1"/>
<div><input name="submitbtn" id="submitbtn" type="submit" value="Resize" style="padding:10px;width:100%;height:60px;background:#8a3ab9;color:#fff;"></div>
</form>

<center><small>&copy; 2018 Iplussoft Technologies, Version 1.1</small></center>

</body>
</html>
