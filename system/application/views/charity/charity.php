<div id="RightColumn">
	<h2 class="first"><?php echo $sections['sidebar_about']['title']; ?></h2>
	<div class="Entry">
		<h3><?php echo $sections['sidebar_about']['subtitle']; ?></h3>
		<?php echo $sections['charity']['target_text']; ?>
	</div>
	<h2><?php echo $sections['sidebar_help']['title']; ?></h2>
	<div class="Entry">
		<?php echo $sections['sidebar_help']['text']; ?>
	</div>
	
	<h2><?php echo $sections['sidebar_related']['title']; ?></h2><!--Next 2 sections basically the same with different data and links-->
	<div class="Entry">
		<?php
	        foreach ($sections['article']['related_articles'] as $related_articles)
		{
			echo '<b><a href="http://www.google.com/">'.$related_articles['heading'].'</a></b><br />';
		};
		?>
	</div>

	<h2><?php echo $sections['sidebar_external']['title']; ?></h2>
	<div class="Entry">
		<?php
	        foreach ($sections['article']['links'] as $links)
		{
			echo '<b><a href="'.$links['url'].'">'.$links['name'].'</a></b><br />';
		};
		?>
	</div>
</div>

<div id="MainColumn">

	<img src="/images/prototype/homepage/rowing.jpg" alt="rowing" width="420" height="93" />

	<div class="BlueBox">
		<h2><?php echo $sections['article']['heading']; ?></h2>
		<span class="black"><?php echo $sections['article']['text']; ?></span>
	</div>

	<div class="BlueBox">
		<h2><?php echo $sections['funding']['title']; ?></h2>
		<div class="Entry">
			<?php echo $sections['funding']['text']; ?>
		</div>
	</div>

	<?php
	if (isset($sections['progress_reports']['entries']))
	{
		echo '<div class="BlueBox">';
		echo '<h2>'.$sections['progress_reports']['title'].'</h2>';
		foreach ($sections['progress_reports']['entries'] as $pr_entry)
		{
			echo '<h5><span class="orange">'.$pr_entry['date'].'</span></h5>';
			echo $pr_entry['text'].'<br /><br />';
		}
		echo '</div>';
	}
	?>

</div>

<?php
/*
echo '<pre>';
echo print_r($data);
echo '</pre>';
*/
?>
