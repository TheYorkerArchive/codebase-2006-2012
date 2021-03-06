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
		<h2>all organisations summary</h2>
		<div id="ArticleBox">
			<table>
				<thead>
					<tr>
						<th>
							Organisation
							<a href="/office/pr/summaryall/org/asc"><img src="/images/icons/bullet_arrow_down.png" /></a>
							<a href="/office/pr/summaryall/org/desc"><img src="/images/icons/bullet_arrow_up.png" /></a>
						<th>
							Priority
							<a href="/office/pr/summaryall/pri/asc"><img src="/images/icons/bullet_arrow_down.png" /></a>
							<a href="/office/pr/summaryall/pri/desc"><img src="/images/icons/bullet_arrow_up.png" /></a>
						</th>
						<th>
							Rep
							<a href="/office/pr/summaryall/rep/asc"><img src="/images/icons/bullet_arrow_down.png" /></a>
							<a href="/office/pr/summaryall/rep/desc"><img src="/images/icons/bullet_arrow_up.png" /></a>
						</th>
						<th>
							Rating
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$alternate = 1;
					foreach($orgs as $org)
					{
						echo('				<tr class="tr'.$alternate.'">'."\n");
						echo('					<td>'."\n");
						echo('						<a href="/office/pr/summaryorg/'.$org['org_dir_entry_name'].'">'.xml_escape($org['org_name']).'</a>'."\n");
						echo('					</td>'."\n");
						echo('					<td>'."\n");
						echo('						'.$org['org_priority']."\n");
						echo('					</td>'."\n");
						echo('					<td>'."\n");
						echo('						'.xml_escape($org['user_firstname'].' '.$org['user_surname'])."\n");
						echo('					</td>'."\n");
						echo('					<td>'."\n");
						echo('						100%'."\n");
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
