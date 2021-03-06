<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file libraries/Calendar_source_facebook.php
 * @brief Calendar source for facebook events.
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * @pre loaded(library calendar_backend)
 *
 * Event source class for obtaining facebook events using the facebook api.
 *
 * @version 18-04-2007 James Hogan (jh559)
 *	- Created.
 */

/// Calendar source for facebook events.
class CalendarSourceFacebook extends CalendarSource
{
	/// Default constructor.
	function __construct($SourceId)
	{
		parent::__construct();
		
		$this->SetSourceId($SourceId);
		$this->mName = 'Facebook';
		//$this->mCapabilities[] = 'attend';
	}
	
	protected function ProfileUrl($uid)
	{
		return 'http://www.facebook.com/profile.php?id='.$uid;
	}
	
	protected function EventUrl($eid)
	{
		return 'http://www.facebook.com/event.php?eid='.$eid;
	}
	
	/// Get all allowed categories.
	/**
	 * @return array[name => array], NULL, TRUE.
	 *	- NULL if categories are not supported
	 *	- TRUE if all categories are allowed.
	 */
	function GetAllCategories()
	{
		return array(
			// Standard facebook categories
			'Social'		=> array('name' => 'Social'),
			'Causes'		=> array('name' => 'Causes'),
			'Academic'		=> array('name' => 'Academic'),
			'Meeting'		=> array('name' => 'Meeting'),
			'Music/Arts'	=> array('name' => 'Music/Arts'),
			'Sports'		=> array('name' => 'Sports'),
			'Trips'			=> array('name' => 'Trips'),
			// transform Other into Social
			
			'Anniversary'	=> array('name' => 'Anniversary'),
		);
	}
	
	/// Fetch the events of the source.
	/**
	 * @param $Data CalendarData Data object to add events to.
	 * @param $Event identifier Source event identitier.
	 */
	protected function _FetchEvent(&$Data, $Event, $Optionals = array())
	{
		$CI = & get_instance();
		if (!$CI->facebook->InUse()) return;
		
		if ('e' === substr($Event, 0, 1)) {
			$Event = substr($Event, 1);
			if (is_numeric($Event)) {
				$this->GetEvents($Data, (int)$Event);
			}
		} elseif ('bd' === substr($Event, 0, 2)) {
			$Event = substr($Event, 2);
			list($uid, $age) = explode('.', $Event);
			if (is_numeric($uid) && is_numeric($age)) {
				$this->GetBirthdays($Data, (int)$uid, (int)$age);
			}
		}
	}
	
	/// Fedge the events of the source.
	/**
	 * @param $Data CalendarData Data object to add events to.
	 */
	protected function _FetchEvents(&$Data)
	{
		if ($this->GroupEnabled('event')) {
			$CI = & get_instance();
			if (!$CI->facebook->InUse()) return;
			
			$this->GetEvents($Data);
			if ($this->CategoryEnabled('Anniversary')) {
				$this->GetBirthdays($Data);
			}
		}
	}
	
	protected function GetEvents(&$Data, $EventId = NULL)
	{
		$CI = & get_instance();
		
		static $event_type_translation = array(
			'Party'      => 'social',
			'Causes'     => 'social',
			'Education'  => 'academic',
			'Meetings'   => 'meeting',
			'Music/Arts' => 'social',
			'Sports'     => 'sport',
			'Trips'      => 'social',
			'Other'      => 'social',
		);
		
		try {
			// Get events in the range.
			if (NULL === $EventId) {
				$event_range = $this->mEventRange;
			} else {
				$event_range = array(NULL, NULL);
			}
			$events = $CI->facebook->Client->events_get(
				$CI->facebook->Uid,
				$EventId,
				$event_range[0],
				$event_range[1],
				null
			);
			
			if (!empty($events)) {
				foreach ($events as $event) {
// 					var_dump($event['event_type']);
					// Check if it matches the search phrase
					if (is_array($this->mSearchPhrases)) {
						// use the following fields
						//  $event['name']
						//  $event['description']
						if (!$this->SearchMatch($this->mSearchPhrases, $event['name'].','.$event['description']))
						{
							continue;
						}
					}
					
					// get the list of members, so we can see if the user is attending.
					$members = $CI->facebook->Client->events_getMembers($event['eid']);
					$attending = '';
					if (is_array($members['attending']) && in_array($CI->facebook->Uid, $members['attending'])) {
						$attending = 'yes';
						if (!$this->GroupEnabled('rsvp')) {
							continue;
						}
					} elseif (is_array($members['declined']) && in_array($CI->facebook->Uid, $members['declined'])) {
						$attending = 'no';
						if (!$this->GroupEnabled('hide')) {
							continue;
						}
					} else {
						$attending = 'maybe';
						if (!$this->GroupEnabled('show')) {
							continue;
						}
					}
					
					$event_obj = & $Data->NewEvent();
					$occurrence = & $Data->NewOccurrence($event_obj);
					$event_obj->SourceEventId = 'e'.$event['eid'];
					$event_obj->Name = $event['name'];
					$event_obj->Description = $event['description'];
					$event_obj->GetDescriptionHtml();
					$event_obj->DescriptionHtml .= '<br /><br /><a href="'.$this->EventUrl($event['eid']).'" target="_blank">This event on Facebook</a>';
					$event_obj->LastUpdate = $event['update_time'];
					if (!empty($event['pic'])) {
						$event_obj->Image = $event['pic'];
					}
					
					if (array_key_exists($event['event_type'], $event_type_translation)) {
						$event_obj->Category = ucfirst($event_type_translation[$event['event_type']]);
					} else {
						$event_obj->Category = 'Facebook';
					}
					
					$occurrence->SourceOccurrenceId = $event_obj->SourceEventId;
					$occurrence->LocationDescription = $event['location'];
					$occurrence->StartTime = new Academic_time((int)$event['start_time']);
					$occurrence->EndTime = new Academic_time((int)$event['end_time']);
					$occurrence->TimeAssociated = TRUE;
					$occurrence->UserAttending = $attending;
					
					$occurrence->UserPermissions[] = 'attend';
					$occurrence->UserPermissions[] = 'set_attend';
					
					unset($occurrence);
					unset($event_obj);
				}
			}
		} catch (FacebookRestClientException $ex) {
			$CI->facebook->HandleException($ex);
		}
	}
	
	function GetBirthdays(&$Data, $UserId = NULL, $Age = NULL)
	{
		$CI = & get_instance();
		
		$event_range = $this->mEventRange;
		if (NULL === $event_range[0]) {
			$event_range[0] = time();
		}
		if (NULL === $event_range[1]) {
			$event_range[1] = strtotime('+1year');
		}
		
		try {
			// Get friends with birthdays in the range.
			$query = 'SELECT uid, name, birthday, profile_update_time, pic FROM user '.
				'WHERE (uid IN (SELECT uid2 FROM friend WHERE uid1 = '.$CI->facebook->Uid.') '.
				'OR uid = '.$CI->facebook->Uid.')';
			$specific = (	NULL !== $UserId &&
							NULL !== $Age &&
							$Age >= 0	);
			if ($specific) {
				$query .= ' AND uid = '.$UserId;
			} else {
				$year = date('Y', $event_range[0]);
			}
			$birthdays = $CI->facebook->Client->fql_query($query);
			
			$yesterday = strtotime('-1day',$event_range[0]);
			foreach ($birthdays as $birthday) {
				if (preg_match('/([A-Z][a-z]+ \d\d?, )(\d\d\d\d)/', $birthday['birthday'], $matches)) {
					// Check if it matches the search phrase
					if (is_array($this->mSearchPhrases)) {
						// use the following fields
						//  $birthday['name']
						if (!$this->SearchMatch($this->mSearchPhrases, 'birthday,anniversary,'.$birthday['name']))
						{
							continue;
						}
					}
					
					if ($specific) {
						$year = $matches[2] + $Age;
						if ($year >= 1970 && $year < 2037) {
							$start_age = $Age;
							$dob = strtotime($matches[1].$year);
							$event_range[1] = $dob+1;
							$yesterday = $dob;
						} else {
							continue;
						}
					} else {
						$start_age = $year - $matches[2];
						$dob = strtotime($matches[1].$year);
					}
					
					while ($dob < $event_range[1]) {
						if ($dob >= $yesterday) {
							$event_obj = & $Data->NewEvent();
							$occurrence = & $Data->NewOccurrence($event_obj);
							$event_obj->SourceEventId = 'bd'.$birthday['uid'].'.'.$start_age;
							$event_obj->Name = 'Birthday '.$start_age.': '.$birthday['name'];
							$event_obj->Description = '';
							$event_obj->DescriptionHtml = '<a href="'.$this->ProfileUrl($birthday['uid']).'" target="_blank">'.$birthday['name'].'\'s profile</a>';
							$event_obj->LastUpdate = (int)$birthday['profile_update_time'];
							if (!empty($birthday['pic'])) {
								$event_obj->Image = $birthday['pic'];
							}
							$event_obj->Category = 'Anniversary';
							
							$occurrence->SourceOccurrenceId = $event_obj->SourceEventId;
							$occurrence->LocationDescription = '';
							$occurrence->StartTime = new Academic_time($dob);
							$occurrence->EndTime = $occurrence->StartTime->Adjust('1day');
							$occurrence->TimeAssociated = FALSE;
							$occurrence->UserAttending = 0;
							unset($occurrence);
							unset($event_obj);
						}
						$dob = strtotime('1year', $dob);
						++$start_age;
					}
				}
			}
		} catch (FacebookRestClientException $ex) {
			$CI->facebook->HandleException($ex);
		}
	}
	
	/// Get list of known attendees.
	/**
	 * @param $Occurrence Occurrence identifier.
	 * @return array Attendees, defined by fields:
	 *	- 'name' string Name of attendee.
	 *	- 'link' string URL about user.
	 *	- 'entity_id' int Entity id if known.
	 *	- 'attend' bool,NULL TRUE for attending, FALSE for not attending, NULL for maybe.
	 */
	function GetOccurrenceAttendanceList($Occurrence)
	{
		if ('e' === substr($Occurrence, 0, 1)) {
			$event = substr($Occurrence, 1);
			if (is_numeric($event)) {
				// get the list of members.
				$CI = & get_instance();
				try {
					// Get the ids + statuses of event members
					$fb_attendings = $CI->facebook->Client->fql_query(
						'SELECT uid, rsvp_status '.
						'FROM event_member '.
						'WHERE eid = '.(int)$event
					);
					// there are only attendees if people are members
					if (true || !empty($fb_attendings)) {
						$fb_attendees = $CI->facebook->Client->fql_query(
							'SELECT uid, name '.
							'FROM user '.
							'WHERE uid IN (SELECT uid, rsvp_status '.
										'FROM event_member '.
										'WHERE eid = '.(int)$event.')'
						);
						$fb_friend_attendees = $CI->facebook->Client->fql_query(
							'SELECT uid '.
							'FROM user '.
							'WHERE uid IN (SELECT uid, rsvp_status '.
										'FROM	event_member '.
										'WHERE	eid = '.(int)$event.') AND '.
								'uid IN (SELECT	uid2 '.
										'FROM	friend '.
										'WHERE	uid1 = '.$CI->facebook->Uid.
											'OR	uid2 = '.$CI->facebook->Uid.')'
						);
						//$members = $CI->facebook->Client->events_getMembers((int)$event);
						$attendees = array();
						foreach ($fb_attendees as $attendee) {
							$attendees[(int)$attendee['uid']] = array(
								'name' => $attendee['name'],
								'link' => $this->ProfileUrl($attendee['uid']),
								'friend' => false,
							);
						}
						foreach ($fb_friend_attendees as $friend_attendee) {
							$attendees[(int)$friend_attendee['uid']]['friend'] = true;
						}
						foreach ($fb_attendings as $attending) {
							$attendees[(int)$attending['uid']]['attend'] = $attending['rsvp_status'];
						}
						return array_values($attendees);
					} else {
						return array();
					}
				} catch (FacebookRestClientException $ex) {
					$CI->facebook->HandleException($ex);
				}
			}
		}
		return parent::GetOccurrenceAttendanceList($Occurrence);
	}
}



/// Dummy class
class Calendar_source_facebook
{
	/// Default constructor.
	function __construct()
	{
		$CI = & get_instance();
		$CI->load->library('facebook');
		
		if ($CI->facebook->InUse()) {
			$CI->facebook->Connect();
		}
	}
}

?>