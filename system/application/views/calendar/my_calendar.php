<?php

/**
 * @file views/calendar/my_calendar.php
 * @brief Peronsalised calendar main view frame.
 *
 * @see
 *	- http://theyorker.gmghosting.com/calendar My Calendar Main Page
 *	- http://real.theyorker.co.uk/wiki/Functional:My_Calendar Functional Specification
 *
 * @version 20/03/2007 James Hogan (jh559)
 *	- Created
 *
 * @param $Filters array Array of filters, indexed by an id:
 *	- 'name'        => string  Name of the filter.
 *	- 'field'       => string  Occurrence field to filter by.
 *	- 'value'       => string  Occurrence field value to filter by.
 *	- 'selected'    => bool    Whether the filter is selected by default.
 *	- 'description' => string  Description of filter.
 *	- 'display'     => string  Display mode {'block','image'}.
 *	- 'colour'      => string  Hexadecimal colour value (if display == 'block').
 *	- 'selected_image'   => string  Path of image for selected (if display == 'image').
 *	- 'unselected_image' => string  Path of image for unselected (if display == 'image').
 * @param $ViewMode FramesView Main subview to display events with.
 * @param $Permissions array(string=>) Permissions such as 'create','edit'
 */

?>

<div class="BlueBox">
<table id="cal_toolbox_info" width="100%">
	<tr>
		<td colspan="2">
	<?php
	foreach ($Filters as $id => $filter) {
		switch ($filter['display']) {
			case 'block':
				// Change colour to that of the category, only if selected
				$classes = array('CalendarFilter');
				if (!$filter['selected']) {
					$classes[] = 'Deselected';
				}
				echo('<a class="'.implode(' ',$classes).'"');
				if ($filter['selected']) {
					echo(' style="background-color:#'.$filter['colour'].';"');
				}
				if (array_key_exists('link', $filter)) {
					echo(' href="'.$filter['link'].'"');
				}
				echo('>');
				echo($filter['name']);
				echo('</a>');
				break;
			case 'image':
				// Draw one of the two images for selected/unselected with linkmy
				if (array_key_exists('link', $filter)) {
					echo('<a class="CalendarButtons" href="'.$filter['link'].'">');
				}
				echo('<img src="'.$filter[$filter['selected']?'selected_image':'unselected_image'].'" alt="'.xml_escape($filter['name']).'" title="'.xml_escape($filter['name']).'" />');
				if (array_key_exists('link', $filter)) {
					echo('</a>');
				}
				break;
		}
	}
	
	?>
		</td>
	</tr>
	<tr>
		<td><?php
if (isset($RangeDescription)) { 
	echo('<strong>'.xml_escape($RangeDescription).'</strong>');
}
?></td>
		<td>
		<?php if (!empty($CreateSources)) { ?>
			<ul>
				<?php foreach ($CreateSources as $source) { ?>
					<li><a href="<?php echo(site_url($Path->EventCreate($source)).get_instance()->uri->uri_string()); ?>">Add event to <?php echo(xml_escape($source->GetSourceName())); ?> Calendar</a></li>
				<?php } ?>
			</ul>
		<?php } ?>
		</td>
	</tr>
</table>
<div style="width:100%;">
<?php
if (isset($streams)) {
	foreach ($streams as $id => $stream) {
		if (isset($stream['subscribed'])) {
			if ($stream['subscribed']) {
				echo($Path->OrganisationUnsubscribeLink($stream['name'], $stream['short_name'], 'calendar'));
			} else {
				echo($Path->OrganisationSubscribeLink($stream['name'], $stream['short_name'], 'calendar'));
			}
		}
	}
}
?>
</div>
</div>

<?php
// Load the main view
$ViewMode->Load();
?>