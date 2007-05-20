<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Image {
	
	private $ci;

	public function Image {
		$this->ci = &get_instance();
	}
	
	public function getPhoto($photoID) {
		$data = $this->get($photoID, 'photos');
		return '<img src="/photo/'.$photoID.'" height="'.$data->photo_height.'" width="'.$data->photo_width.'" alt="'.$data->photo_title.'" title="'.$data->photo_title.'" />';
	}
	
	public function getThumb($photoID, $type, $viewLarge = false, $extraTags = array(), $extraArguements = array()) {
		$data = $this->get($photoID, 'thumbs', $type);
		$tagInner = '';
		foreach ($extraTags as $name => $value) $tagInner.= $name.'="'.$value.'" ';
		$tag = '<img src="/photo/'.$type.'/'.$photoID.'" height="'.$data->image_type_height.'" width="'.$data->image_type_width.'" alt="'.$data->photo_title.'" title="'.$data->photo_title.'" '.$tagInner.'/>';
		if ($viewLarge) $tag = '<a href="/photo/full/'.$photoID.'">'.$tag.'</a>';
		return $tag;
	}
	
	public function getImage($imageID, $type, $extraTags = array(), $extraArguements = array()) {
		$data = $this->get($imageID, 'images');
		foreach ($extraTags as $name => $value) $tagInner.= $name.'="'.$value.'" ';
		return '<img src="/image/'.$type.'/'.$imageID.'" height="'.$data->image_type_height'" width="'.$data->image_type_width.'" alt="'.$data->image_title.'" title="'.$data->image_title.'" '.$tagInner.'/>';
	}
	
	private function get($id, $table, $type = null) {
		switch ($table) {
			case "photos":
				$sql = 'SELECT photo_title, photo_width, photo_height, photo_gallery, photo_complete, photo_deleted
				        FROM photos
				        WHERE photo_id = ? LIMIT 1';
				$result = $this->ci->db->query($sql, array($id));
				break;
			case 'thumbs':
				$sql = 'SELECT image_type_width, image_type_height, photo_title, photo_gallery, photo_deleted
				        FROM photos, photo_thumbs, image_types
				        WHERE photo_id = photo_thumbs_photo_id
				          AND photo_thumbs_image_type_id = image_type_id
				          AND photo_id = ?
				          AND image_type_codename = ?
				        LIMIT 1';
				$result = $this->ci->db->query($sql, array($id, $type));
				break;
			case 'images':
				$sql = 'SELECT image_title, image_type_width, image_type_height
				        FROM images, image_types
				        WHERE image_id = ?
				          AND image_type_id = image_image_type_id
				        LIMIT 1';
				$result = $this->ci->db->query($sql, array($id));
				break;
			default:
				return false;
		}
		if ($result->num_rows() == 1 and $table == 'images') {
			return $result->first_row();
		} elseif ($result->num_rows() == 1 and $result->first_row()->photo_deleted = 0) {
			return $result->first_row();
		} else {
			return $buggered; //TODO NULLS
		}
	}
	
	public function add($type, &$image, $info = array()) {
		$image = file_get_contents($location);
		switch ($type) {
			case 'photo':
				$sql = 'INSERT INTO photos (photo_author_user_entity_id, photo_title, photo_width, photo_height, photo_mime, photo_data)
				        VALUES (?, ?, ?, ?, ?, '.$image.')'; // We don't want the binary escaped
				$this->ci->db->query($sql, array($info['author_id'], $info['title'], $info['x'], $info['y'], $info['mime']));
				break;
			case 'image':
				if ($info['type_id']) {
					$id = $CI->db->select('image_image_type_id')->getwhere('images', array('image_id' => $info['type_id']), 1)->first_row()->image_image_type_id;
				} else {
					$id = $info['type_code'];
				}
				$sql = 'INSERT INTO images (image_title, image_image_type_id, image_mime, image_data)
				        VALUES (?, ?, ?, '.$image.')'; // We don't want the binary escaped
				$this->ci->db->query($sql, array($info['title'], $id, $info['mime']));
				break;
			default:
				return false;
		}
		return $this->ci->db->last_insert_id();
	}
	
	public function delete($type, $id, $image_type = null) {
		switch($type) {
			case 'photo':
				//set switch to deleted
				$sql = 'DELETE FROM photos WHERE photo_id = ? LIMIT 1';
				if ($this->ci->db->simple_query($sql, array($id)) {
					return true;
				}
				break;
			case 'image':
				//delete from db
				$sql = 'DELETE FROM images WHERE image_id = ? LIMIT 1';
				if ($this->ci->db->simple_query($sql, array($id)) {
					return true;
				}
				break;
			case 'thumb':
				//delete from db
				$sql = 'DELETE FROM photo_thumbs WHERE photo_thumbs_photo_id = ? and photo_thumbs_image_type_id = ? LIMIT 1';
				if ($this->ci->db->simple_query($sql, array($id, $image_type)) {
					return true;
				}
				break;
		}
		return false;
	}
	
	public function thumbnail($photoID, $type = array(), $x1, $y1, $x2, $y2) {
		
		function image2string($image, $mime) {
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
		
		//GRAB
		$sql = 'SELECT photo_data, photo_mime FROM photos WHERE photo_id = ? LIMIT 1';
		$result = $this->ci->db->query($sql, array($photoID));
		if ($result->num_rows() == 1) {
			$result = $result->first_row();
			$image = imagecreatefromstring($result->photo_data);
		} else {
			return false;
		}
		//CROP resized too
		if (!imagecopyresampled($image, $newimage, 0, 0, $x1, $y1, $finalx, $finaly, $type->x, $type->y)) {
			return false;
		}
		//STORE
		$image = image2string($image, $result->photo_mime);
		$sql = 'INSERT INTO photo_thumbs VALUES (?, ?, '.$image.')';
		$this->ci->db->query($sql, array($photoID, $type->id));
		return true;
	}