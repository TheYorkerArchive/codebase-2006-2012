<div class="BlueBox">
<?php
if ($business_card['image_id'] != NULL) {
	echo("\t".'<div style="float:right;">'."\n");
	echo($this->image->getImage($business_card['image_id'], 'userimage'));
	echo("\t".'</div>'."\n");
}
?>
	<div style="font-size: large;  color: #2DC6D7; ">
	<?php
	if (	isset($editmode) && $editmode &&
			isset($business_card['user_id']) && NULL != $business_card['user_id']) {
		echo('<a href="'.vip_url('members/info/'.$business_card['user_id']).'">'.xml_escape($business_card['name']).'</a>');
	} else {
		echo(xml_escape($business_card['name']));
	}
	echo('<br />'.xml_escape($business_card['title']));
	?>
	</div>
	<p style='font-size:small;'><?php echo(xml_escape($business_card['blurb'])); ?></p>
	<p>
		<?php
		if (!empty($business_card['course'])) {
			?>
			<img alt="Course" name="Course" src="/images/icons/script.png" /> <?php echo(xml_escape($business_card['course'])); ?><br />
			<?php
		}
		if (!empty($business_card['email'])) {
			if ($this->user_auth->isLoggedIn) {
			?>
			<img alt="Email" name="Email" src="/images/icons/email.png" /> <a href='mailto:<?php echo(xml_escape($business_card['email'])); ?>'><?php echo(xml_escape($business_card['email'])); ?></a><br />
			<?php
			} else {
			?>
			<img alt="Email" name="Email" src="/images/icons/email.png" /> Hidden. Please log in.<br />
			<?php
			}
		}
		if (!empty($business_card['postal_address'])) {
			?>
			<img alt="Address" name="Address" src="/images/icons/map.png" /> <?php echo(xml_escape($business_card['postal_address'])); ?><br />
			<?php
		}
		if(!empty($business_card['phone_internal']) or !empty($business_card['phone_external']) or !empty($business_card['phone_mobile'])){
		?>
			<img alt="Phone" name="Phone" src="/images/icons/phone.png" />
			<?php
			if (!empty($business_card['phone_internal'])) {
				echo(xml_escape($business_card['phone_internal']).', ');
			}
			if (!empty($business_card['phone_external'])) {
				echo(xml_escape($business_card['phone_external']).', ');
			}
			if (!empty($business_card['phone_mobile'])) {
				echo(xml_escape($business_card['phone_mobile']).', ');
			}
			echo('<br />');
		}
		?>
		<?php
		if (isset($editmode) && $editmode) {
		?>
			<form method='post' action='<?php echo vip_url('directory/contacts/deletecard/'.$business_card['id']); ?>' class='form'>
			<fieldset>
				<?php
				if($business_card['approved']){
					echo "<small>This card is live.</small>";
				}else{
					if (PermissionsSubset('editor', GetUserLevel())){
						?>
						<input name='member_approve_button' type='button' onClick="parent.location='<?php echo vip_url('directory/contacts/approvecard/'.$business_card['id']); ?>'" value='Approve' class='button' />
						<?php
					} else {
						echo "<small>Waiting approval.</small>";
					}
				}
				if (PermissionsSubset('pr', GetUserLevel()) || PermissionsSubset('vip', GetUserLevel())){ ?>
					<input name='member_delete_button' type='submit' onClick="return confirm('Are you sure you want to delete <?php echo(xml_escape($business_card['name'])); ?>&#039;s contact card?');" value='Delete' class='button' />
					<?php }
				if(!isset($url)){
					$url = vip_url('directory/cards/'.$business_card['id'].'/edit');
				}
				?>
				<input name='member_edit_button' type='button' onClick="parent.location=<?php echo(xml_escape(js_literalise($url))); ?>" value='Edit' class='button' />
			</fieldset>
			</form>
		<?php
		}
		?>
	</p>
</div>
