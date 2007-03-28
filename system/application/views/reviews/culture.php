<div class='RightToolbar'>
	<h4>Leagues</h4>
	<div class='Entry'>
	<?php
//Display leagues - Not implemented correctly since all of these should be pictures
//And nothing to do with div classes
//Placed freeze on this temp

if (isset($league_data) == 1)
{
	foreach ($league_data as $league_entry)
	{
		if ($league_entry['league_image_id'] != 0) //Don't display if no image otherwise alt text floods out
		{
		echo
		"
		<div class='LifestylePuffer'>
		<a href='/reviews/leagues/".$league_entry['league_codename']."'>
		<img src='/images/images/".$league_entry['league_image_id'].".gif' alt='".$league_entry['league_name']."' />
		</a>
		</div>
		";
		}
	}
}
?>

	</div>
	<h4>College Leagues</h4>
	<div class='Entry'>
		The current standings for the college leagues.
		<ol>
			<li>Halifax
			<li>Derwent
			<li>Langwith
			<li>Alcuin
			<li>Vanbrough
		</ol>
		Click here to find out more
	</div>
	<h4>Bar Crawls</h4>
	<div class='Entry'>
		Planning a night out? Fancy a bar crawl? Or just wanna get HAMMERED and LAID?!<br /><br />
			<a href="/reviews/barcrawl">Bob Bastards Bar Craw</a><br />
			<a href="/reviews/barcrawl">Sids Death Line</a><br />
			<a href="/reviews/barcrawl">Garys Green Mile</a><br />
	</div>
</div>


<div class='grey_box'>
	<h2>browse by</h2>
	<span style="color:#000000;"><?php echo $main_blurb; ?></span><br /><br />
<?php
//As far as I can tell we are going to show the first 2 columns only on this page
//Hence a for loop is probaility not worth it...

echo '<div class="half_right">';

//Check that it exists before trying to display
if (isset($table_data['tag_group_names'][1]) && isset($table_data[$table_data['tag_group_names'][1]]))
{
	echo '<h3 style="display: inline;">';
	echo $table_data['tag_group_names'][1];
	echo '</h3><br />';

	foreach($table_data[$table_data['tag_group_names'][1]] as $tag)
	{
		echo anchor('reviews/table/culture/star/'.$table_data['tag_group_names'][1].'/'.$tag, $tag).'<br />';
	}

	//All types
	echo anchor('reviews/table/food/name','All types');

}

echo'</div>';

echo '<div class="half_left">';

//Check that it exists before trying to display
if (isset($table_data['tag_group_names'][0]) && isset($table_data[$table_data['tag_group_names'][0]]))
{
	echo '<h3 style="display: inline;">';
	echo $table_data['tag_group_names'][0];
	echo '</h3><br />';

	foreach($table_data[$table_data['tag_group_names'][0]] as $tag)
	{
		echo anchor('reviews/table/culture/star/'.$table_data['tag_group_names'][0].'/'.$tag, $tag).'<br />';
	}
}

//All types
echo anchor('reviews/table/culture/name','All types');

echo'</div>';

?>

<div class='blue_box'>
		<h2>featured article</h2>
		<?php
echo '<a href="'.$article_link.'">';
echo '<img style="float: right;" src="'.$article_photo.'" alt="'.$article_photo_alt_text.'" title="'.$article_photo_title.'" /></a>';
?>
		<h3><?php echo anchor($article_link, $article_title); ?></h3>
		<span style='font-size: medium;'><b><?php echo "<a href='".$article_author_link."'>".$article_author."</a>"; ?></b></span><br />
		<?php echo $article_date ?><br />
		<span style='color: #ff6a00;'><?php echo anchor($article_link, 'Read more...'); ?></span>
	        <p>
			<?php echo $article_content; ?>
		</p>
</div>