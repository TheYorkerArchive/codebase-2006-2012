<div id="RightColumn">
	<h2 class="first">
		<?php echo(xml_escape($links_heading)); ?>
	</h2>
	<div class="Entry">
		<div class="Puffer">
			<a href="/reviews/food">
				<img src="/images/prototype/news/food_reviews.jpg" alt="Food Reviews" title="Food Reviews" />
			</a>
		</div>
		<?php $this->homepage_boxes->print_puffer_column($puffers); ?>
	</div>
</div>
<div id="MainColumn">
	<div id="HomeBanner">
		<?php
		$this->homepage_boxes->print_homepage_banner($banner);
		?>
	</div>
	<?php
	$this->homepage_boxes->print_box_with_picture_list($main_articles,$latest_heading,'news');
	if($show_featured_puffer) $this->homepage_boxes->print_specials_box($featured_puffer_title,$featured_puffer);
	if(!empty($lists_of_more_articles)) $this->homepage_boxes->print_box_of_category_lists($more_heading,$more_article_types,$lists_of_more_articles);
	?>
</div>
