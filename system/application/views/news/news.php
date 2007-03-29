<?php
function printarticlelink($article, $blurb = false) {
	$align = $blurb ? 'Right' : 'Left';
	echo('	<div class="Entry">'."\n");
	echo('		<a href="/news/'.$article['article_type'].'/'.$article['id'].'">'."\n");
	echo('			<img class="'.$align.'" src="'.$article['photo_url'].'" alt="'.$article['photo_title'].'" title="'.$article['photo_title'].'" />'."\n");
	echo('		</a>'."\n");
	echo('		<h3>'."\n");
	echo('			<a href="/news/'.$article['article_type'].'/'.$article['id'].'">'."\n");
	echo('				'.$article['heading']."\n");
	echo('			</a>'."\n");
	echo('		</h3>'."\n");
	foreach($article['authors'] as $reporter)
		echo('		<a href="/contact">'.$reporter['name'].'</a>'."\n");
	echo('		<div class="Date">'.$article['date'].'</div>'."\n");
	if (array_key_exists('blurb', $article) && $article['blurb'] != '') {
		echo('		'.anchor('/news/'.$article['article_type'].'/'.$article['id'], 'Read more...')."\n");
		echo('		<p>'.$article['blurb'].'</p>'."\n");
	}
	echo('	</div>'."\n");
}
?>

<div id="RightColumn">
	<h2 class="first"><?php echo($latest_heading); ?></h2>
<?php
foreach($news_previews as $preview)
	printarticlelink($preview, true);
?>
	
	<h2><?php echo($other_heading); ?></h2>
<?php
foreach ($news_others as $other)
	printarticlelink($other, false);

if (count($main_article['related_articles']) > 0)
	echo('	<h2>'.$related_heading.'</h2>'."\n");

foreach ($main_article['related_articles'] as $related)
	printarticlelink($related, false);

foreach($main_article['fact_boxes'] as $fact_box) {
	echo('	<h2>'.$fact_box['title'].'</h2>'."\n");
	echo('	<div class="Entry">'."\n");
	echo('		'.$fact_box['wikitext']."\n");
	echo('	</div>'."\n");
}
?>
</div>

<div id="MainColumn">
	<div class="BlueBox">
		<h2><?php echo $main_article['heading']; ?></h2>
		<?php if ($main_article['subheading'] != '') { ?>
			<h3><?php echo $main_article['subheading']; ?></h3>
		<?php } ?>
		<?php if ($main_article['subtext'] != '') { ?>
		<div><?php echo $main_article['subtext']; ?></div>
		<?php } ?>

		<?php $this->byline->load(); ?>

        <?php echo $main_article['text']; ?>

		<?php if (isset($office_preview)) { ?>
			<p class='form'><button class="button" onclick="window.location='/office/news/<?php echo $main_article['id']; ?>';">GO BACK TO NEWS OFFICE</button></p>
		<?php } ?>
	</div>
	<?php if (count($main_article['links']) > 0) { ?>
	<div class="BlueBox">
		<h2><?php echo $links_heading; ?></h2>
		<ul>
		<?php foreach ($main_article['links'] as $link) {
			echo '<li><a href=\'' . $link['url'] . '\' target=\'_blank\'>' . $link['name'] . '</a></li>';
		} ?>
		</ul>
	</div>
	<?php } ?>
</div>
