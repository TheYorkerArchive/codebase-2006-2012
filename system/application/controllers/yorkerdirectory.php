<?php

/// Yorker directory.
/**
 * @author Owen Jones (oj502@york.ac.uk)
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * The URI /directory maps to this controller (see config/routes.php).
 *
 * Any 2nd URI segment is sent to Yorkerdirectory::view (see config/routes.php).
 *
 * Any 3rd URI segment (e.g. events) is sent to the function with the same value.
 *	(see config/routes.php).
 */
class Yorkerdirectory extends Controller
{
	/// Default constructor.
	function __construct()
	{
		parent::Controller();

		$this->load->library('organisations');

		$this->load->model('directory_model');
		$this->load->model('prefs_model');

		$this->load->helper('text');
		$this->load->library('image');
		$this->load->helper('wikilink');
	}

	/// Set up the directory frame
	/**
	 * @param $DirectoryEntry Organisation data array.
	 * @pre @a $OrganisationData is valid organisation array.
	 * @post Frame_directory frame is loaded and ready to use.
	 */
	private function _SetupOrganisationFrame($DirectoryEntry)
	{
		$this->load->library('frame_directory');
		$data = $this->organisations->_GetOrgData($DirectoryEntry);
		$navbar = $this->main_frame->GetNavbar('directory');
		$navbar->AddItem('about', 'About',
				'/directory/'.$DirectoryEntry);
		$navbar->AddItem('notices', 'Notices',
				'/directory/'.$DirectoryEntry.'/notices');
		$navbar->AddItem('calendar', 'Calendar',
				'/directory/'.$DirectoryEntry.'/calendar');
		$navbar->AddItem('members', 'Members',
				'/directory/'.$DirectoryEntry.'/members');
		if($data['organisation']['type'] == 'Societies')
		{
			$navbar->AddItem('reviews', 'Reviews',
					'/directory/'.$DirectoryEntry.'/reviews');
		}
	}
	
	/// Set up the index navigation bar.
	private function _SetupIndexNavbar()
	{
		$navbar = $this->main_frame->GetNavbar('directory_index');
		$navbar->AddItem('list', 'List', '/directory');
		$navbar->AddItem('map', 'Map', '/directory/map');
	}

	/// Directory index page.
	/**
	 * @note POST data:
	 *	- 'search' (search pattern, optional)
	 */
	function index()
	{
		if (!CheckPermissions('public')) return;

		$this->pages_model->SetPageCode('directory_index');

		$this->_SetupIndexNavbar();
		$this->main_frame->SetPage('list', 'directory_index');

		$data = array();

		$data['maintext'] = $this->pages_model->GetPropertyText('maintext');

		// Get the search pattern from POST (optional)
		$search_pattern = $this->input->post('search_directory', TRUE);
		// Get the organisations matching the search and pass the search pattern
		// to the view as well
		$data['organisations'] = $this->organisations->_GetOrgs($search_pattern);
		$data['search'] = $search_pattern;

		// Get organisations children
		$data['children'] = $this->organisations->_GetOrgsChildren();
		
		// Get organisation types
		$data['organisation_types'] = $this->organisations->_GetOrganisationTypes($data['organisations'], TRUE);

		//Libary for AtoZ system
		$this->load->library('character_lib'); //This character libary is used by the view, so load it here

		// Set up the directory view
		$directory_view = $this->frames->view('directory/directory', $data);

		// Set up the public frame to use the directory view
		$this->main_frame->SetContent($directory_view);

		// Include the javascript
		$this->main_frame->IncludeJs('javascript/directory.js');
		$this->main_frame->IncludeCss('stylesheets/directory.css');

		// Load the public frame view
		$this->main_frame->Load();
	}

	function map() {
		if (!CheckPermissions('public')) return;

		$this->load->library('maps');

		$this->pages_model->SetPageCode('directory_map');

		$this->_SetupIndexNavbar();
		$this->main_frame->SetPage('map', 'directory_index');

		$data = array();

		// Get the search pattern from POST (optional)
		$search_pattern = $this->input->post('search_directory', TRUE);
		// Get the organisations matching the search and pass the search pattern
		// to the view as well
		$data['organisations'] = $this->organisations->_GetOrgs($search_pattern);
		$data['search'] = $search_pattern;

		$data['organisation_types'] = $this->organisations->_GetOrganisationTypes($data['organisations'], TRUE);

		$map = &$this->maps->CreateMap('Test Map', 'map');
		$this->maps->SendMapData();

		$this->main_frame->SetContentSimple('directory/directory_map', $data);

		$this->main_frame->Load();
	}

	/// Directory organisation page.
	function view($organisation)
	{
		if (!CheckPermissions('public')) return;

		$data = $this->organisations->_GetOrgData($organisation);
		if (!empty($data)) {
			$this->pages_model->SetPageCode('directory_view');

			$this->_SetupOrganisationFrame($organisation);

			$subpageview='directory/directory_view';

			//Reviews
			$this->load->model('articles_model');
			$reviews = $this->articles_model->GetDirectoryOrganisationReviewsByEntryName($organisation);
			// sort into types
			$directory_reviews = array();
			$review_types = array();
			foreach ($reviews as $review) {
				if (NULL === $review['type']) {
					$directory_reviews[] = $review;
				} else {
					if (!array_key_exists($review['type'], $review_types)) {
						$review_types[$review['type']] = array();
					}
					$review_types[$review['type']][] = $review;
				}
			}
			$data['organisation']['reviews_untyped'] = $directory_reviews;
			$data['organisation']['reviews_by_type'] = $review_types;

			if ($data['organisation']['location_lat'] !== NULL) {
				$this->load->library('maps');
				$map = &$this->maps->CreateMap('Location', 'googlemaps');
				$map->AddLocation($data['organisation']['name'], $data['organisation']['location_lat'], $data['organisation']['location_lng']);
				$this->maps->SendMapData();
			}
			
			// Event statistics
			$this->load->library('calendar_backend');
			$this->load->library('calendar_source_yorker');
			
			$yorker_source = new CalendarSourceYorker(0);
			// Only those events of the organisation
			$yorker_source->DisableGroup('subscribed');
			$yorker_source->DisableGroup('owned');
			$yorker_source->DisableGroup('private');
			$yorker_source->EnableGroup('active');
			$yorker_source->DisableGroup('inactive');
			$yorker_source->EnableGroup('hide');
			$yorker_source->EnableGroup('show');
			$yorker_source->EnableGroup('rsvp');
			$yorker_source->IncludeStream((int)$data['organisation']['id'], TRUE);
			$result = $yorker_source->MainQuery(
				'*, COUNT(event_occurrences.event_occurrence_id)',
				$yorker_source->ProduceWhereClause().' GROUP BY organisations.organisation_entity_id'
			);
// 			$this->messages->AddDumpMessage('results', $result);

			// Set up the directory frame to use the directory events view
			$this->main_frame->SetPage('about', 'directory');
			$this->frame_directory->SetOrganisation($data['organisation']);
			$this->frame_directory->SetContentSimple($subpageview, $data);

			// Set up the public frame to use the directory view
			$this->main_frame->SetTitleParameters(
					array('organisation' => $data['organisation']['name']));
			$this->main_frame->SetContent($this->frame_directory);

		} else {
			$this->load->library('custom_pages');
			$this->main_frame->SetContent(new CustomPageView('directory_notfound','error'));
		}
		// Load the main frame view
		$this->main_frame->Load();
	}

	/// Directory notices page.
	function notices($organisation)
	{
		if (!CheckPermissions('public')) return;

		$data = $this->organisations->_GetOrgData($organisation);
		if (!empty($data)) {
			$this->pages_model->SetPageCode('directory_notices');

			$this->_SetupOrganisationFrame($organisation);

			$organisation_id = $data['organisation']['id'];
			$this->load->model('notices_model');
			$this->load->model('organisation_model');

			// Get teams
			list($all_teams, $top_team)
				= $this->organisation_model->GetTeamsTree($organisation_id);

			// Get notices and put into teams
			$full_notices = $this->notices_model->GetPublicNoticesForOrganisation($organisation_id, NULL, FALSE);
			$notices = array();
			foreach ($full_notices as $key => $notice) {
				if (array_key_exists($notice['recipient_id'], $all_teams)) {
					if (!array_key_exists('notices', $all_teams[$notice['recipient_id']])) {
						$all_teams[$notice['recipient_id']]['notices'] = array((int)$notice['notice_id']);
					} else {
						$all_teams[$notice['recipient_id']]['notices'][] = (int)$notice['notice_id'];
					}
					if (!array_key_exists((int)$notice['notice_id'], $notices)) {
						$notice['recipients'] = array((int)$notice['recipient_id']);
						$notices[(int)$notice['notice_id']] = $notice;
					} else {
						$notices[(int)$notice['notice_id']]['recipients'][] = (int)$notice['recipient_id'];
					}
				}
			}

			$data['teams_all'] = &$all_teams;
			$data['teams_tree'] = &$top_team;
			$data['notices'] = &$notices;

			$this->main_frame->SetPage('notices', 'directory');
			$this->main_frame->SetContentSimple('directory/directory_notices', $data);

			$this->main_frame->SetTitleParameters(
					array('organisation' => $data['organisation']['name']));

		} else {
			$this->load->library('custom_pages');
			$this->main_frame->SetContent(new CustomPageView('directory_notfound','error'));
		}

		// Load the main frame
		$this->main_frame->Load();
	}

	/// Directory events page.
	function calendar($organisation, $DateRange = NULL, $Filter = NULL)
	{
		$this->load->model('subcontrollers/calendar_subcontroller');
		
		if (!CheckPermissions('public')) return;
		// Set up the filter
		$data = $this->organisations->_GetOrgData($organisation);
		if (!empty($data)) {
			$this->_SetupOrganisationFrame($organisation);
			$this->main_frame->SetPage('calendar', 'directory');
			$this->calendar_subcontroller->SetRangePageCode('directory_calendar');
			// restrict to this organisation
			$this->calendar_subcontroller->UseStreams(array(
				(int)$data['organisation']['id'] => array(
					'subscribed' => isset($data['organisation']['subscription']) && $data['organisation']['subscription']['calendar'],
					'name' => $data['organisation']['name'],
					'short_name' => $organisation,
				)
			));
			// disable cancelled events
			$sources = & $this->calendar_subcontroller->GetSources();
			$sources->DisableGroup('inactive');
			
			$args = func_get_args();
			array_shift($args);
			$this->calendar_subcontroller->_map($args);
		} else {
			$this->load->library('custom_pages');
			$this->main_frame->SetContent(new CustomPageView('directory_notfound','error'));
			$this->main_frame->Load();
		}
		
		return;
		

		$this->pages_model->SetPageCode('directory_calendar');

		$data = $this->organisations->_GetOrgData($organisation);
		if (!empty($data)) {
			$this->_SetupOrganisationFrame($organisation);

			$this->load->library('my_calendar');
			$this->load->library('calendar_source_yorker');
			$this->my_calendar->SetUrlPrefix('/directory/'.$organisation.'/calendar/');
			//$this->My_calendar->SetAgenda(vip_url('calendar/agenda').'/');

			$yorker_source = new CalendarSourceYorker(0);
			// Only those events of the organisation
			$yorker_source->DisableGroup('subscribed');
			$yorker_source->DisableGroup('owned');
			$yorker_source->DisableGroup('private');
			$yorker_source->EnableGroup('active');
			$yorker_source->DisableGroup('inactive');
			$yorker_source->EnableGroup('hide');
			$yorker_source->EnableGroup('show');
			$yorker_source->EnableGroup('rsvp');
			$yorker_source->IncludeStream((int)$data['organisation']['id'], TRUE);

			$now = new Academic_time(time());
			$this->my_calendar->SetTabs(FALSE);
			$this->my_calendar->SetDefaultRange(
				$now->AcademicYear().'-'.$now->AcademicTermNameUnique()
			);
			$this->my_calendar->SetPath('edit', site_url('calendar/event'));
			$calendar_view = $this->my_calendar->GetMyCalendar($yorker_source, $DateRange, $Filter);

			if (FALSE) {
				$this->load->model('calendar/events_model');

				$this->load->library('view_calendar_select_week');
				$this->load->library('view_calendar_list');
				$this->load->library('date_uri');

				$use_default_range = FALSE;
				if (empty($DateRange)) {
					// $DateRange Empty
					$use_default_range = TRUE;

				} else {
					$uri_result = $this->date_uri->ReadUri($DateRange);
					if ($uri_result['valid']) {
						// valid
						$start_time = $uri_result['start'];
						$end_time = $uri_result['end'];
						$format = $uri_result['format'];
						$range_description = $uri_result['description'];

					} else {
						// invalid
						$this->main_frame->AddMessage('error','Unrecognised date range: "'.$DateRange.'"');
						$use_default_range = TRUE;
					}
				}

				if ($use_default_range) {
					// Default to this week
					$start_time = Academic_time::NewToday();
					$start_time = $start_time->BackToMonday();
					$end_time = $start_time->Adjust('1week');
					$format = 'ac';
					//$range_description = 'from today for 1 week';
					$range_description = 'this week';
				}

				// Use the start time, end time, and format to set up views

				//$weeks_start = $start_time->Adjust('-2week')->BackToMonday();
				$weeks_start = $this->academic_calendar->AcademicDayOfTerm(
						$start_time->AcademicYear(),
						$start_time->AcademicTerm(),
						1,
						0,0,0
					);
				/*if ($weeks_start->Timestamp() < $monday->Timestamp()) {
					$weeks_start = $monday;
				}*/

				/*$weeks_end = $end_time->Adjust('5week')->BackToMonday();
				if ($weeks_end->Timestamp() < $monday->Timestamp()) {
					$weeks_end = $monday->Adjust('5week');
				}*/

				// Set up the week select view
				$week_select = new ViewCalendarSelectWeek();
				$week_select->SetUriBase('directory/'.$organisation.'/calendar/');
				$week_select->SetUriFormat($format);
				//$week_select->SetRange($weeks_start, $weeks_end);
				$week_select->SetAcademicTerm($weeks_start->AcademicYear(), $weeks_start->AcademicTerm());
				$week_select->SetSelectedWeek($start_time, $end_time);
				$week_select->Retrieve();

				$occurrence_filter = new EventOccurrenceFilter();
				$occurrence_filter->EnableSource('all');
				$occurrence_filter->SetSpecialCondition(
						'organisations.organisation_directory_entry_name = '.
						$this->db->escape($organisation)
					);

				// Set up the events list
				$events_list = new ViewCalendarList();
				$events_list->SetUriBase('directory/'.$organisation.'/calendar/');
				$events_list->SetUriFormat($format);
				$events_list->SetRange($start_time, $end_time);
				$events_list->SetOccurrenceFilter($occurrence_filter);
				$events_list->Retrieve();

				// Set up the directory events view to contain the week select and
				// events list
				$directory_events = new FramesFrame('directory/directory_view_events',$data);
				$directory_events->SetContent($week_select,'week_select');
				$directory_events->SetContent($events_list,'events_list');
				$directory_events->SetData('date_range_description', $range_description);
			}

			// Set up the directory frame to use the messages frame
			$this->main_frame->SetPage('calendar', 'directory');
			$this->frame_directory->SetOrganisation($data['organisation']);
			$this->frame_directory->SetContent($calendar_view);

			// Set up the public frame to use the directory frame
			$this->main_frame->SetTitleParameters(
					array('organisation' => $data['organisation']['name']));
			$this->main_frame->SetContent($this->frame_directory);

		} else {
			$this->load->library('custom_pages');
			$this->main_frame->SetContent(new CustomPageView('directory_notfound','error'));
		}
		// Load the public frame view
		$this->main_frame->Load();
	}

	/// Directory reviews page.
	function reviews($organisation)
	{
		if (!CheckPermissions('public')) return;

		$this->pages_model->SetPageCode('directory_reviews');
		$this->_SetupOrganisationFrame($organisation);

		$data = $this->organisations->_GetOrgData($organisation);
		if (!empty($data)) {
			$this->load->model('articles_model');
			$reviews = $this->articles_model->GetDirectoryOrganisationReviewsByEntryName($organisation);
			// sort into types
			$directory_reviews = array();
			$review_types = array();
			foreach ($reviews as $review) {
				if (NULL === $review['type']) {
					$directory_reviews[] = $review;
				} else {
					if (!array_key_exists($review['type'], $review_types)) {
						$review_types[$review['type']] = array();
					}
					$review_types[$review['type']][] = $review;
				}
			}
			$data['organisation']['reviews_untyped'] = $directory_reviews;
			$data['organisation']['reviews_by_type'] = $review_types;

			// Set up the directory view
			$directory_view = $this->frames->view('directory/directory_view_reviews', $data);

			// Set up the directory frame to use the directory events view
			$this->main_frame->SetPage('reviews', 'directory');
			$this->frame_directory->SetOrganisation($data['organisation']);
			$this->frame_directory->SetContent($directory_view);

			// Set up the public frame to use the directory view
			$this->main_frame->SetTitleParameters(
					array('organisation' => $data['organisation']['name']));
			$this->main_frame->SetContent($this->frame_directory);

		} else {
			$this->load->library('custom_pages');
			$this->main_frame->SetContent(new CustomPageView('directory_notfound','error'));
		}
		// Load the public frame view
		$this->main_frame->Load();
	}

	/// Directory members page.
	function members($organisation,$business_card_group=-1)
	{
		if (!CheckPermissions('public')) return;

		$this->pages_model->SetPageCode('directory_members');
		$this->_SetupOrganisationFrame($organisation);

		// Normal organisation data
		$data = $this->organisations->_GetOrgData($organisation);
		if (!empty($data)) {

			// Business Card Groups
			$groups = $this->directory_model->GetDirectoryOrganisationCardGroups($organisation);
			// translate into nice names for view
			$data['organisation']['groups'] = array();
			foreach ($groups as $group) {
				$data['organisation']['groups'][] = array(
					'name' => $group['business_card_group_name'],
					'id' => $group['business_card_group_id'],
					'href' => '/directory/'.$organisation.'/members/'.$group['business_card_group_id'],
				);
				if ($business_card_group==-1) $business_card_group = $group['business_card_group_id'];
			}

			// Members data
			$members = $this->directory_model->GetDirectoryOrganisationCardsByGroupId($business_card_group);
			// translate into nice names for view
			$data['organisation']['cards'] = array();
			foreach ($members as $member) {
				$data['organisation']['cards'][] = array(
					'name' => $member['business_card_name'],
					'title' => $member['business_card_title'],
					'image_id' => $member['business_card_image_id'],
					'course' => $member['business_card_course'],
					'blurb' => $member['business_card_blurb'],
					'email' => $member['business_card_email'],
					'phone_mobile' => $member['business_card_mobile'],
					'phone_internal' => $member['business_card_phone_internal'],
					'phone_external' => $member['business_card_phone_external'],
					'postal_address' => $member['business_card_postal_address']
				);
			}

			// Set up the directory view
			$directory_view = $this->frames->view('directory/directory_view_members', $data);

			// Set up the directory frame to use the directory events view
			$this->main_frame->SetPage('members', 'directory');
			$this->frame_directory->SetOrganisation($data['organisation']);
			$this->frame_directory->SetContent($directory_view);

			// Set up the public frame to use the directory view
			$this->main_frame->SetTitleParameters(
					array('organisation' => $data['organisation']['name']));
			$this->main_frame->SetContent($this->frame_directory);

		} else {
			$this->load->library('custom_pages');
			$this->main_frame->SetContent(new CustomPageView('directory_notfound','error'));
		}
		// Load the public frame view
		$this->main_frame->Load();
	}
}
?>
