<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file organisations.php
 * @brief Library for getting organisation information.
 * @author James Hogan (jh559@cs.york.ac.uk)
 */

class Organisations
{
	/// Code igniter instance.
	private $CI;

	/// Default constructor
	function __construct()
	{
		$this->CI = &get_instance();
	}

	/// Get organisation types from organisations.
	/**
	 * @param $Organisations array Organisations as returned by _GetOrgs.
	 * @param $Sorted array Whether to sort the result by name.
	 * @return array of organisation types.
	 */
	function _GetOrganisationTypes($Organisations, $Sorted = FALSE)
	{
		$types = array();
		foreach ($Organisations as $organisation) {
			if (array_key_exists($organisation['type'], $types)) {
				++$types[$organisation['type']];
			} else {
				$types[$organisation['type']] = 1;
			}
		}
		if ($Sorted) {
			asort($types);
			$types = array_reverse($types,TRUE);
		}
		$result = array();
		foreach ($types as $type => $quantity) {
			$result[] = array(
				'id' => $type,
				'name' => $type,
				'quantity' => $quantity,
			);
		}
		return $result;
	}

	/// Temporary function get organisations.
	/**
	 * @param $Pattern string/bool Search pattern or FALSE if all.
	 * @param $urlpath path that links will point to
	 * @param $status of the entry 'live','hidden','suggested'
	 * @return array of organisations matching pattern.
	 */
	function _GetOrgs($Pattern, $urlpathpre='directory/', $urlpathpost='', $status='live')
	{
		$org_description_words = $this->CI->pages_model->GetPropertyInteger('org_description_words', FALSE, 5);

		$orgs = $this->CI->directory_model->GetDirectoryOrganisations($status);
		$organisations = array();
		foreach ($orgs as $org) {
			$organisations[] = array(
				'id' => $org['organisation_entity_id'],
				'name' => $org['organisation_name'],
				'shortname' => $org['organisation_directory_entry_name'],
				'link' => $urlpathpre.$org['organisation_directory_entry_name'].$urlpathpost,
				'description' => $org['organisation_description'],
				'shortdescription' => word_limiter(
					$org['organisation_description'], $org_description_words),
				'type' => $org['organisation_type_name'],
			);
		}
		return $organisations;
	}

	/// Temporary function get organisation data.
	/**
	 * @param $OrganisationShortName Short name of organisation.
	 * @return Organisation data relating to specified organisation or FALSE.
	 */
	 
	function _GetOrgsChildren($urlpathpre='directory/', $urlpathpost='')
	{
		$orgs = $this->CI->directory_model->GetDirectoryOrganisationsChildren();
		$organisations = array();
		foreach ($orgs as $org) {
			$organisations[] = array(
				'id' => $org['child_organisation_entity_id'],
				'parent_id' => $org['parent_organisation_entity_id'],
				'name' => $org['organisation_name'],
				'shortname' => $org['organisation_directory_entry_name'],
				'link' => $urlpathpre.$org['organisation_directory_entry_name'].$urlpathpost
			);
		}
		return $organisations;
	}

	function _GetOrgData($OrganisationShortName, $revision_number=false)
	{
		$this->CI->load->library('image');
		$this->CI->load->model('slideshow');

		$data = array();

		$orgs = $this->CI->directory_model->GetDirectoryOrganisationByEntryName($OrganisationShortName, $revision_number);
		foreach ($orgs as $org) {

			$slideshow_array = $this->CI->slideshow->getPhotos($org['organisation_entity_id']);
			$slideshow = array();
			foreach ($slideshow_array->result() as $slide){
				$slideshow[] = array(
					'title' => $slide->photo_title,
					'id' => $slide->photo_id,
					'url' => $this->CI->image->getPhotoURL($slide->photo_id, 'slideshow')
				);
			}
			$data['organisation'] = array(
				'id'          => $org['organisation_entity_id'],
				'name'        => $org['organisation_name'],
				'slideshow'   => $slideshow,
				'shortname'   => $org['organisation_directory_entry_name'],
				'description' => $org['organisation_description'],
				'type'        => $org['organisation_type_name'],
				'type_codename'	=> $org['organisation_type_codename'],
				'website'     => $org['organisation_url'],
				'open_times'  => $org['organisation_opening_hours'],
				'email_address'   => $org['organisation_email_address'],
				'postal_address'  => $org['organisation_postal_address'],
				'postcode'    => $org['organisation_postcode'],
				'phone_internal'  => $org['organisation_phone_internal'],
				'phone_external'  => $org['organisation_phone_external'],
				'fax_number'  => $org['organisation_fax_number'],
				'revision_id'  => $org['organisation_revision_id'],
				'location' => $org['organisation_location_id'],
				'location_lat' => $org['location_lat'],
				'location_lng' => $org['location_lng']
			);
			if (isset($org['subscription_member'])) {
				$data['organisation']['subscription'] = array(
					'member'				=> $org['subscription_member'],
					'membership_requested'	=> $org['subscription_membership_requested'],
					'calendar'				=> $org['subscription_calendar'],
				);
			}
			if (NULL === $org['organisation_yorkipedia_entry']) {
				$data['organisation']['yorkipedia'] = NULL;
			} else {
				$data['organisation']['yorkipedia'] = array(
						'url'   => WikiLink('yorkipedia', $org['organisation_yorkipedia_entry']),
						'title' => $org['organisation_yorkipedia_entry'],
					);
			}
		}
		return $data;
	}
}

?>
