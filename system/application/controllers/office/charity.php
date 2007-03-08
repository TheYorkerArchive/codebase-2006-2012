<?php

/// Office Charity Pages.
/**
 * @author Richard Ingle (ri504@cs.york.ac.uk)
 */
class Charity extends Controller
{
	/// Default constructor.
	function __construct()
	{
		parent::Controller();

		$this->load->helper('text');
		$this->load->helper('wikilink');
	}

	/// Set up the navigation bar
	private function _SetupNavbar()
	{
		$navbar = $this->main_frame->GetNavbar();
		$navbar->AddItem('progressreports', 'Progress Reports',
				'/office/charity/');
		$navbar->AddItem('charities', 'Charities',
				'/office/charity/');
		$navbar->AddItem('about', 'About',
				'/office/charity/');
	}
	
	function index()
	{
		$this->_CharityList();
	}

	function _CharityList()
	{
		if (!CheckPermissions('office')) return;

		//set the page code and load the required models
		$this->pages_model->SetPageCode('office_charity_list');
		$this->load->model('charity_model','charity_model');
		
		//get list of the charities
		$data['charities'] = $this->charity_model->GetCharities();

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;

		// Set up the view
		$the_view = $this->frames->view('office/charity/office_charity_list', $data);
		
		// Set up the public frame
		$this->main_frame->SetContent($the_view);

		// Load the public frame view
		$this->main_frame->Load();
	}

	function edit($charity_id)
	{
		if (!CheckPermissions('office')) return;

		//set the page code and load the required models
		$this->pages_model->SetPageCode('office_charity_edit');
		$this->load->model('charity_model','charity_model');

		//Get navigation bar and tell it the current page
		$this->_SetupNavbar();
		$this->main_frame->SetPage('charities');

		//get charity from given id
		$data['charity'] = $this->charity_model->GetCharity($charity_id);
		$data['charity']['id'] = $charity_id;

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;

		// Set up the view
		$the_view = $this->frames->view('office/charity/office_charity_edit', $data);
		
		// Set up the public frame
		$this->main_frame->SetTitle($this->pages_model->GetTitle(array(
			'name'=>$data['charity']['name']))
			);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view
		$this->main_frame->Load();
	}

	function article($charity_id, $revision_id)
	{
		if (!CheckPermissions('office')) return;

		//set the page code and load the required models
		$this->pages_model->SetPageCode('office_charity_article');
		$this->load->model('charity_model','charity_model');
		$this->load->model('article_model','article_model');
		$this->load->model('requests_model','requests_model');

		//Get navigation bar and tell it the current page
		$this->_SetupNavbar();
		$this->main_frame->SetPage('charities');

		//get charity from given id
		$data['charity'] = $this->charity_model->GetCharity($charity_id);
		$data['charity']['id'] = $charity_id;
		
		//get the header of the charities article and revisions
		$data['article']['header'] = $this->article_model->GetArticleHeader($data['charity']['article']);
		$data['article']['revisions'] = $this->requests_model->GetArticleRevisions($data['charity']['article']);

		//if the revision id is set to the default
		if ($revision_id == -1)
		{
			/* is a published article, therefore
			   load the live content revision */
			if ($data['article']['header']['live_content'] != FALSE)
			{
				$data['article']['displayrevision'] = $this->article_model->GetRevisionContent($data['charity']['article'], $data['article']['header']['live_content']);
			}
			/* no live content, therefore is a
			   request, so load the latest
			   revision as default */
			else
			{
				//make sure a revision exists
				if (isset($data['article']['revisions'][0]))
				{
					$data['article']['displayrevision'] = $this->article_model->GetRevisionContent($data['charity']['article'], $data['article']['revisions'][0]['id']);
				}
			}
		}
		else
		{
			/* load the revision with the given
			   revision id */
			$data['article']['displayrevision'] = $this->article_model->GetRevisionContent($data['charity']['article'], $revision_id);
			/* if this revision doesn't exist
			   then return an error */
			//if ($data['article']['displayrevision'] == FALSE)
			if (TRUE == FALSE)
			{
                		$this->main_frame->AddMessage('error','Specified revision doesn\'t exist for this charity. Default selected.');
                		redirect('/office/charity/article/'.$data['charity']['article'].'/');
    			}
		}

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;

		// Set up the view
		$the_view = $this->frames->view('office/charity/office_charity_article', $data);

		// Set up the public frame
		$this->main_frame->SetTitle($this->pages_model->GetTitle(array(
			'name'=>$data['charity']['name']))
			);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view
		$this->main_frame->Load();
	}

	function modify($charity_id)
	{
		if (!CheckPermissions('office')) return;

		//set the page code and load the required models
		$this->pages_model->SetPageCode('office_charity_modify');
		$this->load->model('charity_model','charity_model');

		//Get navigation bar and tell it the current page
		$this->_SetupNavbar();
		$this->main_frame->SetPage('charities');

		//get charity from given id
		$data['charity'] = $this->charity_model->GetCharity($charity_id);
		$data['charity']['id'] = $charity_id;

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;

		// Set up the view
		$the_view = $this->frames->view('office/charity/office_charity_modify', $data);
		
		// Set up the public frame
		$this->main_frame->SetTitle($this->pages_model->GetTitle(array(
			'name'=>$data['charity']['name']))
			);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view
		$this->main_frame->Load();
	}

	function progressreports()
	{
		if (!CheckPermissions('office')) return;

		//set the page code and load the required models
		$this->pages_model->SetPageCode('office_charity_progressreports');

		//Get navigation bar and tell it the current page
		$this->_SetupNavbar();
		$this->main_frame->SetPage('progressreports');

		//get the current users id and office access
		$data['user']['id'] = $this->user_auth->entityId;
		$data['user']['officetype'] = $this->user_auth->officeType;

		// Set up the view
		$the_view = $this->frames->view('office/charity/office_charity_progress_report', $data);
		
		// Set up the public frame
		//$this->main_frame->SetTitle($this->pages_model->GetTitle(array(
		//	'name'=>$data['charity']['name']))
		//	);
		$this->main_frame->SetContent($the_view);

		// Load the public frame view
		$this->main_frame->Load();
	}

	/**
	 * This function adds a new charity to the database.
	 */
	function addcharity()
	{
		if (!CheckPermissions('office')) return;

		/* Loads the category edit page
		   $_POST data passed
		   - r_redirecturl => the url to redirect back to
		   - a_charityname => the name of the new charity
    		   - r_submit_add => the name of the submit button
		*/
		if (isset($_POST['r_submit_add']))
		{
			if (trim($_POST['a_charityname']) != '')
			{
				//load the required models
				$this->load->model('charity_model','charity_model');
				$this->load->model('requests_model','requests_model');
	
				//create the charity and its article
				$id = $this->requests_model->CreateRequest('request', 'ourcharity', '', '', $this->user_auth->entityId, time());
				$this->charity_model->CreateCharity($_POST['a_charityname'], $id);
	
				//return to form submit page and pass success message
				$this->main_frame->AddMessage('success','Charity added.');
				redirect($_POST['r_redirecturl']);
			}
			else
			{
				//return to form submit page and pass error message
				$this->main_frame->AddMessage('error','Must enter a name for the new charity.');
				redirect($_POST['r_redirecturl']);
			}
		}

	}

	/**
	 * Modifes the given charity.
	 */
	function domodify()
	{
		if (!CheckPermissions('office')) return;

		/* Updates a charity information
		   $_POST data passed
		   - a_charityid => the id of the charity
		   - r_redirecturl => the url to redirect back to
		   - a_name => new name of the charity
		   - a_goal => the target ammount of money
		   - a_goaltext => the blurb about what we are aiming for
    		   - r_submit_add => the name of the submit button
		*/
		if (isset($_POST['r_submit_modify']))
		{
			if (trim($_POST['a_name']) != '')
			{
				if (is_numeric($_POST['a_goal']))
				{
					//load the required models
					$this->load->model('charity_model','charity_model');
					
					//update the charity
					$this->charity_model->UpdateCharity(
						$_POST['a_charityid'], 
						$_POST['a_name'],
						$_POST['a_goal'],
						$_POST['a_goaltext']);

					//return to form submit page and pass success message
					$this->main_frame->AddMessage('success','Charity updated.');
					redirect($_POST['r_redirecturl']);
				}
				else
				{
					//return to form submit page and pass error message
					$this->main_frame->AddMessage('error','You must enter a numeric value for the goal ammount.');
					redirect($_POST['r_redirecturl']);
				}
			}
			else
			{
				//return to form submit page and pass error message
				$this->main_frame->AddMessage('error','You must enter a name for the charity.');
				redirect($_POST['r_redirecturl']);
			}
			
		}
	}
}