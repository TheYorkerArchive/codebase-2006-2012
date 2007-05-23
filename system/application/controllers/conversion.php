<?php
define ("IMAGE_HASH", 2000); //upload cropper new view has this in javascript

/**
 *	@file	conversion.php
 *	@brief	Moves images/photos into db
 *	@author	Mark Goodall (mark.goodall@gmail.com)
 */

/// Account controller.
class Account extends controller {
	function index() {
		$image['banner'] = $this->db->getwhere('images', array('image_type_type_id', 9));
//		$image['gamethumb'] = $this->db->getwhere('images', array('image_type_type_id', 8));
//		$image['puffer'] = $this->db->getwhere('images', array('image_type_type_id', 5));
//		$image['link'] = $this->db->getwhere('images', array('image_type_type_id', 10));
		
		foreach ($image as $type => $results) {
			foreach ($results->result() as $result) {
				if ($result->image_mime == null && file_exists(imageLocation($result->image_id, $type, $result->image_file_extension))) {
					if (function_exists('exif_imagetype')) {
						$mime = image_type_to_mime_type(exif_imagetype('.'.imageLocation($result->image_id, $type, $result->image_file_extension)));
					} else {
						$byDot = explode('.', imageLocation($result->image_id, $type, $result->image_file_extension));
						switch ($byDot[count($byDot)-1]) {
							case 'jpg':
							case 'jpeg':
							case 'JPG':
							case 'JPEG':
								$data['image_mime'] = 'image/jpeg';
								break;
							case 'png':
							case 'PNG':
								$data['image_mime'] = 'image/png';
								break;
							case 'gif':
							case 'GIF':
								$data['image_mime'] = 'image/gif';
								break;
						}
					}
					$data['image_data'] = file_get_contents('.'.imageLocation($result->image_id, $type, $result->image_file_extension));
					$this->db->where('image_id', $result->image_id)->update('images', $data);
				}
			}
		}
	}
	
	private function imageLocation($id, $type = false, $extension = '.jpg') {
		if (is_string($type)) {
			$location = 'images/images/'.$type.'/'.(floor($id / IMAGE_HASH)).'/'.$id.$extension;
			if ($force or is_file($location)) {
				return '/'.$location;
			} else {
				return false;
			}
		}
	}
	
	private function image2string(&$image, $mime) {
		//THIS SUCKS!!
		$contents = ob_get_contents();
		if ($contents !== false) ob_clean(); else ob_start();
		switch ($mime) {
			case 'image/png':
				imagepng($image);
				break;
			case 'image/jpeg':
				imagejpeg($image);
				break;
			case 'image/gif':
				imagegif($image);
				break;
		}
		$data = ob_get_contents();
		if ($contents !== false) {
		  ob_clean();
		  echo $contents;
		}
		else ob_end_clean();
		return $data;
		//I HATE THIS CODE /\
	}
	
}