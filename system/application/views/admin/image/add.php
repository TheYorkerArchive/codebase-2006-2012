<?php if (isset($image_type_id)) {?>
<div class="BlueBox">
	<form action="<?php echo site_url($this->uri->uri_string()); ?>" method="post" enctype="multipart/form-data">
		<fieldset>
			<label for="image_type_name">Type Name</label>
			<input type="text" name="image_type_name" />
			<label for="image_type_codename">Type Codename</label>
			<input type="text" name="image_type_codename" />
			<label for="image_type_width">Width</label>
			<input type="text" name="image_type_width" />
			<label for="image_type_height">Height</label>
			<input type="text" name="image_type_height" />
			<label for="image_type_photo_thumbnail">Is a photo thumbnail</label>
			<input type="checkbox" name="image_type_photo_thumbnail" value="1" /><br />
			<input type="submit" value="Save"/>
		</fieldset>
	</form>
</div>
<?php }?>