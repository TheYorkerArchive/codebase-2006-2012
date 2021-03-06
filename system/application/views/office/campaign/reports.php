<div id="RightColumn">
	<h2 class="first">Page Information</h2>
	<div class="Entry">
		<?php echo($page_information); ?>
	</div>
</div>
<div id="MainColumn">
	<?php
		//main - request info
		echo('<div class="BlueBox">'."\n");
		echo('	<h2>progress reports</h2>'."\n");
		if (count($progressreports) == 0)
		{
			echo('<p>No Progress Reports Yet.</p>');
		}
		else
		{
			foreach($progressreports as $pr)
			{
				echo('	<hr />'."\n");
				echo('	<p>'.$pr['header']['publish_date']);
				if ($pr['header']['live_content'] != NULL)
					echo(' <span class="orange">(published)</span>');
				echo(' <a href="/office/campaign/editprogressreport/'.$parameters['campaign_id'].'/'.$pr['id'].'">[edit]</a>'."\n");
				echo('</p>'."\n");
				if ($pr['header']['live_content'] != NULL)
					echo('	'.word_limiter($pr['article']['text'], 50)."\n");
				else
					echo('<p>No Preview.</p>'."\n");
			}
		}
		echo('</div>'."\n");
		
		echo('<div class="BlueBox">'."\n");
		echo('	<h2>add new progress report</h2>'."\n");
		echo('	'.$progress_report_wikitext."\n");
		echo('	<form class="form" action="/office/campaign/articlemodify" method="post" >'."\n");
		echo('		<fieldset>'."\n");
		echo('			<input type="hidden" name="r_campaignid" value="'.$parameters['campaign_id'].'" />'."\n");
		echo('		</fieldset>'."\n");
		echo('		<fieldset>'."\n");
		echo('			<label for="a_date">Date:</label>'."\n");
		echo('			<input type="text" name="a_date" size="20" value="');
		echo(date('Y-m-d H:i:s', time()));
		echo('" /><br />'."\n");
		echo('		</fieldset>'."\n");
		echo('		<fieldset>'."\n");
		echo('			<input type="submit" value="Add" class="button" name="r_submit_pr_add" />'."\n");
		echo('		</fieldset>'."\n");
		echo('	</form>'."\n");
		echo('</div>'."\n");
	?>
	<a href="/office/campaign/">Back To Campaign Index</a>
</div>