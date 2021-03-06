<div id="RightColumn">
	<h2 class="first">About</h2>
	<div class="Entry">
		<p>
		<?php echo(xml_escape($review_blurb)); ?>
		</p>
	</div>

	<h2>Details</h2>
	<div class="Entry">
		<p>
			<?php if(strlen(trim($address_main)) > 0) { ?><b>Address:</b>  <?php echo(nl2br(xml_escape($address_main))); ?><br /><?php } ?>
			<?php if(strlen(trim($website)) > 0) { ?><b>Website:</b>  <a href="<?php echo(xml_escape($website)); ?>" target="_blank">Click Here</a><br /><?php } ?>
			<?php if(strlen(trim($email)) > 0) { ?><b>Email:</b>  <?php if($this->user_auth->isLoggedIn) { ?> <a href="mailto:<?php echo(xml_escape($email)); ?>">E-mail Us</a> <?php } else { ?>E-mail hidden. Please log in.<?php } ?><br /><?php } ?>
			<?php if(strlen(trim($telephone)) > 0) { ?><b>Telephone:</b>  <?php echo(xml_escape($telephone)); ?><br /><?php } ?>
			<?php if(strlen(trim($opening_times)) > 0) { ?><b>Opening Times:</b>  <?php echo(xml_escape($opening_times)); ?><br /><?php } ?>
			<?php if(strlen(trim($serving_times)) > 0) { ?> <b>Serving Times:</b> <?php echo(xml_escape($serving_times)); ?><br /><?php } ?>
		</p>
	</div>

	<?php if(strlen(trim($yorker_recommendation)) > 0 || strlen(trim($average_price)) > 0) { ?>
	<h2>Tips</h2>
	<div class="Entry">
		<?php if(strlen(trim($yorker_recommendation)) > 0) {
			?><b>We Recommend:</b>  <?php echo(xml_escape($yorker_recommendation)); ?><br /><?php
		} ?>
<?php 
	switch ($content_type) {
		case 'food': 
			$avg_price_text = 'Meal ';
			break;
		case 'drink':
			$avg_price_text = 'Drink ';
			break;
	}
	if (strlen(trim($average_price)) > 0) {
		echo('			<b>Average '.xml_escape($avg_price_text).'Price:</b>'.xml_escape($average_price).'<br />'."\n");
	}
?>
	</div>
	<?php } ?>
</div>

<div id="MainColumn">
	<div class="BlueBox">
		<?php if(count($slideshow) > 0) { ?>
		<div style="float:right;margin-top:0;line-height:95%;">
			<div id="SlideShow" class="entry">
				<img src="<?php echo(xml_escape($slideshow[0]['url'])); ?>" id="SlideShowImage" alt="Slideshow" title="Slideshow" />
			</div>

			<script type="text/javascript">
		<?php foreach ($slideshow as $slide_photo) { ?>
			Slideshow.add('<?php echo(xml_escape($slide_photo['url'])); ?>');
		<?php } ?>
			Slideshow.load();
			</script>
		</div>
		<?php } ?>

		<?php $this->feedback_article_heading = $review_title; ?>
		<h2><?php echo(xml_escape($review_title)); ?></h2>

	<?php if ($review_quote != '') { ?>
		<p>
		<img src="/images/prototype/news/quote_open.png" />
		<?php echo(xml_escape($review_quote)); ?>
		<img src="/images/prototype/news/quote_close.png" />
		</p>
	<?php } ?>
		<h3>Rating</h3>
		<div>

<?php
echo('			');
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
	</div>

	<div class="BlueBox">
		<h2>reviews</h2>
<?php
foreach($article as $a) {
	$this->byline->AddReporter($a['authors']);
	$this->byline->SetDate($a['date']);
	$this->byline->load();
	$this->byline->Reset();

	echo($a['text']);
}
?>
	</div>
	<?php
		// Show comments
		if (isset($comments)) {
			$comments->Load();
		}
	?>

</div>
