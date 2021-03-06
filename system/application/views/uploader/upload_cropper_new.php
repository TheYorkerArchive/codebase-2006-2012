<div class="BlueBox" id="loadingWrap">
	<br />
	<p><b>Loading...</b></p>
	<br />
</div>
<script type="text/javascript" charset="utf-8">
	function submitPicture()
	{
		if(currentSelectIndex!=0) {
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="Saving...";
			xajax_process_form_data(xajax.getFormValues("pictureCrop"));
		} else {
			alert('Please select a thumbnail.');
		}
		return false;
	}

	/**
	 * A little manager that allows us to swap the image dynamically
	 *
	 */
	var CropImageManager = {
		/**
		 * Holds the current Cropper.Img object
		 * @var obj
		 */
		curCrop: null,

		/**
		 * Initialises the cropImageManager
		 *
		 * @access public
		 * @return void
		 */
		init: function() {
			this.setImage('/images/null-blank.jpg', 380, 235, 1);
		},

		/**
		 * Handles the changing of the select to change the image, the option value
		 * is a pipe seperated list of imgSrc|width|height
		 *
		 * @access public
		 * @param obj event
		 * @return void
		 */
		onChange: function( e ) {
			var vals = $F( Event.element( e ) ).split('|');

			//If the thumb has not been saved, flag an error
			if ( thumbSecondSaveList.grep(currentThumb).length > 0 && currentSelectIndex != 0) {
				if(!confirm('You have not saved changes to this thumbnail. Are you sure you want to switch to a different thumbnail?')) {
					document.getElementById('imageChoice').selectedIndex = currentSelectIndex;
					return false;
				}
			}

			document.getElementById('thumbWrapMaster').style.display = 'block';

			//IE 6 Requires the div to be displayed for setImage is run
			if(document.getElementById('imageChoice').value != 'choose') {
				document.getElementById('uploadedWrapMaster').style.display = 'block';
			}

			this.setImage( vals[0], vals[1], vals[2], vals[3] );

			if(document.getElementById('imageChoice').value == 'choose') {
				document.getElementById('uploadedWrapMaster').style.display = 'none';
			}

			currentThumb = vals[8];
			currentSelectIndex = document.getElementById('imageChoice').selectedIndex;

			//Put the current thumb into a list of unsaved thumbs
			if ( thumbSecondSaveList.grep(currentThumb).length == 0 && currentSelectIndex != 0) thumbSecondSaveList.push(currentThumb);
		},

		/**
		 * Sets the image within the element & attaches/resets the image cropper
		 *
		 * @access private
		 * @param string Source path of new image
		 * @param int Width of new image in pixels
		 * @param int Height of new image in pixels
		 * @return void
		 */
		setImage: function( imgSrc, w, h, imgTypeNew ) {
			$( 'uploadedImage' ).src = imgSrc;
			$( 'uploadedImage' ).width = w;
			$( 'uploadedImage' ).height = h;

<?php		foreach ($ThumbDetails->result() as $Single) : ?>
			if (imgTypeNew == <?php echo $Single->image_type_id?>) {
				if (!$( 'previewArea-<?php echo $Single->image_type_id?>' ).empty()) $( 'previewArea-<?php echo $Single->image_type_id?>' ).removeChild($( 'previewArea-<?php echo $Single->image_type_id?>' ).firstChild);
				if (this.curCrop != null) this.curCrop.remove();
				this.curCrop = new Cropper.ImgWithPreview( 'uploadedImage', {
					maxWidth: w,
					maxHeight: h,
					ratioDim: { x: <?php echo $Single->image_type_width?>, y: <?php echo $Single->image_type_height?> },
					displayOnInit: true,
					onEndCrop: onEndCrop,
					previewWrap: 'previewArea-<?php echo $Single->image_type_id?>'} );
				this.curCrop.reset();
			}
<?php		endforeach; ?>
		}
	};

	var currentThumb = null;
	var currentSelectIndex = 0;

	var thumbList = new Array();
	var thumbSecondSaveList = new Array();
	var thumbNameMap = new Array();

	<?php
	foreach($data as $d) {
		foreach($d as $singleThumb) {
			?>
			thumbNameMap['<?php echo $singleThumb['thumb_id']?>'] = '<?php echo str_replace("'", "\\'", $singleThumb['title'])?>';
			<?php
			if (!(isset($noforcesave) && $noforcesave)) {
			?>
			thumbList.push('<?php echo $singleThumb['thumb_id']?>');
			<?php
			}
		}
	}
	?>

	function registerImageSave(thumb_id) {
		thumbList = thumbList.without(thumb_id);
		thumbSecondSaveList = thumbSecondSaveList.without(thumb_id);
	}

	window.onbeforeunload = function () {
		if(thumbList.length != 0) {
			var msg = 'You have not saved versions of the following thumbnails:\n';
			thumbList.each(function(item) {
			  msg += ' ' + thumbNameMap[item] + '\n';
			});
			return msg;
		} else if(thumbSecondSaveList.length != 0 && currentSelectIndex != 0) {
			var msg = 'You were editing the following thumbnails, but didn\'t save them:\n';
			thumbSecondSaveList.each(function(item) {
			  msg += ' ' + thumbNameMap[item] + '\n';
			});
			return msg;
		}
	}

	function canReturn() {
		if(thumbList.length != 0) {
			var msg = 'You have not saved versions of the following thumbnails:\n';
			thumbList.each(function(item) {
			  msg += ' ' + thumbNameMap[item] + '\n';
			});
			msg += '\nYou must save all thumbnail sizes to continue.';
			alert(msg);
			return false;
		} else if(thumbSecondSaveList.length != 0 && currentSelectIndex != 0) {
			var msg = 'You were editing the following thumbnails, but didn\'t save them:\n';
			thumbSecondSaveList.each(function(item) {
			  msg += ' ' + thumbNameMap[item] + '\n';
			});
			msg += '\nAre you sure you want to continue?';
			return confirm(msg);
		} else {
			return true;
		}
	}

	// setup the callback function
	function onEndCrop( coords, dimensions ) {
		$( 'x1' ).value = coords.x1;
		$( 'y1' ).value = coords.y1;
		$( 'x2' ).value = coords.x2;
		$( 'y2' ).value = coords.y2;
		$( 'width' ).value = dimensions.width;
		$( 'height' ).value = dimensions.height;
	}

	onLoadFunctions.push (function() {
		CropImageManager.init();
		Event.observe( $('imageChoice'), 'change', CropImageManager.onChange.bindAsEventListener( CropImageManager ), false );

		document.getElementById('uploadedWrapMaster').style.display = 'none';
		document.getElementById('blanket').style.display = 'none';

		document.getElementById('loadingWrap').style.display = 'none';
		document.getElementById('dropdownWrap').style.display = 'block';
	});

</script>

<form id="pictureCrop" action="javascript:void(null);" onsubmit="return submitPicture();">

<div id="blanket" style="clear: both; position: relative; width: 480px; height: 4080px;">
	<div style="clear: both; position: relative; width: 400px; height: 4000px;">
	<?php
	//Ensure images are cached by loading them at the bottom of the webpage
	//This div also hides the thumbWrapMaster by pushing it off the bottom of the screen,
	// so that it is still accessable from the DOM in IE6 during page load.
	foreach($data as $d) {
		foreach($d as $singleThumb) {
			echo '<img src="'.$singleThumb['cache_img'].'" height="2" width="2" alt="" />';
		}
	}
	?>
	</div>
</div>

<div id="thumbWrapMaster" class="BlueBox" style="display: block; display: none;">
<h2>thumbnail scratchpad</h2>
<p>This box displays the thumbnails as they are manipulated with the tool below. Clicking the save button under the photo stores the selected thumbnail to our servers, so don't worry if this scratchpad is reused when there are multiple photos.</p>
<table border="0" width="100%">
<tr>
<?php
foreach ($ThumbDetails->result() as $Single) {
	echo '<th>'.$Single->image_type_name.'</th>';
}
?>
</tr>
<tr>
<?php
foreach ($ThumbDetails->result() as $Single) {
	echo '<td><div id="previewArea-'.$Single->image_type_id.'"></div></td>';
}
?>
</tr>
</table>
</div>

<div id="uploadedWrapMaster" class="BlueBox" style="display: block;">
	<h2>original photograph</h2>
	<div id="uploadedWrap">
		<img src="/images/null-blank.jpg" alt="Uploaded image" id="uploadedImage" />
	</div>
	<input type="hidden" name="x1" id="x1" />
	<input type="hidden" name="y1" id="y1" />
	<input type="hidden" name="x2" id="x2" />
	<input type="hidden" name="y2" id="y2" />
	<input type="hidden" name="width" id="width" />
	<input type="hidden" name="height" id="height" />
</div>

<div class="BlueBox" style="">
	<div style="float: right; width: 55%;">
	<ol>
	<li>Select a thumbnail from the drop down box</li>
	<li>Use the tool to crop your photo appropriately</li>
	<li>Press save once you are happy with the crop</li>
	<li>Repeat for every thumbnail in the list</li>
	</ol>
	</div>
	<h2>thumbnail selector</h2>
	<div id="dropdownWrap" style="display: none;">
	<p>
		<select name="imageChoice" id="imageChoice">
			<option value="choose">Please Choose...</option>
			<?php
			foreach($data as $d) {
				foreach($d as $singleThumb) {
					echo '<option value="'.$singleThumb['string'].'">'.$singleThumb['title'].'</option>';
				}
			}
			?>
		</select>
		<input id="submitButton" type="submit" value="Save"/>
	</p>
	</div>
</div>

</form>
<div class="BlueBox">
	<h2>finished</h2>
	<p>If you have thumbnailed all photos, click the button below:</p>
	<p><input type="button" onclick="if (canReturn()) window.location='<?php echo $returnPath?>';" value="Finish" /></p>
</div>