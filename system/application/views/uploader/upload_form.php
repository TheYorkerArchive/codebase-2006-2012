<div id="source" style="display:none">
	<p><input type="file" name="userfile" size="20" /></p>
</div>

<?=form_open_multipart('upload/do_upload'); ?>
Basic test script
<div>
	<input type="file" name="userfile1" size="20" />
</div>
<input type="hidden" id="destination" value="1" />

<input type="button" onClick="AddClones()" value="Another"/>
<input type="submit" value="upload" />