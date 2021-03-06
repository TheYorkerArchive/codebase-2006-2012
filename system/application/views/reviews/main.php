<div id="RightColumn">
<?php
//If there are some leagues print em
if (!empty($league_data)){
	echo ('	<h2 class="first">'.xml_escape($leagues_header).'</h2>'."\n");
	foreach ($league_data as $league_entry) {
		echo ('	<div class="Puffer">'."\n");
		if($league_entry['has_image']){
			//There is a puffer image, so use it
			echo '		<a href="/reviews/leagues/'.$league_entry['league_codename'].'"><img src="'.$league_entry['image_path'].'" alt="'.xml_escape($league_entry['league_name']).'" title="'.xml_escape($league_entry['league_name']).'" /></a>';
		}
		else {
			//There is no puffer image, just put a text link
			echo('		<a href="/reviews/leagues/'.$league_entry['league_codename'].'">'.xml_escape($league_entry['league_name']).'</a><br />'."\n");
		}
		echo ('	</div>'."\n");
	}
}
?>
</div>
<div id="MainColumn">
	<div id="HomeBanner">
		<?php
		$this->homepage_boxes->print_homepage_banner($banner);
		?>
	</div>
	<div class="BlueBox">
		<h2><?php echo(xml_escape($page_header)); ?></h2>
		<?php echo(xml_escape($page_about)); ?>
		<form action="/reviews/table/<?php echo($content_type); ?>/star" method="post">
			<div style="float: left; width: 75%">
				<table>
					<tr>
						<td>
							<fieldset>
								Find based on:
								<select name="item_filter_by" onchange="updatesortby(this.selectedIndex)">
									<option value="any" selected="selected">See All</option>
									<?php
									foreach($table_data['tag_group_names'] as $tag) {
										echo('					');
										echo('<option value="'.xml_escape($tag).'"');
										if (!empty($item_filter_by) && $tag==$item_filter_by) {
											echo ' selected="selected"';
										}
										echo('>'.xml_escape($tag).'</option>'."\n");
									}
									?>
								</select>
							</fieldset>
						</td>
						<td>
							<fieldset>
								Only Show:
								<select name="where_equal_to">
									<option value="any" selected="selected">See All</option>
								</select>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>
			<div style="float: right; width: 25%">
				<fieldset>
					<br />
					<input type="submit" value="Find" style="align: right;" class="button" />
				</fieldset>
			</div>
		</form>
	</div>
	<script type="text/javascript">
	// <![CDATA[
		var filterlist=document.reviews.item_filter_by
		var sortbylist=document.reviews.where_equal_to
		/* The following sets the array which links each selection from the first form select with a series of selections
		 * into the second form select
		 * sortby[0] is See All.
		 * The first value is what the select option text is, the second is the value tag
		*/
		var sortby=new Array()
		sortby[0]=["See All|all"]
			<?php
	//Print out the tags for each tag_group
	//Foreach tag_group
	for ($tag_group_no = 0; $tag_group_no < count($table_data['tag_group_names']); $tag_group_no++) {
		echo('		sortby['.($tag_group_no+1).']=[');
		//Print each tag
		for ($tag_no = 0; $tag_no < count($table_data[$table_data['tag_group_names'][$tag_group_no]]); $tag_no++) {
			echo('"'.$table_data[$table_data['tag_group_names'][$tag_group_no]][$tag_no].'|'.$table_data[$table_data['tag_group_names'][$tag_group_no]][$tag_no].'", ');
		}
		echo("]\n");
	}
	?>
		function updatesortby(selectedsortby){
			sortbylist.options.length=0
			if (selectedsortby>=0){
			for (i=0; i<sortby[selectedsortby].length; i++)
			sortbylist.options[sortbylist.options.length]=new Option(sortby[selectedsortby][i].split("|")[0], sortby[selectedsortby][i].split("|")[1])
			}
		}
		updatesortby(filterlist.selectedIndex)
		for (index=0; index<=sortbylist.options.length;index++){
			if(sortbylist.options[index].value == "<?php if (!empty($where_equal_to)){echo $where_equal_to;}?>")
			{
			sortbylist.options[index].selected = true;
			}
		}
	// ]]>
	</script>
<?php if (!isset($main_review)) { ?>
		<div class="BlueBox">
		<h2 class="Headline">No reviews</h2>
		<div class="Date"><?php echo(date('l, jS F Y')); ?></div>
		<p>Sorry, there are currently no reviews available for this section. Please check back soon.</p>
		</div>
<?php } else { ?>
		<div class="BlueBox">
			
			<h2><?php echo(xml_escape($main_review_header)); ?></h2>
			<?php $this->feedback_article_heading = 'Main Review Page: '.xml_escape($main_review['organisation_name']); ?>
			<div style="float: right"><a href="<?php echo '/reviews/'.$main_review['content_type_codename'].'/'.$main_review['organisation_directory_entry_name']; ?>"><b>View Guide</b> <img src="/images/icons/book_go.png" /></a></div>
			<h2 class="Headline"><?php echo(xml_escape($main_review['organisation_name'])); ?></h2>
			<?php if(count($main_review['slideshow']) > 0) { ?>
			<div style="float:right;margin-top:0;line-height:95%;">
				<div id="SlideShow" class="entry">
					<img src="<?php echo(xml_escape($main_review['slideshow'][0]['url'])); ?>" id="SlideShowImage" alt="Slideshow" title="Slideshow" />
				</div>

				<script type="text/javascript">
				// <![CDATA[
					<?php foreach ($main_review['slideshow'] as $slide_photo) { ?>
					Slideshow.add(<?php echo(js_literalise($slide_photo['url'])); ?>);
					<?php } ?>
					Slideshow.load();
				// ]]>
				</script>
			</div>
			<?php } ?>
			<div class="Date"><?php echo($main_review['date']); ?></div>
			<div class="Author">
	<?php foreach($main_review['authors'] as $reporter) { ?>
				<a href="/contact"><?php echo(xml_escape($reporter['name'])); ?></a>
	<?php } ?>
			</div>

	<?php if ($main_review['quote'] != '') { ?>
			<div class="SubText">
				<img src="/images/prototype/news/quote_open.png" />
				<?php echo(xml_escape($main_review['quote'])); ?>
				<img src="/images/prototype/news/quote_close.png" />
			</div>
	<?php } ?>

			<h3>Rating</h3>
			<div>
			<?php
			$review_rating = $main_review['rating'];
			$star = 0;
			while ($star < floor($review_rating/2)) {
				echo('<img src="/images/prototype/reviews/star.png" alt="*" title="*" />');
				$star++;
			}
			if ($review_rating % 2 == 1) {
				echo('<img src="/images/prototype/reviews/halfstar.png" alt="-" title="-" />');
				$star++;
			}
			while ($star < 5) {
				echo('<img src="/images/prototype/reviews/emptystar.png" alt=" " title=" " />');
				$star++;
			}
			?>

			</div>

			<?php echo(xml_escape($main_review['text'])); ?>
		</div>

		<?php
		// Comments if they're included
		if (isset($comments) && NULL !== $comments) {
			$comments->Load();
		}
}
?>
</div>
