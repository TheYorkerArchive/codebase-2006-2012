<?php

/**
 * @file calendar.php
 * @brief Calendar controller.
 */

/// Controller for event manager.
/**
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * @version 20/03/2007 James Hogan (jh559)
 *	- Doxygen tidy up.
 */
class Calendar extends Controller
{

	/// Default constructor.
	function __construct()
	{
		parent::Controller();
	}
	
	/// Default function.
	function index()
	{
		return $this->range();
	}
	
	function actions()
	{
		// do the magic, use calendar_actions as a controller
		$this->load->model('calendar/calendar_actions');
		$args = func_get_args();
		$func = array_shift($args);
		if ('_' !== substr($func,0,1) && method_exists($this->calendar_actions, $func)) {
			call_user_func_array(array(&$this->calendar_actions, $func), $args);
		} else {
			show_404();
		}
	}
	
	protected function _SetupMyCalendar()
	{
		$this->load->library('My_calendar');
		$this->load->library('calendar_source_my_calendar');
		$this->my_calendar->SetUrlPrefix('/calendar/range/');
		$this->my_calendar->SetAgenda('/calendar/agenda/');
		return new CalendarSourceMyCalendar();
	}
	
	/// Display event information.
	function event($SourceId = NULL, $EventId = NULL, $OccurrenceId = NULL)
	{
		if (!CheckPermissions('public')) return;
		
		$this->pages_model->SetPageCode('calendar_event');
		
		$this->load->library('my_calendar');
		
		$this->main_frame->SetContent(
			$this->my_calendar->GetEvent(
				$SourceId, $EventId, $OccurrenceId)
		);
		
		$this->main_frame->Load();
	}
	
	function range($DateRange = NULL, $Filter = NULL)
	{
		if (!CheckPermissions('public')) return;
		
		$this->pages_model->SetPageCode('calendar_personal');
		
		$sources = & $this->_SetupMyCalendar();
		
		// Gotta be a rep or admin to edit
		$date_range_split = explode(':', $DateRange);
		$this->my_calendar->SetPath('add', site_url('calendar/add/'.$date_range_split[0]));
		$this->my_calendar->SetPath('edit', site_url('calendar/event'));
		
		$this->main_frame->SetContent(
			$this->my_calendar->GetMyCalendar($sources, $DateRange, $Filter)
		);
		
		$this->main_frame->Load();
	}
	
	function agenda($DateRange = NULL, $Filter = NULL)
	{
		if (!CheckPermissions('public')) return;
		
		$sources = & $this->_SetupMyCalendar();
		$this->main_frame->SetContent(
			$this->my_calendar->GetAgenda($sources, $DateRange, $Filter)
		);
		
		$this->main_frame->Load();
	}
	
	function ical()
	{
		if (!CheckPermissions('public')) return;
		
		$this->_LoadCalendarSystem();
		$sources = $this->_SetupSources(time(), strtotime('1week'));
		$calendar_data = new CalendarData();
		
		$this->messages->AddMessages($sources->FetchEvents($calendar_data));
		
		// Display data
		$this->load->library('calendar_view_icalendar');
		
		$ical = new CalendarViewICalendar();
		$ical->SetCalendarData($calendar_data);
		
		$ical->Load();
	}
	
	/// Load the calendar system libraries.
	function _LoadCalendarSystem()
	{
		// Load libraries
		$this->load->library('academic_calendar');
		$this->load->library('calendar_backend');
		$this->load->library('calendar_frontend');
	}
	
	function add()
	{
		if (!CheckPermissions('student')) return;
		
		$this->load->library('My_calendar');
		
		$this->pages_model->SetPageCode('calendar_new_event');
		
		$this->main_frame->SetContent($this->my_calendar->GetAdder());
		
		$this->main_frame->Load();
	}
	
	/// Set up the tabs on the main_frame.
	/**
	 * @param $SelectedPage string Selected Page.
	 * @pre CheckPermissions must have already been called.
	 */
	protected function _SetupTabs($SelectedPage, $Start, $Filter = NULL)
	{
		$navbar = $this->main_frame->GetNavbar();
		if (NULL === $Filter) {
			$Filter = '/';
		} else {
			$Filter = '/'.$Filter;
		}
		$now = new Academic_time(time());
		$navbar->AddItem('day', 'Today',	site_url(
			'calendar/range/today'.
			$Filter
		));
		$monday = $Start->BackToMonday();
		$navbar->AddItem('days', 'Week',	site_url(
			'calendar/range/'.
			$monday->AcademicYear().'-'.$monday->AcademicTermNameUnique().'-'.$monday->AcademicWeek().
			$Filter
		));
		$navbar->AddItem('weeks', 'Term',	site_url(
			'calendar/range/'.
			$monday->AcademicYear().'-'.$monday->AcademicTermNameUnique().
			$Filter
		));
		$navbar->AddItem('agenda', 'Agenda',site_url(
			'calendar/agenda/'.
			$Start->Year().'-'.strtolower($Start->Format('M')).'-'.$Start->DayOfMonth().
			$Filter
		));
		$this->main_frame->SetPage($SelectedPage);
	}
	
	
	
	
	
	function oldweek($DateRange = '')
	{
		if (!CheckPermissions('public')) return;
		
		// Load libraries
		$this->load->library('event_manager');      // Processing events
		$this->load->library('academic_calendar');  // Using academic calendar
		$this->load->library('date_uri');           // Nice date uri segments
		$this->load->library('view_calendar_days'); // Days calendar view
		
		
		// Show change events
		$this->load->model('calendar/events_model');
		$ChangedEvents = new EventOccurrenceFilter();
		$ChangedEvents->DisableFilter('private');
		//$ChangedEvents->DisableFilter('active');
		$ChangedEvents->DisableFilter('show');
		$ChangedEvents->SetSpecialCondition(
			'event_occurrence_users.event_occurrence_user_timestamp <
					event_occurrences.event_occurrence_last_modified');
		$notices = $ChangedEvents->GenerateOccurrences(array(
				'state'			=> $ChangedEvents->ExpressionPublicState(),
				'name'			=> 'events.event_name',
				'organisation'	=> 'organisations.organisation_name',
				'start'			=> 'UNIX_TIMESTAMP(event_occurrences.event_occurrence_start_time)',
			));
		foreach ($notices as $notice) {
			$date = new Academic_time($notice['start']);
			$start = $date->Format('l') . ' of week ' . $date->AcademicWeek() .
				' of the ' . $date->AcademicTermName() . ' ' . $date->AcademicTermTypeName();
			
			$message = 'The event ' . $notice['name'] . ' from ' .
				$notice['organisation'] . ' (' . $start . ') has been ' . $notice['state'];
			$this->messages->AddMessage('information', $message, FALSE);
		}
		
		
		$this->pages_model->SetPageCode('calendar_personal');
		if (!empty($DateRange)) {
			// $DateRange Not empty
			
			// Read the date, only allowing a single date (no range data)
			$uri_result = $this->date_uri->ReadUri($DateRange, FALSE);
			if ($uri_result['valid']) {
				// $DateRange Valid
				$start_time = $uri_result['start'];
				$start_time = $start_time->BackToMonday();
				$format = $uri_result['format']; // Use the format in all links
				$days = 7; // force 7 days until view can handle different values.
				
				$this->_ShowCalendar(
						$start_time, $days,
						'/calendar/week/', $format
					);
				return;
				
			} else {
				// $DateRange Invalid
				$this->main_frame->AddMessage('error','Unrecognised date: "'.$DateRange.'"');
			}
		}
		
		// Default to this week
		$format = 'ac';
		$base_time = new Academic_time(time());
		
		$monday = $base_time->BackToMonday();
	
		$this->_ShowCalendar(
				$monday, 7,
				'/calendar/week/', $format
			);
	}
	
	/// Show the calendar between certain Academic_times.
	/**
	 * @param $StartTime Academic_time Start date.
	 * @param $Days integer Number of days to display.
	 * @param $UriBase string The base of the uri on which to build links,
	 *	e.g. '/calendar/week/'
	 * @param $UriFormat string Uri date format identifier as used in Date_uri.
	 */
	function _ShowCalendar($StartTime, $Days, $UriBase, $UriFormat)
	{
		// Sorry about the clutter, this will be moved in a bit but it isn't
		// practical to put it in the view
		$extra_head = <<<EXTRAHEAD
		
			<script src="/javascript/prototype.js" type="text/javascript"></script>
			<script src="/javascript/scriptaculous.js" type="text/javascript"></script>
			<script src="/javascript/calendar.js" type="text/javascript"></script>
			<link href="/stylesheets/calendar.css" rel="stylesheet" type="text/css" />
			
EXTRAHEAD;
		
		// Set up the days view
		$view_calendar_days = new ViewCalendarDays();
		$view_calendar_days->SetUriBase($UriBase);
		$view_calendar_days->SetUriFormat($UriFormat);
		$view_calendar_days->SetRange($StartTime, $Days);
		// Get the data from the db, then we're ready to load
		$view_calendar_days->Retrieve();
		
		// Set up the public frame to use the messages frame
		$this->main_frame->SetExtraHead($extra_head);
		$this->main_frame->SetContent($view_calendar_days);
		
		// Load the public frame view
		$this->main_frame->Load();
	}
	
}
?>
