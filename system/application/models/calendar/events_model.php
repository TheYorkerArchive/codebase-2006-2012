<?php

/// Class for creating parts of queries.
class EventOccurrenceQuery
{
	/// Entity id of relevent user/organisation.
	protected $mEntityId;
	
	/// Default constructor
	function __construct()
	{
		$CI = &get_instance();
		$this->mEntityId = $CI->events_model->GetActiveEntityId();
	}
	
	/// Produce an SQL expression for all and only public events.
	function ExpressionPublic(&$DataArray)
	{
		return	'(	event_occurrences.event_occurrence_state = \'published\'
				OR	event_occurrences.event_occurrence_state = \'cancelled\')';
	}
	
	/// Produce an SQL expression for all and only owned events.
	function ExpressionOwned(&$DataArray)
	{
		$DataArray[] = $this->mEntityId;
		return	'(	event_entities.event_entity_entity_id = ?
				AND	event_entities.event_entity_confirmed = 1
				AND	event_entities.event_entity_relationship = \'own\')';
	}
	
	/// Produce an SQL expression for all and only subscribed events.
	function ExpressionSubscribed(&$DataArray)
	{
		$DataArray[] = $this->mEntityId;
		$DataArray[] = $this->mEntityId;
		return	'(	(	event_entities.event_entity_entity_id = ?
					AND	event_entities.event_entity_confirmed = 1
					AND	(	event_entities.event_entity_relationship = \'own\'
						OR	event_entities.event_entity_relationship = \'subscribe\'))
				OR	subscriptions.subscription_user_entity_id = ?)';
	}
	
	/// Produce an SQL expression for all and only rsvp'd events.
	function ExpressionVisibilityRsvp(&$DataArray)
	{
		return	'event_occurrence_users.event_occurrence_user_state=\'rsvp\'';
	}
	
	/// Produce an SQL expression for all and only hidden events.
	function ExpressionVisibilityHidden(&$DataArray)
	{
		return	'event_occurrence_users.event_occurrence_user_state=\'hide\'';
	}
	
	/// Produce an SQL expression for all and only normal visibility events.
	function ExpressionVisibilityNormal(&$DataArray)
	{
		return	'(		event_occurrence_users.event_occurrence_user_state=\'show\'
					OR	event_occurrence_users.event_occurrence_user_state IS NULL)';
	}
	
	/// Produce an SQL expression for all and only occurrences in a range of time.
	function ExpressionDateRange(&$DataArray, $Range)
	{
		assert('is_array($Range)');
		assert('is_int($Range[0])');
		assert('is_int($Range[1])');
		return	'(		event_occurrences.event_occurrence_end_time >
								FROM_UNIXTIME('.$Range[0].')
					AND	event_occurrences.event_occurrence_start_time <
								FROM_UNIXTIME('.$Range[1].'))';;
	}
}

/// Filter class for retrieving event occurrences.
/**
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * The purpose of this class is to allow classes which provide a layer of
 *	abstraction over the model (e.g. View_calendar_days) to provide a consistent
 *	filtering interface.
 *
 * always in relation to an entity who is accessing, to determin permission.
 *	occurrence must belong to an event owned by the entity or which is public
 *	event must be owned by an entity and have public occurrences
 *	event must not be deleted
 * sources
 *	owned events
 *	subscribed feeds
 *	extra feeds
 * filters
 *	private? active? inactive?
 *	hidden events? normal events? rsvpd events?
 *	date range
 */
class EventOccurrenceFilter extends EventOccurrenceQuery
{
	/// array[bool] For enabling/disabling sources of events.
	protected $mSources;
	
	/// array[bool] For filtering sources of events.
	protected $mFilters;
	
	/// array[feed] For including additional sources.
	protected $mInclusions;
	
	/// array[2*timestamp] For filtering by time.
	protected $mRange;
	
	/// string Special mysql condition.
	protected $mSpecialCondition;
	
	/// Default constructor
	function __construct()
	{
		parent::__construct();
		
		// Initialise sources + filters
		$this->mSources = array(
				'owned'      => TRUE,
				'subscribed' => TRUE,
				'inclusions' => FALSE,
				'all'        => FALSE,
			);
		$this->mFilters = array(
				'private'    => TRUE,
				'active'     => TRUE,
				'inactive'   => TRUE,
				
				'hide'       => FALSE,
				'show'       => TRUE,
				'rsvp'       => TRUE,
			);
		
		$this->mInclusions = array();
		
		$this->mRange = array( time(), time() );
		
		$this->mSpecialCondition = FALSE;
	}
	
	/// Retrieve specified fields of event occurrences.
	/**
	 * @param $Fields array of aliases to select expressions (field names).
	 *	e.g. array('name' => 'events.event_name')
	 * @return array Results from db query.
	 */
	function GenerateOccurrences($Fields)
	{
		// MAIN QUERY ----------------------------------------------------------
		/*
			owned:
				occurrence.event.owners.id=me
				OR subscription.vip=1
			subscribed:
				occurrence.event.entities.subscribers.id=me
				subscription.interested & not subscription.deleted
			inclusions:
				occurrence.event.entities.id=inclusion
				
			own OR (public AND (subscribed OR inclusion))
		*/
		
		$FieldStrings = array();
		foreach ($Fields as $Alias => $Expression) {
			if (is_string($Alias)) {
				$FieldStrings[] = $Expression.' AS '.$Alias;
			} else {
				$FieldStrings[] = $Expression;
			}
		}
		
		/// @todo Optimise main event query to avoid left joins amap
		$parameters = array();
		$sql = '
			SELECT '.implode(',',$FieldStrings).' FROM event_occurrences
			INNER JOIN events
				ON	event_occurrences.event_occurrence_event_id = events.event_id
				AND	events.event_deleted = 0
			LEFT JOIN event_types
				ON	events.event_type_id = event_types.event_type_id
			LEFT JOIN event_entities
				ON	event_entities.event_entity_event_id = events.event_id
			LEFT JOIN organisations
				ON	organisations.organisation_entity_id
						= event_entities.event_entity_entity_id
			LEFT JOIN subscriptions
				ON	subscriptions.subscription_organisation_entity_id
						= event_entities.event_entity_entity_id
				AND	subscriptions.subscription_user_entity_id	= ?
				AND	subscriptions.subscription_deleted			= 0
				AND	subscriptions.subscription_interested		= 1
				AND	subscriptions.subscription_user_confirmed	= 1
			LEFT JOIN event_occurrence_users
				ON event_occurrence_users.event_occurrence_user_event_occurrence_id
						= event_occurrences.event_occurrence_id
				AND	event_occurrence_users.event_occurrence_user_user_entity_id
						= ?';
		$parameters[] = $this->mEntityId;
		$parameters[] = $this->mEntityId;
		
		// SOURCES -------------------------------------------------------------
		
		if ($this->mSources['owned']) {
			$own = $this->ExpressionOwned($parameters);
		} else {
			$own = '0';
		}
		
		$public = $this->ExpressionPublic($parameters);
		
		if ($this->mSources['all']) {
			$public_sources = '';
		} else {
			if ($this->mSources['subscribed']) {
				$subscribed = $this->ExpressionSubscribed($parameters);
			} else {
				$subscribed = '0';
			}
			
			if ($this->mSources['inclusions'] && count($this->mInclusions) > 0)
{
				$includes = array();
				foreach ($this->mInclusions as $inclusion) {
					$includes[] = 'event_entities.event_entity_event_id=?';
					$parameters[] = $inclusion;
				}
				$included = '('.implode(' AND ', $includes).')';
			} else {
				$included = '0';
			}
			
			$public_sources = ' AND ('.$subscribed.' OR '.$included.')';
		}
		
		$sources = '('.$own.' OR ('.$public.$public_sources.'))';
		
		// FILTERS -------------------------------------------------------------
		
		$occurrence_states = array(
				'private' => array('draft','trashed'),
				'active' => array('published'),
				'inactive' => array('cancelled'),
			);
		$state_predicates = array();
		foreach ($occurrence_states as $filter => $states) {
			if ($this->mFilters[$filter]) {
				foreach ($states as $state) {
					$state_predicates[] = 'event_occurrences.event_occurrence_state=\''.$state.'\'';
				}
			}
		}
		if (count($state_predicates) > 0) {
			$state = '('.implode(' OR ',$state_predicates).')';
		} else {
			$state = '0';
		}
		
		$visibility_predicates = array();
		if ($this->mFilters['hide']) {
			$visibility_predicates[] = $this->ExpressionVisibilityHidden($parameters);
		}
		if ($this->mFilters['show']) {
			$visibility_predicates[] = $this->ExpressionVisibilityNormal($parameters);
		}
		if ($this->mFilters['rsvp']) {
			$visibility_predicates[] = $this->ExpressionVisibilityRsvp($parameters);
		}
		if (count($visibility_predicates) > 0) {
			$visibility = '('.implode(' OR ',$visibility_predicates).')';
		} else {
			$visibility = '0';
		}
		
		$filters = '('.$state.' AND '.$visibility.')';
		
		// DATE RANGE ----------------------------------------------------------
		
		$date_range = $this->ExpressionDateRange($parameters, $this->mRange);
		
		// SPECIAL CONDITION ---------------------------------------------------
		
		$conditions = array($date_range,$sources,$filters);
		
		if (FALSE !== $this->mSpecialCondition) {
			$conditions[] = '('.$this->mSpecialCondition.')';
		}
		
		// WHERE CLAUSE --------------------------------------------------------
		
		$sql .= ' WHERE '.implode(' AND ',$conditions).';';
		
		// Try it out
		$CI = &get_instance();
		$query = $CI->db->query($sql,$parameters);
		return $query->result_array();
	}
	
	/// Enable a source.
	/**
	 * @param $SourceName string Index to $this->mSources.
	 * @return bool Whether successfully enabled.
	 */
	function EnableSource($SourceName)
	{
		if (array_key_exists($SourceName, $this->mSources)) {
			$this->mSources[$SourceName] = TRUE;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/// Disable a source.
	/**
	 * @param $SourceName string Index to $this->mSources.
	 * @return bool Whether successfully disabled.
	 */
	function DisableSource($SourceName)
	{
		if (array_key_exists($SourceName, $this->mSources)) {
			$this->mSources[$SourceName] = FALSE;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/// Enable a filter.
	/**
	 * @param $FilterName string Index to $this->mFilters.
	 * @return bool Whether successfully enabled.
	 */
	function EnableFilter($FilterName)
	{
		if (array_key_exists($FilterName, $this->mFilters)) {
			$this->mFilters[$FilterName] = TRUE;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/// Disable a filter.
	/**
	 * @param $FilterName string Index to $this->mFilters.
	 * @return bool Whether successfully disabled.
	 */
	function DisableFilter($FilterName)
	{
		if (array_key_exists($FilterName, $this->mFilters)) {
			$this->mFilters[$FilterName] = FALSE;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/// Add inclusion.
	/**
	 * @param $FeedId integer/array Feed id(s).
	 * @param $EnableInclusions bool Whether to enable inclusions.
	 */
	function AddInclusion($FeedId, $EnableInclusions = FALSE)
	{
		if (is_array($FeedId)) {
			$this->mInclusions = array_merge($this->mInclusions, $FeedId);
		} else {
			$this->mInclusions[] = $FeedId;
		}
		if ($EnableInclusions) {
			$this->EnableSource('inclusions');
		}
	}
	
	/// Clear inclusions.
	/**
	 * @param $DisableInclusions bool Whether to disable inclusions.
	 */
	function ClearInclusions($DisableInclusions = FALSE)
	{
		$this->mInclusions = array();
		if ($DisableInclusions) {
			$this->DisableSource('inclusions');
		}
	}
	
	/// Set the date range.
	/**
	 * @param $Start timestamp Start time.
	 * @param $End timestamp End time.
	 */
	function SetRange($Start, $End)
	{
		if (!is_int($Start))
			throw new Exception('Events_model::SetRange: parameter Start is not a valid timestamp');
		if (!is_int($End))
			throw new Exception('Events_model::SetRange: parameter End is not a valid timestamp');
		$this->mRange[0] = $Start;
		$this->mRange[1] = $End;
	}
	
	/// Set the special condition.
	/**
	 * @param $Condition string SQL condition.
	 */
	function SetSpecialCondition($Condition = FALSE)
	{
		$this->mSpecialCondition = $Condition;
	}
	
}



/// Model for access to events.
/**
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * User operations
 * Org operations
 *		create new event (with subevents/occurrences)
 *		State transitions
 *			Trash a draft
 *			restore a trashed draft
 *			delete (from anything except draft)
 *			publish a draft
 *			cancel an active published
 *			move an active published
 *			restore a cancelled
 *		get RSVP lists
 * Auto operations
 *		generate recurrences
 */
class Events_model extends Model
{
	private $mStates;
	
	private $mDayInformation;
	private $mOccurrences;
	
	protected $mOccurrenceFilter;
	
	/// Entity id of user/organisation.
	protected $mActiveEntityId;
	
	/// Type of acting entity.
	protected $mActiveEntityType;
	
	/// Whether the user is in read only mode.
	protected $mReadOnly;
	
	/// Message to give to the exception when a user in read only mode tries to write.
	protected static $cReadOnlyMessage = 'Public calendar is read-only. Please log in.';
	
	protected static $cEntityPublic = 0; ///< $mActiveEntityType, indicates not logged in.
	protected static $cEntityUser   = 1; ///< $mActiveEntityType, indicates logged in.
	protected static $cEntityVip    = 2; ///< $mActiveEntityType, indicates in viparea.
	
	/// Default constructor
	function __construct()
	{
		parent::Model();
		
		// Get entity id from user_auth library
		$CI = &get_instance();
		$CI->load->library('user_auth');
		if ($CI->user_auth->isLoggedIn) {
			$this->mReadOnly = FALSE;
			if ($CI->user_auth->organisationLogin >= 0) {
				$this->mActiveEntityId = $CI->user_auth->organisationLogin;
				$this->mActiveEntityType = self::$cEntityVip;
			} else {
				$this->mActiveEntityId = $CI->user_auth->entityId;
				$this->mActiveEntityType = self::$cEntityUser;
			}
		} else {
			// Default to an entity id with default events
			$this->mReadOnly = TRUE;
			$this->mActiveEntityId = 0;
			$this->mActiveEntityType = self::$cEntityPublic;
		}
		
		$this->mStates = array();
		$this->mDayInformation = FALSE;
		$this->mOccurrences = FALSE;
		$this->SetOccurrenceFilter();
		
		$this->load->library('academic_calendar');
	}
	
	/// Get the entity id of the active entity.
	function GetActiveEntityId()
	{
		return $this->mActiveEntityId;
	}
	
	/// Set whether some option is enabled
	function SetEnabled($StateName, $Enabled = TRUE)
	{
		$this->mStates[$StateName] = $Enabled;
	}
	
	/// Find if some data is enabled to be retrieved.
	function IsEnabled($StateName, $Default = FALSE)
	{
		if (array_key_exists($StateName,$this->mStates)) {
			return $this->mStates[$StateName];
		} else {
			return $Default;
		}
	}
	
	/// Set whether day information is enabled.
	function IncludeDayInformation($Enabled)
	{
		$this->SetEnabled('day-info', $Enabled);
	}
	
	/// Set whether event occurrences are enabled.
	function IncludeOccurrences($Enabled)
	{
		$this->SetEnabled('occurrences-all', $Enabled);
	}
	
	/// Set the event occurrence filter to use.
	/**
	 * @param $Filter EventOccurrenceFilter Event filter object
	 *	(A value of FALSE means use a default filter)
	 */
	function SetOccurrenceFilter($Filter = FALSE)
	{
		$this->mOccurrenceFilter = $Filter;
	}
	
	/// Retrieve the specified data between certain dates.
	/**
	 * @param $StartTime Academic_time Start time.
	 * @param $EndTime Academic_time End time.
	 * @pre @a $StartTime < @a $EndTime
	 * @return bool Success state:
	 *	- TRUE (Success).
	 *	- FALSE (Failure).
	 *
	 * @todo Retrieve event data from the database instead of hardcoded.
	 */
	function Retrieve($StartTime, $EndTime)
	{
		// Validate input
		if ($StartTime >= $EndTime)
			return FALSE;
		
		$success = TRUE;
		
		// Day information
		if ($this->IsEnabled('day-info')) {
			$this->mDayInformation = array();
			
			// Initialise the day info array
			$current_time = $StartTime->Midnight();
			$current_index = 0;
			while ($current_time->Timestamp() < $EndTime->Timestamp()) {
				$this->mDayInformation[$current_time->Timestamp()] = array(
						'index' => $current_index,
						'date' => $current_time,
						'special_headings' => array(),
					);
				
				// Onto the next day
				$current_time = $current_time->Adjust('1day');
				++$current_index;
			}
		}
		
		// Event occurrences
		if ($this->IsEnabled('occurrences-all')) {
			$filter = $this->mOccurrenceFilter;
			if (FALSE === $filter) {
				$filter = new EventOccurrenceFilter();
			}
			$filter->SetRange($StartTime->Timestamp(),$EndTime->Timestamp());
			
			$fields = array(
					'ref_id' => 'event_occurrences.event_occurrence_id',
					'name'   => 'events.event_name',
					'start'  => 'UNIX_TIMESTAMP(event_occurrences.event_occurrence_start_time)',
					'end'    => 'UNIX_TIMESTAMP(event_occurrences.event_occurrence_end_time)',
					'system_update_ts' => 'UNIX_TIMESTAMP(events.event_timestamp)',
					//'user_update_ts'   => 'UNIX_TIMESTAMP(events_occurrence_users.event_occurrence_user_timestamp)',
					'blurb'    => 'events.event_blurb',
					'shortloc' => 'event_occurrences.event_occurrence_location',
					'type'     => 'event_types.event_type_name',
					'state'    => 'event_occurrences.event_occurrence_state',
					'organisation' => 'organisations.organisation_name',
					'organisation_directory' => 'organisations.organisation_directory_entry_name',
					//'',
				);
			$this->mOccurrences = $filter->GenerateOccurrences($fields);
		}
		
		return $success;
	}
	
	/// Get the day information retrieved.
	/**
	 * @pre IsEnabled('day-info')
	 * @pre Retrieve has been called successfully.
	 * @return Array of days with related information, indexed by timestamp of
	 *    midnight on the morning of the day.
	 */
	function GetDayInformation()
	{
		return $this->mDayInformation;
	}
	
	/// Get the event occurrences retrieved.
	/**
	 * @pre IsEnabled('occurrences-all')
	 * @pre Retrieve has been called successfully.
	 * @return Array of occurrences.
	 */
	function GetOccurrences()
	{
		return $this->mOccurrences;
	}
	
	
	
	// SELECTORS
	// organiser
	/// Get information about the RSVP's to the occurrences of an event
	/**
	 * @param $EventId integer Id of event.
	 * @return array/bool
	 *	- False on failure.
	 *	- Array of rsvp's to occurrences of event
	 * @pre Own occurrence
	 */
	function GetEventRsvp($EventId)
	{
		/// @todo Implement.
	}
	
	/// Get information about the RSVP's to an occurrence
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return array/bool
	 *	- False on failure.
	 *	- Array of rsvp's
	 * @pre Own occurrence
	 */
	function GetOccurrenceRsvp($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	// subscriber
	/// Get the information relating to a notice type.
	/**
	 * @param $NoticeType string Notice type as ['type'] in return of
	 *	GetNotices.
	 * @return array Notice type information including:
	 *	- 'actions' array of action names.
	 */
	function GetNoticeTypeInformation($NoticeType)
	{
		static $notice_types = array(
			'requested_subscription' => array(
				'description' => 'You have been invited to join the specified events feed.',
				'actions' => array(
					'Accept' => 'Subscribe to the events',
					'Reject' => 'Reject the subscription',
				),
			),
			'requested_membership' => array(
				'description' => 'You have been invited to join the specified organisation as a member.',
				'actions' => array(
					'Interested' => 'Accept the membership and subscribe to events',
					'Not interested' => 'Accept the membership but don\'t subscribe to events',
					'Reject' => 'Reject the membership',
				),
			),
		);
		return $notice_types[$NoticeType];
	}
	
	/// Get notices relating to a user's calendar.
	/**
	 * Get all pending
	 *	- subscriptions/memberships.
	 *	- event ownerships/event feed inclusions
	 * Moved RSVP'd events.
	 * @return array Array of notice information including:
	 *	- 'type' (e.g. requested_subscription, requested_membership)
	 *	- 'subscription_id' (if type == requested_subscription)
	 */
	function GetNotices()
	{
		/// @todo Implement.
	}
	
	/// Get an occurrence's active occurrence.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return array Information about the active occurrence.
	 */
	function GetOccurrenceActiveOccurrence($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	// TRANSACTIONS
	// organiser
	/// Create preliminary subscriptions for a set of users to a feed.
	/**
	 * @param $EntityId integer Feed id.
	 * @param $Users array Array of email addresses.
	 * @return integer Number of subscriptions created.
	 * @pre Feed @a $EntityId has events enabled.
	 * @post Each user in @a $User without an existing subscription will have
	 *	one set up without user confirmation yet.
	 */
	function FeedSubscribeUsers($EntityId, $Users)
	{
		/// @todo Implement.
	}
	
	
	/// Create a new event.
	/**
	 * @param $EventData array Event data.
	 * @return array(
	 *	'event_id' => new event id,
	 *	'occurrences' => occurrences added)
	 * @exception Exception The event could not be created.
	 */
	function EventCreate($EventData)
	{
		if ($this->mReadOnly) {
			throw new Exception(self::$cReadOnlyMessage);
		}
		static $translation = array(
			'name'			=> 'events.event_name',
			'description'	=> 'events.event_description',
			'parent'		=> 'events.event_parent_id',
		);
		$fields = array();
		$bind_data = array();
		foreach ($translation as $input_name => $field_name) {
			if (array_key_exists($input_name, $EventData)) {
				$fields[] = $field_name.'=?';
				$bind_data[] = $EventData[$input_name];
			}
		}
		
		// create new event
		$sql = 'INSERT INTO events SET '.implode(',',$fields);
		$query = $this->db->query($sql, $bind_data);
		$affected_rows = $this->db->affected_rows();
		if ($affected_rows == 0) {
			throw new Exception('Event could not be created (1)');
		}
		$event_id = $this->db->insert_id();
		
		// link event to creater
		$sql = 'INSERT INTO event_entities (
				event_entity_entity_id,
				event_entity_event_id,
				event_entity_relationship,
				event_entity_confirmed)
			VALUES';
		$sql .= ' ('.$this->mActiveEntityId.','.$event_id.',\'own\',1)';
		$query = $this->db->query($sql);
		$affected_rows = $this->db->affected_rows();
		if ($affected_rows == 0) {
			// Should now delete event since couldn't bind to entity
			$sql = 'DELETE FROM events WHERE events.event_id='.$event_id;
			$query = $this->db->query($sql);
			$affected_rows = $this->db->affected_rows();
			// Throw error message
			$message = 'Event could not be created (';
			if ($affected_rows == 1) {
				$message .= '2-clean)';
			} else {
				$message .= '2-unclean)';
			}
			throw new Exception($message);
		}
		
		if (array_key_exists('occurrences',$EventData)) {
			static $translation2 = array(
				'description',
				'location',
				'postcode',
				'all_day',
				'ends_late',
			);
			// create each occurrences
			$sql = 'INSERT INTO event_occurrences (
					event_occurrence_event_id,
					event_occurrence_description,
					event_occurrence_location,
					event_occurrence_postcode,
					event_occurrence_start_time,
					event_occurrence_end_time,
					event_occurrence_all_day,
					event_occurrence_ends_late)
				VALUES';
			$first = TRUE;
			foreach ($EventData['occurrences'] as $occurrence) {
				$values = array_fill(0, count($translation2)-1, 'DEFAULT');
				foreach ($translation2 as $field_name => $input_name) {
					if (array_key_exists($input_name, $occurrence)) {
						$values[$field_name] = $this->db->escape($occurrence[$input_name]);
					}
				}
				if (!$first)
					$sql .= ',';
				$sql .=	' ('.$event_id.
						','.$values[0].		// description
						','.$values[1].		// location
						','.$values[2];		// postcode
				// start time
				if (array_key_exists('start', $occurrence))
					$sql .= ',FROM_UNIXTIME('.$occurrence['start'].')';
				else
					$sql .= ',DEFAULT';
				// end time
				if (array_key_exists('end', $occurrence))
					$sql .= ',FROM_UNIXTIME('.$occurrence['end'].')';
				else
					$sql .= ',DEFAULT';
				
				$sql .=	','.$values[3].		// all_day
						','.$values[4].')'; // ends_late
				
				$first = FALSE;
			}
			$query = $this->db->query($sql);
			$num_occurrences = $this->db->affected_rows();
		} else {
			$num_occurrences = 0;
		}
		
		// Return some information about the created event.
		return array(
			'event_id'		=> $event_id,
			'occurrences'	=> $num_occurrences,
		);
	}
	function EventPublish($EventId)
	{
		/// @todo Implement.
	}
	
	function OccurrenceAdd($EventId, $OccurrenceData)
	{
		/// @todo Implement.
	}
	function OccurrenceAlter($OccurrenceId, $OccurrenceData)
	{
		/// @todo Implement.
	}
	
	
	/// Publish a draft occurrence to the feed.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre 'draft'
	 * @pre Occurrence.start NOT NULL and Occurrence.end NOT NULL
	 * @post 'published'
	 */
	function OccurrenceDraftPublish($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	/// Trash a draft occurrence.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre 'draft'
	 * @post 'trashed'
	 */
	function OccurrenceDraftTrash($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	/// Restore a trashed occurrence.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre 'trashed'
	 * @post 'draft'
	 */
	function OccurrenceTrashedRestore($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	/// Cancel a published occurrence.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre 'published'
	 * @post 'cancelled'
	 */
	function OccurrencePublishedCancel($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	/// Move a published occurrence.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre 'published' | ('cancelled' & active)
	 * @post 'cancelled' linking to new occurrence
	 * @post new occurrence created in 'draft' at new position
	 */
	function OccurrencePublishedMove($OccurrenceId, $Data)
	{
		/// @todo Implement.
	} // to draft_moved
	
	/// Delete an occurrence
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre 'draft' | 'trashed' | in past
	 * @post 'deleted'
	 */
	function OccurrenceDelete($OccurrenceId)
	{
		/// @todo Implement.
	}
	
	
	// subscriber
	/// Set user-occurrence link.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @param $NewState string new db state:
	 *	- 'show' (show the event normally).
	 *	- 'hide' (hide the event).
	 *	- 'rsvp' (rsvp the event).
	 * @return bool True on success, False on failure.
	 * @pre Occurrence is visible to user.
	 * @post Occurrence is RSVP'd to user.
	 */
	protected function SetOccurrenceUserState($OccurrenceId, $NewState)
	{
		/// @pre $mActiveEntityId === $sEntityUser
		assert('$this->mActiveEntityId === self::$cEntityUser');
		
		$occurrence_query = new EventOccurrenceQuery();
		
		$sql = '
			INSERT INTO event_occurrence_users (
				event_occurrence_users.event_occurrence_user_user_entity_id,
				event_occurrence_users.event_occurrence_user_event_occurrence_id,
				event_occurrence_users.event_occurrence_user_state)
			SELECT	'.$this->mActiveEntityId.', '.$OccurrenceId.', ?
			FROM	event_occurrences
			WHERE	(	event_occurrences.event_occurrence_id = '.$OccurrenceId.'
					AND	'.$occurrence_query->ExpressionPublic().')
			ON DUPLICATE KEY UPDATE
				event_occurrence_users.event_occurrence_user_state = ?';
		$bind_data = array(
				$NewState,
				$NewState,
			);
		$query = $this->db->query($sql, $bind_data);
		return ($this->db->affected_rows > 0);
	}
	
	/// Set user-occurrence link to RSVP.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre Occurrence is visible to user.
	 * @pre $mActiveEntityId === $sEntityUser
	 * @post Occurrence is RSVP'd to user.
	 */
	function OccurrenceRsvp($OccurrenceId)
	{
		return $this->SetOccurrenceUserState($OccurrenceId, 'rsvp');
	}
	
	/// Set user-occurrence link to Hide.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre Occurrence is visible to user.
	 * @pre $mActiveEntityId === $sEntityUser
	 * @post Occurrence is hidden to user.
	 */
	function OccurrenceHide($OccurrenceId)
	{
		return $this->SetOccurrenceUserState($OccurrenceId, 'hide');
	}
	
	/// Set user-occurrence link to Show.
	/**
	 * @param $OccurrenceId integer Id of occurrence.
	 * @return bool True on success, False on failure.
	 * @pre Occurrence is visible to user.
	 * @pre $mActiveEntityId === $sEntityUser
	 * @post Occurrence is neither RSVP'd or hidden to user.
	 */
	function OccurrenceShow($OccurrenceId)
	{
		return $this->SetOccurrenceUserState($OccurrenceId, 'show');
	}
	
	
	/// Create subscription event-entity link
	function EventSubscribe($EventId)
	{
		/// @todo Implement.
	}
	/// Remove subscription event-entity link
	function EventUnsubscribe($EventId)
	{
		/// @todo Implement.
	}
	
	/// Create subscription to @a $EntityId
	function FeedSubscribe($EntityId, $Data)
	{
		/// @todo Implement.
	}
	/// Remove subscription to @a $EntityId
	function FeedUnsubscribe($EntityId)
	{
		/// @todo Implement.
	}
	
	
	
	
	
	
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! //
	// THE FOLLOWING IS TEMPORARY HARDCODED DATA: //
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! //
	
	/// TEMP: Put the standard england special days into an array.
	/**
	 * @return array of recurrence rules, each in this form:
	 *	- element 0: Name.
	 *	- element 1: RecurrenceRule describing when it takes place.
	 */
	private function RuleCollectionStdEngland()
	{
		$this->load->library('recurrence');
		
		$result = array();
		$result[] = array('New Years Day',$this->NewYearsDay());
		$result[] = array('Valentines Day',$this->ValentinesDay());
		$result[] = array('St. Patricks Day',$this->StPatricksDay());
		$result[] = array('Shrove Tuesday',$this->ShroveTuesday());
		$result[] = array('Ash Wednesday',$this->AshWednesday());
		$result[] = array('Mothering Sunday',$this->MotheringSunday());
		$result[] = array('April Fools Day',$this->AprilFoolsDay());
		$result[] = array('Good Friday',$this->GoodFriday());
		$result[] = array('Bank Holiday',$this->GoodFriday());
		$result[] = array('Easter Sunday',$this->EasterSunday());
		$result[] = array('Bank Holiday',$this->EasterMonday());
		$result[] = array('British Summer Time Begins',$this->BstBegins());
		$result[] = array('Fathers Day',$this->FathersDay());
		$result[] = array('St. Georges Day',$this->StGeorgesDay());
		$result[] = array('British Summer Time Ends',$this->BstEnds());
		$result[] = array('Early May Bank Holiday',$this->EarlyMayBankHoliday());
		$result[] = array('Spring Bank Holiday',$this->SpringBankHoliday());
		$result[] = array('Summer Bank Holiday',$this->SummerBankHoliday());
		$result[] = array('Halloween',$this->Halloween());
		$result[] = array('Bonfire Night',$this->BonfireNight());
		$result[] = array('Remembrance Day',$this->RemembranceDay());
		$result[] = array('Remembrance Sunday',$this->RemembranceSunday());
		$result[] = array('Christmas Eve',$this->ChristmasEve());
		$result[] = array('Christmas Day',$this->ChristmasDay());
		$result[] = array('Boxing Day',$this->BoxingDay());
		$result[] = array('New Years Eve',$this->NewYearsEve());
		return $result;
	}
	
	/// TEMP: Return the RecurrenceRule for easter sunday.
	function EasterSunday()
	{
		$rule = new RecurrenceRule();
		$rule->EasterSunday();
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for easter monday.
	function EasterMonday()
	{
		// monday after easter sunday
		$rule = $this->EasterSunday();
		$rule->OffsetDays(+1);
		return $rule;
	}
	
	/// TEMP:  the RecurrenceRule for good friday.
	function GoodFriday()
	{
		// friday before easter sunday
		$rule = $this->EasterSunday();
		$rule->OffsetDays(-2);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for mothering sunday.
	function MotheringSunday()
	{
		// 3 weeks before easter
		$rule = $this->EasterSunday();
		$rule->OffsetDays(-3*7);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for fathers day.
	function FathersDay()
	{
		$rule = new RecurrenceRule();
		// 3rd sunday in june
		$rule->MonthDate(6);
		$rule->OnlyDayOfWeek(0);
		$rule->SetWeekOffset(2);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for ash wednesday.
	function AshWednesday()
	{
		// beginning of lent (39 days before easter)
		$rule = $this->EasterSunday();
		$rule->OffsetDays(-39);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for shrove tuesday.
	function ShroveTuesday()
	{
		// day before ash wednesday
		$rule = $this->AshWednesday();
		$rule->OffsetDays(-1);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for christmas day.
	function ChristmasDay()
	{
		$rule = new RecurrenceRule();
		// 25th december
		$rule->MonthDate(12,25);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for christmas eve.
	function ChristmasEve()
	{
		$rule = new RecurrenceRule();
		// 24th december
		$rule->MonthDate(12,24);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for boxing day.
	function BoxingDay()
	{
		$rule = new RecurrenceRule();
		// 24th december
		$rule->MonthDate(12,26);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for new years eve.
	function NewYearsEve()
	{
		$rule = new RecurrenceRule();
		// 24th december
		$rule->MonthDate(12,31);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for new years day.
	function NewYearsDay()
	{
		$rule = new RecurrenceRule();
		// 24th december
		$rule->MonthDate(1,1);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for St. George's day.
	function StGeorgesDay()
	{
		$rule = new RecurrenceRule();
		// normally 23th april
		$rule->MonthDate(4,23);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for St. Patrick's day.
	function StPatricksDay()
	{
		$rule = new RecurrenceRule();
		// normally 17th march (my mum's birthday)
		$rule->MonthDate(3,17);
		return $rule;
	}
	
	/// TEMP: Return the RecurrenceRule for St. Stythian's day.
	function StStythiansFeastDay()
	{
		$rule = new RecurrenceRule();
		// first sunday in july with double figures
		$rule->MonthDate(7,10);
		$rule->OnlyDayOfWeek(0);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for Stithians show day.
	function StithiansShowDay()
	{
		// monday after St. Stythian's day
		$rule = $this->StStythiansFeastDay();
		$rule->OffsetDays(+1);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for the leap day in the gregorian calendar.
	function LeapDay()
	{
		$rule = new RecurrenceRule();
		// 29th february
		$rule->MonthDate(2,29);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for valentines day.
	function ValentinesDay()
	{
		$rule = new RecurrenceRule();
		// 14th february
		$rule->MonthDate(2,14);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for april fools day.
	function AprilFoolsDay()
	{
		$rule = new RecurrenceRule();
		// 1st April
		$rule->MonthDate(4,1);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for halloween.
	function Halloween()
	{
		$rule = new RecurrenceRule();
		// 31st October
		$rule->MonthDate(10,31);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for bonfire night.
	function BonfireNight()
	{
		$rule = new RecurrenceRule();
		// 5th november
		$rule->MonthDate(11,5);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for remembrance day.
	function RemembranceDay()
	{
		$rule = new RecurrenceRule();
		// 11th november
		$rule->MonthDate(11,11);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for remembrance day.
	function RemembranceSunday()
	{
		$rule = new RecurrenceRule();
		// nearest sunday to 11th november
		$rule->MonthDate(11,11);
		$rule->UseClosestEnabledDay();
		$rule->OnlyDayOfWeek(0);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for spring bank holiday.
	function EarlyMayBankHoliday()
	{
		$rule = new RecurrenceRule();
		// first monday in may
		$rule->MonthDate(5);
		$rule->OnlyDayOfWeek(1);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for spring bank holiday.
	function SpringBankHoliday()
	{
		$rule = new RecurrenceRule();
		// last monday in may
		$rule->MonthDate(6);
		$rule->OnlyDayOfWeek(1);
		$rule->SetWeekOffset(-1);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for summer bank holiday.
	function SummerBankHoliday()
	{
		$rule = new RecurrenceRule();
		// last monday in august
		$rule->MonthDate(9);
		$rule->OnlyDayOfWeek(1);
		$rule->SetWeekOffset(-1);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for changing clocks forward.
	function BstBegins()
	{
		$rule = new RecurrenceRule();
		// last sunday in april
		$rule->MonthDate(5);
		$rule->OnlyDayOfWeek(0);
		$rule->SetWeekOffset(-1);
		$rule->Time(2);
		return $rule;
	}
	
	/// TEMP: Returns the RecurrenceRule for changing clocks back.
	function BstEnds()
	{
		$rule = new RecurrenceRule();
		// last sunday in october
		$rule->MonthDate(11);
		$rule->OnlyDayOfWeek(0);
		$rule->SetWeekOffset(-1);
		$rule->Time(2);
		return $rule;
	}

}

?>