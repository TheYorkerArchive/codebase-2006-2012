<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/// Calendar actions controller.
class Organizer_actions extends model
{
	function __construct()
	{
		parent::model();
		
		$this->load->helper('uri_tail');
		$this->load->model('calendar/calendar_actions');
	}
	
	function publish($EventId = NULL, $OccurrenceId = NULL)
	{
		if (!CheckPermissions('vip+pr')) return;
		
		if (is_numeric($EventId)) {
			$EventId = (int)$EventId;
			if (NULL !== $OccurrenceId) {
				$OccurrenceId = (int)$OccurrenceId;
			}
			
			$this->main_frame->SetContentSimple('calendar/publish', $data);
			
		} else {
			show_404();
		}
	}
	
	function delete()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this->calendar_actions, 'delete'), $args);
	}
}

?>
