<div class="RightToolbar">
	<h4>Areas for Attention</h4>
	You have been requested to answer this question.
	<form class="form" action="/office/charity/#" method="post" >
		<fieldset>
			<input type="submit" value="Accept" class="button" name="r_submit_accept" />
			<input type="submit" value="Decline" class="button" name="r_submit_decline" />
		</fieldset>
	</form>
</div>

<div class="blue_box">
	<h2>charity info</h2>
	<b>Title:</b> blah<br />
	<b>Current Total:</b> �1,242<br />
	<b>Goal Total:</b> �15,000<br />
	<b>Progress To Goal:</b> 8.3%<br />
	<b>Goal Text:</b> Cool Runnings is a 1993 comedy film directed b<br />
	<a href="/office/charity/#">[Modify]</a>
</div>

<div class="grey_box">
	<h2>edit charity</h2>
	<form class="form" action="/charity/howdoi/#" method="post" >
		<fieldset>
			<label for="a_question">Heading:</label>
			<input type="text" name="a_question" value="why we want blah?" /><br />
			<label for="a_answer">Description:</label>
			<textarea name="a_answer" rows="5" cols="30" />Every time you click or view our ads it earns us money and we likes monies. It goes into our pocketsies very nicely. We likes pockets, theyre full of monies from you clicking.</textarea><br />
			<input type="submit" value="Save" class="button" name="r_submit_save" />
		</fieldset>
	</form>
</div>

<div class="blue_box">
	<h2>latest progress report</h2>
	<b>Date:</b> Saturday, 3rd February 2007<br />
	<b>Details:</b> This is a test for a random charity progress report.<br />
	<a href="/office/charity/#">[Modify]</a>
</div>

<div class="grey_box">
	<h2>old progress reports</h2>
	<b>02/02/2007</b>: Jeff ate a salad at some... <a href="/office/charity/#">[Modify]</a><br />
	<b>24/01/2007</b>: Our first protest began... <a href="/office/charity/#">[Modify]</a><br />
</div>

<div class="blue_box">
	<h2>options</h2>
	<form class="form" action="/office/charity/#" method="post" >
		<fieldset>
			<input type="submit" value="Publish" class="button" name="r_submit_publish" />
		</fieldset>
		<fieldset>
			<input type="submit" value="Unpublish" class="button" name="r_submit_unpublish" />
		</fieldset>
	</form>
</div>

<?php

echo '<pre>';
echo print_r($data);
echo '</pre>';

?>
