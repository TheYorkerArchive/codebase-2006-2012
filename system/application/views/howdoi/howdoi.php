<div id="RightColumn">
	<h2 class="first"><?php echo($sidebar_ask['title']); ?></h2>
	<div class="Entry">
		<?php echo($sidebar_ask['text']); ?>
		<form action="/howdoi/ask" method="post" >
			<fieldset>
				<?php echo('<input type="hidden" name="r_redirecturl" value="'.$_SERVER['REQUEST_URI'].'" />'); ?>
				<textarea name="a_question" cols="24" rows="5">How Do I?</textarea>
				<input type="submit" class="button" value="Ask" name="r_submit_ask" />
			</fieldset>
		</form>
	</div>

	<h2><?php echo($sidebar_question_categories['title']); ?></h2>
	<div class="Entry">
		<ul>
<?php
foreach ($categories as $category) {
	echo('			<li><a href="/howdoi/'.$category['codename'].'">'.$category['name'].'</a></li>');
}
?>
		</ul>
	</div>
</div>

<div id="MainColumn">
	<div class="BlueBox">
	<h2><?php echo($section_howdoi['title']); ?></h2>
	<?php echo($section_howdoi['text']); ?>
	</div>

	<div class="BlueBox">
<?php
echo('		<h2>'.$question_categories['title'].'</h2>'."\n");
foreach ($categories as $key => $category) {
	//echo '<h5><a href="'.$category['codename'].'/">'.$category['name'].'</a><br /></h5>';
	echo('		<h3>'.$category['name'].'</h3>'."\n");
	if (count($categories[$key]['articles']) > 0) {
		echo('		<ul>'."\n");
		foreach ($categories[$key]['articles'] as $articles) {
			echo('			');
			echo('<li><a href="/howdoi/'.$category['codename'].'#q'.$articles['id'].'">'.$articles['heading'].'</a></li>'."\n");
		}
		echo('		</ul>'."\n");
	}
}
?>
	</div>
</div>
