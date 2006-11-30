<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file Academic_calendar.php
 * @brief Library of calendar helper functions.
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * This file is very much a work in progress.
 * It'll probably get renamed in the near future.
 *
 * The Academic_time class is intended to allow views to choose what format to
 *	represent dates and times.
 *
 * The Academic_calendar class is the main library which can be loaded by a controller.
 *
 */
 
/**
 * @brief Time class with academic calendar capabilities.
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * Represents a time, which can be obtained in various formats.
 *
 * This class is intended to allow views to choose what format to
 *	represent dates and times.
 *
 * @todo jh559: Perform tests (can't right now, at uni with php 4!)
 */
class Academic_time
{
	// Time information (cached in other forms)
	private $mTimestamp;      ///< @brief Main timestamp.
	
	private $mGregorianYear;  ///< @brief Year integer [2006...].
	private $mGregorianMonth; ///< @brief Month integer [1..12].
	private $mGregorianDate;  ///< @brief Day of month integer [1..31).
	
	private $mAcademicYear;   ///< @brief Year at start of academic year [2006...].
	private $mAcademicTerm;   ///< @brief Academic term integer [0..5].
	private $mAcademicWeek;   ///< @brief Academic term integer [1..10(or more)].
	
	private $mDayOfWeek;      ///< @brief Day of week integer [1..7] where 1=monday.
	
	private $mHours;          ///< @brief Number of hours integer [0..23].
	private $mMinutes;        ///< @brief Number of minutes integer [0..59].
	private $mSeconds;        ///< @brief Number of seconds integer [0..59].
	
	private $mTime;           ///< @brief Formatted time.
	
	
	/**
	 * @brief Associative cache of academic year data.
	 * @see GetAcademicYearData
	 */
	private static $sAcademicYears = array();
	
	/**
	 * @brief Names of terms.
	 */
	private static $sTermNames = array(
		0 => 'autumn', 1 => 'christmas',
		2 => 'spring', 2 => 'easter',
		4 => 'summer', 5 => 'summer');
	
	/**
	 * @brief Names of term types.
	 */
	private static $sTermTypeNames = array(
		0 => 'term',   1 => 'holiday');
	
	/**
	 * @brief Construct a time object from a timestamp.
	 * @param $Timestamp Timestamp to initialise time object to.
	 */
	function Academic_time($Timestamp)
	{
		$this->mTimestamp = $Timestamp;
	}
	
	/**
	 * @brief Format the timestamp using the php date function.
	 * @param $Format Formatting string to use in the php date function.
	 * @return The formatted time string.
	 */
	function Format($Format)
	{
		return date($Format, $this->mTimestamp);
	}
	
	/**
	 * @brief Get the time as a timestamp.
	 * @return The time stored as a timestamp.
	 */
	function Timestamp()
	{
		return $this->mTimestamp;
	}
	
	// GREGORIAN
	/**
	 * @brief Get the year.
	 * @return The full year of the time stored, as an integer (e.g. 2006).
	 */
	function Year()
	{
		if (!isset($this->mGregorianYear))
			$this->mGregorianYear = (int)date('Y', $this->mTimestamp);
		return $this->mGregorianYear;
	}
	
	/**
	 * @brief Get the month.
	 * @return The month of the time stored, as an integer [1..12].
	 */
	function Month()
	{
		if (!isset($this->mGregorianMonth))
			$this->mGregorianMonth = (int)date('n', $this->mTimestamp);
		return $this->mGregorianMonth;
	}
	
	/**
	 * @brief Get the day of the month.
	 * @return The day of the month of the time stored, as an integer [1..31].
	 */
	function DayOfMonth()
	{
		if (!isset($this->mGregorianDate))
			$this->mGregorianDate = (int)date('j', $this->mTimestamp);
		return $this->mGregorianDate;
	}
	
	// ACADEMIC
	/**
	 * @brief Get the academic year.
	 * @return The year at the start of the academic year.
	 */
	function AcademicYear()
	{
		/// @todo jh559: Test
		if (!isset($this->mAcademicYear)) {
			$academic_year_start = self::StartOfAcademicTerm($this->Year());
			if ($this->mTimestamp >= $academic_year_start)
				$this->mAcademicYear = $this->mGregorianYear;
			else
				$this->mAcademicYear = $this->mGregorianYear-1;
		}
		return $this->mAcademicYear;
	}
	 
	/**
	 * @brief Get the academic term.
	 * @return The id of the academic term of the time stored, as an integer:
	 *	- 0: Autumn Term
	 *	- 1: Christmas Holidays
	 *	- 2: Spring Term
	 *	- 3: Easter Holidays
	 *	- 4: Summer Term
	 *	- 5: Summer Holidays
	 */
	function AcademicTerm()
	{
		/// @todo jh559: Test
		if (!isset($this->mAcademicTerm)) {
			// get the term data for the $this->AcademicYear()
			// go through to find out which term we're in
			$academic_year_data = self::GetAcademicYearData($this->AcademicYear());
			if ($academic_year_data === FALSE) {
				// No records about the specified academic year exist!
				$error_message = 'Unknown academic year: ' .
						$AcademicYear .
						'provided to Academic_time::AcademicTerm';
				throw new Exception($error_message);
				
			} else {
				$this->mAcademicTerm = 5;
				// Records exist, see where the timestamp fits in
				// (no point doing binary search on just 6 items)
				for ($term_counter = 0; $term_counter <= 4; ++$term_counter) {
					// If the date is before the end of the term, its in the term.
					if ($this->mTimestamp < $academic_year_data['term_starts'][$term_counter+1]) {
						$this->mAcademicTerm = $term_counter;
						break;
					}
				}
			}
			
		}
		return $this->mAcademicTerm;
	}
	
	/**
	 * @brief Get the week of the academic term.
	 * @return The week number of the academic term of the time stored,
	 *	as an integer (1: week 1 etc).
	 */
	function AcademicWeek()
	{
		/// @todo jh559: Test
		if (!isset($this->mAcademicWeek)) {
			// get the start of the academic term
			// find out how many weeks have elapsed
			$start_of_term = self::StartOfAcademicTerm($this->AcademicYear(), $this->AcademicTerm());
			$days_in_between = self::DaysBetweenTimestamps($start_of_term, $this->mTimestamp);
			$this->mAcademicWeek = $days_in_between/7+1;
		}
		return $this->mAcademicWeek;
	}
	
	/**
	 * @brief Get the day of the week.
	 * @return The day of the week of the time stored, as an integer:
	 *	- 1: Monday
	 *	- 7: Sunday
	 */
	function DayOfWeek()
	{
		if (!isset($this->mDayOfWeek)) {
			$this->mDayOfWeek = (int)date('N', $this->mTimestamp);
		}
		return $this->mDayOfWeek;
	}
	
	// TIME
	/**
	 * @brief Get the hour of the day.
	 * @return The hour of the day of the time stored, as an integer [0..23].
	 */
	function Hours()
	{
		if (!isset($this->mHours))
			$this->mHours = date('H', $this->mTimestamp);
		return $this->mHours;
	}
	
	/**
	 * @brief Get the minute of the hour.
	 * @return The minute of the hour of the time stored, as an integer [0..59].
	 */
	function Minutes()
	{
		if (!isset($this->mMinutes))
			$this->mMinutes = date('i', $this->mTimestamp);
		return $this->mMinutes;
	}
	
	/**
	 * @brief Get the second of the minute.
	 * @return The second of the minute of the time stored, as an integer [0..59].
	 */
	function Seconds()
	{
		if (!isset($this->mSeconds))
			$this->mSeconds = date('s', $this->mTimestamp);
		return $this->mSeconds;
	}
	
	// Custom
	/**
	 * @brief Get the time, formatted as appropriate.
	 * @return The time formatted as HH:MM [am/pm].
	 */
	function Time()
	{
		if (!isset($this->mTime)) {
			if (self::IsTwentyFourHourClock())
				$this->mTime = date('H:i', $this->mTimestamp);
			else
				$this->mTime = date('g:ia', $this->mTimestamp);
		}
		return $this->mTime;
	}
	
	/**
	 * @brief Get the name associated with the academic year.
	 *
	 * @param $YearLength Integer indicating how long each year should be.
	 *	- if @a $YearLength == 2, return value might be 06/07
	 *	- if @a $YearLength == 4, return value might be 2006/2007
	 * @param $Separator String separator between years.
	 *
	 * @return String in the form 'Y1' . @a $Separator . 'Y2' where:
	 *	- Y1 is the first year trimmed to length @a $YearLength .
	 *	- Y2 is the second year trimmed to length @a $YearLength .
	 */
	function AcademicYearName($YearLength = 4, $Separator = '/')
	{
		$academic_year = $this->AcademicYear()
		return substr($academic_year, -$YearLength)
			. $Separator
			. substr($academic_year+1, -$YearLength);
	}
	
	/**
	 * @brief Get the name associated with the academic term.
	 * @return String containing term name.
	 */
	function AcademicTermName()
	{
		return $this->sTermNames[$this->AcademicTerm()];
	}
	
	/**
	 * @brief Find out whether the time is in term time.
	 * @return Boolean whether in term time.
	 */
	function IsTermTime()
	{
		return ($this->AcademicTerm() % 2) === 0;
	}
	
	/**
	 * @brief Find out whether the time is in holday.
	 * @return Boolean whether in holday.
	 */
	function IsHoliday()
	{
		return ($this->AcademicTerm() % 2) === 1;
	}
	
	
	// Static
	/**
	 * @brief Find the number of days between close timestamps.
	 * @param $FirstTimestamp Earlier timestamp;
	 * @param $SecondTimestamp Later timestamp;
	 * @return integer The number of days between two timestamps.
	 *
	 * @pre @a $FirstTimestamp <= @a $SecondTimestamp
	 * @pre @a $FirstTimestamp and @a $SecondTimestamp are less than a 365 days apart.
	 */
	static function DaysBetweenTimestamps($FirstTimestamp, $SecondTimestamp)
	{
		/// @todo jh559: Test
		$day_of_year_of_first = (int)date('z',$FirstTimestamp);
		$day_of_year_of_second = (int)date('z',$SecondTimestamp);
		$difference = $day_of_year_of_second-$day_of_year_of_first;
		if ($difference < 0) {
			// $SecondTimestamp is in the year after $FirstTimestamp
			// dif = doy2 + diy(1)+1-doy1
			// dif = dif + diy(1)
			$difference += 366 + (int)date('L',$FirstTimestamp) + 1;
		}
		return $difference;
	}
	
	/**
	 * @brief Find out whether to use 24 hour times.
	 * @return Whether to use 24 hour times.
	 */
	private static function IsTwentyFourHourClock()
	{
		/// @todo jh559: Get whether to use 24 hour clock from user preferences.
		return TRUE;
	}
	
	/**
	 * @brief Get the start timestamp of an academic term.
	 * @param $AcademicYear Year at start of academic year integer [2006..].
	 * @param $Term Term of the year integer [0..5].
	 * @return Timestamp of midnight on the morning of the first monday of the
	 *	specified term.
	 * @pre 0 <= @a $Term < 6.
	 */
	static function StartOfAcademicTerm($AcademicYear, $Term = 0)
	{
		$academic_year_data = self::GetAcademicYearData($AcademicYear);
		if ($academic_year_data === FALSE) {
			// No records about the specified academic year exist!
			$error_message = 'Unknown academic year: ' .
					$AcademicYear .
					'provided to Academic_time::StartOfAcademicTerm';
			throw new Exception($error_message);
			
		} elseif (array_key_exists($Term, $academic_year_data['term_starts'])) {
			// The records exist and $Term is valid.
			return $academic_year_data['term_starts'][$Term];
			
		} else {
			// The records exist but $Term is invalid.
			$error_message = 'Invalid $Term: ' .
					$Term .
					'provided to Academic_time::StartOfAcademicTerm';
			throw new Exception($error_message);
		}
	}
	
	/**
	 * @brief Get the term dates of an academic year.
	 * @param $AcademicYear integer Year of start of academic year.
	 * @return FALSE if @a $AcademicYear is unknown,
	 *	or an array of academic year data formatted as follows:
	 *	- 'year': year integer (e.g. 2006)
	 *	- 'term_starts': array of timestamps:
	 *		- 0: First day of autumn term       (midnight on a monday morning)
	 *		- 1: First day of christmas holiday (midnight on a monday morning)
	 *		- 2: First day of spring term       (midnight on a monday morning)
	 *		- 3: First day of easter holiday    (midnight on a monday morning)
	 *		- 4: First day of summer term       (midnight on a monday morning)
	 *		- 5: First day of summer holiday    (midnight on a monday morning)
	 *	- 'term_weeks': array of integers:
	 *		- 0: Number of weeks in autumn term
	 *		- 1: Number of weeks in spring term
	 *		- 2: Number of weeks in summer term
	 *
	 * @todo jh559: Consider fact that summer term begins on wednesday when
	 *	easter falls late. Perhaps terms shouldn't be restricted to number of
	 *	weeks.
	 *	- see http://www.york.ac.uk/admin/po/terms.htm
	 */ 
	private static function GetAcademicYearData($AcademicYear)
	{
		if (!array_key_exists($AcademicYear, $this->mAcademicYears)) {
			// The academic year hasn't been cached, so do it now
			
			/**
			 * @todo jh559: Implement GetAcademicYearData using data from db.
			 * The academic term data needs to be stored in the database:
 			 *	DB Structure:
			 *	- AcademicYear
			 *		- start_term_[1-3] -- could be timestamp or week number
			 *			(since this must be midnight on a monday morning)
			 *		- num_term_weeks_[1-3]
			 */
			$year_data = array(
					'year' => $AcademicYear,
					'term_weeks' => array(
							0 => 10,
							1 => 10,
							2 => 10);
			// Hardwire the term dates:
			if ($AcademicYear == 2004) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10,11, 2004),
						2 => mktime(0,0,0,  1,10, 2005),
						4 => mktime(0,0,0,  4,25, 2005)),
			} elseif ($AcademicYear == 2005) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10,10, 2005),
						2 => mktime(0,0,0,  1,09, 2006),
						4 => mktime(0,0,0,  4,24, 2006)),
			} elseif ($AcademicYear == 2006) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10, 9, 2006),
						2 => mktime(0,0,0,  1, 8, 2007),
						4 => mktime(0,0,0,  4,23, 2007)),
			} elseif ($AcademicYear == 2007) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10, 8, 2007),
						2 => mktime(0,0,0,  1, 7, 2008),
						4 => mktime(0,0,0,  4,21, 2008)),
			} elseif ($AcademicYear == 2008) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10,13, 2008),
						2 => mktime(0,0,0,  1,12, 2009),
						4 => mktime(0,0,0,  4,27, 2009)),
			} elseif ($AcademicYear == 2009) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10,12, 2009),
						2 => mktime(0,0,0,  1,11, 2010),
						4 => mktime(0,0,0,  4,26, 2010)),
			} elseif ($AcademicYear == 2010) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10,11, 2010),
						2 => mktime(0,0,0,  1,10, 2011),
						4 => mktime(0,0,0,  4,25, 2011)),
			} elseif ($AcademicYear == 2011) {
				$year_data['term_starts'] = array(
						0 => mktime(0,0,0, 10,10, 2011),
						2 => mktime(0,0,0,  1, 9, 2012),
						4 => mktime(0,0,0,  4,23, 2012)),
			} else {
				// The year in question is invalid
				// (cause it ain't yet implemented)
				return FALSE;
			}
			// Calculate holiday start dates
			for ($term_counter = 0; $term_counter < 3; ++$term_counter) {
				$year_data['term_starts'][$term_counter*2 + 1] = strtotime(
						'+' . $year_data['term_weeks'][$term_counter],
						$year_data['term_starts'][$term_counter*2]);
			}	
			// Cache the result
			$this->mAcademicYears[$AcademicYear] = $year_data;
			
		}
		// The academic year should now have been cached
		return $this->mAcademicYears[$AcademicYear];
	}
}

/**
 * @brief Library of calendar helper functions.
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * The Academic_calendar class is the main library which can be loaded by a controller.
 */
class Academic_calendar {

	
	/**
	 * @brief Default constructor.
	 */
	function Academic_calendar()
	{
		// Nothing to do yet
	}


	/**
	 * @brief Get a time object from a timestamp.
	 * @param $Timestamp timestamp.
	 * @return Academic_time object set to @a $Timestamp.
	 */
	function Timestamp($Timestamp)
	{
		return new Academic_time($Timestamp);
	}
	
	/**
	 * @brief Get a time object from a gregorian date & time.
	 * @param $Year Year.
	 * @param $Month Month.
	 * @param $Day Day of month.
	 * @param $Hour Hour of day.
	 * @param $Minute Minute of hour.
	 * @param $Second Second of minute.
	 * @param $IsDst Is the time in daylight saving time.
	 * @return Academic_time object set using php mktime function.
	 */
	function Gregorian($Year,$Month,$Day,$Hour = 0,$Minute = 0,$Second = 0,$IsDst = 0)
	{
		return new Academic_time(mktime($Hour,$Minute,$Second,$Month,$Day,$Year,$IsDst));
	}
	
	/**
	 * @brief Get a time object from an academic date & time.
	 * @param $AcademicYear Year at start of academic year integer [2006..].
	 * @param $Term Term of the year integer [0..5].
	 * @param $Week Week integer [1..].
	 * @param $DayOfWeek Day of week integer [1..7].
	 * @param $Hour Hour of the day integer [0..23].
	 * @param $Minute Minute of the hour integer [0..59].
	 * @param $Second Second of the minute integer [0..59].
	 * @return Academic_time object set academic term data.
	 */
	function Academic($AcademicYear, $Term = 0, $Week = 1, $DayOfWeek = 1, $Hour = 0, $Minute = 0, $Second = 0)
	{
		$start_of_term = Academic_time::StartOfAcademicTerm($AcademicYear, $Term);
		return new Academic_time(strtotime(
				'+' . ($Week-1) . ' week ' .
				'+' . ($DayOfWeek-1) . 'day' .
				'+' . ($Hour) . 'hour' .
				'+' . ($Minute) . 'min' .
				'+' . ($Second) . 'sec',$start_of_term));
	}
	
}

?>