<?php
/**
 * @file views/crosswords/index.php
 * @author James Hogan <james_hogan@theyorker.co.uk>
 * @param $Categories array of categories.
 * @param $Search array of categories.
 */
?>
<div class="BlueBox">
	<h2>welcome to the yorker crosswords</h2>

	<ul>
		<li>Handy <a href="<?php echo(site_url('crosswords/tips')); ?>">crossword tips</a> are published with each crossword.</li>
	</ul>

	<?php
	//$Search->Load();
	?>
</div>
<div class="HalfColumns">
<?php
	foreach ($Categories as $category) {
		if (count($category['latest'])+count($category['next']) == 0) {
			continue;
		}
		?><div class="Column"><?php
		?><div class="BlueBox"><?php
			if (count($category['next']) > 0) {
				$pub = new Academic_time($category['next'][0]['publication']);
				?><div class="crossword_note"><?php
				echo('next: '.$pub->Format('D').' week '.$pub->AcademicWeek().$pub->Format(' H:i'));
				?></div><?php
			}
			if (count($category['latest']) > 0) {
				?><div class="crossword_note"><?php
					?><a href="<?php echo(site_url('crosswords/'.$category['short_name'].'/archive')); ?>">archive</a><?php
				?></div><?php
			}
			?><h2><a href="<?php echo(site_url('crosswords/'.$category['short_name'])); ?>"><?php
				echo(xml_escape($category['name']));
			?></a></h2><?php

			foreach ($category['latest'] as $crossword) {
				$pub = new Academic_time($crossword['publication']);
				?><div class="crossword_box BlueBox"><?php
				?><div class="crossword_preview"><?php
					?><a href="<?php echo(site_url('crosswords/'.$crossword['id'])); ?>"><?php
						?><img alt="" src="<?php echo(site_url('crosswords/'.$crossword['id'].'/preview')); ?>" /><?php
					?></a><?php
				?></div><?php
				// Find if crossword is "new"
				$now = new Academic_time(time());
				$since_publication = Academic_time::Difference($pub, $now, array('days'));
				if (!$crossword['expired'] || $since_publication['days'] < 7)
				{
					?><div class="crossword_new">new!</div><?php
				}
				// Title and details
				?><div class="crossword_title"><?php
					?><a href="<?php echo(site_url('crosswords/'.$crossword['id'])); ?>"><?php
					echo($pub->Format('D ').$pub->AcademicTermNameUnique().' week '.$pub->AcademicWeek());
					?></a><?php
				?></div><?php
				if (false) {
					?><div class="crossword_note">not attempted</div><?php
				}
				if (count($crossword['author_fullnames']) > 0) {
					?><em>by <?php echo(xml_escape(join(', ', $crossword['author_fullnames']))); ?></em><?php
				}
				$max_winners = $crossword['winners'];
				if ($max_winners > 0) {
					$winners_so_far = (int)$crossword['winners_so_far'];
					if ($crossword['expired']) {
						$medals = ($winners_so_far != 1 ? 'medals' : 'medal');
						?><em><?php
							echo(($winners_so_far==0) ? 'no' : $winners_so_far);
							echo(" $medals awarded");
						?></em><?php
					}
					else {
						$medals = ($max_winners != 1 ? 'medals' : 'medal');
						?><em><?php
							echo("$winners_so_far of $max_winners $medals awarded");
						?></em><?php
					}
				}
				?></div><?php
			}
		?></div><?php
		?></div><?php
	}
?>
</div>
