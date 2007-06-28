<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file helpers/calendar_control.php
 * @brief General calendar controller implementing the main calendar uri interface.
 * @author James Hogan (jh559@cs.york.ac.uk)
 */

/* URL HIERARCHY

/calendar							- personal calendar
	/index							- index page with more config links + preview of cal
	/[view]							- viewing multiple items over time
		/[range]/$range/$filter		- view depends on range
		/agenda/$range/$filter		- agenda style
		/export/$range/$filter		- export to a file format
			/[index]				- information about export options
			/ical					- ical export
	/src							- sources
		/[index]					- information about event sources
		/$source					- event source
			/[index]				- information about the source
			/create					- create an event in the source
			/event/$event			- about a specific event
				/[info]				- view info about specific event
				/edit				- edit a specific event
				/op/$op				- operations such as delete, publish etc
				/occ/$occurrence	- stuff about a specific occurrence
					/[info]/$range	- view info
					/edit/$range	- make changes
					/op				- operations such as delete, publish etc
			/export					- export the contents of a source
			/import					- import data into the source
*/

/// Interface to give to view for providing paths to bits of calendar system.
class CalendarPaths
{
	/// string Base path of calendar.
	protected $mPath = NULL;
	
	/// string Set the default range in usual format.
	protected $mDefaultRange = NULL;
	
	/// string Current calendar mode (range/agenda).
	protected $mCalendarMode = NULL;
	
	/// Set the base path.
	function SetPath($Path)
	{
		$this->mPath = $Path;
	}
	
	/// Set the default range.
	function SetDefaultRange($Range)
	{
		$this->mDefaultRange = $Range;
	}
	
	/// Set the calendar mode (range/agenda).
	function SetCalendarMode($Mode)
	{
		$this->mCalendarMode = $Mode;
	}
	
	/// Get the range path.
	function Range($range = NULL, $filter = NULL)
	{
		$path = $this->mPath . '/view/range/';
		if (NULL !== $range) {
			$path .= $range.'/';
			if (NULL !== $filter) {
				$path .= $filter.'/';
			}
		}
		return $path;
	}
	
	/// Get the agenda path.
	function Agenda($range = NULL, $filter = NULL)
	{
		$path = $this->mPath . '/view/agenda/';
		if (NULL !== $range) {
			$path .= $range.'/';
			if (NULL !== $filter) {
				$path .= $filter.'/';
			}
		}
		return $path;
	}
	
	/// Gets the path to the set calendar mode.
	function Calendar($range = NULL, $filter = NULL)
	{
		switch ($this->mCalendarMode) {
			case 'agenda':
				return $this->Agenda($range, $filter);
				
			case 'range':
				return $this->Range($range, $filter);
				
			default:
				return NULL;
		}
	}
	
	/// Get the event creation path.
	function EventCreate($Source)
	{
		$path = $this->mPath .
			'/src/'.	$Source->GetSourceId().
			'/create/';
		if (NULL !== $this->mDefaultRange) {
			$path .= $this->mDefaultRange.'/';
		}
		return $path;
	}
	
	/// Get the event information path.
	function EventInfo($Event, $range = NULL, $filter = NULL)
	{
		return $this->mPath .
			'/src/'.	$Event->Source->GetSourceId().
			'/event/'.	$Event->SourceEventId.
			'/info'.
			'/'.(NULL !== $range  ? $range  : 'default').
			'/'.(NULL !== $filter ? $filter : 'default');
	}
	
	/// Get the event information path.
	function EventEdit($Event, $range = NULL, $filter = NULL)
	{
		return $this->mPath .
			'/src/'.	$Event->Source->GetSourceId().
			'/event/'.	$Event->SourceEventId.
			'/edit'.
			'/'.(NULL !== $range  ? $range  : 'default').
			'/'.(NULL !== $filter ? $filter : 'default');
	}
	
	/// Get the event occurrence information path.
	function OccurrenceInfo($Occurrence, $range = NULL, $filter = NULL)
	{
		return $this->mPath .
			'/src/'.	$Occurrence->Event->Source->GetSourceId().
			'/event/'.	$Occurrence->Event->SourceEventId.
			'/occ/'.	$Occurrence->SourceOccurrenceId.
			'/info'.
			'/'.(NULL !== $range  ? $range  : 'default').
			'/'.(NULL !== $filter ? $filter : 'default');
	}
	
	/// Get the event occurrence information path.
	function OccurrenceEdit($Occurrence, $range = NULL, $filter = NULL)
	{
		return $this->mPath .
			'/src/'.	$Occurrence->Event->Source->GetSourceId().
			'/event/'.	$Occurrence->Event->SourceEventId.
			'/occ/'.	$Occurrence->SourceOccurrenceId.
			'/edit'.
			'/'.(NULL !== $range  ? $range  : 'default').
			'/'.(NULL !== $filter ? $filter : 'default');
	}
}


$CI = & get_instance();
$CI->load->model('subcontrollers/uri_tree_subcontroller');

/// Calendar main controller.
class Calendar_subcontroller extends UriTreeSubcontroller
{
	/// string Required permission level.
	protected $mPermission = 'public';
	/// CalendarPaths Path calculator.
	protected $mPaths = NULL;
	/// array[name => category] Categories to display filters for.
	protected $mCategories = NULL;
	/// string Current range of dates.
	protected $mDateRange = 'today';
	/// string The default date range.
	protected $mDefaultRange = 'today:1week';
	
	/// CalendarSource Main source
	protected $mMainSource = NULL;
	/// CalendarSource Source in use.
	protected $mSource = NULL;
	/// string Event identifier
	protected $mEvent = NULL;
	
	/// bool Whether to have tabs.
	protected $mTabs = TRUE;
	
	/// Filter definition.
	protected $sFilterDef = array(
		// category
		'cat' => array(
			'name' => 'category',
			array(
				// Filled in by SetupCategories
			),
		),
		'att' => array(
			'name' => 'attending',
			array(
				'no-declined',
				'no-maybe',
				'no-accepted',
				'declined',
				'maybe',
				'accepted',
			),
		),
		'source' => array(
			array(
				'type' => 'int',
			),
		),
		'search' => array(
			array(
				'name' => 'field',
				'all',
				'name',
				'description',
			),
			array(
				'name' => 'criteria',
				'type' => 'string',
			),
			array(
				'name' => 'flags',
				'count' => array(0),
				'regex',
				'case',
			),
		),
	);
	
	/// virtual Set the base path.
	function SetPath($Path)
	{
		$this->mPaths = new CalendarPaths();
		$this->mPaths->SetPath($Path);
	}
	
	/// Default constructor.
	function __construct()
	{
		// Provide the ComplexController class with the url structure
		parent::__construct(array(
			'' => 'view',
			'index' => 'index',
			'view' => array(
				'' => 'range',
				'_in' => array(
					array(
						array(
							'' => '*',
							'*' => array(
								'_store' => 'Range',
								'' => '*',
								'*' => array('_store' => 'Filter'),
							),
						),
						'range' => 'range',
						'agenda' => 'agenda',
						'export' => array(
							'' => 'index',
							'index' => NULL,
							'ical' => NULL,
						),
					),
				),
			),
			'src' => array(
				'' => 'index',
				'index' => 'src_index',
				'_match' => array(
					'is_numeric' => array(
						'_store' => 'SourceId',
						'' => 'index',
						'index' => 'src_source_index',
						'create' => 'src_source_create',
						'event' => array(
							'*' => array(
								'_store' => 'EventId',
								'' => 'info',
								'_in' => array(
									array(
										array(
											'' => '*',
											'*' => array(
												'_store' => 'Range',
												'' => '*',
												'*' => array(
													'_store' => 'Filter',
												),
											),
										),
										'info' => 'src_event_info',
										'edit' => 'src_event_edit',
									),
								),
								'op' => 'src_event_op',
								'occ' => array(
									'*' => array(
										'_store' => 'OccurrenceId',
										'' => 'info',
										'_in' => array(
											array(
												array(
													'' => '*',
													'*' => array(
														'_store' => 'Range',
														'' => '*',
														'*' => array(
															'_store' => 'Filter',
														),
													),
												),
												'info' => 'src_event_info',
												'edit' => 'src_event_edit',
											),
										),
										'op' => 'src_event_op',
									),
								),
							),
						),
						'export' => NULL,
						'import' => NULL,
					),
				),
			),
		));
	}
	
	/// Set the required permission level.
	/**
	 * @param $Permission string Required permission level.
	 */
	function _SetPermission($Permission = 'public')
	{
		/// @pre is_string(@a $Permission).
		assert('is_string($Permission)');
		// Initialise
		$this->mPermission = $Permission;
	}
	
	/// Index page with calendar preview + other stuff.
	function index(/* ... */)
	{
		OutputModes('xhtml','fbml');
		if (!CheckPermissions($this->mPermission)) return;
		
		/// @todo Put this into a view library.
		$data = array();
		$this->main_frame->SetContentSimple('calendar/index', $data);
		
		$this->main_frame->Load();
	}
	
	function range()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$this->pages_model->SetPageCode('calendar_personal');
		
		$this->_SetupMyCalendar();
		$this->mPaths->SetCalendarMode('range');
		$this->SetupCategories();
		
		$date_range = array_key_exists('Range', $this->mData)
					? $this->mData['Range']
					: $this->mDefaultRange;
		$filter = array_key_exists('Filter', $this->mData)
				? $this->mData['Filter']
				: NULL;
		
		$date_range_split = explode(':', $date_range);
		$this->mPaths->SetDefaultRange($date_range_split[0]);
		
		$this->load->library('date_uri');
		$range = $this->date_uri->ReadUri($date_range, TRUE);
		$now = new Academic_time(time());
		if (!$range['valid']) {
			$date_range = $this->mDefaultRange;
			$range = $this->date_uri->ReadUri($date_range, TRUE);
			assert($range['valid']);
		}
		$start	= $range['start'];
		$end	= $range['end'];
		
		$days = Academic_time::DaysBetweenTimestamps(
			$start->Timestamp(),
			$end->Timestamp()
		);
		
		$this->mDateRange = $date_range;
		
		$this->main_frame->SetTitleParameters(array(
			'range' => $range['description'],
		));
		
		/// @todo it seems to be calling ReadUri twice, once in this function and once in each callee.
		if ($days > 7) {
			$range_view = $this->GetWeeks(
				$this->mMainSource,
				$date_range,
				$filter,
				$range['format']);
		} elseif ($days > 1) {
			$range_view = $this->GetDays(
				$this->mMainSource,
				$date_range,
				$filter,
				$range['format']);
		} else {
			$range_view = $this->GetDay(
				$this->mMainSource,
				$date_range,
				$filter,
				$range['format']);
		}
		
		$this->main_frame->SetContent($range_view);
		
		$this->main_frame->Load();
	}
	
	function GetDay(&$sources, $DateRange = NULL, $Filter = NULL, $Format = 'ac:re')
	{
		$range = $this->date_uri->ReadUri($DateRange, TRUE);
		$now = new Academic_time(time());
		if ($range['valid']) {
			$start = $range['start'];
		} else {
			$start = $now->Midnight();
		}
		$end = $start->Adjust('1day');
		
		$sources->SetRange($start->Timestamp(), $end->Timestamp());
		$sources->SetTodoRange(time(), time());
		$this->ReadFilter($sources, $Filter);
		$sources->EnableGroup('todo');
		
		$create_sources = array();
		foreach ($sources->GetSources() as $source) {
			if ($source->IsSupported('create')) {
				$create_sources[] = $source;
			}
		}
		
		$calendar_data = new CalendarData();
		
		$this->messages->AddMessages($sources->FetchEvents($calendar_data));
		
		// Display data
		$this->load->library('calendar_frontend');
		$this->load->library('calendar_view_days');
		$this->load->library('calendar_view_todo_list');
		
		$days = new CalendarViewDays();
		$days->SetCalendarData($calendar_data);
		$days->SetPaths($this->mPaths);
		$days->SetRangeFormat($Format);
		$days->SetRangeFilter(NULL !== $Filter ? '/'.$Filter : '');
		$days->SetStartEnd($start->Timestamp(), $end->Timestamp());
		$days->SetCategories($this->mCategories);
		
		
		$todo = new CalendarViewTodoList();
		$todo->SetCalendarData($calendar_data);
		$todo->SetCategories($this->mCategories);
		
		$view_mode_data = array(
			'DateDescription' => 'Today probably!',
			'DaysView'        => &$days,
			'TodoView'        => &$todo,
		);
		$view_mode = new FramesFrame('calendar/day', $view_mode_data);
		
		$data = array(
			'Filters'	=> $this->GetFilters($sources),
			'ViewMode'	=> $view_mode,
			'RangeDescription' => $range['description'],
			'Path'		=> $this->mPaths,
			'CreateSources'	=> $create_sources,
		);
		
		$this->SetupTabs('day', $start, $Filter);
		
		return new FramesView('calendar/my_calendar', $data);
	}
	
	function GetDays(&$sources, $DateRange = NULL, $Filter = NULL, $Format = 'ac:re')
	{
		// Read date range
		$range = $this->date_uri->ReadUri($DateRange, TRUE);
		$now = new Academic_time(time());
		if ($range['valid']) {
			$start = $range['start'];
			$end = $range['end'];
		} else {
			$start = $now->Midnight();
			$end = $start->Adjust('7day');
		}
		
		$sources->SetRange($start->Timestamp(), $end->Timestamp());
		$this->ReadFilter($sources, $Filter);
		
		$calendar_data = new CalendarData();
		
		$this->messages->AddMessages($sources->FetchEvents($calendar_data));
		
		// Display data
		$this->load->library('calendar_frontend');
		$this->load->library('calendar_view_days');
		
		$days = new CalendarViewDays();
		$days->SetCalendarData($calendar_data);
		$days->SetStartEnd($start->Timestamp(), $end->Timestamp());
		$days->SetPaths($this->mPaths);
		$days->SetRangeFormat($Format);
		$days->SetRangeFilter(NULL !== $Filter ? '/'.$Filter : '');
		$days->SetCategories($this->mCategories);
		
		$data = array(
			'Filters'	=> $this->GetFilters($sources),
			'ViewMode'	=> $days,
			'RangeDescription' => $range['description'],
			'Path' => $this->mPaths,
		);
		
		$this->SetupTabs('days', $start, $Filter);
		
		return new FramesView('calendar/my_calendar', $data);
	}
	
	function GetWeeks(&$sources, $DateRange = NULL, $Filter = NULL, $Format = 'ac:re')
	{
		// Read date range
		$range = $this->date_uri->ReadUri($DateRange, TRUE);
		$now = new Academic_time(time());
		if ($range['valid']) {
			$start = $range['start'];
			$end = $range['end'];
		} else {
			$start = $now->BackToMonday();
			$end = $start->Adjust('4weeks');
		}
		
		$sources->SetRange($start->Timestamp(), $end->Timestamp());
		$this->ReadFilter($sources, $Filter);
		
		$calendar_data = new CalendarData();
		
		$this->messages->AddMessages($sources->FetchEvents($calendar_data));
		
		// Display data
		$this->load->library('calendar_frontend');
		$this->load->library('calendar_view_weeks');
		
		$weeks = new CalendarViewWeeks();
		$weeks->SetCalendarData($calendar_data);
		$weeks->SetStartEnd($start->Timestamp(), $end->Timestamp());
		$weeks->SetPaths($this->mPaths);
		$weeks->SetRangeFormat($Format);
		$weeks->SetRangeFilter(NULL !== $Filter ? '/'.$Filter : '');
		$weeks->SetCategories($this->mCategories);
		
		$data = array(
			'Filters'	=> $this->GetFilters($sources),
			'ViewMode'	=> $weeks,
			'RangeDescription' => $range['description'],
			'Path' => $this->mPaths,
		);
		
		$this->SetupTabs('weeks', $start, $Filter);
		
		return new FramesView('calendar/my_calendar', $data);
	}
	
	/// Display agenda.
	function agenda()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$this->_SetupMyCalendar();
		$this->mPaths->SetCalendarMode('agenda');
		$this->pages_model->SetPageCode('calendar_agenda');
		
		$date_range = array_key_exists('Range', $this->mData)
					? $this->mData['Range']
					: NULL;
		$filter = array_key_exists('Filter', $this->mData)
				? $this->mData['Filter']
				: NULL;
		
		if (NULL !== $date_range) {
			$this->mDateRange = $date_range;
		}
		
		$this->SetupCategories();
		$this->mMainSource->SetRange(time(), strtotime('2month'));
		$this->ReadFilter($this->mMainSource, $filter);
		
		$calendar_data = new CalendarData();
		
		$this->messages->AddMessages($this->mMainSource->FetchEvents($calendar_data));
		
		// Display data
		$this->load->library('calendar_frontend');
		$this->load->library('calendar_view_agenda');
		
		$agenda = new CalendarViewAgenda();
		$agenda->SetCalendarData($calendar_data);
		$agenda->SetCategories($this->mCategories);
		
		$data = array(
			'Filters'	=> $this->GetFilters($this->mMainSource),
			'ViewMode'	=> $agenda,
		);
		
		$this->SetupTabs('agenda', new Academic_time(time()), $filter);
		
		$this->main_frame->SetContentSimple('calendar/my_calendar', $data);
		$this->main_frame->Load();
	}
	
	/// Sources index page.
	function src_index()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$this->_SetupMyCalendar();
		$sources = $this->mMainSource->GetSources();
		foreach ($sources as $source) {
			
		}
		
		$this->main_frame->SetContentSimple('calendar/sources');
		$this->main_frame->Load();
	}
	
	/// Specific source index page.
	function src_source_index()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$this->main_frame->SetContentSimple('calendar/source');
		$this->main_frame->Load();
	}
	
	function src_source_create()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$this->_GetSource();
		$this->pages_model->SetPageCode('calendar_new_event');
		$this->main_frame->SetTitleParameters(array(
			'source' => $this->mSource->GetSourceName(),
		));
		if (!$this->mSource->IsSupported('create')) {
			// Create isn't supported with this source
			$this->messages->AddMessage('error', 'You cannot create events in this calendar source');
			$this->main_frame->Load();
			return;
		}
		
		$categories = $this->mSource->GetAllCategories();
		
		/// @todo make standard functions and views for recurrence interface
		$form_id = 'caladd_';
		$length_ranges = array(
			'summary' => array(3, 255),
			'description' => array(NULL, 1 << 24 - 1),
		);
		
		$input = array(
			'name' => '',
			'summary' => '',
			'description' => '',
			'start' => strtotime('+3hour'),
			'end'   => strtotime('+4hour'),
			'allday' => FALSE,
			'time_associated'    => TRUE,
			'eventcategory' => -1,
		);
		$summary = $this->input->post($form_id.'summary');
		if (FALSE !== $summary) {
			// Get the data
			$failed_validation = FALSE;
			$input['summary']        = $summary;
			$input['start']          = $this->input->post($form_id.'start');
			$input['end']            = $this->input->post($form_id.'end');
			$input['allday']         = (FALSE !== $this->input->post($form_id.'allday'));
			$input['location']       = $this->input->post($form_id.'location');
			$input['category']       = $this->input->post($form_id.'category');
			$input['description']    = $this->input->post($form_id.'description');
			$input['frequency']      = $this->input->post($form_id.'frequency');
			// Simple derived data
			$input['time_associated'] = !$input['allday'];
			$input['name'] = $input['summary'];
			
			// Validate numbers
			foreach (array('start','end') as $ts_name) {
				if (is_numeric($input[$ts_name])) {
					$input[$ts_name] = (int)$input[$ts_name];
				} else {
					$this->messages->AddMessage('error', 'Invalid '.$ts_name.' timestamp.');
				}
			}
			
			// Validate strings
			foreach ($length_ranges as $field => $range) {
				if (FALSE !== $input[$field]) {
					$len = strlen($input[$field]);
					if (NULL !== $range[0] && $len < $range[0]) {
						$failed_validation = TRUE;
						$this->messages->AddMessage('error', 'The specified '.$field.' was not long enough. It must be at least '.$range[0].' characters long.');
					}
					if (NULL !== $range[1] && $len > $range[1]) {
						$failed_validation = TRUE;
						$this->messages->AddMessage('error', 'The specified '.$field.' was too long. It must be at most '.$range[1].' characters long.');
					}
				}
			}
			
			// Validate category
			if (!array_key_exists($input['category'], $categories))
			{
				$failed_validation = TRUE;
				$this->messages->AddMessage('error', 'You did not specify a valid event category');
			} else {
				$input['eventcategory'] = $input['category'];
				$input['category'] = $categories[$input['category']]['id'];
			}
			
			// Validate recurrence based on frequency
			if ('none' !== $input['frequency']) {
				// Read interval
				$input['interval']     = $this->input->post($form_id.'interval');
				// Validate interval
				if (!is_numeric($input['interval']) || $input['interval'] < 1) {
					$failed_validation = TRUE;
					$this->messages->AddMessage('error', 'You specified an invalid interval');
				} else {
					$input['interval'] = (int)$input['interval'];
				}
				if ('daily' === $input['frequency']) {
				} elseif ('weekly' === $input['frequency']) {
					$input['onday']          = $this->input->post($form_id.'onday');
				} elseif ('yearly' === $input['frequency']) {
				}
			}
			
			if (!$failed_validation) {
				$start = $input['start'];
				$end   = $input['end'];
				if ($end < $start) {
					$this->messages->AddMessage('error', 'You specified the end time before the start time.');
					$failed_validation = TRUE;
				} else {
					if (!$input['time_associated']) {
						$end = strtotime('1day', $end);
					}
					$input['recur'] = new RecurrenceSet();
					$input['recur']->SetStartEnd($start, $end);
					
					// daily
					if ('daily' === $input['frequency']) {
						$rrule = new CalendarRecurRule();
						$rrule->SetFrequency('daily');
						$rrule->SetInterval($input['interval']);
						$input['recur']->AddRRules($rrule);
						
					} elseif ('weekly' === $input['frequency']) {
						$rrule = new CalendarRecurRule();
						$rrule->SetFrequency('weekly');
						$rrule->SetInterval($input['interval']);
						static $onday_translate = array(
							'mon' => 'MO',
							'tue' => 'TU',
							'wed' => 'WE',
							'thu' => 'TH',
							'fri' => 'FR',
							'sat' => 'SA',
							'sun' => 'SU',
						);
						foreach ($input['onday'] as $day => $on) {
							$short_day = strtoupper(substr($day,0,2));
							if (array_key_exists($short_day, CalendarRecurRule::$sWeekdays)) {
								$rrule->SetByDay(CalendarRecurRule::$sWeekdays[$short_day]);
							}
						}
						$input['recur']->AddRRules($rrule);
						
					} elseif ('yearly' === $input['frequency']) {
						$rrule = new CalendarRecurRule();
						$rrule->SetFrequency('yearly');
						$rrule->SetInterval($input['interval']);
						$input['recur']->AddRRules($rrule);
						
					}
					
					$messages = $this->mSource->CreateEvent($input);
					if (!array_key_exists('error', $messages) && !empty($messages['error'])) {
						$this->messages->AddMessage('success', 'Event created successfully.');
					}
					$this->messages->AddMessages($messages);
				}
			}
		}
		
		$input['target'] = $this->uri->uri_string();
		$data = array(
			'EventCategories' => $categories,
			'AddForm' => $input,
		);
		
		$this->main_frame->SetContent(new FramesView('calendar/simpleadd', $data));
		
		/// Load extra files required for JS date and time selector
		$this->main_frame->SetData('extra_head',
			'<style type="text/css">@import url("/stylesheets/calendar_select.css");</style>'."\n".
			'<script type="text/javascript" src="/javascript/calendar_select.js"></script>'."\n".
			'<script type="text/javascript" src="/javascript/calendar_select-en.js"></script>'."\n".
			'<script type="text/javascript" src="/javascript/calendar_select-setup.js"></script>'."\n");
		
		$this->main_frame->Load();
	}
	
	/// Event information.
	function src_event_info()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$source_id = $this->mData['SourceId'];
		$event_id = $this->mData['EventId'];
		$occurrence_specified = array_key_exists('OccurrenceId', $this->mData);
		if ($occurrence_specified) {
			$occurrence_id = $this->mData['OccurrenceId'];
		} else {
			$occurrence_id = NULL;
		}
		
		$this->_GetSource();
		
		$calendar_data = new CalendarData();
		$this->mMainSource->FetchEvent($calendar_data, $source_id, $event_id);
		$calendar_data->FindOrganisationInformation();
		$events = $calendar_data->GetEvents();
		// Get the redirect url tail
		$args = func_get_args();
		$tail = implode('/', $args);
		if (array_key_exists(0, $events)) {
			$event = $events[0];
		
			$this->pages_model->SetPageCode('calendar_event_info');
			
			// Find the occurrence
			$found_occurrence = NULL;
			if (NULL !== $occurrence_id) {
				foreach ($event->Occurrences as $key => $occurrence) {
					if ($occurrence->SourceOccurrenceId == $occurrence_id) {
						$found_occurrence = & $event->Occurrences[$key];
						break;
					}
				}
				if (NULL === $found_occurrence) {
					$this->messages->AddMessage('warning', 'The event occurrence with id '.$occurrence_id.' does not belong to the event with id '.$event_id.'.');
					redirect($this->mPaths->EventInfo($event).'/'.$tail);
					return;
				}
			}
			if (NULL === $occurrence_id) {
				// default to the only occurrence if there is only one.
				if (count($event->Occurrences) == 1) {
					$found_occurrence = & $event->Occurrences[0];
					$occurrence_id = $found_occurrence->SourceOccurrenceId;
				}
			}
			if ($this->input->post('evview_return')) {
				// REDIRECT
				redirect($tail);
				
			} elseif ($this->input->post('evview_edit')) {
				if ($event->ReadOnly) {
					$this->messages->AddMessage('error', 'You do not have permission to make changes to this event.');
				} else {
					// REDIRECT
					if ($occurrence_specified) {
						$path = $this->mPaths->OccurrenceEdit($found_occurrence);
					} else {
						$path = $this->mPaths->EventEdit($event);
					}
					return redirect($path.'/'.$tail);
				}
			}
			
			$data = array(
				'Event' => &$event,
				'ReadOnly' => $this->mSource->IsSupported('create'),
				'FailRedirect' => '/'.$tail,
				'Path' => $this->mPaths,
			);
			if (NULL !== $occurrence_id) {
				$data['Occurrence'] = &$found_occurrence;
				$data['Attendees'] = $this->mSource->GetOccurrenceAttendanceList($occurrence_id);
			} else {
				$data['Occurrence'] = NULL;
			}
			
			$this->main_frame->SetTitleParameters(array(
				'source' => $this->mSource->GetSourceName(),
				'event' => $event->Name,
			));
			
			if (NULL !== $occurrence_id) {
				$link_time = $found_occurrence->StartTime;
			} else {
				$link_time = new Academic_time(time());
			}
			$this->SetupTabs('', $link_time);
			
			$this->main_frame->SetContent(
				new FramesView('calendar/event', $data)
			);
			
		} else {
			$this->ErrorNotAccessible($tail);
		}
		$this->main_frame->Load();
	}
	
	function src_event_edit()
	{
		if (!CheckPermissions($this->mPermission)) return;
		
		$source_id = $this->mData['SourceId'];
		$event_id = $this->mData['EventId'];
		$occurrence_specified = array_key_exists('OccurrenceId', $this->mData);
		if ($occurrence_specified) {
			$occurrence_id = $this->mData['OccurrenceId'];
		} else {
			$occurrence_id = NULL;
		}
		
		$this->pages_model->SetPageCode('calendar_event_edit');
		
		$this->_GetSource();
		
		$calendar_data = new CalendarData();
		$this->mMainSource->FetchEvent($calendar_data, $source_id, $event_id);
		$events = $calendar_data->GetEvents();
		// Get the redirect url tail
		$args = func_get_args();
		$tail = implode('/', $args);
		if (array_key_exists(0, $events)) {
			$event = $events[0];
			
			// Find the occurrence
			$found_occurrence = NULL;
			if (NULL !== $occurrence_id) {
				foreach ($event->Occurrences as $key => $occurrence) {
					if ($occurrence->SourceOccurrenceId == $occurrence_id) {
						$found_occurrence = & $event->Occurrences[$key];
						break;
					}
				}
				if (NULL === $found_occurrence) {
					$this->messages->AddMessage('warning', 'The event occurrence with id '.$occurrence_id.' does not belong to the event with id '.$event_id.'.');
					redirect($this->mPaths->EventInfo($event).'/'.$tail);
					return;
				}
			}
			if (NULL === $occurrence_id) {
				// default to the only occurrence if there is only one.
				if (count($event->Occurrences) == 1) {
					$found_occurrence = & $event->Occurrences[0];
					$occurrence_id = $found_occurrence->SourceOccurrenceId;
				}
			}
			$return_button = (bool)$this->input->post('evview_return');
			if (NULL !== $event && $event->ReadOnly) {
				$return_button = TRUE;
				$this->messages->AddMessage('error', 'You do not have permission to make changes to this event.');
			}
			if ($return_button) {
				// REDIRECT
				if ($occurrence_specified) {
					$path = $this->mPaths->OccurrenceInfo($found_occurrence);
				} else {
					$path = $this->mPaths->EventInfo($event);
				}
				return redirect($path.'/'.$tail);
				
			} else {
				$data = array(
					'Event' => &$event,
					'ReadOnly' => $this->mSource->IsSupported('create'),
					'FailRedirect' => '/'.$tail,
					'Path' => $this->mPaths,
				);
				if (NULL !== $occurrence_id) {
					$data['Occurrence'] = &$found_occurrence;
					$data['Attendees'] = $this->mSource->GetOccurrenceAttendanceList($occurrence_id);
				} else {
					$data['Occurrence'] = NULL;
				}
				
				$this->main_frame->SetTitleParameters(array(
					'source' => $this->mSource->GetSourceName(),
					'event' => $event->Name,
				));
				
				if (NULL !== $occurrence_id) {
					$link_time = $found_occurrence->StartTime;
				} else {
					$link_time = new Academic_time(time());
				}
				$this->SetupTabs('', $link_time);
				
				$this->main_frame->SetContent(
					new FramesView('calendar/event_edit', $data)
				);
			}
		} else {
			$this->ErrorNotAccessible($tail);
		}
		$this->main_frame->Load();
	}
	
	/// Export as ical.
	function export_ical()
	{
		OutputModes('ical');
		if (!CheckPermissions($this->mPermission)) return;
		
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
	
	/// Show an error, not accessible message.
	protected function ErrorNotAccessible($Tail)
	{
		$this->pages_model->SetPageCode('calendar_event_not_accessible');
		$description = '<p>The specified event does not exist or has not been published for public viewing.</p>';
		if (!$this->user_auth->isLoggedIn) {
			$description .= '<p>You are not currently logged in. If you created the event you may be able to see it after you <a href="'.site_url('login/main'.$this->uri->uri_string()).'">log in</a>.</p>';
		}
		$this->main_frame->SetContentSimple('general/return',array(
			'Title' => 'Event Not Accessible',
			'Description' => $description,
			'Target' => site_url($Tail),
			'Caption' => 'Return',
		));
	}
	
	/// Setup the main source.
	protected function _SetupMyCalendar()
	{
		$this->load->library('calendar_backend');
		$this->load->library('calendar_source_my_calendar');
		
		$this->mMainSource = new CalendarSourceMyCalendar();
	}
	
	/// Setup main source and get specific source, erroring if problem.
	protected function _GetSource()
	{
		$this->_SetupMyCalendar();
		$this->mSource = $this->mMainSource->GetSource((int)$this->mData['SourceId']);
		if (NULL === $this->mSource) {
			show_404();
		}
	}
	
	/// Get the categories.
	protected function SetupCategories()
	{
		// Get categories and reindex by name
		$categories = $this->mMainSource->GetSource(0)->GetAllCategories();
		foreach ($categories as $category) {
			$this->mCategories[$category['name']] = $category;
			$this->sFilterDef['cat'][0][] = 'no-'.$category['name'];
		}
	}
	
	/// Set up the tabs on the main_frame.
	/**
	 * @param $SelectedPage string Selected Page.
	 * @pre CheckPermissions must have already been called.
	 */
	protected function SetupTabs($SelectedPage, $Start, $Filter = NULL)
	{
		if ($this->mTabs) {
			$navbar = $this->main_frame->GetNavbar();
			if (NULL === $Filter) {
				$Filter = '/';
			} else {
				$Filter = '/'.$Filter;
			}
			$navbar->AddItem('day', 'Day',
				site_url($this->mPaths->Range(
					$Start->AcademicYear().'-'.$Start->AcademicTermNameUnique().'-'.$Start->AcademicWeek().'-'.$Start->Format('D'),
					$Filter
				))
			);
			$monday = $Start->BackToMonday();
			$navbar->AddItem('days', 'Week',
				site_url($this->mPaths->Range(
					$monday->AcademicYear().'-'.$monday->AcademicTermNameUnique().'-'.$monday->AcademicWeek(),
					$Filter
				))
			);
			$navbar->AddItem('weeks', 'Term',
				site_url($this->mPaths->Range(
					$monday->AcademicYear().'-'.$monday->AcademicTermNameUnique(),
					$Filter
				))
			);
			//if (is_string($this->mAgenda)) {
				$navbar->AddItem('agenda', 'Agenda',
					site_url($this->mPaths->Agenda(
						$Start->Year().'-'.strtolower($Start->Format('M')).'-'.$Start->DayOfMonth(),
						$Filter
					))
				);
			//}
			$this->main_frame->SetPage($SelectedPage);
		}
	}
	
	/// Read the filter url and set up the sources.
	function ReadFilter(&$sources, $Filter)
	{
		// Read filter
		// eg
		// cat:no-social.att:no-no.search:all:yorker:case
		$this->load->library('filter_uri');
		$filter_def = new FilterDefinition($this->sFilterDef);
		if (NULL === $Filter) {
			$Filter = '';
		}
		
		$filter = $filter_def->ReadUri($Filter);
		
		if (FALSE === $filter) {
			$this->messages->AddMessage('error', 'The filter text in the uri was not valid');
		} else {
			$sources->DisableGroup('hide');
			$sources->EnableGroup('show');
			$sources->EnableGroup('rsvp');
			if (array_key_exists('att', $filter)) {
				foreach ($filter['att'] as $attendence) {
					switch ($attendence[0]) {
						case 'no-declined':
							$sources->DisableGroup('hide');
							break;
						case 'no-maybe':
							$sources->DisableGroup('show');
							break;
						case 'no-accepted':
							$sources->DisableGroup('rsvp');
							break;
						case 'declined':
							$sources->EnableGroup('hide');
							break;
						case 'maybe':
							$sources->EnableGroup('show');
							break;
						case 'accepted':
							$sources->EnableGroup('rsvp');
							break;
					}
				}
			}
			if (array_key_exists('cat', $filter)) {
				$cats = array();
				foreach ($filter['cat'] as $category) {
					$cats[] = $category[0];
				}
				foreach ($this->mCategories as $category) {
					$negator = 'no-'.$category['name'];
					if (in_array($negator, $cats)) {
						$sources->DisableCategory($category['name']);
					}
				}
			}
		}
	}
	
	
	protected function GenFilterUrl($Filters)
	{
		$results = array();
		foreach ($Filters as $key => $values) {
			foreach ($values as $name => $value) {
				if ($value) {
					$results[] = $key.':'.$name;
				}
			}
		}
		$date_range = (NULL === $this->mDateRange ? $this->mDefaultRange : $this->mDateRange);
		return site_url($this->mPaths->Calendar($date_range, implode('.',$results)));
	}
	
	protected function AlteredFilter($Filter, $key, $name, $value = NULL)
	{
		if (NULL === $value) {
			$Filter[$key][$name] = !$Filter[$key][$name];
		} else {
			$Filter[$key][$name] = $value;
		}
		return $Filter;
	}
	
	/// Get the filters.
	/**
	 */
	protected function GetFilters($Sources)
	{
		$Filter = array(
			'att' => array(
				'declined' => $Sources->GroupEnabled('hide'),
				'no-accepted' => !$Sources->GroupEnabled('rsvp'),
				'no-maybe'    => !$Sources->GroupEnabled('show'),
			),
			'cat' => array(
				// Filled in in after initialisation
			),
		);
		// Fill categories
		foreach ($this->mCategories as $category) {
			$Filter['cat']['no-'.$category['name']] = !$Sources->CategoryEnabled($category['name']);
		}
		
		// First add categories to the filters
		$filters = array();
		foreach ($this->mCategories as $category) {
			$filters['cat_'.$category['name']] = array(
				'name'			=> $category['name'],
				'field'			=> 'category',
				'value'			=> $category['name'],
				'selected'		=> $Sources->CategoryEnabled($category['name']),
				'description'	=> $category['name'],
				'display'		=> 'block',
				'colour'		=> $category['colour'],
				'link'			=> $this->GenFilterUrl($this->AlteredFilter($Filter, 'cat', 'no-'.$category['name'])),
			);
		}
		
		// Then the attendance filters
		$filters['hidden'] = array(
			'name'			=> 'not attending',
			'field'			=> 'visibility',
			'value'			=> 'no',
			'selected'		=> $Sources->GroupEnabled('hide'),
			'description'	=> 'Include those which I have hidden',
			'display'		=> 'image',
			'selected_image'	=> '/images/prototype/calendar/filter_hidden_select.gif',
			'unselected_image'	=> '/images/prototype/calendar/filter_hidden_unselect.gif',
			'link'			=> $this->GenFilterUrl($this->AlteredFilter($Filter, 'att', 'declined')),
		);
		$filters['visible'] = array(
			'name'			=> 'maybe attending',
			'field'			=> 'visibility',
			'value'			=> 'maybe',
			'selected'		=> $Sources->GroupEnabled('show'),
			'description'	=> 'Include those which I have not hidden',
			'display'		=> 'image',
			'selected_image'	=> '/images/prototype/calendar/filter_visible_select.png',
			'unselected_image'	=> '/images/prototype/calendar/filter_visible_unselect.png',
			'link'			=> $this->GenFilterUrl($this->AlteredFilter($Filter, 'att', 'no-maybe')),
		);
		$filters['rsvp'] = array(
			'name'			=> 'attending',
			'field'			=> 'visibility',
			'value'			=> 'yes',
			'selected'		=> $Sources->GroupEnabled('rsvp'),
			'description'	=> 'Only those to which I\'ve RSVPd',
			'display'		=> 'image',
			'selected_image'	=> '/images/prototype/calendar/filter_rsvp_select.gif',
			'unselected_image'	=> '/images/prototype/calendar/filter_rsvp_unselect.gif',
			'link'			=> $this->GenFilterUrl($this->AlteredFilter($Filter, 'att', 'no-accepted')),
		);
		
		// The filters are the deliverable
		return $filters;
	}
}

?>
