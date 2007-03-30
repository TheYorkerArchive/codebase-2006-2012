<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file libraries/Calendar_view_days.php
 * @brief Calendar view for a set of days.
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * @pre loaded(library Calendar_frontend)
 *
 * Cunning fuzzy-absolute time.
 *
 * @version 29-03-2007 James Hogan (jh559)
 *	- Created.
 */

/// Days calendar view class.
class CalendarViewDays extends CalendarView
{
	/// Default constructor.
	function __construct()
	{
		parent::__construct('calendar/days');
	}
	
	/// Process the calendar data to produce view data.
	/**
	 * @param $Data CalendarData Calendar data.
	 * @param $Categories array[category] Array of categories.
	 *
	 * This should be the data which is specific to the view.
	 * General data such as day information should be calculated then passed in.
	 */
	protected function ProcessEvents(&$Data, $Categories)
	{
		$occurrences = $Data->GetOccurrences();
		$events = $Data->GetEvents();
		
		$this->SetData('Occurrences', $occurrences);
		$this->SetData('Events', $events);
	}
}

/// Dummy class.
class Calendar_view_days
{

}


?>