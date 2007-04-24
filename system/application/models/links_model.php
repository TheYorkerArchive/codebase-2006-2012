<?php
/*
 * Model for managment of user quick links
 * 
 *
 * \author Alex Fargus (agf501)
 *
 *
 *
 */
class Links_Model extends Model {
	/*
	 * Constructor, calls default model constructor
	 */
	function Home_Model() {
		parent::Model();
	}
	/*
	 * Adds a user link with default image to database
	 */
	function AddLink($name, $url, $nominated) {
		$sql = 'INSERT INTO links(link_url,link_name,link_nominated) VALUES (?, ?, ?)';
		$query = $this->db->query($sql,array($url, $name, $nominated));
		return $this->db->insert_id();
	}
	/*
	 * Removes user link from database (+ any non default or official pics)
	 * Unknown what will happen if user has same link twice
	 */
	function DeleteLink($user,$id) {
		$this->db->trans_start();
		$rmimage = False;
		$sql = 'SELECT link_official, link_image_id 
			FROM links WHERE link_id = ?';
		$query = $this->db->query($sql,array($id));
		$row = $query->result();
		if ($row->link_id == 0) {
			$sql ='DELETE FROM links WHERE link_id = ?';
			$this->db->query($sql,array($id));
			$sql = 'DELETE FROM images WHERE image_id = ?';
			$this->db->query($sql,array($row->link_image_id));
			$rmimage = True;
		}
		$sql = 'SELECT user_link_order FROM user_links 
			WHERE user_link_link_id = ?';
		$query = $this->db->query($sql,array($id));
		$position = $query->result()->user_link_order;
		$sql ='DELETE FROM user_links WHERE user_link_link_id = ?';
		$this->db->query($sql,array($id));
		$sql = 'SELECT user_link_id, user_link_order 
			FROM user_links WHERE user_link_user_entity_id = ?';
		$query = $this->db->query($sql,array($user));
		foreach ($query->result() as $row){
			if ($row->user_link_order > $position) {
				$sql = 'UPDATE user_links 
					SET user_link_order = (user_link_order - 1) 
					WHERE user_link_id = ?';
				$this->db->query($sql,array($row->user_link_id));
			}
		}
		$this->db->trans_complete();
		if ($rmimage){
			$this->load->helper('images_helper');
			delete(photoLocation($row->link_image_id));
		}
	}

	function ModifyLink() {
		echo "not impl.";
	}

	/*
	 * Makes a link an officical link, cannot be undone!
	 */
	function PromoteLink($user) {
		$sql = 'UPDATE links SET link_official = 1, link_editor_entity_id = ? WHERE link_id = ?';
		$this->db->query($sql,array($user));
	}

	/*
	 * Takes an array of link ids and orders them in the order of the array
	 * Returns false on error.
	 */
	function SetUserLinkOrder($user,$ordered_array){
		$this->db->trans_start();
		$sql = 'SELECT user_link_id FROM user_links WHERE user_link_user_entity_id = ?';
		$query = $this->db->query($sql,array($user));
		if (sizeof($ordered_arry) == $query->num_rows()) {
			for ($i = 0; $i <= sizeof($ordered_array); $i++){
				$sql = 'UPDATE user_links SET user_link_order = ? WHERE user_link_id = ?';
				$this->db->query($sql,array($i,$ordered_array[$i]));
			}
			$this->db->trans_complete();
			return True;
		} else {
			$this->db->trans_rollback();
			return False;
		}
	}

	function DropUserLinks($user) {
		//TODO Check Link if link is not official, so we can delete it totally
		$sql = 'DELETE FROM user_links WHERE user_link_user_entity_id= ?';
		return $this->db->query($sql, array($user));
	}

	/*
	 * Returns an array with users images + links
	 */
	function GetUserLinks($user) {
		$sql = 'SELECT link_id, link_url,link_name,link_image_id, image_file_extension
			FROM images, links
			INNER JOIN user_links
			ON user_link_link_id = links.link_id
			WHERE image_id = link_image_id
			AND user_link_user_entity_id = ?
			ORDER BY user_link_order ASC';
		return $this->db->query($sql, array($user));
	}

	function GetAllOfficialLinks() {
		$sql = 'SELECT link_id, link_url,link_name,link_image_id
			FROM links
			WHERE link_official = 1
			ORDER BY link_name ASC';
		$query = $this->db->query($sql);
		return $query;
	}

	function ReplaceImage() {
		echo "notimp";
	}

	function UserTotalLinks($user) {
		$sql = 'SELECT COUNT(*) AS total FROM user_links WHERE user_link_user_entity_id = ?';
		return $this->db->query($sql, array($user))->first_row()->total;
	}

	function AddUserLink($user, $link) {
		$sql = 'INSERT INTO user_links
		        (user_link_user_entity_id, user_link_link_id, user_link_order)
		        VALUES (?, ?, ?)';
		$this->db->query($sql, array($user, $link, $this->UserTotalLinks($user)));
	}

	function AddUserLinks($user, $links) {
		$first = true;
		$sql = 'INSERT INTO user_links (user_link_user_entity_id, user_link_link_id, user_link_order) VALUES';
		for ($i = 0; $i < count($links); $i++) {
			if (!$first) {
				$sql.= ',';
			} else {
				$first = false;
			}
			$sql.= ' ('.$user.', ?, '.$i.')';
		}
		
		return $this->db->query($sql, $links);
	}
}
?>
