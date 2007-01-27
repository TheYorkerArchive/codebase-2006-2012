<div id='custom_page' align='center'>
	<form name='custom_page_form' action='/admin/pages/custom/<?php echo $page['codename']; ?>/save' method='POST' class='form'>
		<fieldset>
			<label for='codename'>Codename:</label>
			<input name='codename' value="<?php echo $page['codename']; ?>">
			<br />
			<label for='title'>Title:</label>
			<input name='title' value="<?php echo $page['title']; ?>">
			<br />
			<label for='description'>Description</label>
			<input name='description' value="<?php echo $page['description']; ?>">
			<br />
			<label for='keywords'>Keywords</label>
			<input name='keywords' value="<?php echo $page['keywords']; ?>">
			<br />
			<label for='comments'>Comments</label>
			<input type='checkbox' name='comments' id="comments" value="<?php echo $page['comments']; ?>">
			<br />
			<label for='ratings'>Ratings</label>
			<input type='checkbox' name='ratings' id="ratings" value="<?php echo $page['ratings']; ?>">
			<br />
			
			<textarea name="main" cols="80" rows="10" id="main" style="width:100%" ><?php echo $page['main']; ?></textarea>
			
			<br />
			
			<label for='save_button'></label>
			<input type='submit' class='button' name='save_button' value='Save'>
			<br />
			<label for='preview_button'></label>
			<input type='submit' class='button' name='preview_button' value='Preview'>
		</fieldset>
	</form>
</div>