<?php

/// Main viparea controller.
class Account extends Controller
{
	/**
	 * @brief Default constructor.
	 */
	function __construct()
	{
		parent::Controller();
		
		$this->load->model('pages_model');
	}
	
	function index()
	{
		$this->pages_model->SetPageCode('viparea_account');
		
		// Load the main frame
		if (SetupMainFrame('organisation')) {
			$data = array(
					'main_text' => $this->pages_model->GetPropertyWikitext('main_text'),
			);
			// Set up the content
			$this->main_frame->SetContentSimple('viparea/account', $data);
		}
		
		// Load the main frame
		$this->main_frame->Load();
	}
}

?>