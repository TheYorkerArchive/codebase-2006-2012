<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/// Calendar actions controller.
class Calendar_actions extends model
{
	/// Default constructor
	function __construct()
	{
		parent::model();
		
		$this->load->helper('uri_tail');
	}
	
	/// Display a form for setting occurrence attendence.
	function attend($SourceId = NULL, $OccurrenceId = NULL, $Action = NULL)
	{
		if (is_numeric($SourceId)) {
			static $mapping = array(
				'accept'  => TRUE,
				'decline' => FALSE,
				'maybe'   => NULL,
			);
			if (array_key_exists($Action, $mapping)) {
				$action = $mapping[$Action];
				// Now determine what protocol to use
				$this->load->library('calendar_backend');
				$this->load->library('calendar_source_my_calendar');
				$my_calendar = new CalendarSourceMyCalendar();
				$messages = $my_calendar->AttendingOccurrence((int)$SourceId, $OccurrenceId, $action);
				if (!array_key_exists('error', $messages)) {
					$this->messages->AddMessage('success', 'Your attending status has been set to '.$Action);
				}
				$this->messages->AddMessages($messages);
				$args = array_shift(array_shift(array_shift(func_get_args())));
				redirect(implode('/',$args));
			} else {
				return show_404();
			}
		} else {
			return show_404();
		}
	}
	
	function delete($SourceId = NULL, $EventId = NULL)
	{
		if (is_numeric($SourceId)) {
			if (FALSE !== $this->input->post('caldel_confirm')) {
				// User has confirmed deletion
				static $mapping = array(
					'accept'  => TRUE,
					'decline' => FALSE,
					'maybe'   => NULL,
				);
				// Now determine what protocol to use
				$this->load->library('calendar_backend');
				$this->load->library('calendar_source_my_calendar');
				$my_calendar = new CalendarSourceMyCalendar();
				$messages = $my_calendar->DeleteEvent((int)$SourceId, $EventId);
				if (!array_key_exists('error', $messages)) {
					$this->messages->AddMessage('success', 'The event was successfully deleted.');
				}
				$this->messages->AddMessages($messages);
				// shift 2 arguments and redirect
				$args = array_shift(array_shift(func_get_args()));
				redirect(implode('/',$args));
			} else {
				// User has not yet confirmed deleting
				$data = array(
					'Event' => NULL,
					'Occurrences' => array(),
				);
				$this->main_frame->SetContentSimple('calendar/event_delete', $data);
				$this->main_frame->Load();
			}
		} else {
			return show_404();
		}
	}
	
	function add($type = '')
	{
		$method = '_add_'.$type;
		if (method_exists($this, $method)) {
			$this->$method();
			$args = array_shift(func_get_args());
			redirect(implode('/',$args));
		} else {
			show_404();
		}
	}
	
	function _add_todo()
	{
		// Read the post data
		$name = $this->input->post('todo_name');
		if (FALSE !== $name) {
			if (empty($name)) {
				$this->messages->AddMessage('warning', 'You didn\'t specify a name for the to do list item.');
			} else {
				$CI = & get_instance();
				$CI->load->model('calendar/events_model');
				$input['recur'] = new RecurrenceSet();
				$input['todo'] = TRUE;
				$input['name'] = $name;
				
				try {
					$results = $CI->events_model->EventCreate($input);
					$this->messages->AddMessage('success', 'To do list item added.');
				} catch (Exception $e) {
					$this->messages->AddMessage('error', $e->getMessage());
				}
			}
		} else {
			$this->messages->AddMessage('error', 'Invalid todo name');
		}
	}
	
	function _add_event()
	{
		$this->messages->AddMessage('error', 'Not yet implemented');
	}
}

?>
