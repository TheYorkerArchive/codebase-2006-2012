<?php
/**
 * This model retrieves for Slideshows of any type.
 *
 * @author Nick Evans
 * @author Mark Goodall
 */
class Slideshow extends Model {

	function Slideshow()
	{
		// Call the Model constructor
		parent::Model();
	}

	//Assumes a directory slideshow (used for reviews as well since 17/5/2008)
	function getFirstPhotoIDFromSlideShow($organisation_codename){
		$sql = "
		SELECT 
			organisation_slideshows.organisation_slideshow_photo_id AS photo_id
		FROM organisations 
		INNER JOIN organisation_slideshows ON
			organisation_slideshows.organisation_slideshow_organisation_entity_id = organisations.organisation_entity_id
		WHERE organisations.organisation_directory_entry_name = ?
		ORDER BY organisation_slideshows.organisation_slideshow_order ASC 
		LIMIT 1";
		$query = $this->db->query($sql,$organisation_codename);
		if($query->num_rows() == 1)
		{
			return $query->row()->photo_id;
		}else{
			return null;
		}
	}
	/**
	 * This grabs photos from an organisation in order
	 * @param organisation_type_id Is the type of organisations to return
	 * @return Result object
	 */
	function getPhotos($organisation_id, $contextType = null) {
		//This is why its better not to affix table names to columns...
		if (is_null($contextType)) {
			$query = $this->db->select('*')->from('photos');
			$query = $query->join('organisation_slideshows', 'organisation_slideshow_photo_id = photo_id');
			$query = $query->where('organisation_slideshow_organisation_entity_id', $organisation_id);
			$result = $query->orderby('organisation_slideshow_order', 'asc')->get();
		} else {
			$query = $this->db->select('*')->from('photos');
			$query = $query->join('review_context_slideshows', 'review_context_slideshow_photo_id = photo_id');
			$query = $query->where('review_context_slideshow_organisation_entity_id', $organisation_id);
			//one line different...
			$query = $query->where('review_context_slideshow_content_type_id', $contextType);
			$result = $query->orderby('review_context_slideshow_order', 'asc')->get();
		}

		return $result;
	}
	
	/* pingus version of the getPhotos function as he couldn't get it to work 
	    if $is_string
		= true then $content_type can be 'food', 'drink' etc
		= false then $content_type can be 7, 8, 9 (an id) */
	function GetReviewPhotos($organisation_id, $content_type, $is_string)
	{
		if ($is_string == true)
		{
			$sql = 'SELECT content_type_id
					FROM content_types
					WHERE content_type_codename = ?';
			$query = $this->db->query($sql,array($content_type));
			$row = $query->row();
			if ($query->num_rows() == 1)
			{
				$content_type = $row->content_type_id;
			}
			else
			{
				$content_type = null;
			}
		}		
		if (isset($content_type))
		{
			$sql = 'SELECT	photo_id,
							photo_title,
							review_context_slideshow_order
					FROM	photos
					JOIN	review_context_slideshows ON review_context_slideshow_photo_id = photo_id
					WHERE	review_context_slideshow_organisation_entity_id = ?
					AND		review_context_slideshow_content_type_id = ?
					AND		photo_deleted = 0
					ORDER BY review_context_slideshow_order ASC ';
			$query = $this->db->query($sql,array($organisation_id, $content_type));
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$result[$row->review_context_slideshow_order] = array(
						'id'=>$row->photo_id,
						'title'=>$row->photo_title
						);
				}
				return $result;	
			}
			else
			{
				return array();
			}
		}
		else
		{
			return array();
		}
	}
	
	function pushUp($photo_id, $organisation_id, $contextType = null, $order = 'asc') {
		//ditto
		if (is_null($contextType)) {
			$query = $this->db->orderby('organisation_slideshow_order', $order)->getwhere('organisation_slideshows', array('organisation_slideshow_organisation_entity_id' => $organisation_id), 1);
			foreach($query->result() as $result){
				if ($photo_id == $result->organisation_slideshow_photo_id) {
					return false;
				}
			}
			if ($order == 'asc') {
				$sql = 'UPDATE organisation_slideshows
			            SET organisation_slideshow_order=organisation_slideshow_order-1
			            WHERE organisation_slideshow_organisation_entity_id=? AND organisation_slideshow_photo_id=?';
			} else {
				$sql = 'UPDATE organisation_slideshows
			            SET organisation_slideshow_order=organisation_slideshow_order+1
			            WHERE organisation_slideshow_organisation_entity_id=? AND organisation_slideshow_photo_id=?';
			}
			$this->db->query($sql, array($organisation_id, $photo_id));
			$result = $this->db->getwhere('organisation_slideshows', array('organisation_slideshow_organisation_entity_id' => $organisation_id, 'organisation_slideshow_photo_id' => $photo_id), 1);
			foreach ($result->result() as $row) {
				if ($order == 'asc') {
					$sql = 'UPDATE organisation_slideshows
					        SET organisation_slideshow_order=organisation_slideshow_order+1
					        WHERE organisation_slideshow_organisation_entity_id=? AND (NOT organisation_slideshow_photo_id=?) AND organisation_slideshow_order=?';
				} else {
					$sql = 'UPDATE organisation_slideshows
					        SET organisation_slideshow_order=organisation_slideshow_order-1
					        WHERE organisation_slideshow_organisation_entity_id=? AND (NOT organisation_slideshow_photo_id=?) AND organisation_slideshow_order=?';
					
				}
				$this->db->query($sql, array($organisation_id, $photo_id, $row->organisation_slideshow_order));
			}
		} else {
			$query = $this->db->orderby('review_context_slideshow_order', $order)->getwhere('review_context_slideshows', array('review_context_slideshow_organisation_entity_id' => $organisation_id), 1);
			foreach($query->result() as $result) {
				if ($photo_id == $result->review_context_slideshow_photo_id) {
					return false;
				}
			}
			if ($order == 'asc') {
				$sql = 'UPDATE review_context_slideshows
			            SET review_context_slideshow_order=organisation_slideshow_order-1
			            WHERE review_context_slideshow_organisation_entity_id=? AND organisation_slideshow_photo_id=?';
			} else {
				$sql = 'UPDATE review_context_slideshows
			            SET organisation_slideshow_order=review_context_slideshow_order+1
			            WHERE organisation_slideshow_organisation_entity_id=? AND review_context_slideshow_photo_id=?';
			}
			$this->db->query($sql, array($organisation_id, $photo_id));
			$result = $this->db->getwhere('review_context_slideshows', array('review_context_slideshow_organisation_entity_id' => $organisation_id, 'review_context_slideshow_photo_id' => $photo_id), 1);
			foreach ($result->result() as $row) {
				if ($order == 'asc') {
					$sql = 'UPDATE review_context_slideshows
					        SET review_context_slideshow_order=review_context_slideshow_order+1
					        WHERE review_context_slideshow_organisation_entity_id=? AND (NOT review_context_slideshow_photo_id=?) AND review_context_slideshow_order=?';
				} else {
					$sql = 'UPDATE review_context_slideshows
					        SET review_context_slideshow_order=review_context_slideshow_order-1
					        WHERE review_context_slideshow_organisation_entity_id=? AND (NOT review_context_slideshow_photo_id=?) AND review_context_slideshow_order=?';
					
				}
				$this->db->query($sql, array($organisation_id, $photo_id, $row->review_context_slideshow_order));
			}
		}
		return true;
	}
	
	function pushDown($photo_id, $organisation_id, $contextType = null) {
		return $this->pushUp($photo_id, $organisation_id, $contextType, 'desc');
	}

	function deletePhoto($photo_id, $organisation_id, $contextType = null) {
		return $this->db->delete('organisation_slideshows', array('organisation_slideshow_organisation_entity_id' => $organisation_id,
		                                                          'organisation_slideshow_photo_id' => $photo_id));
	}

	function addPhoto($photo_id, $organisation_id, $contextType = null) {
		$count = $this->db->query('SELECT COUNT(*) AS row_count FROM organisation_slideshows WHERE organisation_slideshow_organisation_entity_id = '.$organisation_id);
		$count = $count->first_row()->row_count;
		return $this->db->insert('organisation_slideshows', array('organisation_slideshow_organisation_entity_id' => $organisation_id,
		                                                          'organisation_slideshow_photo_id' => $photo_id,
		                                                          'organisation_slideshow_order' => $count));
	}
	//checks to see if an org has a photo
	function hasPhoto($photo_id, $organisation_id) {
		$count = $this->db->query('SELECT COUNT(*) AS row_count FROM organisation_slideshows WHERE organisation_slideshow_organisation_entity_id = '.$organisation_id.' AND organisation_slideshow_photo_id = '.$photo_id);
		$count = $count->first_row()->row_count;
		return ($count == 1);
	}

}
?>