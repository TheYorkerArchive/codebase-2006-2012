<div id="RightColumn">
	<h2 class="first">Page Information</h2>
	<div class="Entry">
		<?php echo($page_information); ?>
	</div>
</div>
<div id="MainColumn">
	<div class="BlueBox">
		<h2>current leagues</h2>
		<p>
		<?php
		echo form_open('office/reviews/delleague');
		echo form_hidden('organisation_name',$organisation_name);
		echo form_hidden('context_type',$context_type);
		?>
		<select size="5" name="league_id" style="float: none; width: 350px;"><?php
		foreach ($existing_leagues as $existing_league)
		{
			echo '<option value="'.$existing_league['id'].'">'.$existing_league['section_name'].' -> '.$existing_league['name'].' ( '.$existing_league['rank'].' / '.$existing_league['size'].' )</option>';
		}
		?></select><br />
	 	<input type="submit" value="Delete league" style="float: none;">
		</form>
		</p>
	</div>
	
	<div class="BlueBox">
		<h2>potential leagues</h2>
		<p>
		<?php
		echo form_open('office/reviews/addleague');
		echo form_hidden('organisation_name',$organisation_name);
		echo form_hidden('context_type',$context_type);
		?>
		<select size="8" name="league_id" style="float: none;  width: 350px;"><?php
		foreach ($new_leagues as $new_league)
		{
			echo '<option value="'.$new_league['id'].'">'.$new_league['section_name'].' -> '.$new_league['name'].'</option>';
		}
		?></select><br />
	 	<input type="submit" value="Add new league" style="float: none;">
		</form>
		</p>
	</div>
	<a href="/office/reviewlist/<?php echo $context_type; ?>">Back to the attention list</a>
</div>

