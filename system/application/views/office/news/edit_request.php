<script type='text/javascript' src='/javascript/calendar_select.js'></script>
<script type='text/javascript' src='/javascript/calendar_select-en.js'></script>
<script type='text/javascript' src='/javascript/calendar_select-setup.js'></script>

<div id="RightColumn">
	<h2 class="first"><?php echo(xml_escape($heading)); ?></h2>
	<div class="Entry">
		<?php echo($intro); ?>
	</div>
	<h2>Unassigned Requests</h2>
	<div class="Entry">
		<?php echo($unassigned_requests); ?>
	</div>
</div>
<div id="MainColumn">
	<form id='new_request' action='<?php echo($this->uri->uri_string()); ?>' method='post' class='form'>
		<div class='BlueBox'>
			<fieldset>
				<label for='r_title'>Title:</label>
				<?php if ($edit_enable) { ?>
					<input type='text' name='r_title' id='r_title' value='<?php echo(xml_escape($this->validation->r_title)); ?>' size='30' />
				<?php } else { ?>
					<div id='r_title' style='float: left; margin: 5px 10px;'><?php echo(xml_escape($this->validation->r_title)); ?></div>
				<?php } ?>
				<br />
				<label for='r_brief'>Brief:</label>
				<?php if ($edit_enable) { ?>
					<textarea name='r_brief' id='r_brief' cols='25' rows='5'><?php echo(xml_escape($this->validation->r_brief)); ?></textarea>
				<?php } else { ?>
					<div id='r_brief' style='float: left; margin: 5px 10px;'><?php echo(xml_escape($this->validation->r_brief)); ?></div>
				<?php } ?>
				<br />
				<label for='r_suggest'>Suggested by:</label>
				<div id='r_suggest' style='float: left; margin: 5px 10px;'>
				<?php if ($status == 'suggestion') {
					echo(xml_escape($article['username']));
				} elseif ($status == 'request') {
					echo(xml_escape($article['suggestionusername']));
				} ?>
				</div>
			    <br />
				<?php if ($status == 'request') { ?>
					<label for='r_editor'>Editor:</label>
					<div id='r_editor' style='float: left; margin: 5px 10px;'><?php echo(xml_escape($article['editorname'])); ?></div>
				    <br />
				<?php } ?>
				<?php if ($status == 'suggestion') { ?>
					<label for='r_created'>Created:</label>
					<div id='r_created' style='float: left; margin: 5px 10px;'><?php echo(date('D jS F Y @ H:i:s',$article['created'])); ?></div>
				    <br />
				<?php } ?>
				<?php if (($user_level == 'editor') || ($status == 'request')) { ?>
					<label for='deadline_trigger'>Deadline:</label>
					<div id='r_deadline_show' style='float: left; margin: 5px 10px;'><?php if ($this->validation->r_deadline != '') { echo(date('D j M, Y @ H:i',$this->validation->r_deadline)); } else { echo('None'); } ?></div>
					<?php if ($edit_enable) { ?>
						<input type='hidden' name='r_deadline' id='r_deadline' value='<?php echo(xml_escape($this->validation->r_deadline)); ?>' />
						<br />
						<button id='deadline_trigger' style='margin: 0 0 5px 125px;'>Select</button>
					<?php } ?>
					<br />
				<?php } ?>
			 	<label for='r_box'>Section:</label>
				<?php if ($edit_enable) { ?>
					<select name='r_box' id='r_box' size='1'>
					<?php foreach ($boxes as $box) {
						echo('<option value=\'' . $box['code'] . '\'');
						if ($box['name'] == $this->validation->r_box) {
							echo(' selected=\'selected\'');
						}
						echo('>' . xml_escape($box['name']) . '</option>');
					} ?>
					</select>
				<?php } else { ?>
					<div id='r_box' style='float: left; margin: 5px 10px;'><?php echo(xml_escape($article['box_name'])); ?></div>
				<?php } ?>
		  		<br />
				<?php if (($user_level == 'editor') || ($status == 'request')) { ?>
					<label for='r_reporter'>Reporter(s):</label>
					<?php if ($user_level == 'editor') { ?>
						<select name='r_reporter[]' id='r_reporter' size='6' multiple='multiple'>
						<?php
							// Create lookup array
							$reporters_array = array();
							foreach ($assigned_reporters as $reporter) {
								array_push($reporters_array, $reporter['id']);
							}
							foreach ($reporters as $reporter) {
								echo('<option value=\'' . $reporter['id'] . '\'');
								if (($status == 'request') && (array_search($reporter['id'], $reporters_array) !== FALSE)) {
									echo('selected=\'selected\'');
								}
								echo('>' . xml_escape($reporter['firstname'] . ' ' . $reporter['surname']) . '</option>');
							} ?>
						</select>
						<i>Hold down Ctrl to select more than one.</i>
					<?php } else { ?>
						<div id='r_reporter' style='float: left; margin: 5px 10px;'>
						<?php foreach ($assigned_reporters as $reporter) {
							echo(xml_escape($reporter['name']) . ' (' . xml_escape($reporter['status']) . ') <br />');
						} ?>
						</div>
					<?php } ?>
			 		<br />
				<?php } ?>
			</fieldset>
		</div>
	<div>
		<?php
		if ($edit_enable) {
			if ($user_level == 'editor') {
				if ($status == 'suggestion') { ?>
				 	<input type='submit' name='decline' id='submit2' value='Reject' class='button' />
				 	<input type='submit' name='accept' id='submit' value='Accept' class='button' />
				<?php } else { ?>
				 	<input type='submit' name='edit_request' id='submit' value='Edit Details' class='button' />
   				<?php } ?>
			<?php } else { ?>
			 	<input type='submit' name='changes' id='submit' value='Save Changes' class='button' />
			<?php } ?>
		<?php } elseif (($status == 'request') && ($isUserAssigned)) { ?>
				 	<input type='submit' name='decline' id='submit2' value='Decline Request' class='button' />
				 	<input type='submit' name='accept' id='submit' value='Accept Request' class='button' />
		<?php } ?>
	</div>
	</form>
	<?php if (($edit_enable) && ($user_level == 'editor')) { ?>
		<script type='text/javascript'>
		// <![CDATA[
		Calendar.setup(
			{
				inputField	: 'r_deadline',
				ifFormat	: '%s',
				displayArea	: 'r_deadline_show',
				daFormat	: '%a %e %b, %Y @ %H:%M',
				button		: 'deadline_trigger',
				singleClick	: false,
				firstDay	: 1,
				<?php if ($status == 'request') { ?>
				date		: <?php echo(js_literalise($this->validation->r_deadline)); ?>,
				<?php } ?>
				weekNumbers	: false,
				range		: [<?php echo((date('Y') . ',' . (date('Y') + 1))); ?>],
				showsTime	: true,
				timeFormat	: '24'
			}
		);
		// ]]>
		</script>
	<?php } ?>
</div>