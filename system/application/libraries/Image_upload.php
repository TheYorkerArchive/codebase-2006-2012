<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
if (!defined('VIEW_WIDTH')) {
	define('VIEW_WIDTH', 650);
}
define('VIEW_HEIGHT', 650);
class Image_upload {
	
	private $ci;
	
	public function Image_upload() {
		$this->ci = &get_instance();
		$this->ci->load->library(array('xajax', 'image'));
		$this->ci->load->helper('url');
		$this->ci->xajax->registerFunction(array("process_form_data", &$this, "process_form_data"));
	}
	
	public function automatic($returnPath, $types = false, $multiple = false, $photos = false) {
		if ($this->uploadForm($multiple, $photos)) {
			$this->recieveUpload($returnPath, $types, $photos);
		}
	}
	
	public function uploadForm($multiple = false, $photos = false) {
		$this->ci->xajax->processRequests();
		if ($this->ci->input->post('destination')) return true;
		if ($multiple && $photos) {
			$this->ci->main_frame->SetTitle('Multiple Photo Uploader');
			$this->ci->main_frame->SetExtraHead('<script src="/javascript/clone.js" type="text/javascript"></script>');
			$this->ci->main_frame->SetContentSimple('uploader/upload_multiple_photos');
		} elseif ($multiple) {
			$this->ci->main_frame->SetTitle('Multiple Image Uploader');
			$this->ci->main_frame->SetExtraHead('<script src="/javascript/clone.js" type="text/javascript"></script>');
			$this->ci->main_frame->SetContentSimple('uploader/upload_multiple_images');
		} elseif ($photos) {
			$this->ci->main_frame->SetTitle('Photo Upload');
			$this->ci->main_frame->SetContentSimple('uploader/upload_single_photo');
		} else {
			$this->ci->main_frame->SetTitle('Image Upload');
			$this->ci->main_frame->SetContentSimple('uploader/upload_single_image');
		}
		$this->ci->main_frame->Load();
	}
	
	private function checkImageProperties(&$imgData, &$imgTypes) {
		foreach ($imgTypes->result() as $imgType) {
			if ($imgData['image_width'] < $imgType->image_type_width) return false;
			if ($imgData['image_height'] < $imgType->image_type_height) return false;
		}
		return true;
	}
	
	//types is an array
	public function recieveUpload($returnPath, $types = false, $photo = true) {
		$this->ci->load->library(array('image_lib', 'upload'));
		
		//get data about thumbnails
		
		$config['upload_path'] = './tmp/uploads/';
		$config['allowed_types'] = 'jpg|png|gif|jpeg';
		$config['max_size'] = 16384;
		
		if (is_array($types)) {
			$query = $this->ci->db->select('image_type_id, image_type_name, image_type_width, image_type_height');
			$query = $query->where('image_type_photo_thumbnail', $photo);
			$type = array_pop($types);
			$query = $query->where('image_type_codename', $type);
			foreach ($types as $type) {
				$query = $query->orwhere('image_type_codename', $type);
			}
			$query = $query->get('image_types');
		} else {
			$query = $this->ci->db->select('image_type_id, image_type_name, image_type_width, image_type_height')->getwhere('image_types', array('image_type_photo_thumbnail' => '1'));
		}
		$data = array();
		$this->ci->upload->initialize($config);
		for ($x = 1; $x <= $this->ci->input->post('destination'); $x++) {
			if ( ! $this->ci->upload->do_upload('userfile'.$x)) {
				$this->main_frame->AddMessage('error', $this->ci->upload->display_errors());
			} else {
				$data[] = $this->ci->upload->data();
				
				if ($this->checkImageProperties($data[$x - 1], $query))
				//var_dump( $this->processImage($data[$x - 1], $x, $query, $photo) );
					$data[$x - 1] = $this->processImage($data[$x - 1], $x, $query, $photo);
			}
		}
		$this->ci->main_frame->SetTitle('Photo Uploader');
		$head = $this->ci->xajax->getJavascript(null, '/javascript/xajax.js');
		$head.= '<link rel="stylesheet" type="text/css" href="/stylesheets/cropper.css" media="all" /><script src="/javascript/prototype.js" type="text/javascript"></script><script src="/javascript/scriptaculous.js?load=builder,effects,dragdrop" type="text/javascript"></script><script src="/javascript/cropper.js" type="text/javascript"></script>';
		$this->ci->main_frame->SetExtraHead($head);
		$this->ci->main_frame->SetContentSimple('uploader/upload_cropper_new', array('returnPath' => $returnPath, 'data' => $data, 'ThumbDetails' => &$query, 'type' => $photo));
		return $this->ci->main_frame->Load();
	}
	
	public function process_form_data($formData) {
		$objResponse = new xajaxResponse();

		$selectedThumb = explode("|", $formData['imageChoice']);
		// 0 location
		// 1 original width(?)
		// 2 original height(?)
		// 3 type
		// 4 image id
		// 5 image type width
		// 6 image type height
		
		/* REDO
		$securityCheck = array_search($selectedThumb[4], $_SESSION['img'][]['list']);// this is the line to change
		if ($securityCheck === false) {
			exit("LOGOUT #1" . print_r($selectedThumb) . '****' . var_dump($_SESSION['img']));
			$this->ci->user_auth->logout();
			redirect('/', 'location');
			//TODO add some kind of logging
			exit;
		} else {
			if ($_SESSION['img'][$securityCheck]['type'] != $selectedThumb[3]) {
				exit("LOGOUT #2" . print_r($selectedThumb) . '****' . var_dump($_SESSION['img']));
				$this->ci->user_auth->logout();
				redirect('/', 'location');
				//TODO add some kind of logging
				exit;
			}
		}
		*/

		$sql = 'SELECT image_type_id AS id, image_type_width AS x, image_type_height AS y
		        FROM image_types WHERE image_type_id = ? LIMIT 1';
		$result = $this->ci->db->query($sql, array($selectedThumb[3]));
		if($result->num_rows() != 1) {
			$this->ci->user_auth->logout();
			redirect('/', 'location');
			//TODO add some kind of logging
			exit;
		}

		$bits = explode('/', $selectedThumb[0]);
		if ($bits[1] == 'tmp') {
			//Get mime
			if (function_exists('exif_imagetype')) {
				$mime = image_type_to_mime_type(exif_imagetype($selectedThumb[0]));
			} else {
				$byDot = explode('/', $selectedThumb[0]);
				switch ($byDot[count($byDot)-1]) {
					case 'jpg':
					case 'jpeg':
					case 'JPG':
					case 'JPEG':
						$mime = 'image/jpeg';
						$image = imagecreatefromjpeg($selectedThumb[0]);
						break;
					case 'png':
					case 'PNG':
						$mime = 'image/png';
						$image = imagecreatefrompng($selectedThumb[0]);
						break;
					case 'gif':
					case 'GIF':
						$mime = 'image/gif';
						$image = imagecreatefromgif($selectedThumb[0]);
						break;
				}
			}
			$result = $result->first_row();
			$newImage = imagecreatetruecolor($result->x, $result->y);
			imagecopyresampled($image, $newImage, 0, 0, 0, 0, $result->x, $result->y, imagesx($image), imagesy($image));
			$id = $this->ci->image->add($type, $image, array($title, $mime));
			if ($id != false) {
				foreach ($_SESSION['img'] as &$newImages) {
					if ($selectedThumb[4] == $newImages['list'] and $selectedThumb[3] == $newImages['type']) {
						if (isset($newImages['oldID'])) {
							$this->ci->image->delete('image', $id); //TODO log orphaned image if false
							$newImage['oldID'] = $newImages['list'];
						} else {
							$newImages['oldID'] = true;
						}
						$newImages['list'] = $id;
					}
				}
			} else {
				$objResponse->addAssign("submitButton","value","Not Saved");
				$objResponse->addAssign("submitButton","disabled",false);
				return $objResponse;
			}
		} else {
			$sql = 'DELETE FROM photo_thumbs WHERE photo_thumbs_photo_id = ? AND photo_thumbs_image_type_id = ? LIMIT 1';
			$this->ci->db->query($sql, array($selectedThumb[4], $selectedThumb[3]));
			$this->ci->image->thumbnail($selectedThumb[4], $result->first_row(), $formData['x1'], $formData['y1'], $formData['width'] , $formData['height']);
		}

		$objResponse->addAssign("submitButton","value","Save");
		$objResponse->addAssign("submitButton","disabled",false);

		return $objResponse;
	}

	private function processImage($data, $form_value, &$ThumbDetails, $photo) {
		switch ($data['file_type']) {
			case 'image/gif':
				$image = imagecreatefromgif($data['full_path']);
				break;
			case 'image/jpeg':
				$image = imagecreatefromjpeg($data['full_path']);
				break;
			case 'image/png':
				$image = imagecreatefrompng($data['full_path']);
				break;
		}
		if ($data['image_width'] > VIEW_WIDTH) {
			$ratio_orig = $data['image_width']/$data['image_height'];
			$width = VIEW_WIDTH;
			$height = VIEW_HEIGHT;
			if (VIEW_WIDTH/VIEW_HEIGHT > $ratio_orig) {
			   $width = VIEW_HEIGHT*$ratio_orig;
			} else {
			   $height = VIEW_WIDTH/$ratio_orig;
			}
			$newImage = imagecreatetruecolor($width, $height);
			imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $data['image_width'], $data['image_height']);
		} else {
			$newImage = $image;
		}
		$x = imagesx($newImage);
		$y = imagesy($newImage);
		
		if ($photo) {
			$info = array('author_id' => $this->ci->user_auth->entityId,
			              'title'     => $this->ci->input->post('title'.$form_value),
			              'x'         => $x,
			              'y'         => $y,
			              'mime'      => $data['file_type'],);
			$id = $this->ci->image->add('photo', &$newImage, $info);
			if ($id === false) {
				return false;
			} else {
				foreach ($ThumbDetails->result() as $Thumb) {
					$_SESSION['img'][] = array('list' => $id, 'type' => $Thumb->image_type_id);
					$output[] = array('title'  => $this->ci->input->post('title'.$form_value).' - '.$Thumb->image_type_name,
					                  'string' => '/photos/full/'.$id.'|'.$x.'|'.$y.'|'.$Thumb->image_type_id.'|'.$id.'|'.$Thumb->image_type_width.'|'.$Thumb->image_type_height);
				}
			}
		} else {
			foreach ($ThumbDetails->result() as $Thumb) {
				$_SESSION['img'][] = array('list' => count($_SESSION['img']),
				                           'type' => $Thumb->image_type_id);
				$output[] = array('title'  => $this->ci->input->post('title'.$form_value).' - '.$Thumb->image_type_name,
				                  'string' => '/tmp/uploads/'.$data['file_name'].'|'.$x.'|'.$y.'|'.$Thumb->image_type_id.'|'.count($output).'|'.$Thumb->image_type_width.'|'.$Thumb->image_type_height);
			}
		}
		return $output;
	}
}
?>