<?php

/**
 * @file prindex.php
 * @brief Main PR page for an organisation.
 */

/// Main PR area for an organisation controller.
/**
 */
class Prindex extends controller
{
	/// Default constructor
	function __construct()
	{
		parent::controller();
	}

	/// Set up the navigation bar
	private function _SetupNavbar()
	{
		$navbar = $this->main_frame->GetNavbar();
		$navbar->AddItem('summary', 'Summary',
				'/office/pr/summary');
		$navbar->AddItem('unnassigned', 'Unnassigned',
				'/office/pr/unnassigned');
		$navbar->AddItem('suggestions', 'Suggestions',
				'/office/pr/suggestions');
	}
	
	/// Index page (accessed through /office/pr/org/$organisation)
	function orgindex()
	{
		if (!CheckPermissions('pr')) return;
		$this->pages_model->SetPageCode('office_pr_main');
		$this->main_frame->SetTitleParameters(array(
			'organisation' => VipOrganisationName()
		));
		$this->main_frame->load();
	}

	function summary($type = NULL, $id = NULL)
	{
		// Not accessed through /office/pr/org/$organisation, not organisation
		// specific so needs to be office permissions.
		if (!CheckPermissions('office')) return;

		$this->_SetupNavbar();
		$this->main_frame->SetPage('summary');
		
		if ($type == NULL)
		{
			self::_SummaryOverall();
		}
		else if ($type == 'rep')
		{
			self::_SummaryRep($id);
		}
		else if ($type == 'org')
		{
			self::_SummaryOrganisation($id);
		}
	}
	
	function _SummaryOverall()
	{
		//navbar and page codes
		$this->main_frame->SetPage('summary');
		$this->pages_model->SetPageCode('office_pr_summary_overall');

		/** store the parameters passed to the method so it can be
		    used for links in the view */
		$data['parameters']['type'] = 'ovr';
		$data['parameters']['name'] = NULL;

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;
		
		// Set up the public frame
		$the_view = $this->frames->view('office/pr/summary_overall', $data);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view (which will load the content view)
		$this->main_frame->load();
	}
	
	function _SummaryRep($id)
	{
		//navbar and page codes
		$this->main_frame->SetPage('summary');
		$this->pages_model->SetPageCode('office_pr_summary_rep');
		
		/** store the parameters passed to the method so it can be
		    used for links in the view */
		$data['parameters']['type'] = 'rep';
		$data['parameters']['name'] = $id;

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;
	
		// Set up the public frame
		$the_view = $this->frames->view('office/pr/summary_rep', $data);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view (which will load the content view)
		$this->main_frame->load();
	}
	
	function _SummaryOrganisation($id)
	{
		//navbar and page codes
		$this->main_frame->SetPage('summary');
		$this->pages_model->SetPageCode('office_pr_summary_org');
		
		/** store the parameters passed to the method so it can be
		    used for links in the view */
		$data['parameters']['type'] = 'rep';
		$data['parameters']['name'] = $id;
	
		// Set up the public frame
		$the_view = $this->frames->view('office/pr/summary_org', $data);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view (which will load the content view)
		$this->main_frame->load();
	}

	function suggestions()
	{
		// Not accessed through /office/pr/org/$organisation, not organisation
		// specific so needs to be office permissions.
		if (!CheckPermissions('office')) return;

		$this->_SetupNavbar();
		$this->main_frame->SetPage('suggestions');
		$this->pages_model->SetPageCode('office_pr_suggestions');

		$data['user'] = array(
			'access'=>$this->user_auth->officeType,
			'id'=>$this->user_auth->entityId
			);
//		$data['user']['access'] = 'Low';

		// Set up the public frame
		$the_view = $this->frames->view('office/pr/suggestions', $data);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view (which will load the content view)
		$this->main_frame->load();
	}

	function unnassigned()
	{
		// Not accessed through /office/pr/org/$organisation, not organisation
		// specific so needs to be office permissions.
		if (!CheckPermissions('office')) return;

		$this->_SetupNavbar();
		$this->main_frame->SetPage('unnassigned');
		$this->pages_model->SetPageCode('office_pr_unnassigned');

		$data['user'] = array(
			'access'=>$this->user_auth->officeType,
			'id'=>$this->user_auth->entityId
			);
//		$data['user']['access'] = 'Low';

		// Set up the public frame
		$the_view = $this->frames->view('office/pr/unnassigned', $data);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view (which will load the content view)
		$this->main_frame->load();
	}
}

?>