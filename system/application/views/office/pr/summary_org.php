<div id="RightColumn">
	<h2 class="first">Quick Links</h2>
	<div class="Entry">
		<ul>
			<li><a href="/wizard/organisation/">Organisations wizard</a></li>
		</ul>
	</div>
</div>
<div id="MainColumn">
	<div class="BlueBox">
		<h2>organisation summary</h2>
		<div class="Entry">
		<?php 
		echo('		Name: '.xml_escape($organisation['info']['name'])."\n");
		echo('		<br />'."\n");
		echo('		Rep: '.xml_escape($organisation['rep']['firstname'].' '.$organisation['rep']['surname'])."\n");
		echo('		<br />'."\n");
		echo('		Organisation Rating: ');
		if ($organisation['score']['score_current'] >= $organisation['score']['score_possible'])
		{
			echo($organisation['score']['score_current'].' / '.$organisation['score']['score_possible']."\n");
		}
		else
		{
			echo('<span class="orange">'.$organisation['score']['score_current'].' / '.$organisation['score']['score_possible'].'</span>'."\n");
		}
		echo('		<br />'."\n");
		echo('		PR Info: <a href="/office/pr/info/'.$organisation['info']['dir_entry_name'].'">Click Here</a>'."\n");
		echo('		<br />'."\n");
		?>
		</div>
		<br />
		<?php
		if ($user['officetype'] != 'Low')
		{
		?>
		<div class="Entry">
			To set the new priority for this organisation select an option from the drop down list.
		<?php
		echo('		<form class="form" action="/office/pr/modify" method="post">'."\n");
		echo('			<fieldset>'."\n");
		echo('				<input type="hidden" name="r_redirecturl" value="'.$_SERVER['REQUEST_URI'].'" />'."\n");
		echo('				<input type="hidden" name="r_direntryname" value="'.$organisation['info']['dir_entry_name'].'" />'."\n");
		echo('			</fieldset>'."\n");
		echo('			<fieldset>'."\n");
		echo('				<select name="a_priority">'."\n");
		echo('					<optgroup label="Set New Priority:">'."\n");
		for($i=1;$i<=5;$i++)
		{
			if ($organisation['info']['priority'] == $i)
				echo('						<option value="'.$i.'" selected="selected">to '.$i.'</option>'."\n");
			else
				echo('						<option value="'.$i.'">to '.$i.'</option>'."\n");
		}
		echo('					</optgroup>'."\n");
		echo('				</select>'."\n");
		echo('			</fieldset>'."\n");
		echo('			<fieldset>'."\n");
		echo('				<input type="submit" value="Set Priority" class="button" name="r_submit_priority" />'."\n");
		echo('			</fieldset>'."\n");
		echo('		</form>'."\n");
		?>
		</div>
		<?php
		}
		?>
		<br />
		<div id="ArticleBox">
			<table>
				<thead>
					<tr>
						<th>Info</th>
						<th>Rating<br />(Current/Expected)</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($table as $row_head)
				{
					echo('				<tr class="tr2">'."\n");
					echo('					<td>'."\n");
					echo('						'.xml_escape($row_head['head']['name'])."\n");
					echo('					</td>'."\n");
					echo('					<td>'."\n");
					echo('						&nbsp;'."\n");
					echo('					</td>'."\n");
					echo('				</tr>'."\n");
					if (isset($row_head['body']))
					{
						foreach ($row_head['body'] as $row)
						{
							echo('				<tr class="tr1">'."\n");
							echo('					<td>'."\n");
							echo('						&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.xml_escape($row['link']).'">'.xml_escape($row['name']).'</a>'."\n");
							echo('					</td>'."\n");
							echo('					<td>'."\n");
							if ($row['score_current'] >= $row['score_possible'])
							{
								echo('						'.$row['score_current'].' / '.$row['score_possible']."\n");
							}
							else
							{
								echo('						<span class="orange">'.$row['score_current'].' / '.$row['score_possible'].'</span>'."\n");
							}
							echo('					</td>'."\n");
							echo('				</tr>'."\n");
						}
					}
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>
