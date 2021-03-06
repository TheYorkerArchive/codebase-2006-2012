<?php

//$session_var defined in controller, but for some reason won't let it be accessed in these functions so i pass it through $sessionvar

// prints the given list of a certain name filling in default if no data is in the session
function PrintRadioList($input_name, $items, $default, $sessionvar)
{
	foreach ($items as $key => $item)
	{
		echo('<label for="'.$input_name.'_'.$key.'">'.xml_escape($item).'</label>');
		echo('<input type="radio" name="'.$input_name.'" id="'.$input_name.'_'.$key.'" value="'.xml_escape($item).'" ');
		if (isset($_SESSION[$sessionvar][$input_name])) {
			if ($_SESSION[$sessionvar][$input_name] == $item) {
				echo('checked="checked" ');
			}
		} else {
			if ($item == $default) {
				echo('checked="checked" ');
			}
		}
		echo('/><br />');
	}
}

function PrintDropDownList($input_name, $label, $items, $default, $sessionvar)
{
	echo('<label for="'.$input_name.'">'.xml_escape($label).'</label>
			<select name="'.$input_name.'" size="1">');
	foreach($items as $item){
		echo("<option value='".xml_escape($item['value'])."' ");
		if (isset($_SESSION[$sessionvar][$input_name])) {
			if ($_SESSION[$sessionvar][$input_name] == $item['value']) {
				echo('selected="selected" ');
			}
		} else {
			if ($item == $default) {
				echo('selected="selected" ');
			}
		}
		echo('>'.xml_escape($item['name']).'</option>');
	}
	echo('</select><br />');
}

function PrintTextBox ($input_name, $item, $sessionvar)
{
	echo('<label for="'.$input_name.'">'.xml_escape($item).'</label>');
	echo('<input type="text" name="'.$input_name.'" id="'.$input_name.'" style="width: 220px;" ');
	if (isset($_SESSION[$sessionvar][$input_name])) {
		echo('value="'.xml_escape($_SESSION[$sessionvar][$input_name]).'" ');
	}
	echo('/>');
}

function PrintTextArea ($input_name, $item, $sessionvar)
{
	echo('<label for="'.$input_name.'">'.$item.'</label>');
	echo('<textarea name="'.$input_name.'" id="'.$input_name.'" cols="25" rows="5">');
	if (isset($_SESSION[$sessionvar][$input_name]))
		echo(xml_escape($_SESSION[$sessionvar][$input_name]));
	echo('</textarea>');
}

function addstrong($text)
{
	return '<strong>'.$text.'</strong>';
}

function addstrike($text)
{
	return '<strike>'.$text.'</strike>';
}

?>

<div id="RightColumn">
	<h2 class="first">Pages</h2>
	<div class="Entry">
		<ol>
		<?php

		$headings = $stage_list['headings'];

		foreach ($headings as $key => &$heading)
		{
			$heading = xml_escape($heading);
			if ($stage == $key)
				$heading = addstrong($heading);
			if ($is_connected == 'No' && in_array($key, $stage_list['skip']))
				$heading = addstrike($heading);
			echo '<li>'.$heading.'</li>';
		}

		?>
		</ol>
	</div>

	<h2>What's this?</h2>
	<div class="Entry">
		This wizard will guide you through the process of suggesting an organisation to us. Once submitted, your suggestion will be reviewed by our PR team, and will appear on our site in the near future.
		<br />
		<br />
		Please note that you may only add <b>Photos</b> or enter <b>More Details</b> if you are connected with the organisation.
        </div>
</div>

<div id="MainColumn">
<?php
	if ($stage == 1)
	{
?>
<div class="BlueBox">
<h2>start suggesting</h2>
	<form id="orgdetails" action="/wizard/organisation" method="post" class="form">
		<fieldset>
			<input type="hidden" name="r_stage" value="<?php echo($stage); ?>" />
			<input type="hidden" name="r_dump" value="<?php echo(xml_escape(serialize($_SESSION[$session_var]))); ?>" />
			<?php
			$list_data = array();
			foreach($organisations as $organisation)
			{
				$list_data[] = array(
					'value' => $organisation['organisation_type_id'],
					'name' => $organisation['organisation_type_name']
				);
			}
			PrintDropDownList('a_type', 'Type of directory entry', $list_data, 2, $session_var); ?>
		</fieldset>
		<?php if($office) { ?>
			<p>You are accessing this wizard as an office member.</p>
			<input name="a_connected" type="hidden" value="Yes" />
		<?php } else { ?>
			<fieldset>
				<h3>Are you connected to this organisation?</h3>
				<?php PrintRadioList('a_connected', array('Yes', 'No'), 'No', $session_var); ?>
			</fieldset>
		<?php } ?>
		<fieldset>
			<input type="submit" name="r_submit_finish" value="Finish" class="button" disabled="disabled" />
			<input type="submit" name="r_submit_next" value="Next" class="button" />
			<input type="submit" name="r_submit_back" value="Back" class="button" disabled="disabled" />
		</fieldset>
	</form>
</div>
<?php
	}
?>

<?php
	if ($stage == 2)
	{
?>
<div class="BlueBox">
<h2>basic details</h2>
	<form id="orgdetails" action="/wizard/organisation" method="post" class="form">
		<fieldset>
			<input type="hidden" name="r_stage" value="<?php echo($stage); ?>" />
			<input type="hidden" name="r_dump" value="<?php echo(xml_escape(serialize($_SESSION[$session_var]))); ?>" />
			<?php PrintTextBox('a_name', 'Organisation Name: ', $session_var); ?>
			<?php PrintTextArea('a_description', 'Description: ', $session_var); ?>
			<?php PrintTextBox('a_phone_number_address', 'Email Address: ', $session_var); ?>
			<?php PrintTextBox('a_website', 'Website: ', $session_var); ?>
		</fieldset>
		<fieldset>
			<input type="submit" name="r_submit_finish" value="Finish" class="button" />
			<input type="submit" name="r_submit_next" value="Next" class="button" />
			<input type="submit" name="r_submit_back" value="Back" class="button" />
		</fieldset>
	</form>
</div>
<?php
	}
?>

<?php
	if ($stage == 3)
	{
?>
<div class="BlueBox">
<h2>more details</h2>
	<form id="orgdetails" action="/wizard/organisation" method="post" class="form">
		<fieldset>
			<input type="hidden" name="r_stage" value="<?php echo($stage); ?>" />
			<input type="hidden" name="r_dump" value="<?php echo(xml_escape(serialize($_SESSION[$session_var]))); ?>" />
			<?php PrintTextArea('a_address', 'Address: ', $session_var); ?>
			<?php PrintTextBox('a_postcode', 'Postcode: ', $session_var); ?>
			<?php PrintTextBox('a_opening_times', 'Opening Times: ', $session_var); ?>
			<?php PrintTextBox('a_phone_internal', 'Internal Phone: ', $session_var); ?>
			<?php PrintTextBox('a_phone_external', 'External Phone: ', $session_var); ?>
			<?php PrintTextBox('a_fax', 'Fax Number: ', $session_var); ?>
		</fieldset>
		<fieldset>
			<input type="submit" name="r_submit_finish" value="Finish" class="button" />
			<input type="submit" name="r_submit_next" value="Next" class="button" />
			<input type="submit" name="r_submit_back" value="Back" class="button" />
		</fieldset>
	</form>
</div>
<?php
	}
?>

<?php
	if ($stage == 4)
	{
?>

<?php
	$CI = &get_instance();
	$CI->load->view('uploader/upload_single_photo', array('action_url' => '/wizard/organisation/upload/images') );
?>

<?php if (isset($_SESSION['org_wizard']['img'])) { ?>
<div class="GreyBox">
	<?php 	$count = 0;
		foreach ($_SESSION['org_wizard']['img'] as $img) { ?>
		<?php echo $this->image->getThumb($img, 'slideshow')?>
		<br />
		<?=anchor('wizard/organisation/photo/move/'.$img.'/up/', 'move up')?> |
		<?=anchor('wizard/organisation/photo/move/'.$img.'/down/', 'move down')?> |
		<a href="/wizard/organisation/photo/delete/<?php echo($img); ?>" onclick="return confirm('Are you sure you want to delete this photo?');">delete</a>
		<br />
	<?php } ?>
</div>
<?php } ?>
<div class="BlueBox">
	<form id="orgdetails" action="/wizard/organisation" method="post" class="form">
		<fieldset>
			<input type="hidden" name="r_stage" value="<?php echo($stage); ?>" />
			<input type="hidden" name="r_dump" value="<?php echo(xml_escape(serialize($_SESSION[$session_var]))); ?>" />
		</fieldset>
		<fieldset>
			<input type="submit" name="r_submit_finish" value="Finish" class="button" />
			<input type="submit" name="r_submit_next" value="Next" class="button" />
			<input type="submit" name="r_submit_back" value="Back" class="button" />
		</fieldset>
	</form>
</div>
<?php
	}
?>

<?php
	if ($stage == 5)
	{
?>
<div class="BlueBox">
	<h2>jump to location</h2>
	<p>To move the map to closer to the location you want, select one of these options:</p>

	<div style="width: 43%; float: left; margin-right: 0px;">
		<b>On Campus:</b><br />
		<a href='javascript:maps["googlemaps"].setCenter(new GLatLng(53.94704447468437, -1.0529279708862305));'>
		<div style="float: left">
		<ul>
			<li>Central Hall</li>
		</ul>
		</div>
		<div style="float: right">
		<img  src="/images/prototype/directory/central_hall.gif" title="Central Hall" alt="" />
		</div>
		</a>
	</div>
	<div style="width: 50%; float: right; margin-left: 0px;">
		<b>Off Campus:</b><br />
		<p>Enter a place name or postcode:</p>
		<fieldset>
		<input style="width: 55%" type="text" id="MapSearch"/>
		<input style="width: 35%; float: right" type="submit" value="Search" onclick="maps_search(document.getElementById('MapSearch').value, 'googlemaps', document.getElementById('MapSearchResults'));"/>
		</fieldset>
		<ul id="MapSearchResults">
	</div>
</ul>

</table>
</div>
<div class="BlueBox">
	<h2>location map</h2>
	<form id="orgdetails" action="/wizard/organisation" method="post" class="form">
		<fieldset>
			<input type="hidden" name="r_stage" value="<?php echo($stage); ?>" />
			<input type="hidden" name="r_dump" value="<?php echo(xml_escape(serialize($_SESSION[$session_var]))); ?>" />
		</fieldset>
		<div id="googlemaps" style="height: 300px"></div>
		<noscript>
			<p>Javascript support is required for map editing</p>
		</noscript>
		<fieldset>
			<input type="submit" name="r_submit_finish" value="Finish" class="button" />
			<input type="submit" name="r_submit_finish" value="Next" class="button" />
			<input type="submit" name="r_submit_back" value="Back" class="button" />
		</fieldset>
	</form>
</div>
<?php
	}
?>

<?php
	if ($stage == 6)
	{
?>
<div class="BlueBox">
<h2>about you</h2>
	<form id="orgdetails" action="/wizard/organisation" method="post" class="form">
		<fieldset>
			<input type="hidden" name="r_stage" value="<?php echo($stage); ?>" />
			<input type="hidden" name="r_dump" value="<?php echo(xml_escape(serialize($_SESSION[$session_var]))); ?>" />
			<label for="username">Name: </label><span id="username"><?php echo(xml_escape($username)); ?></span>
	<?php if ($is_connected != 'No' && !$office) {
				 PrintTextBox('a_user_phone_number', 'Phone Number: ', $session_var);
				 PrintTextBox('a_user_position', 'Position In Organisation: ', $session_var);
			 ?>
		</fieldset>
			 <p>As you are connected with this organisation, we will automatically request VIP status for you. We will contact you to confirm your position in the organisation in the near future.</p>
	<?php } elseif ($office) { ?>
			 <p>This organisation is being submitted from an office member.</p>
		</fieldset>
	<?php } else { ?>
			 <p>Should we accept your organisation, you will be automatically subscribed to it.</p>
		</fieldset>
	<?php }?>
		<fieldset>
			<input type="submit" name="r_submit_finish" value="Finish" class="button" />
			<input type="submit" name="r_submit_next" value="Next" class="button" disabled="disabled" />
			<input type="submit" name="r_submit_back" value="Back" class="button" />
		</fieldset>
	</form>
</div>
<?php
	}
?>

</div>
