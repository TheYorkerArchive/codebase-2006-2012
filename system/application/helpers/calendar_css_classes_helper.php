<?php

/**
 * @file helpers/calendar_css_classes_helper.php
 * @brief Provides functions for obtaining CSS classes for date.
 * @author James Hogan (jh559)
 */

/// Get array of css classes for a given date.
/**
 * @param $Date &Academic_time Start time of day.
 * @return array[string] Class names.
 */
function CalCssGetDateClasses(& $day_start, $day_of_week, $past = NULL)
{
	$today = Academic_time::NewToday();
	$today_id = $today->Format('Ymd');
	
	$cell_id = $day_start->Format('Ymd');
	
	$classes_list = array();
	if ($cell_id == $today_id) {
		$classes_list[] = 'tod';
	}
	if ($day_start->Month() % 2 == 0) {
		$classes_list[] = 'ev';
	}
	if (($day_of_week+6)%7>4) {
		$classes_list[] = 'we';
	}
	if ((NULL === $past && $day_start->Timestamp() < $today->Timestamp()) ||
		(true === $past))
	{
		$classes_list[] = 'pa';
	}
	return $classes_list;
}

/// Get the styles associated with an event occurrence
function CalCssGetEventClasses(&$event_info)
{
	$names = array();
	if ($event_info->UserAttending == 'yes') {
		$names[] = 'attend';
	} elseif ($event_info->UserAttending == 'no') {
		$names[] = 'noattend';
	}
	if ($event_info->Event->UserStatus == 'owner' &&
		$event_info->State == 'draft')
	{
		if (!$event_info->UserHasPermission('publish')) {
			$names[] = 'personal';
		} else {
			$names[] = 'draft';
		}
	}
	if ($event_info->State == 'cancelled') {
		$names[] = 'cancelled';
	}
	return $names;
}

// Change new lines into <br />
function js_nl2br ($string) {
	return str_replace(array("\r\n", "\r", "\n"), '<br />', $string);
}


?>