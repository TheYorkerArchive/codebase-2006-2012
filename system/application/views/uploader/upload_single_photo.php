<div class="blue_box">
	<form action="<?=site_url($this->uri->uri_string())?>" method="post" enctype="multipart/form-data">
		Generic helping text should go here, each file is limited to 2Mb.
		<fieldset>
			Image Filename: <input type="file" name="userfile1" size="30" />
			<input type="hidden" name="destination" id="destination" value="1" />
			<input type="submit" value="upload" />
		</fieldset>
	</form>
</div>