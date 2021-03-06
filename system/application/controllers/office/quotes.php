<?php
/*
 * Controller for quotes office pages
 * \author Nick Evans nse500
 */

class Quotes extends Controller
{
	/// Default constructor.
	function __construct()
	{
		parent::controller();
		$this->load->model('Contact_Model');
	}

	/// Default page.
	function index()
	{
		if (!CheckPermissions('office')) return;

		$this->pages_model->SetPageCode('office_quotes');

		$data = array();
		$data['page_information']  = $this->pages_model->GetPropertyWikitext('page_information');
		
		$this->load->model('Quote_Model');
		$data['quotes'] = $this->Quote_Model->GetQuotes();

		$this->main_frame->SetContentSimple('office/quotes/quote_list', $data);

		$this->main_frame->Load();
	}

	//Add contact page.
	function edit($quote_id)
	{
		//has user got access to office
		if (!CheckPermissions('office')) return;
		
		$this->pages_model->SetPageCode('office_quotes');
		$data = array();
		$data['page_information']  = $this->pages_model->GetPropertyWikitext('page_information');
		
		$this->load->model('user_auth');

		if (!($this->user_auth->officeType == 'High' || $this->user_auth->officeType == 'Admin')) {
			$this->messages->AddMessage('error', 'Permission denied. You must be an editor to perform this operation.');
			redirect('/office/quotes');
		}

		$this->load->model('Quote_Model');
		$data['quote'] = $this->Quote_Model->GetQuote($quote_id);
		
		$this->main_frame->SetContentSimple('office/quotes/quote_edit', $data);

		$this->main_frame->Load();
	}

	//Add contact page.
	function update($quote_id)
	{
		//has user got access to office
		if (!CheckPermissions('office')) return;

		$this->load->model('user_auth');

		if (!($this->user_auth->officeType == 'High' || $this->user_auth->officeType == 'Admin')) {
			$this->messages->AddMessage('error', 'Permission denied. You must be an editor to perform this operation.');
			redirect('/office/quotes');
		}

		$quote_text = $this->input->post('quote_text');
		$quote_author = $this->input->post('quote_author');
		$quote_scheduled = $this->input->post('quote_scheduled');
		$quote_schedule_date = $this->input->post('quote_schedule_date');

		$quote_last_displayed_timestamp = ($quote_scheduled ? $quote_schedule_date : null);

		$delete = ($this->input->post('name_delete_button') == 'Delete');

		$this->load->model('Quote_Model');
		if ($delete) {
			$this->Quote_Model->RemoveQuote($quote_id);
			$this->messages->AddMessage('success', 'Quote deleted successfully');
		} else {
			if ($quote_text && $quote_author){
				$this->Quote_Model->UpdateQuote($quote_id, $quote_text, $quote_author, $quote_last_displayed_timestamp);
				$this->messages->AddMessage('success', 'Quote update successfully');
			} else {
				$this->messages->AddMessage('error', 'Quote update failed: no data was provided.');
			}
		}
		redirect('/office/quotes');
	}
}

?>
