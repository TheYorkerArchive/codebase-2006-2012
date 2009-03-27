<?php
/**
 * @file views/crosswords/office/category_view.php
 * @param $Permissions array[string => bool]
 * @param $Category array of category data
 *  - name
 *  - short_name
 *  - default_width
 *  - default_height
 *  - default_layout_id
 *  - default_has_normal_clues
 *  - default_has_cryptic_clues
 * @param $Crosswords array of crossword objects
 * @param $PostAction
 */
?>

<div class="BlueBox">
	<ul>
<?php
	if ($Permissions['category_edit']) {
		?><li><a href="<?php echo(site_url('office/crosswords/cats/'.(int)$Category['id'].'/edit').'?ret='.xml_escape(urlencode($PostAction))); ?>">Edit this category</a></li><?php
	}
	if ($Permissions['categories_index']) {
		?><li><a href="<?php echo(site_url('office/crosswords/cats')); ?>">Return to crosswords categories</a></li><?php
	}
?>
	</ul>
</div>

<div class="BlueBox">
	<h2>Crosswords</h2>
	<ul>
<?php
	if ($Permissions['crossword_add']) {
		?>
		<form method="POST" action="<?php echo($PostAction); ?>" class="form">
			<fieldset>
				<input	name="xword_cat_view_add_crossword" class="button"
						type="submit" value="Add Crossword" />
			</fieldset>
		</form>
		<?php
	}
	?></ul><?php
	?><div class="crossword_items"><?php
	foreach ($Crosswords as $id => $crossword) {
		// Set up styles
		$classes = array('crossword_item');
		$classes[] = 'complete'.(10*((int)($crossword['completeness']/10)));
		if (null != $crossword['overdue'] && $crossword['overdue']) {
			$classes[] = 'overdue';
		}
		if (null != $crossword['published'] && $crossword['published']) {
			$classes[] = 'published';
		}
		if (null != $crossword['expired'] && $crossword['expired']) {
			$classes[] = 'expired';
		}
		if ((int)$crossword['winners_so_far'] >= (int)$crossword['winners']) {
			$classes[] = 'no_winners_left';
		}

		?><div class="<?php echo(join(' ',$classes)); ?>"><?php
			// Publishing date
			?><div class="publish_date"><?php
				if (null === $crossword['publication']) {
					?><a>schedule</a><?php
				}
				else {
					echo(date("DD MM YYYY", $crossword['publication']));
				}
			?></div><?php
			// Authors
			?><div class="authors"><?php
				echo(xml_escape(join(', ', $crossword['authors'])));
			?></div><?php
			// Progress bar
			?><div class="completeness"><?php
				?><div class="bar" style="width: <?php echo((int)$crossword['completeness'].'%'); ?>"><?php
					echo((int)$crossword['completeness']."%");
				?></div><?php
			?></div><?php
			// Links
			?><ul><?php
				?><li><a href="<?php echo(site_url('office/crosswords/crossword/'.(int)$crossword['id'])); ?>">view</a></li><?php
				?><li><a href="<?php echo(site_url('office/crosswords/crossword/'.(int)$crossword['id'].'/edit')); ?>">edit</a></li><?php
			?></ul><?php
		?></div><?php
	}
	?></div><?php
?>
</div>
