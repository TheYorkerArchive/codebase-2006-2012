<?php
/**
 *Template for request_model,  still to be tested
 *@author Alex Fargus (agf501)
 **/

class Requests_Model extends Model
{
	function Requests_Model()
	{
		parent::Model();
	}

	// Retrieve list of all reporters (this includes editors and photographers) that a request can be assigned to
	function getReporters ()
	{
		$sql = 'SELECT user_entity_id AS id, user_firstname AS firstname, user_surname AS surname
			FROM users
			WHERE user_office_access = 1
			AND user_admin = 0
			ORDER BY user_firstname ASC, user_surname ASC';
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	function reportersExist ($reporter_array)
	{
		$sql = 'SELECT user_entity_id
			FROM users
			WHERE user_office_access = 1
			AND user_admin = 0
			AND (';
		for ($i = 1; $i <= count($reporter_array); $i++) {
			$sql .= 'user_entity_id = ? OR ';
		}
		$sql = substr($sql, 0, -4) . ')';
		$query = $this->db->query($sql, $reporter_array);
		return $query->num_rows() == count($reporter_array);
	}

	// Validation check to ensure selected article box exists
	function isBox ($box_codename)
	{
		$sql = 'SELECT content_type_id
			FROM content_types
			WHERE content_type_codename = ?
			AND content_type_section != \'hardcoded\'';
		$query = $this->db->query($sql, array($box_codename));
		return $query->num_rows();
	}

	// Retrieve a list of all the boxes a request can be assigned to
	function getBoxes ()
	{
		$sql = 'SELECT content_type_id AS id, content_type_name AS name, content_type_codename AS code, content_type_has_children AS subcats
			FROM content_types
			WHERE content_type_parent_content_type_id IS NULL
			AND content_type_section != \'hardcoded\'
			ORDER BY content_type_section_order ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if ($row->subcats) {
					$get_sub_cats = '
						SELECT content_type_id AS id, content_type_name AS name
						FROM content_types
						WHERE content_type_parent_content_type_id = ?
						AND content_type_section != \'hardcoded\'
						ORDER BY content_type_section_order ASC';
					$sub_cats = $this->db->query($get_sub_cats, array($row->id));
					if ($sub_cats->num_rows() > 0) {
						foreach ($sub_cats->result() as $category) {
							$result[] = array(
								'id' => $category->id,
								'name' => $row->name . ' - ' . $category->name,
								'code' => $row->code
							);
						}
					}
				} else {
					$result[] = array(
						'id' => $row->id,
						'name' => $row->name,
						'code' => $row->code
					);
				}
			}
		}
		return $result;
	}

	//Add a  new request to the article table
	function CreateRequest($status,$type_codename,$title,$description,$user,$date)
	{
		$sql = 'SELECT 	content_type_id
			FROM	content_types
			WHERE	(content_type_codename = ?)';
		$query = $this->db->query($sql,array($type_codename));
		if ($query->num_rows() == 1)
		{
			$type_id = $query->row()->content_type_id;
			if ($status == 'suggestion')
			{
				$this->db->trans_start();
				$sql = 'INSERT INTO articles(
						article_content_type_id,
						article_created,
						article_request_title,
						article_request_description,
						article_suggestion_accepted,
						article_request_entity_id)
					VALUES (?,CURRENT_TIMESTAMP,?,?,0,?)';
				$this->db->query($sql,array($type_id,$title,$description,$user));
				$sql = 'SELECT 	article_id
					FROM	articles
					WHERE	(article_id=LAST_INSERT_ID())';
				$query = $this->db->query($sql);
				$id = $query->row()->article_id;
				$this->db->trans_complete();
		
				return $id;
			}
			elseif ($status == 'request')
			{
				$this->db->trans_start();
				$sql = 'INSERT INTO articles(
						article_content_type_id,
						article_created,
						article_request_title,
						article_request_description,
						article_suggestion_accepted,
						article_request_entity_id,
						article_editor_approved_user_entity_id,
						article_publish_date)
					VALUES (?,CURRENT_TIMESTAMP,?,?,1,?,?,?)';
				$query = $this->db->query($sql,array($type_id,$title,$description,$user,$user,$date));
				$sql = 'SELECT 	article_id
					FROM	articles
					WHERE	(article_id=LAST_INSERT_ID())';
				$query = $this->db->query($sql);
				$id = $query->row()->article_id;
				$this->db->trans_complete();
		
				return $id;
			}
			else
			{
				return FALSE;
			}
		}
		else
			return FALSE;
	}

	//Make a change to a request status in the article table
	function UpdateRequestStatus($article_id,$status,$data)
	{
		if ($status == 'request')
		{
			$sql = 'UPDATE 	articles
				SET	article_suggestion_accepted = 1,
					article_editor_approved_user_entity_id = ?,
					article_publish_date = ?,
					article_request_title = ?,
					article_request_description = ?,
					article_content_type_id = ?
				WHERE	(article_id = ?)';
			$query = $this->db->query($sql,array($data['editor'],$data['publish_date'],$data['title'],$data['description'],$data['content_type'],$article_id));
		}
		else if ($status == 'publish')
		{
			$sql = 'UPDATE 	articles
				SET	article_live_content_id = ?,
					article_publish_date = ?,
					article_editor_approved_user_entity_id = ?,
					article_pulled = 0
				WHERE	(article_id = ?)';
			$query = $this->db->query($sql,array($data['content_id'],$data['publish_date'],$data['editor'],$article_id));
		}

		return $status;
	}

	//Make a change to a request status in the article table
	function UpdatePulledToRequest($article_id, $editor_id)
	{
		$sql = 'UPDATE 	articles
			SET	article_pulled = 0,
				article_editor_approved_user_entity_id = ?,
				article_publish_date = CURRENT_TIMESTAMP,
				article_live_content_id = NULL
			WHERE	(article_id = ?)';
		$query = $this->db->query($sql,array($editor_id, $article_id));
	}

	//Make a change to the title, description and content type of a suggestion
	function UpdateSuggestion($article_id,$data)
	{
		$sql = 'UPDATE 	articles
			SET	article_request_title = ?,
				article_request_description = ?,
				article_content_type_id = ?
			WHERE	(article_id = ?)';
		$query = $this->db->query($sql,array($data['title'],$data['description'],$data['content_type'],$article_id));
	}

	//Make a change to the content type of an article
	function UpdateContentType($article_id,$content_type)
	{
		$sql = 'UPDATE 	articles
			SET	article_content_type_id = ?
			WHERE	(article_id = ?)';
		$query = $this->db->query($sql,array($content_type,$article_id));
	}

	//can also use the GetPublishedArticles to get more data setting is_pulled to TRUE
	function GetPulledArticles($type_id)
	{
		$sql = 'SELECT	article_id
			FROM	articles
			LEFT JOIN content_types
			ON 	article_content_type_id = content_type_id
			WHERE	(content_type_codename = ?
			AND	article_pulled = 1)
			ORDER BY article_publish_date DESC';
		$query = $this->db->query($sql,array($type_id));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result[] = $row->article_id;
			}
		}
	}

	//Should this be get completed articles?
	function GetPublishedArticles($type_codename, $is_published, $is_pulled = FALSE)
	{
		$sql = 'SELECT 	content_type_id
			FROM	content_types
			WHERE	(content_type_codename = ?)';
		$query = $this->db->query($sql,array($type_codename));
		if ($query->num_rows() == 1)
		{
			$type_id = $query->row()->content_type_id;
			$sql = 'SELECT	article_id,
				UNIX_TIMESTAMP(article_publish_date) as article_publish_date,
				article_live_content_id,
				UNIX_TIMESTAMP(article_content_last_author_timestamp) as article_content_last_author_timestamp,
				article_content_heading,
	                        article_content_last_author_user_entity_id,
				article_editor_approved_user_entity_id,
				author_user.business_card_name as author_name,
				editor_user.business_card_name as editor_name
	
				FROM	articles
				JOIN	article_contents
				ON      article_content_id = article_live_content_id
	
				JOIN	business_cards as editor_user
				ON	editor_user.business_card_user_entity_id = article_editor_approved_user_entity_id
				JOIN	business_cards as author_user
				ON	author_user.business_card_user_entity_id = article_content_last_author_user_entity_id
	
				WHERE	article_suggestion_accepted = 1
				AND	article_content_type_id = ?
				AND	article_live_content_id IS NOT NULL
				AND	article_deleted = 0
				AND	article_pulled = ?';
			if ($is_published == TRUE)
				$sql .= ' AND	article_publish_date <= CURRENT_TIMESTAMP';
			else
				$sql .= ' AND	article_publish_date > CURRENT_TIMESTAMP';
			$query = $this->db->query($sql,array($type_id, $is_pulled));
			$result = array();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$result_item = array(
						'id'=>$row->article_id,
						'heading'=>$row->article_content_heading,
						'publish'=>$row->article_publish_date,
						'lastedit'=>$row->article_content_last_author_timestamp,
						'editorid'=>$row->article_editor_approved_user_entity_id,
						'editorname'=>$row->editor_name,
						'authorid'=>$row->article_content_last_author_user_entity_id,
						'authorname'=>$row->author_name
						);
					$result[] = $result_item;
				}
			}
			return $result;
		}
	}

	function GetRequestedArticle($article_id)
	{
		$sql = 'SELECT	article_id,
				article_request_title,
				article_request_description,
				article_content_type_id,
				article_request_entity_id,
				UNIX_TIMESTAMP(article_publish_date) as article_publish_date,
				article_request_entity_id,
				article_editor_approved_user_entity_id,
				suggestion_user.business_card_name as suggestion_name,
				editor_user.business_card_name as editor_name
			FROM	articles

			JOIN	business_cards as editor_user
			ON	editor_user.business_card_user_entity_id = article_editor_approved_user_entity_id
			JOIN	business_cards as suggestion_user
			ON	suggestion_user.business_card_user_entity_id = article_request_entity_id
			
			WHERE	article_suggestion_accepted = 1
			AND	article_id = ?
			AND	article_live_content_id IS NULL
			AND	article_deleted = 0
			AND	article_pulled = 0';
		$query = $this->db->query($sql,array($article_id));
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$sql = 'SELECT content_type_name
				FROM content_types
				WHERE content_type_id = ?';
			$query = $this->db->query($sql, array($row->article_content_type_id));
			$row2 = $query->row();
			$result = array(
				'id'=>$row->article_id,
				'title'=>$row->article_request_title,
				'description'=>$row->article_request_description,
				'deadline'=>$row->article_publish_date,
				'box'=>$row->article_content_type_id,
				'box_name'=>$row2->content_type_name,
				'suggestionuserid'=>$row->article_request_entity_id,
				'suggestionusername'=>$row->suggestion_name,
				'editorid'=>$row->article_editor_approved_user_entity_id,
				'editorname'=>$row->editor_name
				);
		}

		return $result;
	}

	function GetRequestedArticles($type_codename, $get_children = TRUE)
	{
		$sql = 'SELECT content_type_id,
				 content_type_has_children
				FROM content_types
				WHERE content_type_codename = ?';
		$query = $this->db->query($sql,array($type_codename));
		if ($query->num_rows() == 1)
		{
			$row = $query->row();
			$type_codenames = array($type_codename);
			$type_sql = array('content_types.content_type_codename = ?');
			if ($get_children == TRUE)
			{
				if ($row->content_type_has_children) {
					$sql = 'SELECT content_type_codename
							FROM content_types
							WHERE content_type_parent_content_type_id = ?';
					$query = $this->db->query($sql,array($row->content_type_id));
					if ($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
							$type_codenames[] = $row->content_type_codename;
							$type_sql[] = 'content_types.content_type_codename = ?';
						}
					}
				}
			}
	
			$sql = 'SELECT articles.article_id,
					 UNIX_TIMESTAMP(articles.article_created) AS article_created,
					 articles.article_request_title,
					 content_types.content_type_name
					FROM articles, content_types
					WHERE articles.article_suggestion_accepted = 1
					AND	content_types.content_type_id = articles.article_content_type_id
					AND	articles.article_live_content_id IS NULL
					AND	articles.article_deleted = 0
					AND	articles.article_pulled = 0
					AND	(';
			$sql .= implode(' OR ',$type_sql) . ')';
			$query = $this->db->query($sql,$type_codenames);
			$result = array();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$result_item = array(
						'id'=>$row->article_id,
						'created'=>$row->article_created,
						'title'=>$row->article_request_title,
						'box'=>$row->content_type_name
						);
					$result_item['reporters'] = $this->GetWritersForArticle($result_item['id']);
					$result[] = $result_item;
				}
			}
	
			return $result;
		}
		else
			return FALSE;
	}
	
	function GetRequestsForUser($user_id)
	{
		$sql = 'SELECT articles.article_id,
				 UNIX_TIMESTAMP(articles.article_created) AS article_created,
				 articles.article_request_title,
				 content_types.content_type_name AS box_name
				FROM article_writers, articles, content_types
				WHERE article_writers.article_writer_user_entity_id = ?
				AND article_writers.article_writer_article_id = articles.article_id
				AND articles.article_suggestion_accepted = 1
				AND	content_types.content_type_id = articles.article_content_type_id
				AND	articles.article_live_content_id IS NULL
				AND	articles.article_deleted = 0
				AND	articles.article_pulled = 0
				ORDER BY content_types.content_type_name ASC, articles.article_created ASC';				
		$query = $this->db->query($sql,array($user_id));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item = array(
					'id'=>$row->article_id,
					'created'=>$row->article_created,
					'title'=>$row->article_request_title,
					'box'=>$row->box_name
					);
				$result_item['reporters'] = $this->GetWritersForArticle($result_item['id']);
				$result[] = $result_item;
			}
		}
		return $result;
	}

	function GetSuggestedArticle($article_id)
	{
		$sql = 'SELECT	article_id,
				article_request_title,
				article_content_type_id,
				article_request_description,
				UNIX_TIMESTAMP(article_created) as article_created,
				article_request_entity_id,
				business_card_name
			FROM	articles
			JOIN	business_cards
			ON	business_card_user_entity_id = article_request_entity_id
			WHERE article_suggestion_accepted = 0
			AND	article_id = ?
			AND	article_deleted = 0
			AND	article_pulled = 0';
		$query = $this->db->query($sql,array($article_id));
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$sql = 'SELECT content_type_name
				FROM content_types
				WHERE content_type_id = ?';
			$query = $this->db->query($sql, array($row->article_content_type_id));
			$row2 = $query->row();
			$result = array(
				'id'=>$row->article_id,
				'title'=>$row->article_request_title,
				'description'=>$row->article_request_description,
				'box'=>$row->article_content_type_id,
				'box_name'=>$row2->content_type_name,
				'userid'=>$row->article_request_entity_id,
				'username'=>$row->business_card_name,
				'created'=>$row->article_created
				);
		}
		return $result;
	}

	function GetSuggestedArticles($type_codename, $get_children = TRUE)
	{
		$sql = 'SELECT content_type_id,
				 content_type_has_children
				FROM content_types
				WHERE content_type_codename = ?';
		$query = $this->db->query($sql,array($type_codename));
		if ($query->num_rows() == 1)
		{
			$row = $query->row();
			$type_codenames = array($type_codename);
			$type_sql = array('content_types.content_type_codename = ?');
			if ($get_children == TRUE)
			{
				if ($row->content_type_has_children) {
					$sql = 'SELECT content_type_codename
							FROM content_types
							WHERE content_type_parent_content_type_id = ?';
					$query = $this->db->query($sql,array($row->content_type_id));
					if ($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
							$type_codenames[] = $row->content_type_codename;
							$type_sql[] = 'content_types.content_type_codename = ?';
						}
					}
				}
			}

			$sql = 'SELECT articles.article_id,
					 UNIX_TIMESTAMP(articles.article_created) AS article_created,
					 articles.article_request_title,
					 article_request_entity_id,
					 business_card_name,
					 content_types.content_type_name
					FROM content_types, articles
					JOIN business_cards
					 ON business_card_user_entity_id = article_request_entity_id
					WHERE articles.article_suggestion_accepted = 0
					AND	content_types.content_type_id = articles.article_content_type_id
					AND	articles.article_live_content_id IS NULL
					AND	articles.article_deleted = 0
					AND	articles.article_pulled = 0
					AND	(';
			$sql .= implode(' OR ',$type_sql) . ')';
			$query = $this->db->query($sql,$type_codenames);
			$result = array();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$result_item = array(
						'id'=>$row->article_id,
						'title'=>$row->article_request_title,
						'box'=>$row->content_type_name,
						'userid'=>$row->article_request_entity_id,
						'username'=>$row->business_card_name,
						'created'=>$row->article_created
						);
					$result[] = $result_item;
				}
			}
			return $result;
		}
		else
			return FALSE;
	}

	function GetArticleRevisions($article_id)
	{
		$sql = 'SELECT	article_content_id,
				article_content_heading,
				article_content_last_author_user_entity_id,
				UNIX_TIMESTAMP(article_content_last_author_timestamp) AS article_content_last_author_timestamp,
				business_card_name
			FROM	article_contents

			JOIN	business_cards
			ON      business_card_user_entity_id = article_content_last_author_user_entity_id

			WHERE	article_content_article_id = ?
			ORDER BY	article_content_last_author_timestamp DESC';
		$query = $this->db->query($sql,array($article_id));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item = array(
					'id'=>$row->article_content_id,
					'title'=>$row->article_content_heading,
					'updated'=>$row->article_content_last_author_timestamp,
					'userid'=>$row->article_content_last_author_user_entity_id,
					'username'=>$row->business_card_name
					);
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	function GetArticleWriters($article_id)
	{
		$sql = 'SELECT	article_writer_user_entity_id
				business_card_name
			FROM	article_writers

			JOIN	business_cards
			ON	business_card_user_entity_id = article_writer_user_entity_id

			WHERE	article_writer_article_id = ?';
		$query = $this->db->query($sql,array($article_id));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result[] = array(
					'id'=>$row->article_writer_user_entity_id,
					'name'=>$row->business_card_name
					);
			}
		}
		return $result;
	}
	
	function GetWritersForType($type_codename)
	{
		$sql = 'SELECT 	content_type_id
			FROM	content_types
			WHERE	(content_type_codename = ?)';
		$query = $this->db->query($sql,array($type_codename));
		if ($query->num_rows() == 1)
		{
			$type_id = $query->row()->content_type_id;
			$sql = 'SELECT	user_entity_id,
					business_card_name
				FROM	content_types
	
				JOIN	organisations
				ON	organisation_entity_id = content_type_related_organisation_entity_id
				
				JOIN	subscriptions
				ON	subscription_organisation_entity_id = organisation_entity_id
				
				JOIN	users
				ON	user_entity_id = subscription_user_entity_id
	
				JOIN	business_cards
				ON	business_card_user_entity_id = user_entity_id
	
				WHERE	content_type_id = ?
				ORDER BY business_card_name ASC';
			$query = $this->db->query($sql,array($type_id));
			$result = array();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$result[] = array(
						'id'=>$row->user_entity_id,
						'name'=>$row->business_card_name
						);
				}
			}
			return $result;
		}
		else
			return FALSE;
	}
	
	function GetWritersForArticle($article_id)
	{
		$sql = 'SELECT	article_writer_user_entity_id,
				article_writer_status,
				business_card_name
			FROM	article_writers

			JOIN	business_cards
			ON	business_card_user_entity_id = article_writer_user_entity_id

			WHERE	article_writer_article_id = ?
			ORDER BY business_card_name ASC';
		$query = $this->db->query($sql,array($article_id));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result[] = array(
					'id'=>$row->article_writer_user_entity_id,
					'name'=>$row->business_card_name,
					'status'=>$row->article_writer_status
					);
			}
		}
		return $result;
	}
	
	function AddUserToRequest($article_id, $reporter_id, $editor_id)
	{
		$sql = 'INSERT INTO article_writers
				(
				 article_writer_user_entity_id,
				 article_writer_article_id,
				 article_writer_editor_accepted_user_entity_id
				)
				VALUES (?,?,?)';
		$this->db->query($sql,array($reporter_id, $article_id, $editor_id));
	}
	
	function RemoveUserFromRequest($article_id, $user_id)
	{
		$sql = 'DELETE FROM article_writers
			WHERE	(article_writer_article_id = ?
			AND	article_writer_user_entity_id = ?)';
		$this->db->query($sql,array($article_id, $user_id));
	}

	function RemoveAllUsersFromRequest($article_id)
	{
		$sql = 'DELETE FROM article_writers
			WHERE	(article_writer_article_id = ?)';
		$this->db->query($sql,array($article_id));
	}
	
	function AcceptRequest($article_id, $user_id)
	{
		$sql = 'UPDATE 	article_writers
			SET	article_writer_status = "accepted"
			WHERE	(article_writer_user_entity_id = ?
			AND	article_writer_article_id = ?)';
		$this->db->query($sql,array($user_id,$article_id));
	}
	
	function DeclineRequest($article_id, $user_id)
	{
		$sql = 'UPDATE 	article_writers
			SET	article_writer_status = "declined"
			WHERE	(article_writer_user_entity_id = ?
			AND	article_writer_article_id = ?)';
		$this->db->query($sql,array($user_id, $article_id));
	}
	
	function IsUserRequestedForArticle($article_id, $user_id)
	{
		$sql = 'SELECT	article_writer_status
			FROM	article_writers
			WHERE	article_writer_user_entity_id = ?
			AND	article_writer_article_id = ?';
		$query = $this->db->query($sql,array($user_id, $article_id));
		if ($query->num_rows() == 1)
		{
			$row = $query->row();
			return $row->article_writer_status;
		}
		else
			return FALSE;
	}
	
	function RequestPhoto($article_id,$user_id,$photo_position,$title,$description,$large_photo=1)
	{
		$sql = 'INSERT 	INTO photo_reqests(
				photo_request_user_entity_id,
				photo_request_article_id,
				photo_request_relative_photo_number,
				photo_request_view_large,
				photo_request_title,
				photo_request_description)
			VALUES	(?,?,?,?,?,?)';
		$query = $this->db->query($sql,array($user,$article_id,$photo_position,$large_photo,$title,$description));
	}
	
	function ApprovePhotoRequest($request_id,$photographer_id,$editor_id)
	{	//Photo editor must approve the request before it is passed on to the photographer
		$sql = 'UPDATE	photo_requests
			SET	photo_request_accepted_user_entity_id = ?
			WHERE 	(photo_request_id = ?);
			INSERT	INTO photo_request_users(
				photo_request_user_photo_request_id,
				photo_request_user_entity_id)
			VALUES	(?,?)';
		$query = $this->db->query($sql,array($editor_id,$request_id,$request_id,$photographer_id));			
	}

	function AcceptPhotoRequest($request_id,$user_id)
	{	//Photographer accepts photo request
		$slq = 'UPDATE 	photo_request_users
			SET	photo_request_user_status = "accepted"
			WHERE	(photo_request_user_photo_request_id = ?
			AND	photo_request_user_user_entity_id = ?)';
		$query = $this->db->query($sql,array($request_id,$user_id));
	}

	function RejectPhotoRequest($request_id,$user_id)
	{	//Photographer declines photo request
		$slq = 'UPDATE 	photo_request_users
			SET	photo_request_user_status = "declined"
			WHERE	(photo_request_user_photo_request_id = ?
			AND	photo_request_user_user_entity_id = ?)';
		$query = $this->db->query($sql,array($request_id,$user_id));
	}

	function SuggestPhoto($request_id,$photo_id,$comment,$recommended = 0)
	{	//Photographer reccomends a photo to be used
		$sql = 'INSERT	INTO photo_request_photos(
				photo_request_photo_photo_request_id,
				photo_request_photo_photo_id,
				photo_request_photo_comment,
				photo_request_photo_recommended)
			VALUES	(?,?,?,?)';
		$query = $this->db->query($sql,array($request_id,$photo_id,$comment,$recommended));			
	}

	function AcceptPhoto($request_id,$user_id,$photo_id)
	{	//Editor accepts the photo for use
		$sql = 'UPDATE	photo_requests
			SET	photo_request_approved_user_entity_id = ?,
				photo_request_chosen_photo_id = ?
			WHERE 	(photo_request_id = ?)';
		$query = $this->db->query($sql,array($user_id,$photo_id,$request_id));			
	}

	function GetPhotoRequests($id)
	{	//Return all photo_request ids for a given article
		$sql = 'SELECT	photo_request_id
			FROM	photo_requests
			WHERE	(photo_request_article_id = ?)';
		$query = $this->db->query($sql,array($id));
		$request_ids = array();
		foreach ($query->result() as $row)
		{
			$request_ids[] = $row->photo_request_id;
		}
		return	$request_ids;
	}

	function GetPhotoRequestDetails($request_id)
	{
		$sql = 'SELECT';	
	}

	function CreateArticleRevision($id,$user,$heading,$subheading,$subtext,$wikitext,$blurb)
	{
		$this->load->library('wikiparser');
		$cache = $this->wikiparser->parse($wikitext);
		$sql = 'INSERT	INTO article_contents(
				article_content_article_id,
				article_content_last_author_user_entity_id,
				article_content_heading,
				article_content_subheading,
				article_content_subtext,
				article_content_wikitext,
				article_content_wikitext_cache,
				article_content_blurb)
			VALUES	(?,?,?,?,?,?,?,?)';
		$query = $this->db->query($sql,array($id,$user,$heading,$subheading,$subtext,$wikitext,$cache,$blurb));
		return $this->db->insert_id();
	}

	function UpdateArticleRevision($id,$user,$heading,$subheading,$subtext,$wikitext,$blurb)
	{
		$this->load->library('wikiparser');
                $cache = $this->wikiparser->parse($wikitext);
		$sql = 'UPDATE	article_contents
			SET	article_content_last_author_user_entity_id = ?,
				article_content_heading = ?,
				article_content_subheading = ?,
				article_content_subtext = ?,
				article_content_wikitext = ?,
				article_content_wikitext_cache = ?,
				article_content_blurb = ?
			WHERE	(article_content_id = ?)';
		$query = $this->db->query($sql,array($user,$heading,$subheading,$subtext,$wikitext,$cache,$blurb,$id));
	}	

	function RejectSuggestion($id)
	{
		$sql = 'UPDATE	articles
			SET	article_deleted = 1
			WHERE	(article_id = ?)';
		$query = $this->db->query($sql,array($id));
	}
	
	function GetNameFromUsers($user_id)
	{
		$sql = 'SELECT	user_firstname,
				user_surname
			FROM	users
			WHERE	user_entity_id = ?';
		$query = $this->db->query($sql,array($user_id));
		if ($query->num_rows() == 1)
		{
			$row = $query->row();
			return $row->user_firstname.' '.$row->user_surname;
		}
		else
			return FALSE;
	}
	
	function GetNameFromBusinessCards($user_id)
	{
		$sql = 'SELECT	business_card_name
			FROM	business_cards
			WHERE	business_card_user_entity_id = ?';
		$query = $this->db->query($sql,array($user_id));
		if ($query->num_rows() == 1)
		{
			$row = $query->row();
			return $row->business_card_name;
		}
		else
			return FALSE;
	}
}
?>
