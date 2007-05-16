	<script type='text/javascript' src='/javascript/calendar_select.js'></script>
	<script type='text/javascript' src='/javascript/calendar_select-en.js'></script>
	<script type='text/javascript' src='/javascript/calendar_select-setup.js'></script>

	<div class='RightToolbar'>
		<h4><?php echo $heading; ?></h4>
		<?php echo $intro; ?>
	</div>

	<form name='new_request' id='new_request' action='/office/news/create' method='post' class='form'>
		<div class='blue_box'>
			<fieldset>
				<label for='r_title'>Title:</label>
				<input type='text' name='r_title' id='r_title' value='' size='30' />
				<br />
				<label for='r_brief'>Brief:</label>
				<textarea name='r_brief' id='r_brief' cols='25' rows='5'></textarea>
			    <br />
				<label for='deadline_trigger'>Deadline:</label>
				<div id='r_deadline_show' style='float: left; margin: 5px 10px;'>None</div>
				<input type='hidden' name='r_deadline' id='r_deadline' value='0' />
				<br />
				<button id='deadline_trigger' style='margin: 0 0 5px 125px;'>Select</button>
				<br />
			 	<label for='r_box'>Box:</label>
				<select name='r_box' id='r_box' size='1'>
				<?php foreach ($boxes as $box) { ?>
		  			<option value='<?php echo $box['code']; ?>'><?php echo $box['name']; ?></option>
				<?php } ?>
				</select>
		  		<br />
			</fieldset>
		</div>
	<div style='width: 422px;'>
	 	<input type='submit' name='submit' id='submit' value='Create Article' class='button' />
	</div>
	</form>

	<script type='text/javascript'>
	Calendar.setup(
		{
			inputField	: 'r_deadline',
			ifFormat	: '%s',
			displayArea	: 'r_deadline_show',
			daFormat	: '%a %e %b, %Y @ %H:%M',
			button		: 'deadline_trigger',
			singleClick	: false,
			firstDay	: 1,
			weekNumbers	: false,
			range		: [<?php echo (date('Y') . ',' . (date('Y') + 1)); ?>],
			showsTime	: true,
			timeFormat	: '24'
		}
	);
	</script>