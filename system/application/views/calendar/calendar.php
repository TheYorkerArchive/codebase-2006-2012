<?php

// Put special headings at top
foreach ($dayinfo as $id => $info) {
	$eventBoxCode[$id] = '';
	foreach ($info['special_headings'] as $name) {
		$eventBoxCode[$id] .= '<div class="calviewEBCSpecialDayHeading">' .
				$name . '</div>';
	}
}

/*
// Then events
foreach ($events as $events_array_index => $event) {
	
	$replace = array (
		'%%arrid%%' => $events_array_index, 
		'%%refid%%' => $event['ref_id'],
		'%%name%%' => $event['name'],
		'%%date%%' => $event['date'],
		'%%day%%' =>  $event['day'],
		'%%starttime%%' => $event['starttime'],
		'%%endtime%%' => $event['endtime'],
		'%%blurb%%' => $event['blurb'],
		'%%shortloc%%' => $event['shortloc'],
		'%%type%%' => $event['type'],
	);
	
	$mypath = pathinfo(__FILE__);
	$snippets_dir = $mypath['dirname'] . "/snippets";
	@$eventBoxCode[$event['day']] .= apinc ($snippets_dir . "/calviewEventBox.inc",$replace);
	
}

// put &nbsp; onto end of all days
for ($i = 0;$i < 7;$i++) {
	@$eventBoxCode[$i] .= '&nbsp;';
}
*/

// as JS array instead




$mypath = pathinfo(__FILE__);
$snippets_dir = $mypath['dirname'] . "/snippets";
$days_JSON = array ();

foreach ($Events as $events_array_index => $event_main) {
	//var_dump($event_main);
	foreach ($event_main->Occurrences as $event) {
		//var_dump($event);
		if (isset ($days_JSON[$event->StartTime->Midnight()->Timestamp()]))
			$days_JSON[$event->StartTime->Midnight()->Timestamp()] .= ",\n";
		
			
		/* ($events_array_index <= (count ($events) - 1))
			$fcom = '';
		else
			$fcom = ',';*/
			
		$replace = array (
			'%%arrid%%' => $events_array_index, 
			'%%refid%%' => $event->OccurrenceId,
			'%%name%%' => htmlspecialchars ($event_main->Name),
			'%%date%%' => $event->StartTime->Format('Ymd'),
			'%%starttime%%' => $event->StartTime->Format('Hi'),
			'%%endtime%%' => $event->EndTime->Format('Hi'),
			'%%day%%' => $event->Day,
			'%%blurb%%' => htmlspecialchars ($event_main->Description),
			'%%shortloc%%' => htmlspecialchars ($event->LocationDescription),
		);
		if (isset($days_JSON[$event->Day])) {
			$days_JSON[$event->Day] .= ',';
		}
		@$days_JSON[$event->Day] .= apinc ($snippets_dir . "/singleEventJSArr.inc",$replace);
	}
}

$ops = "";
foreach ($days_JSON as $did => $JSD) {
	if (strlen ($JSD) == 0)
		$ccom = '';
	else
		$ccom = ',';
		
	$ops .= "\n\t'$did': {\n{$days_JSON[$did]}\n\t}$ccom\n";
}

$ops = trim ($ops);
$ops = substr ($ops,0,strlen ($ops) -1);

$js_ops = "\ncalevents = ({ \n\t$ops\n})";

?>

		<?php
if (isset($prev) && isset($next)) {
	echo '<a href="'.$prev.'">Previous Week</a><br/>';
	echo '<a href="'.$next.'">Next Week</a><br/>';
}

//$mypath = pathinfo(__FILE__);
//$snippets_dir = $mypath['dirname'] . "/snippets";
//echo apinc ($snippets_dir . "/calviewEventMenu.inc",array ());

		?>

<script type="text/javascript">
var calevents = {};
<?php echo $js_ops ?>

// this is to store events hidden in future.
var revokeRefids = [];
var daysDiv = ['calviewMonday','calviewTuesday','calviewWednesday','calviewThursday','calviewFriday','calviewSaturday','calviewSunday'];

function init_calendar () {
	draw_calendar (calevents);
}
</script>


<!-- Container div; contains everything
	will make it easier to shove in a template later! -->
<div id="calviewContainer">
	
	<!-- Holds left hand menu -->
	<div id="calviewLeftBar">

		This is an &uuml;ber mockup! The JS code is NOT a proper app and is not
		scalable in any way. This does not use any established conventions and is
		here as an interface "rfc" if you like...
		
		
	
	</div>
	<!-- Holds main calendary thinger -->
	<div id="calviewCalendarWindow">
		

		<table id="calviewCalTable" cellpadding="0" cellspacing="0" border="0">
			
			<!-- headings w/ date & time -->
			<tr>
				<td class="calviewCalHeadingCell">
					<strong>Monday</strong><br />
					<div style="text-align: center"><?php echo $days[0] ?></div>
				</td>
				<td class="calviewCalHeadingCell">
					<strong>Tuesday
					<div style="text-align: center"><?php echo $days[1] ?></div>
				</td>
				<td class="calviewCalHeadingCell">
					<strong>Wednesday
					<div style="text-align: center"><?php echo $days[2] ?></div>
				</td>
				<td class="calviewCalHeadingCell">
					<strong>Thursday
					<div style="text-align: center"><?php echo $days[3] ?></div>
				</td>
				<td class="calviewCalHeadingCell">
					<strong>Friday
					<div style="text-align: center"><?php echo $days[4] ?></div>
				</td>
				<td class="calviewCalHeadingCell">
					<strong>Saturday
					<div style="text-align: center"><?php echo $days[5] ?></div>
				</td>
				<td class="calviewCalHeadingCell">
					<strong>Sunday
					<div style="text-align: center"><?php echo $days[6] ?></div>
				</td>
			</tr>
			
			<!-- cells to contain javascript-fu -->
			<tr>
				<td class="calviewCalEventsCell" id="calviewMonday">
					<?php 
						// echo all of Monday's events
						echo @$eventBoxCode[0];
					?>
				</td>
				<td class="calviewCalEventsCell" id="calviewTuesday">
					<?php 
						// echo all of Tuesday's events
						echo @$eventBoxCode[1];
					?>
				</td>
				<td class="calviewCalEventsCell" id="calviewWednesday">
					<?php 
						// echo all of Wednesday's events
						echo @$eventBoxCode[2];
					?>
				</td>
				<td class="calviewCalEventsCell" id="calviewThursday">
					<?php 
						// echo all of Thursday's events
						echo @$eventBoxCode[3];
					?>
				</td>
				<td class="calviewCalEventsCell" id="calviewFriday">
					<?php 
						// echo all of Friday's events
						echo @$eventBoxCode[4];
					?>
				</td>
				<td class="calviewCalEventsCell" id="calviewSaturday">
					<?php 
						// echo all of Saturday's events
						echo @$eventBoxCode[5];
					?>
				</td>
				<td class="calviewCalEventsCell" id="calviewSunday">
					<?php 
						// echo all of Sunday's events
						echo @$eventBoxCode[6];
					?>
				</td>
				
			</tr>
			
			<script type="text/javascript">
				
				// Calendar is loaded now so safe to render (provided browser isn't smoking crack)
				init_calendar ();
			
			</script>
			
		</table>
	
	</div>
	
	
</div>
