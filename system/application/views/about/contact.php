<div id="RightColumn">
	<h2 class="first">Need Help?</h2>
	<div class="Entry">
		Don't know who to talk to? Below is a short decsription of who to talk to.
	</div>
	<?php
		foreach($contacts as $contact){
			echo('<h2>'.xml_escape($contact['name']).'</h2>'."\n");
			echo('<div class="Entry">'."\n");
			echo('	<p>'.xml_escape($contact['description']).'</p>'."\n");
			echo('</div>'."\n");
		}
	?>
</div>

<div id="MainColumn">
	<div class="BlueBox">
		<h2>Contact Us</h2>
		<form class="form" action="/about/sendmail" method="post">
			<fieldset>
				<label for="recipient"> Contact: </label>
				<select id="recipient" name="recipient">
				<?php
					//Note plural becomes singular
					foreach ($contacts as $contact){
						echo('<option value="'.$contact['id'].'">'.xml_escape($contact['name']).'</option>'."\n");
					}
				?>
				</select>
				<label for="contact_email">Your Email: </label>
				<input id="contact_email" name="contact_email" style="width:60%" />
				<label for="contact_subject">Subject: </label>
				<input id="contact_subject" name="contact_subject" style="width:60%" />
				<textarea name="contact_message" cols="40" rows="14" style="width:95%" ></textarea>
			</fieldset>
			<fieldset>
				<input type="submit" class="button" value="Send" id="contact_send" />
			</fieldset>
		</form>
	</div>
</div>
