<?php

/// Main vip index controller.
class Vipindex extends Controller
{
	/**
	 * @brief Default constructor.
	 */
	function __construct()
	{
		parent::Controller();
	}
	
	function index()
	{
		if (!CheckPermissions('vip')) return;
		
		$organisation = VipOrganisation();
		if (empty($organisation)) {
			$organisation = VipOrganisation(TRUE);
			redirect('viparea/'.$organisation);
			return;
		}
		
		$this->pages_model->SetPageCode('viparea_index');
		
		$data = array(
				'main_text' => $this->pages_model->GetPropertyWikitext('main_text'),
				'organisation' => VipOrganisation(),
				'enable_members' => TRUE, //example for the moment change this to logged in organisation
		);
		// Set up the content
		$this->main_frame->SetTitleParameters(
				array('organisation' => VipOrganisationName())
		);
		$this->main_frame->SetContentSimple('viparea/main', $data);
		
		// Load the main frame
		$this->main_frame->Load();
	}
}

?>