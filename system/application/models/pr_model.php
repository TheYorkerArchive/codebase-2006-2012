<?php

// Pr Model

class Pr_model extends Model {

	function Pr_Model()
	{
		parent::Model();
	}

	///	Return list of all organisations and thier Name of Place, Date of Last Review, Number of Reviews, and Info Complete status
	function GetContentTypeId($content_type_codename)
	{
		$sql = 'SELECT content_type_id FROM content_types WHERE content_type_codename = ?';
		$query = $this->db->query($sql, $content_type_codename );
		if ($query->num_rows() != 0) {
			return $query->row()->content_type_id;
		} else {
			return 0;
		}
	}
	function GetContentTypeNiceName($content_type_codename)
	{
		$sql = 'SELECT content_type_name FROM content_types WHERE content_type_codename = ?';
		$query = $this->db->query($sql, $content_type_codename );
		if ($query->num_rows() != 0) {
			return $query->row()->content_type_name;
		} else {
			return 0;
		}
	}

	///	Return list of all organisations and thier Name of Place, Date of Last Review, Number of Reviews, and Info Complete status
	function GetReviewContextListFromId($content_type)
	{

		$sql =
		'
		SELECT
			organisations.organisation_name as name,
			organisations.organisation_directory_entry_name as shortname,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,

			(
			 review_context_contents.review_context_content_blurb IS NOT NULL AND
			 review_context_contents.review_context_content_quote IS NOT NULL AND
			 review_context_contents.review_context_content_average_price IS NOT NULL AND
			 review_context_contents.review_context_content_recommend_item IS NOT NULL AND
			 review_context_contents.review_context_content_rating IS NOT NULL
			) as info_complete,
  	
			(
			 SELECT COUNT(*)
			 FROM league_entries
			 INNER JOIN leagues ON
			  league_entries.league_entry_league_id = leagues.league_id
			 WHERE league_entry_organisation_entity_id = organisations.organisation_entity_id 
			 AND leagues.league_content_type_id = ? 
			) as number_of_leagues,
			
			(
			 SELECT COUNT(*)
			 FROM organisation_tags
			 INNER JOIN tags ON
			  organisation_tags.organisation_tag_tag_id = tags.tag_id
			 LEFT OUTER JOIN tag_groups ON
			  tags.tag_id = tag_groups.tag_group_id
			 WHERE organisation_tag_organisation_entity_id = organisations.organisation_entity_id 
			 AND (tag_groups.tag_group_content_type_id = ? OR tag_groups.tag_group_content_type_id IS NULL)
			) as number_of_tags,
			
			(
			 SELECT UNIX_TIMESTAMP(MAX(article_publish_date))
			 FROM articles
			 WHERE
				articles.article_content_type_id = ?
			  AND
				articles.article_organisation_entity_id = organisations.organisation_entity_id
			  AND
			  	articles.article_deleted = 0
			  AND
			  	articles.article_live_content_id IS NOT NULL
			) as date_of_last_review,

			(
			 SELECT COUNT(*)
			 FROM articles
			 WHERE
				articles.article_content_type_id = ?
			  AND
				articles.article_organisation_entity_id = organisations.organisation_entity_id
			  AND
			  	articles.article_deleted = 0
			  AND
			  	articles.article_live_content_id IS NOT NULL
			) as review_count

		FROM organisations
		INNER JOIN review_contexts
		  ON
			review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		  AND
			review_contexts.review_context_content_type_id = ?
		  AND
			review_contexts.review_context_deleted = 0

		LEFT JOIN users
		  ON
		   	users.user_entity_id =  review_contexts.review_context_assigned_user_entity_id

		LEFT JOIN review_context_contents
		  ON
			review_contexts.review_context_live_content_id = review_context_contents.review_context_content_id

		WHERE organisation_parent_organisation_entity_id IS NULL

		ORDER BY organisations.organisation_name ASC
		';

		$query = $this->db->query($sql, array($content_type, $content_type, $content_type, $content_type, $content_type) );

		return $query->result_array();

	}
	
	function GetWorstVenuesForInformation($content_type_codename, $limit){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,
			review_context_contents.review_context_content_blurb IS NOT NULL as venue_has_blurb,
			review_context_contents.review_context_content_rating IS NOT NULL as venue_has_rating,
			review_context_contents.review_context_content_quote IS NOT NULL as venue_has_quote,
			review_context_contents.review_context_content_average_price IS NOT NULL as venue_has_average_price,
			review_context_contents.review_context_content_recommend_item IS NOT NULL as venue_has_recommend_item,
			review_context_contents.review_context_content_serving_times IS NOT NULL as venue_has_serving_times 
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		LEFT JOIN review_context_contents ON
		review_contexts.review_context_live_content_id = review_context_contents.review_context_content_id 
		LEFT JOIN users ON
		review_contexts.review_context_assigned_user_entity_id = users.user_entity_id
		WHERE content_types.content_type_codename=? AND review_contexts.review_context_deleted=0 
		ORDER BY venue_has_blurb ASC, venue_has_rating ASC, venue_has_quote ASC, venue_has_average_price ASC, venue_has_recommend_item ASC, venue_has_serving_times ASC LIMIT '.$limit;
		$query = $this->db->query($sql, array($content_type_codename));
		$result = array();
		//remove any venues with complete information
		foreach ($query->result_array() as $venue){
			if(!$venue['venue_has_blurb'] || !$venue['venue_has_rating'] || !$venue['venue_has_quote'] || !$venue['venue_has_average_price'] || !$venue['venue_has_recommend_item'] || !$venue['venue_has_serving_times']) $result[] = $venue;
		}
		return $result;
	}
	function GetWorstVenuesForReviews($content_type_codename, $limit){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,
			(
				SELECT UNIX_TIMESTAMP(MAX(article_publish_date))
				FROM articles
				WHERE articles.article_content_type_id = content_types.content_type_id
				AND articles.article_organisation_entity_id = venue_id
				AND articles.article_deleted = 0
				AND articles.article_live_content_id IS NOT NULL
			) as date_of_last_review,
			(
				SELECT COUNT(*)
				FROM articles
				WHERE articles.article_content_type_id = content_types.content_type_id
				AND articles.article_organisation_entity_id = venue_id
				AND articles.article_deleted = 0
				AND articles.article_live_content_id IS NOT NULL
			) as review_count
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		LEFT JOIN users ON
		review_contexts.review_context_assigned_user_entity_id = users.user_entity_id
		WHERE content_types.content_type_codename=? 
		AND review_contexts.review_context_deleted=0 		
		ORDER BY review_count ASC, date_of_last_review ASC, venue_name ASC LIMIT '.$limit;
		$query = $this->db->query($sql, array($content_type_codename));
		return $query->result_array();
	}
	function GetWorstVenuesForTags($content_type_codename, $limit){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,
			(
			 SELECT COUNT(*)
			 FROM organisation_tags
			 INNER JOIN tags ON
			  organisation_tags.organisation_tag_tag_id = tags.tag_id
			 LEFT OUTER JOIN tag_groups ON
			  tags.tag_id = tag_groups.tag_group_id
			 WHERE organisation_tag_organisation_entity_id = venue_id 
			 AND (tag_groups.tag_group_content_type_id = content_types.content_type_id OR tag_groups.tag_group_content_type_id IS NULL)
			) as tags_count
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		LEFT JOIN users ON
		review_contexts.review_context_assigned_user_entity_id = users.user_entity_id
		WHERE content_types.content_type_codename=? 
		AND review_contexts.review_context_deleted=0 		
		ORDER BY tags_count ASC, venue_name ASC LIMIT '.$limit;
		$query = $this->db->query($sql, array($content_type_codename));
		return $query->result_array();
	}
	function GetWorstVenuesForLeagues($content_type_codename, $limit){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,
			review_context_contents.review_context_content_rating as venue_rating,
			(
			 SELECT COUNT(*)
			 FROM league_entries
			 INNER JOIN leagues ON
			  league_entries.league_entry_league_id = leagues.league_id
			 WHERE league_entry_organisation_entity_id = venue_id 
			 AND leagues.league_content_type_id = content_types.content_type_id 
			) as leagues_count
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		LEFT JOIN review_context_contents ON
		review_contexts.review_context_live_content_id = review_context_contents.review_context_content_id 
		LEFT JOIN users ON
		review_contexts.review_context_assigned_user_entity_id = users.user_entity_id
		WHERE content_types.content_type_codename=? 
		AND review_contexts.review_context_deleted=0 		
		ORDER BY leagues_count ASC, venue_rating DESC, venue_name ASC LIMIT '.$limit;
		$query = $this->db->query($sql, array($content_type_codename));
		return $query->result_array();
	}
	/*
	* Warning reviews at the moment seems to be using a shared slideshow with the directory even though there is a slideshow table for reviews! Be warned this makes no sense!
	*
	* If at some point you want to convert to getting true review slideshows here is some code that might come in handy!
			(
				SELECT COUNT(*)
				FROM review_context_slideshows
				INNER JOIN photos ON
				review_context_slideshows.review_context_slideshow_photo_id = photos.photo_id
				WHERE review_context_slideshows.review_context_slideshow_content_type_id = content_types.content_type_id
				AND review_context_slideshows.review_context_slideshow_organisation_entity_id = venue_id
				
			) as photo_count
	*/
	//This returns all venues with no thumbnails. This removes venues with no thumbnails AND no images, because a thumbnail cant be made for it untill it gets an image.
	function GetVenuesWithoutThumbnails($content_type_codename,$thumbnail_size_codename='small'){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,
			(
				SELECT COUNT(*)
				FROM organisation_slideshows
				INNER JOIN photos ON
				organisation_slideshows.organisation_slideshow_photo_id = photos.photo_id
				WHERE organisation_slideshows.organisation_slideshow_organisation_entity_id = venue_id
			) as photo_count
		FROM review_contexts 
		INNER JOIN organisations ON
			review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
			review_contexts.review_context_content_type_id = content_types.content_type_id
		LEFT JOIN users ON
			review_contexts.review_context_assigned_user_entity_id = users.user_entity_id
		WHERE content_types.content_type_codename=? 
		AND review_contexts.review_context_deleted=0 
		AND NOT EXISTS(
				SELECT *
				FROM organisation_slideshows
				LEFT JOIN photo_thumbs ON
					organisation_slideshows.organisation_slideshow_photo_id = photo_thumbs.photo_thumbs_photo_id
				LEFT JOIN image_types ON
					photo_thumbs.photo_thumbs_image_type_id = image_types.image_type_id
				WHERE organisation_slideshows.organisation_slideshow_organisation_entity_id = organisation_entity_id
				AND image_types.image_type_codename = ?
				AND organisation_slideshows.organisation_slideshow_order = 
				( 
					SELECT MIN(os.organisation_slideshow_order)
					FROM organisation_slideshows AS os
					WHERE os.organisation_slideshow_organisation_entity_id = organisation_entity_id
				)
				LIMIT 1
			)
		ORDER BY photo_count DESC, venue_name ASC';
		$query = $this->db->query($sql, array($content_type_codename,$thumbnail_size_codename));
		$result = array();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row)
			{
				if($row->photo_count > 0){//Prune ones with no images, as a thumbnail cant be made if there is no image!
					$result_item['venue_id'] = $row->venue_id;
					$result_item['venue_shortname'] = $row->venue_shortname;
					$result_item['venue_name'] = $row->venue_name;
					$result_item['assigned_user_id'] = $row->assigned_user_id;
					$result_item['assigned_user_name'] = $row->assigned_user_name;
					$result_item['photo_count'] = $row->photo_count;
					$result[] = $result_item;
				}
			}
		}
		return $result;
	}
	/*
	* Warning reviews at the moment seems to be using a shared slideshow with the directory even though there is a slideshow table for reviews! Be warned this makes no sense!
	*
	* If at some point you want to convert to getting true review slideshows here is some code that might come in handy!
			(
				SELECT UNIX_TIMESTAMP(MAX(photos.photo_timestamp))
				FROM review_context_slideshows
				INNER JOIN photos ON
					review_context_slideshows.review_context_slideshow_photo_id = photos.photo_id
				WHERE review_context_slideshows.review_context_slideshow_content_type_id = content_types.content_type_id
				AND review_context_slideshows.review_context_slideshow_organisation_entity_id = venue_id
			) as date_of_last_photo,
			(
				SELECT COUNT(*)
				FROM review_context_slideshows
				INNER JOIN photos ON
				review_context_slideshows.review_context_slideshow_photo_id = photos.photo_id
				WHERE review_context_slideshows.review_context_slideshow_content_type_id = content_types.content_type_id
				AND review_context_slideshows.review_context_slideshow_organisation_entity_id = venue_id
				
			) as photo_count
	*/
	function GetWorstVenuesForPhotos($content_type_codename, $limit){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_contexts.review_context_assigned_user_entity_id as assigned_user_id,
			CONCAT(users.user_firstname, " ", users.user_surname) as assigned_user_name,
			(
				SELECT UNIX_TIMESTAMP(MAX(photos.photo_timestamp))
				FROM organisation_slideshows
				INNER JOIN photos ON
					organisation_slideshows.organisation_slideshow_photo_id = photos.photo_id
				WHERE organisation_slideshows.organisation_slideshow_organisation_entity_id = venue_id
			) as date_of_last_photo,
			(
				SELECT COUNT(*)
				FROM organisation_slideshows
				INNER JOIN photos ON
				organisation_slideshows.organisation_slideshow_photo_id = photos.photo_id
				WHERE organisation_slideshows.organisation_slideshow_organisation_entity_id = venue_id
			) as photo_count
		FROM review_contexts 
		INNER JOIN organisations ON
			review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
			review_contexts.review_context_content_type_id = content_types.content_type_id
		LEFT JOIN users ON
			review_contexts.review_context_assigned_user_entity_id = users.user_entity_id
		WHERE content_types.content_type_codename=? 
		AND review_contexts.review_context_deleted=0 
		ORDER BY photo_count ASC, date_of_last_photo ASC, venue_name ASC LIMIT '.$limit;
		$query = $this->db->query($sql, array($content_type_codename));
		return $query->result_array();
	}
	
	function GetUsersAssignedReviewVenues($user_id, $content_type_codename){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		WHERE review_contexts.review_context_assigned_user_entity_id=? AND content_types.content_type_codename=? 
		AND review_contexts.review_context_deleted=0 
		ORDER BY organisations.organisation_name ASC';
		$query = $this->db->query($sql, array($user_id, $content_type_codename));
		return $query->result_array();
	}
	
	//returns 1 if the user is assigned to the given venue and context, 0 otherwise.
	function IsUserAssignedToReviewVenue($content_type_codename, $org_short_name, $user_id=null){
		$sql = '
		SELECT COUNT(*) as user_has_venue
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		WHERE review_contexts.review_context_assigned_user_entity_id ';
		if(empty($user_id)){
			$sql .= 'IS NULL AND content_types.content_type_codename=? 
			AND organisations.organisation_directory_entry_name=?';
			$query = $this->db->query($sql, array($content_type_codename, $org_short_name));
		}else{
			$sql .= '=? AND content_types.content_type_codename=? 
			AND organisations.organisation_directory_entry_name=?';
			$query = $this->db->query($sql, array($user_id, $content_type_codename, $org_short_name));
		}
		return $query->row()->user_has_venue;
	}
	
	//Overwrites any assigned user to the provided user id, if no user id is given the assigned user will be removed.
	function AssignReviewVenueToUser($org_id, $content_type_id, $user_id=0){
		if($user_id==0){
			$sql = 'UPDATE	review_contexts
						SET		review_contexts.review_context_assigned_user_entity_id = NULL
						WHERE	review_contexts.review_context_organisation_entity_id = ? AND review_context_content_type_id=? LIMIT 1';
			$query = $this->db->query($sql, array($org_id, $content_type_id));
		}else{
			$sql = 'UPDATE	review_contexts
						SET		review_contexts.review_context_assigned_user_entity_id = ?
						WHERE	review_contexts.review_context_organisation_entity_id = ? AND review_context_content_type_id=? LIMIT 1';
			$query = $this->db->query($sql, array($user_id, $org_id, $content_type_id));
		}
	}
	
	function GetWaitingVenueInformationRevisions($content_type_codename){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			review_context_contents.review_context_content_last_author_timestamp as current_revision_timestamp,
			(
				SELECT COUNT(*) FROM review_context_contents WHERE 
				review_context_contents.review_context_content_last_author_timestamp > current_revision_timestamp AND
				review_context_contents.review_context_content_content_type_id = content_types.content_type_id AND
				review_context_contents.review_context_content_organisation_entity_id = venue_id AND
				review_context_contents.review_context_content_deleted = 0
			) as revisions_waiting
		FROM review_contexts 
		INNER JOIN organisations ON
		review_contexts.review_context_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		review_contexts.review_context_content_type_id = content_types.content_type_id
		INNER JOIN review_context_contents ON
		review_contexts.review_context_live_content_id = review_context_contents.review_context_content_id
		WHERE content_types.content_type_codename=?';
		$query = $this->db->query($sql, array($content_type_codename));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				if($row->revisions_waiting > 0){
					$result_item['venue_id'] = $row->venue_id;
					$result_item['venue_shortname'] = $row->venue_shortname;
					$result_item['venue_name'] = $row->venue_name;
					$result_item['current_revision_timestamp'] = $row->current_revision_timestamp;
					$result_item['revisions_waiting'] = $row->revisions_waiting;
					$result[] = $result_item;
				}
			}
		}
		return $result;
	}
	//This Produces a list of evey waiting review (so there can be multiple per venue) if the review is published it returns how many new revisions are waiting,
	//if its never been published returns the number of revisions of the review waiting to be published
	function GetWaitingVenueReviewRevisions($content_type_codename){
		$sql = '
		SELECT 
			organisations.organisation_entity_id as venue_id,
			organisations.organisation_directory_entry_name as venue_shortname,
			organisations.organisation_name as venue_name,
			articles.article_id,
			article_contents.article_content_id as revision_id,
			article_contents.article_content_last_author_timestamp as current_revision_timestamp,
			users.user_firstname,
			users.user_surname,
			articles.article_live_content_id,
			(
				SELECT COUNT(*) FROM article_contents WHERE 
				article_contents.article_content_article_id = articles.article_id
			) as revisions,
			(
				SELECT COUNT(*) FROM article_contents WHERE 
				article_contents.article_content_article_id = articles.article_id AND
				article_contents.article_content_last_author_timestamp > current_revision_timestamp 
			) as revisions_waiting
		FROM articles 
		INNER JOIN users ON
		articles.article_request_entity_id = users.user_entity_id
		INNER JOIN organisations ON
		articles.article_organisation_entity_id = organisations.organisation_entity_id
		INNER JOIN content_types ON 
		articles.article_content_type_id = content_types.content_type_id
		LEFT OUTER JOIN article_contents ON
		articles.article_live_content_id = article_contents.article_content_id
		WHERE content_types.content_type_codename=? AND articles.article_deleted=0 AND articles.article_pulled=0
		ORDER BY organisations.organisation_name ASC, users.user_firstname ASC, users.user_surname ASC';
		$query = $this->db->query($sql, array($content_type_codename));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['venue_id'] = $row->venue_id;
				$result_item['venue_shortname'] = $row->venue_shortname;
				$result_item['venue_name'] = $row->venue_name;
				$result_item['article_id'] = $row->article_id;
				$result_item['revision_id'] = $row->revision_id;
				$result_item['user_name'] = $row->user_firstname." ".$row->user_surname;
				if(empty($row->revision_id)){
					$result_item['published']=false;
					$result_item['revisions_waiting'] = $row->revisions;
					$result[] = $result_item;
				}else{
					$result_item['published']=true;
					if($row->revisions_waiting>0){
						$result_item['revisions_waiting'] = $row->revisions_waiting;
						$result[] = $result_item;
					}else{
						//ignore this result, as its been published and has no revisions waiting, its in good order.
					}
				}
				
			}
		}
		return $result;
	}
	
	// gets a list of all organisations which are suggestions for the directory
	function GetSuggestedOrganisations()
	{
		$sql = 'SELECT	organisations.organisation_entity_id,
						organisations.organisation_name,
						organisations.organisation_directory_entry_name,
						organisations.organisation_suggesters_name,
						organisations.organisation_timestamp
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_type_directory = 1
				WHERE	organisations.organisation_needs_approval = 1
				AND		organisations.organisation_pr_rep = 0
				AND		organisations.organisation_deleted = 0
				ORDER BY organisations.organisation_name ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['org_id'] = $row->organisation_entity_id;
				$result_item['org_name'] = $row->organisation_name;
				$result_item['org_dir_entry_name'] = $row->organisation_directory_entry_name;
				$result_item['user_name'] = $row->organisation_suggesters_name;
				$result_item['suggested_time'] = $row->organisation_timestamp;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	// gets a list of all organisations which are accepted suggestions for the directory, but are in the unassigned state
	function GetUnassignedOrganisations()
	{
		$sql = 'SELECT	organisations.organisation_entity_id,
						organisations.organisation_name,
						organisations.organisation_directory_entry_name
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_types.organisation_type_directory = 1
				WHERE	organisations.organisation_needs_approval = 0
				AND		organisations.organisation_pr_rep = 0
				AND		organisations.organisation_deleted = 0
				ORDER BY organisation_name ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['org_id'] = $row->organisation_entity_id;
				$result_item['org_name'] = $row->organisation_name;
				$result_item['org_dir_entry_name'] = $row->organisation_directory_entry_name;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	// returns a list, in the same order as GetUnassignedOrganistions(), of all reps which have asked to look after the organisation
	function GetUnassignedOrganisationsReps()
	{
		$sql = 'SELECT	subscriptions.subscription_organisation_entity_id,
						organisations.organisation_directory_entry_name,
						subscriptions.subscription_user_entity_id,
						users.user_firstname,
						users.user_surname
				FROM	subscriptions
				INNER JOIN organisations
				ON		organisations.organisation_entity_id = subscriptions.subscription_organisation_entity_id
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_type_directory = 1
				INNER JOIN users
				ON		subscriptions.subscription_user_entity_id = users.user_entity_id
				WHERE	subscriptions.subscription_pr_rep = 1
				AND		subscriptions.subscription_pr_rep_chosen = "suggestion"
				AND		organisations.organisation_deleted = 0
				ORDER BY organisation_name ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['org_id'] = $row->subscription_organisation_entity_id;
				$result_item['org_dir_name'] = $row->organisation_directory_entry_name;
				$result_item['user_id'] = $row->subscription_user_entity_id;
				$result_item['user_firstname'] = $row->user_firstname;
				$result_item['user_surname'] = $row->user_surname;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	// returns a list of all reps which have asked to look after the organisation specified
	function GetOrganisationReps($shortname)
	{
		$sql = 'SELECT	subscriptions.subscription_user_entity_id,
						users.user_firstname,
						users.user_surname
				FROM	subscriptions
				INNER JOIN organisations
				ON		organisations.organisation_entity_id = subscriptions.subscription_organisation_entity_id
				AND		organisations.organisation_deleted = 0
				AND		organisations.organisation_directory_entry_name = ?
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_types.organisation_type_directory = 1
				INNER JOIN users
				ON		subscriptions.subscription_user_entity_id = users.user_entity_id
				WHERE	subscriptions.subscription_pr_rep = 1
				AND		subscriptions.subscription_pr_rep_chosen = "suggestion"
				ORDER BY users.user_firstname ASC, users.user_surname ASC';
		$query = $this->db->query($sql, array($shortname));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['user_id'] = $row->subscription_user_entity_id;
				$result_item['user_firstname'] = $row->user_firstname;
				$result_item['user_surname'] = $row->user_surname;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	// gets a list of all organisations which are accepted suggestions for the directory, but are in the pending state
	function GetPendingOrganisations()
	{
		$sql = 'SELECT	organisations.organisation_entity_id,
						organisations.organisation_name,
						organisations.organisation_directory_entry_name,
						subscriptions.subscription_user_entity_id,
						users.user_firstname,
						users.user_surname
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_type_directory = 1
				INNER JOIN subscriptions
				ON		subscriptions.subscription_organisation_entity_id = organisations.organisation_entity_id
				AND		subscriptions.subscription_pr_rep = 1
				AND		subscriptions.subscription_pr_rep_chosen = "choosing"
				AND		subscriptions.subscription_deleted = 0
				INNER JOIN users
				ON		users.user_entity_id = subscriptions.subscription_user_entity_id
				WHERE	organisations.organisation_needs_approval = 0
				AND		organisations.organisation_pr_rep = 1
				AND		organisations.organisation_deleted = 0
				ORDER BY organisation_name ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['org_id'] = $row->organisation_entity_id;
				$result_item['org_name'] = $row->organisation_name;
				$result_item['org_dir_entry_name'] = $row->organisation_directory_entry_name;
				$result_item['user_id'] = $row->subscription_user_entity_id;
				$result_item['user_firstname'] = $row->user_firstname;
				$result_item['user_surname'] = $row->user_surname;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	//returns the status of an organisation in the pr system
	//can be: suggestion, unassigned, pending and assigned
	function GetOrganisationStatus($shortname)
	{
		$sql = 'SELECT	organisations.organisation_pr_rep,
						organisations.organisation_needs_approval,
						organisations.organisation_entity_id
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_type_directory = 1
				WHERE	organisations.organisation_directory_entry_name = ?';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			if ($row1->organisation_needs_approval == 1)
				return 'suggestion';
			else
			{
				if ($row1->organisation_pr_rep == 0)
					return 'unassigned';
				else
				{
					$sql = 'SELECT	subscriptions.subscription_pr_rep_chosen
							FROM	organisations
							INNER JOIN subscriptions
							ON		subscriptions.subscription_organisation_entity_id = ?
							AND		subscriptions.subscription_deleted = 0
							AND		subscriptions.subscription_pr_rep = 1
							WHERE	organisations.organisation_entity_id = ?';
					$query2 = $this->db->query($sql,array($row1->organisation_entity_id, $row1->organisation_entity_id));
					$row2 = $query2->row();
					if ($query2->num_rows() == 1)
					{
						if ($row2->subscription_pr_rep_chosen == 'choosing')
							return 'pending';
						else if ($row2->subscription_pr_rep_chosen == 'chosen')
							return 'assigned';
						else
							return FALSE;
					}
					else
						return FALSE;
				}
			}
		}
		else
			return FALSE;
	}
	
	//assumes organisation is in pending status
	function GetPendingOrganisationRep($shortname)
	{
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			$sql = 'SELECT	subscriptions.subscription_user_entity_id,
							users.user_firstname,
							users.user_surname
					FROM	subscriptions
					INNER JOIN users
					ON		users.user_entity_id = subscriptions.subscription_user_entity_id
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_deleted = 0
					AND		subscriptions.subscription_pr_rep_chosen = "choosing"
					AND		subscriptions.subscription_pr_rep = 1';
			$query2 = $this->db->query($sql,array($row1->organisation_entity_id));
			$row2 = $query2->row();
			if ($query2->num_rows() == 1)
			{
				$result['user_id'] = $row2->subscription_user_entity_id;
				$result['user_firstname'] = $row2->user_firstname;
				$result['user_surname'] = $row2->user_surname;
				return $result;
			}
			else
				return FALSE;
		}
		else
			return FALSE;
	}
	
	function GetSuggestedOrganisationInformation($shortname)
	{
		$sql = 'SELECT	organisations.organisation_suggesters_name,
						organisations.organisation_suggesters_position,
						organisations.organisation_suggesters_email,
						organisations.organisation_suggesters_notes
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$query = $this->db->query($sql,array($shortname));
		$row = $query->row();
		if ($query->num_rows() == 1)
		{
			$result['name'] = $row->organisation_suggesters_name;
			$result['position'] = $row->organisation_suggesters_position;
			$result['email'] = $row->organisation_suggesters_email;
			$result['notes'] = $row->organisation_suggesters_notes;
			return $result;
		}
	}
	
	function GetAssignedOrganisationList($sort, $asc_desc)
	{
		$sql = 'SELECT	organisations.organisation_entity_id,
						organisations.organisation_name,
						organisations.organisation_directory_entry_name,
						users.user_firstname,
						users.user_surname,
						organisations.organisation_priority
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_types.organisation_type_directory = 1
				INNER JOIN subscriptions
				ON		subscriptions.subscription_organisation_entity_id = organisations.organisation_entity_id
				AND		subscriptions.subscription_deleted = 0
				AND		subscriptions.subscription_pr_rep = 1
				AND		subscriptions.subscription_pr_rep_chosen = "chosen"
				INNER JOIN users
				ON		subscriptions.subscription_user_entity_id = users.user_entity_id
				WHERE	organisations.organisation_deleted = 0
				AND		organisations.organisation_needs_approval = 0
				AND		organisations.organisation_pr_rep = 1';
		if ($sort == 'org')
			$sql = $sql.' ORDER BY organisations.organisation_name '.$asc_desc;
		else if ($sort == 'rep')
			$sql = $sql.' ORDER BY users.user_firstname '.$asc_desc.', users.user_surname '.$asc_desc.', organisations.organisation_name ASC';
		if ($sort == 'pri')
			$sql = $sql.' ORDER BY organisations.organisation_priority '.$asc_desc.', organisations.organisation_name ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['org_id'] = $row->organisation_entity_id;
				$result_item['org_name'] = $row->organisation_name;
				$result_item['org_dir_entry_name'] = $row->organisation_directory_entry_name;
				$result_item['org_priority'] = $row->organisation_priority;
				$result_item['user_firstname'] = $row->user_firstname;
				$result_item['user_surname'] = $row->user_surname;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	//gets a list of all office members with at least one assigned organisation
	function GetRepList()
	{
		$sql = 'SELECT	DISTINCT subscriptions.subscription_user_entity_id,
						users.user_firstname,
						users.user_surname
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_type_directory = 1
				INNER JOIN subscriptions
				ON		subscriptions.subscription_organisation_entity_id = organisations.organisation_entity_id
				AND		subscriptions.subscription_pr_rep = 1
				AND		subscriptions.subscription_pr_rep_chosen = "chosen"
				AND		subscriptions.subscription_deleted = 0
				INNER JOIN users
				ON		users.user_entity_id = subscriptions.subscription_user_entity_id
				WHERE	organisations.organisation_needs_approval = 0
				AND		organisations.organisation_pr_rep = 1
				AND		organisations.organisation_deleted = 0
				ORDER BY users.user_firstname ASC, users.user_surname ASC';
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['user_id'] = $row->subscription_user_entity_id;
				$result_item['user_firstname'] = $row->user_firstname;
				$result_item['user_surname'] = $row->user_surname;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	//get the list of organisations assigned to the given rep
	function GetRepOrganisationList($user_id)
	{
		$sql = 'SELECT	organisations.organisation_entity_id,
						organisations.organisation_name,
						organisations.organisation_directory_entry_name,
						organisations.organisation_priority
				FROM	organisations
				INNER JOIN organisation_types
				ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
				AND		organisation_types.organisation_type_directory = 1
				INNER JOIN subscriptions
				ON		subscriptions.subscription_organisation_entity_id = organisations.organisation_entity_id
				AND		subscriptions.subscription_deleted = 0
				AND		subscriptions.subscription_pr_rep = 1
				AND		subscriptions.subscription_pr_rep_chosen = "chosen"
				AND		subscriptions.subscription_user_entity_id = ?
				WHERE	organisations.organisation_deleted = 0
				AND		organisations.organisation_needs_approval = 0
				AND		organisations.organisation_pr_rep = 1
				ORDER BY organisations.organisation_priority ASC, organisations.organisation_name ASC';
		$query = $this->db->query($sql,array($user_id));
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$result_item['org_id'] = $row->organisation_entity_id;
				$result_item['org_name'] = $row->organisation_name;
				$result_item['org_dir_entry_name'] = $row->organisation_directory_entry_name;
				$result_item['org_priority'] = $row->organisation_priority;
				$result[] = $result_item;
			}
		}
		return $result;
	}
	
	function GetOrganisationRatings($shortname)
	{
		//find the org and if it exists
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			//get general org and rep data
			$sql = 'SELECT	organisations.organisation_name,
							organisations.organisation_directory_entry_name,
							organisations.organisation_priority,
							subscriptions.subscription_user_entity_id,
							users.user_firstname,
							users.user_surname
					FROM	organisations
					INNER JOIN organisation_types
					ON		organisations.organisation_organisation_type_id = organisation_types.organisation_type_id
					AND		organisation_types.organisation_type_directory = 1
					INNER JOIN subscriptions
					ON		subscriptions.subscription_organisation_entity_id = organisations.organisation_entity_id
					AND		subscriptions.subscription_deleted = 0
					AND		subscriptions.subscription_pr_rep = 1
					AND		subscriptions.subscription_pr_rep_chosen = "chosen"
					INNER JOIN users
					ON		subscriptions.subscription_user_entity_id = users.user_entity_id
					WHERE	organisations.organisation_deleted = 0
					AND		organisations.organisation_needs_approval = 0
					AND		organisations.organisation_pr_rep = 1
					AND		organisations.organisation_directory_entry_name = ?';
			$query2 = $this->db->query($sql,array($shortname));
			$row2 = $query2->row();
			$result = array();
			if ($query2->num_rows() == 1)
			{
				$result['info']['id'] = $row1->organisation_entity_id;
				$result['info']['name'] = $row2->organisation_name;
				$result['info']['dir_entry_name'] = $row2->organisation_directory_entry_name;
				$result['info']['priority'] = $row2->organisation_priority;
				$result['rep']['id'] = $row2->subscription_user_entity_id;
				$result['rep']['firstname'] = $row2->user_firstname;
				$result['rep']['surname'] = $row2->user_surname;
			}
			
			return $result;
		}
		else
			return FALSE;
	}
	
	function GetOrganisationID($shortname)
	{
		//find the org and if it exists
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$query = $this->db->query($sql,array($shortname));
		$row = $query->row();
		if ($query->num_rows() == 1)
		{
			return($row->organisation_entity_id);
		}
	}
	
	//this deletes the organisation and its contents with the given shortname
	function SetOrganisationDeleted($shortname)
	{
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?';
		$query = $this->db->query($sql,array($shortname));
		$row = $query->row();
		if ($query->num_rows() == 1)
		{
			$sql = 'UPDATE	organisations
					SET		organisation_deleted = 1
					WHERE	organisations.organisation_entity_id = ?';
			$this->db->query($sql,array($row->organisation_entity_id));
			$sql = 'UPDATE	organisation_contents
					SET		organisation_content_deleted = 1
					WHERE	organisation_contents.organisation_content_organisation_entity_id = ?';
			$this->db->query($sql,array($row->organisation_entity_id));
			return TRUE;
		}
		else
			return FALSE;
	}
	
	function SetOrganisationUnassigned($shortname)
	{
		$sql = 'UPDATE	organisations
				SET		organisations.organisation_needs_approval = 0,
						organisations.organisation_pr_rep = 0
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$query = $this->db->query($sql,array($shortname));
	}
	
	//NOTE: must also make sure there is only one non deleted rep subscription to this org with "choosing" rep state
	function SetOrganisationPending($shortname, $user_id)
	{
		//find the org and if it exists
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			//update the organisation to pending status
			$sql = 'UPDATE	organisations
					SET		organisations.organisation_needs_approval = 0,
							organisations.organisation_pr_rep = 1
					WHERE	organisations.organisation_directory_entry_name = ?
					AND		organisation_deleted = 0';
			$this->db->query($sql,array($shortname));
			//set pr_rep = false for all org subscriptions that are not $user_id
			$sql = 'UPDATE	subscriptions
					SET		subscriptions.subscription_pr_rep = 0,
							subscriptions.subscription_pr_rep_chosen = "suggestion"
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_user_entity_id != ?
					AND		subscriptions.subscription_deleted = 0';
			$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			//does a subscription exist for user id / org id?
			$sql = 'SELECT	subscriptions.subscription_user_entity_id
					FROM	subscriptions
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_user_entity_id = ?';
			$query2 = $this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			if ($query2->num_rows() == 1)
			{
				//if yes, set pr_rep = 1 and pr_chosen to "choosing"
				$sql = 'UPDATE	subscriptions
						SET		subscriptions.subscription_pr_rep = 1,
								subscriptions.subscription_pr_rep_chosen = "choosing"
						WHERE	subscriptions.subscription_organisation_entity_id = ?
						AND		subscriptions.subscription_user_entity_id = ?
						AND		subscriptions.subscription_deleted = 0';
				$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			}
			else
			{
				//if no, create a new subscription with pr_rep = 1 and pr_chosen to "choosing"
				$sql = 'INSERT INTO subscriptions(
									subscription_organisation_entity_id,
									subscription_user_entity_id,
									subscription_pr_rep,
									subscription_pr_rep_chosen)
						VALUES (?,?,1,"choosing")';
				$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			}
		}
		else
			return FALSE;
	}
	
	//@pre assumes organisation is in pending state
	//@post organisation is in assigned state
	function SetOrganisationAssigned($shortname, $user_id)
	{
		//find the org and if it exists
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisation_deleted = 0';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			//if there is a subscription unset the pr rep flag
			$sql = 'UPDATE	subscriptions
					SET		subscriptions.subscription_pr_rep_chosen = "chosen"
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_user_entity_id = ?
					AND		subscriptions.subscription_deleted = 0';
			$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			return TRUE;
		}
		else
			return FALSE;
	}
	
	function SetOrganisationPriority($shortname, $priority)
	{
		$sql = 'UPDATE	organisations
				SET		organisations.organisation_priority = ?
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisations.organisation_deleted = 0';
		$this->db->query($sql,array($priority, $shortname));
	}
	
	function RequestRepToUnassignedOrganisation($shortname, $user_id)
	{
		//find the org and if it exists
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisation_deleted = 0';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			//does a subscription exist for user id / org id?
			$sql = 'SELECT	subscriptions.subscription_user_entity_id
					FROM	subscriptions
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_user_entity_id = ?
					AND		subscriptions.subscription_deleted = 0';
			$query2 = $this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			if ($query2->num_rows() == 1)
			{
				//if yes, set pr_rep = 1 and pr_chosen to "suggestion"
				$sql = 'UPDATE	subscriptions
						SET		subscriptions.subscription_pr_rep = 1,
								subscriptions.subscription_pr_rep_chosen = "suggestion"
						WHERE	subscriptions.subscription_organisation_entity_id = ?
						AND		subscriptions.subscription_user_entity_id = ?
						AND		subscriptions.subscription_deleted = 0';
				$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			}
			else
			{
				//if no, create a new subscription with pr_rep = 1 and pr_chosen to "suggestion"
				$sql = 'INSERT INTO subscriptions(
									subscription_organisation_entity_id,
									subscription_user_entity_id,
									subscription_pr_rep,
									subscription_pr_rep_chosen)
						VALUES (?,?,1,"suggestion")';
				$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			}
		}
		else
			return FALSE;
	}
	
	function WithdrawRepFromUnassignedOrganisation($shortname, $user_id)
	{
		//find the org and if it exists
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisation_deleted = 0';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1)
		{
			//does a subscription exist for user id / org id?
			$sql = 'SELECT	subscriptions.subscription_user_entity_id
					FROM	subscriptions
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_user_entity_id = ?
					AND		subscriptions.subscription_deleted = 0';
			$query2 = $this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			if ($query2->num_rows() == 1)
			{
				//if there is a subscription unset the pr rep flag
				$sql = 'UPDATE	subscriptions
						SET		subscriptions.subscription_pr_rep = 0,
								subscriptions.subscription_pr_rep_chosen = "suggestion"
						WHERE	subscriptions.subscription_organisation_entity_id = ?
						AND		subscriptions.subscription_user_entity_id = ?
						AND		subscriptions.subscription_deleted = 0';
				$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
				return TRUE;
			}
			else //else there is nothing to withdraw
				return FALSE;
		}
		else
			return FALSE;
	}
	
	function WithdrawRepFromPendingOrganisation($shortname, $user_id)
	{
		//find the pending org 
		$sql = 'SELECT	organisations.organisation_entity_id
				FROM	organisations
				WHERE	organisations.organisation_directory_entry_name = ?
				AND		organisation_deleted = 0
				AND		organisations.organisation_needs_approval = 0
				AND		organisations.organisation_pr_rep = 1';
		$query1 = $this->db->query($sql,array($shortname));
		$row1 = $query1->row();
		if ($query1->num_rows() == 1) //if it exists
		{
			//unset the pr rep flag of the pending subscription rep
			$sql = 'UPDATE	subscriptions
					SET		subscriptions.subscription_pr_rep = 0,
							subscriptions.subscription_pr_rep_chosen = "suggestion"
					WHERE	subscriptions.subscription_organisation_entity_id = ?
					AND		subscriptions.subscription_user_entity_id = ?
					AND		subscriptions.subscription_deleted = 0';
			$this->db->query($sql,array($row1->organisation_entity_id, $user_id));
			//set the organisation back to unassigned
			$sql = 'UPDATE	organisations
					SET		organisations.organisation_needs_approval = 0,
							organisations.organisation_pr_rep = 0
					WHERE	organisations.organisation_directory_entry_name = ?
					AND		organisations.organisation_deleted = 0';
			$this->db->query($sql,array($shortname));
			return TRUE;
		}
		else
			return FALSE;
	}
}


