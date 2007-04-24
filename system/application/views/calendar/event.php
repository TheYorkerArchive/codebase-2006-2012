<?php

/**
 * @file views/calendar/event.php
 * @brief View for event information.
 *
 * @param $Event CalendarEvent Event information.
 * @param $Occurrence CalendarEvent,NULL Occurrence information.
 * @param $ReadOnly bool Whether the event is read only.
 */



?>
<div class="bluebox">
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
						echo('</i>');
						$CI = & get_instance();
						if ($Occurrence->EndTime->Timestamp() > time()) {
							echo('<br />');
							if (FALSE === $Occurrence->UserAttending) {
								echo('not attending');
								if ($Occurrence->Event->Source->IsSupported('attend')) {
									echo(' (<a href="'.site_url('calendar/actions/attend/'.
										$Occurrence->Event->Source->GetSourceId().
										'/'.urlencode($Occurrence->SourceOccurrenceId).
										'/accept'.$CI->uri->uri_string()).'">attend</a>');
									echo(', <a href="'.site_url('calendar/actions/attend/'.
										$Occurrence->Event->Source->GetSourceId().
										'/'.urlencode($Occurrence->SourceOccurrenceId).
										'/maybe'.$CI->uri->uri_string()).'">maybe attend</a>)');
								}
							} elseif (TRUE === $Occurrence->UserAttending) {
								echo('attending');
								if ($Occurrence->Event->Source->IsSupported('attend')) {
									echo(' (<a href="'.site_url('calendar/actions/attend/'.
										$Occurrence->Event->Source->GetSourceId().
										'/'.urlencode($Occurrence->SourceOccurrenceId).
										'/maybe'.$CI->uri->uri_string()).'">maybe attend</a>');
									echo(', <a href="'.site_url('calendar/actions/attend/'.
										$Occurrence->Event->Source->GetSourceId().
										'/'.urlencode($Occurrence->SourceOccurrenceId).
										'/decline'.$CI->uri->uri_string()).'">don\'t attend</a>)');
								}
							} else {
								echo('maybe attending');
								if ($Occurrence->Event->Source->IsSupported('attend')) {
									echo(' (<a href="'.site_url('calendar/actions/attend/'.
										$Occurrence->Event->Source->GetSourceId().
										'/'.urlencode($Occurrence->SourceOccurrenceId).
										'/accept'.$CI->uri->uri_string()).'">attend</a>');
									echo(', <a href="'.site_url('calendar/actions/attend/'.
										$Occurrence->Event->Source->GetSourceId().
										'/'.urlencode($Occurrence->SourceOccurrenceId).
										'/decline'.$CI->uri->uri_string()).'">don\'t attend</a>)');
								}
							}
						}
						if ('owned' === $Occurrence->Event->UserStatus) {
							echo('<br />');
							echo('<a href="'.site_url('calendar/actions/delete/'.
								$Occurrence->Event->Source->GetSourceId().
								'/'.urlencode($Occurrence->Event->SourceEventId).
								$CI->uri->uri_string()).'">delete</a>');
						}
						if (!$Squash && NULL !== $Occurrence->Event->Image) {
							echo('<br />');
							echo('<img src="'.$Occurrence->Event->Image.'" />');
						}
					}
					?>
				</div>
			</div>

		</div>
	</div>
</div>