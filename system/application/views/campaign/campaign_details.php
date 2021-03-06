<div id="RightColumn">
	<h2 class="first"><?php echo(xml_escape($sidebar_vote['title'])); ?></h2>
	<div class="Entry">
<?php
if ($user == TRUE) {
	$name = xml_escape($user['firstname'].' '.$user['surname']);
	if ($user['vote_id'] == FALSE) {
		echo('		'.$sidebar_vote['newvote']);
?>
		<form id="voteform" action="/campaign/castvote" method="post">
			<fieldset>
				<input type="hidden" name="a_campaignid" value="<?php echo($parameters['campaign']); ?>" />
				<input type="hidden" name="r_redirecturl" value="<?php echo($_SERVER['REQUEST_URI']); ?>" />
			</fieldset>
			<fieldset>
<?php
	if ($preview_mode == TRUE)
		echo('				<input type="submit" value="Vote" class="button" name="r_castvote" disabled="disabled" />');
	else
		echo('				<input type="submit" value="Vote" class="button" name="r_castvote" />');
?>				
			</fieldset>
		</form>
<?php
	} elseif ($user['vote_id'] == $parameters['campaign']) {
		echo('		'.$sidebar_vote['withdrawvote']);
?>
		<form id="withdrawform" action="/campaign/withdrawvote" method="post">
			<fieldset>
				<input type="hidden" name="a_campaignid" value="<?php echo($parameters['campaign']); ?>" />
				<input type="hidden" name="r_redirecturl" value="<?php echo($_SERVER['REQUEST_URI']); ?>" />
				<input type="submit" value="Withdraw" class="button" name="r_withdrawvote" />
			</fieldset>
		</form>
<?php
	} else {
		echo('		'.$sidebar_vote['changevote']);
?>
		<form id="voteform" action="/campaign/castvote" method="POST" class="form">
			<fieldset>
				<input type="hidden" name="a_campaignid" value="<?php echo($parameters['campaign']); ?>" />
				<input type="hidden" name="r_redirecturl" value="<?php echo($_SERVER['REQUEST_URI']); ?>" />
				<input type="submit" value="Vote" class="button" name="r_changevote" />
			</fieldset>
		</form>
<?php
	}
}
else
	echo('		'.$sidebar_vote['not_logged_in']);
?>
	</div>
	
	<h2><?php echo(xml_escape($sidebar_more['title'])); ?></h2>
	<div class="Entry">
		<?php echo($sidebar_more['text']); ?>
	</div>

<?php
	if (count($article['related_articles']) > 0)
	{
		echo('	<h2>');
		echo(xml_escape($sidebar_related['title']));
		echo('</h2>'."\n");
		echo('	<div class="Entry">'."\n");
		echo('		<ul>'."\n");
		foreach ($article['related_articles'] as $related_articles)
		{
			echo('			<li><a href="#">'.xml_escape($related_articles['heading']).'</a></li>'."\n");
		}
		echo('		</ul>'."\n");
		echo('	</div>'."\n");
	}
?>

<?php
	if (count($article['links']) > 0)
	{
		echo('	<h2>');
		echo(xml_escape($sidebar_external['title']));
		echo('</h2>'."\n");
		echo('	<div class="Entry">'."\n");
		echo('		<ul>'."\n");
	        foreach ($article['links'] as $links) {
			echo('			');
			echo('<li><a href="'.xml_escape($links['url']).'" target="_blank">'.xml_escape($links['name']).'</a></li>'."\n");
		}
		echo('		</ul>'."\n");
		echo('	</div>'."\n");
	}
?>

	<h2><?php echo(xml_escape($sidebar_other_campaigns['title'])); ?></h2>
	<div class="Entry">
		<ul>
<?php
        foreach ($campaign_list as $key => $campaigns) {
		if ($key != $selected_campaign) {
			echo('			');
			echo('<li><a href="'.site_url('campaign/details/').'/'.$key.'">'.xml_escape($campaigns['name']).'</a></li>'."\n");
		}
	}
?>
		</ul>
	</div>
</div>

<div id="MainColumn">
	<div class="BlueBox">
		<h2><?php echo(xml_escape($article['heading'])); ?></h2>
		<?php echo($article['text']); ?>

	</div>

<?php
foreach ($article['fact_boxes'] as $fact_box) {
	echo('	<div class="BlueBox">');
	echo('		<h2>'.xml_escape($fact_box['title']).'</h2>');
	echo('		'.$fact_box['wikitext']);
	echo('	</div>');

	echo('	<div class="BlueBox">'."\n");
	echo('		<h2>'.xml_escape($comments['title']).'</h2>');
	echo('	</div>'."\n");
}
?>

</div>