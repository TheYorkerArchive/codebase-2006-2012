<div id="RightColumn">
	<h2 class="first">What's this?</h2>
	<div class="Entry">
		<?php echo($main_text); ?>
	</div>
</div>
<?php
	$checked_attr = 'checked="checked"';
?>
<div id="MainColumn">
	<div class="BlueBox">
		<h2>account administration</h2>
		<p>
			<?php echo($account_maintenance_text); ?>
		</p>
		<form action='<?php echo vip_url('account/maintainer'); ?>' class='form' method='POST'>
			<fieldset>
				<label for='maintainer_type'>Maintenance by :</label><br />
				<input type='radio' name='maintainer_type' value='yorker'
					<?php if($maintainer['maintained'] == false) { echo($checked_attr); } ?>
					onclick="document.getElementById('nonstudent_details').style.display = 'none';" /> The Yorker<br />
				
				<?php if($is_student){ ?>
				<input type='radio' name='maintainer_type' value='student'
					<?php if($maintainer['is_user']) { echo($checked_attr); } ?>
					onclick="document.getElementById('nonstudent_details').style.display = 'none';" /> Me (<?php echo(xml_escape($user_fullname)); ?>)<br />
				<?php } else { ?>
				<input type='radio' name='maintainer_type' value='nonstudent'
					<?php if($maintainer['maintained'] && ($maintainer['student'] == false)) { echo($checked_attr); } ?>
					onclick="document.getElementById('nonstudent_details').style.display = 'block';" /> Non student member<br />
				<?php } ?>
				
				<div id='nonstudent_details'
					<?php if(!$maintainer['maintained'] || ($maintainer['student'] != false)) { echo('style="display: none;"'); } ?> >
					<label for='maintainer_name'>Admin's Name:</label>
					<input type='text' name='maintainer_name' style='width: 150px;'
						value='<?php echo(xml_escape($maintainer['maintainer_name'])); ?>'/>
					<br />
					<label for='maintainer_email'>Admin's Email:</label>
					<input type='text' name='maintainer_email' style='width: 220px;'
						value='<?php echo(xml_escape($maintainer['maintainer_email'])); ?>'/>
					<br />
				</div>
			</fieldset>
			<fieldset>
				<label for='maintainer_button'></label>
				<input type='submit' name='maintainer_button' value='Update' class='button' />
			</fieldset>
		</form>
		<p>
			<a href='<?php echo(vip_url('account/update')); ?>'>Back to my account settings.</a>
		</p>
	</div>
</div>