<?php

if (isset($BackwardUrl)) {
	echo('<a href="'.$BackwardUrl.'">Backward</a>');
}
if (isset($ForwardUrl)) {
	echo('<a href="'.$ForwardUrl.'">Forward</a>');
}

$squash = count($Days) > 3;

function DrawOccurrence(&$Occurrence, $Squash)
{
	?>
	<div id="ev_15" class="calviewIndEventBox2" style="width: 100%;">
		<div style="padding: 2px;font-size: small;">
			<span><?php echo($Occurrence->Event->Name); ?></span>
			<div class="calviewExpandedSmall" id="ev_es_%%refid%%" style="margin-top: 2px;">
				<div>
					<?php
					if ($Occurrence->TimeAssociated) {
						echo($Occurrence->StartTime->Format('g:ia'));
						echo('-');
						echo($Occurrence->EndTime->Format('g:ia'));
						echo('<br />');
					}
					if (!$Squash) {
						if (!empty($Occurrence->LocationDescription)) {
							echo($Occurrence->LocationDescription);
							echo('<br />');
						}
						echo('<i>');
						echo($Occurrence->Event->Description);
						echo('</i><br />');
						if (FALSE === $Occurrence->UserAttending) {
							echo('not attending');
						} elseif (TRUE === $Occurrence->UserAttending) {
							echo('attending');
						} else {
							echo('maybe attending');
						}
					}
					?>
				</div>
			</div>

		</div>
	</div>
	<?php
}

echo('<table id="calviewCalTable" border="0" cellpadding="0" cellspacing="0" width="100%">');
$last_term = -1;
foreach ($Weeks as $key => $week) {
	if ($last_term !== $week['start']->AcademicTerm()) {
		echo('<tr><td colspan="'.(count($week['days'])+1).'"><h2>');
		echo($week['start']->AcademicTermName().' '.
			 $week['start']->AcademicTermTypeName().' '.
			 $week['start']->AcademicYearName());
		echo('</h2></td></tr>');
		$last_term = $week['start']->AcademicTerm();
	}
	echo('<tr>');
	echo('<th></th>');
	foreach ($week['days'] as $date => $day) {
		echo('<th class="calviewCalHeadingCell">');
		echo('<a href="'.$day['link'].'">');
		echo($day['date']->Format('l'));
		echo('<br />');
		echo($day['date']->Format('jS M'));
		echo('</a>');
		echo('</th>');
	}
	echo('</tr><tr>');
	echo('<th><a href="'.$week['link'].'">'.$week['start']->AcademicWeek().'</a></th>');
	foreach ($week['days'] as $date => $day) {
		$times = $day['events'];
		echo('<td><a href="'.$day['link'].'">');
		foreach ($times as $time => $ocs) {
			foreach ($ocs as $occurrence) {
				DrawOccurrence($occurrence, $squash);
			}
		}
		echo('</a></td>');
	}
	echo('</tr>');
}
echo('</table>');

?>