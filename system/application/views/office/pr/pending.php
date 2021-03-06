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
		<h2>pending</h2>
		<p>
			This table contains a list of all organisations which have been accepted and are waiting for the editor rep request to be accepted. Click an organisation name for more information and to accept if necessary.
		</p>
		<div id="ArticleBox">
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>PR Rep</th>
					</tr>
				</thead>
				<tbody>
<?php
		$alternate = 1;
		foreach($pending_orgs as $org)
		{
			echo('				<tr class="tr'.$alternate.'">'."\n");
			echo('					<td>'."\n");
			echo('						<a href="/office/pr/info/'.$org['org_dir_entry_name'].'">'.xml_escape($org['org_name']).'</a>'."\n");
			echo('					</td>'."\n");
			echo('					<td>'."\n");
			echo('						'.xml_escape($org['user_firstname'].' '.$org['user_surname'])."\n");
			echo('					</td>'."\n");
			echo('				</tr>'."\n");
			$alternate == 1 ? $alternate = 2 : $alternate = 1;
		}
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
