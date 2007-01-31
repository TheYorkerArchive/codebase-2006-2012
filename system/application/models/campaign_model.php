<?php
/**
 * This model retrieves data for the Campaign pages.
 *
 * @author Richard Ingle (ri504)
 * 
 */
 
//TODO - prevent erros if no data present
 
class Campaign_model extends Model
{
	function CampaignModel()
	{
		//Call the Model Constructor
		parent::Model();
	}
	
	/**
	 * Returns an array of the Campaigns that are currently being voted on
	 * in ascending order of name.
	 * @return An array of arrays containing campaign id, names and votes.
	 */
	function GetCampaignList()
	{
		$sql = "SELECT campaign_name, campaign_votes, campaign_id
			FROM campaigns
			WHERE campaign_deleted = false
				AND campaign_timestamp < CURRENT_TIMESTAMP
			ORDER BY campaign_name ASC";
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				//$result_item = array('id'=>$row->campaign_id,'name'=>$row->campaign_name,'votes'=>$row->campaign_votes);
				//$result[] = $result_item;
				$result_item = array('name'=>$row->campaign_name,'votes'=>$row->campaign_votes);
				$result[$row->campaign_id] = $result_item;
			}
		}
		return $result;
	}
	
	/**
	 * Returns the name of the given campaign id
	 * @return the name as a string.
	 */
	function GetCampaignName($campaign_id)
	{
		$sql = "SELECT campaign_name
			FROM campaigns
			WHERE campaign_id = ".$campaign_id;
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->campaign_name;
	}
	
	/**
	 * Returns an array of the last $count progress report items.
	 * @return An array of arrays containing campaign id, names and votes.
	 */
	function GetProgressReports($count)
	{
		
	}
}
?>