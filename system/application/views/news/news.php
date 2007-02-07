	<div class='RightToolbar'>
		<h4><?php echo $latest_heading; ?></h4>

		<?php if ($article_type != 'lifestyle') { ?>

			<?php foreach ($news_previews as $preview) { ?>
			<div class='NewsPreview'>
				<a href='/news/<?php echo $preview['id']; ?>'><img src='<?php echo $preview['image']; ?>' alt='<?php echo $preview['image_description']; ?>' title='<?php echo $preview['image_description']; ?>' /></a>
				<h3><?php echo anchor('news/'.$preview['id'], $preview['heading']); ?></h3>
				<?php foreach ($preview['authors'] as $reporter) { ?>
				<p class='Writer'><a href='/directory/view/<?php echo $reporter['id']; ?>'><?php echo $reporter['name']; ?></a></p>
				<?php } ?>
				<p class='Date'><?php echo $preview['date']; ?></p>
				<p class='More'><?php echo anchor('news/'.$preview['id'], 'Read more...'); ?></p>
			    <p><?php echo $preview['blurb']; ?></p>
				<br style='clear: both;' />
			</div>
			<?php } ?>

		<?php } else { ?>

			<div class='LifestylePuffer' style='background-color: #a38b69;'>
				<a href='/news/article/1'>
				<img src='/images/prototype/news/puffer2.jpg' alt='Cooking' title='Cooking' />
		 	    <h3>Cooking</h3>
				<p>This week an awesome recipe for a chocolate cake</p>
				</a>
				<div style='clear:both'></div>
			</div>
			<div class='LifestylePuffer' style='background-color: #000;'>
				<a href='/news/article/1'>
				<img src='/images/prototype/news/puffer3.jpg' alt='Workout' title='Workout' />
		 	    <h3>Workout</h3>
				<p>This week we look at using weights and other heavy stuff</p>
				</a>
				<div style='clear:both'></div>
			</div>
			<div class='LifestylePuffer' style='background-color: #ef7f94;'>
				<a href='/news/article/1'>
				<img src='/images/prototype/news/puffer4.jpg' alt='Love' title='Love' />
		 	    <h3>Romance</h3>
				<p>This week we review what is the best valentine day's present</p>
				</a>
				<div style='clear:both'></div>
			</div>
			<div class='LifestylePuffer' style='background-color: #000;'>
				<a href='/news/article/1'>
				<img src='/images/prototype/news/puffer3.jpg' alt='Workout' title='Workout' />
		 	    <h3>Workout</h3>
				<p>This week we look at using weights and other heavy stuff</p>
				</a>
				<div style='clear:both'></div>
			</div>
			<div class='LifestylePuffer' style='background-color: #a38b69;'>
				<a href='/news/article/1'>
				<img src='/images/prototype/news/puffer2.jpg' alt='Cooking' title='Cooking' />
		 	    <h3>Cooking</h3>
				<p>This week an awesome recipe for a chocolate cake</p>
				</a>
				<div style='clear:both'></div>
			</div>

		<?php } ?>
		<h4><?php echo $other_heading; ?></h4>
	   	<?php foreach ($news_others as $other) { ?>
		<div class='NewsOther'>
			<a href='/news/<?php echo $other['id']; ?>'><img src='<?php echo $other['image']; ?>' alt='<?php echo $other['image_description']; ?>' title='<?php echo $other['image_description']; ?>' /></a>
		    <p class='Headline'><a href='/news/<?php echo $other['id']; ?>'><?php echo $other['heading']; ?></a></p>
			<?php foreach ($other['authors'] as $reporter) { ?>
			<p class='Writer'><a href='/directory/view/<?php echo $reporter['id']; ?>'><?php echo $reporter['name']; ?></a></p>
			<?php } ?>
			<p class='Date'><?php echo $other['date']; ?></p>
		</div>
   		<?php } ?>
		<div class='clear'></div>
		<?php if (count($main_article['related_articles']) > 0) { ?>
		<h4><?php echo $related_heading; ?></h4>
			<?php foreach ($main_article['related_articles'] as $related) { ?>
			<div class='NewsOther'>
				<a href='/news/<?php echo $related['id']; ?>'><img src='<?php echo $related['image']; ?>' alt='<?php echo $related['image_description']; ?>' title='<?php echo $related['image_description']; ?>' /></a>
			    <p class='Headline'><a href='/news/<?php echo $related['id']; ?>'><?php echo $related['heading']; ?></a></p>
				<?php foreach ($related['authors'] as $reporter) { ?>
				<p class='Writer'><a href='/directory/view/<?php echo $reporter['id']; ?>'><?php echo $reporter['name']; ?></a></p>
				<?php } ?>
				<p class='Date'><?php echo $related['date']; ?></p>
			</div>
			<?php } ?>
		<?php } ?>
		<?php if ($article_type == 'uninews') { ?>
		<div style='clear: both; text-align: center;'>
			<a href='/news/rss/'><img src='/images/prototype/news/feed.gif' alt='<?php echo $rss_feed_title; ?>' title='<?php echo $rss_feed_title; ?>' /> <?php echo $rss_feed_title; ?></a>
		</div>
		<?php } ?>
		<?php foreach ($main_article['fact_boxes'] as $fact_box) { ?>
		<div style='padding-bottom: 150px;'>&nbsp;</div>
		<div class='orange_box'>
			<h2><?php echo $fact_box['title']; ?></h2>
			<?php echo $fact_box['wikitext']; ?>
		</div>
		<?php } ?>
	</div>
	<div class='blue_box'>
		<?php foreach ($main_article['authors'] as $reporter) { ?>
		<img src='<?php echo $main_article['writerimg']; ?>' alt='<?php echo $reporter['name']; ?>' title='<?php echo $reporter['name']; ?>' style='float: right;' />
		<?php } ?>
		<h2 style='margin-bottom: 5px;'><?php echo $byline_heading; ?></h2>
		<?php foreach ($main_article['authors'] as $reporter) { ?>
		<span style='font-size: medium;'><b><?php echo $reporter['name']; ?></b></span><br />
		<?php } ?>
		<?php echo $main_article['date']; ?><br />
		<a href='/archive'><span style='color: #ff6a00;'><?php echo $byline_more; ?></span></a>
	</div>
	<div class='grey_box'>
		<div class='ArticleColumn'>
			<h1><?php echo $main_article['heading']; ?></h1>
			<?php if ($main_article['subheading'] != '') { ?>
			<h2><?php echo $main_article['subheading']; ?></h2>
			<?php } ?>
	        <p><?php echo $main_article['text']; ?></p>
			<br style='clear: both;' />
		</div>
	</div>
	<?php if (count($main_article['links']) > 0) { ?>
		<div class='blue_box'>
			<h2><?php echo $links_heading; ?></h2>
			<ul>
			<?php foreach ($main_article['links'] as $link) {
				echo '<li><a href=\'' . $link['url'] . '\'>' . $link['name'] . '</a></li>';
			} ?>
			</ul>
		</div>
	<?php } ?>