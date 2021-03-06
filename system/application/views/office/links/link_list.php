<div id="RightColumn">
	<h2 class="first">Page Information</h2>
	<div class="Entry">
		<?php echo($page_information); ?>
	</div>
	<h2>Actions</h2>
	<div class="Entry">
		<ul>
			<li><a href="/office/links/customlink">Nominate new link</a></li>
		</ul>
	</div>
</div>
<div id="MainColumn">
	<div class='BlueBox'>
		<h2>homepage links</h2>
		<table width="100%">
			<tr>
				<th>Official Links</th>
				<th>Nominations</th>
			</tr>
		</table>
		<div style="width: 45%; height: 300px; overflow: auto; float: right; margin-left: 2%;">
			<table width="98%">
				<?php foreach($nominatedlinks as $link) { ?>
				<tr>
					<td><a href="<?php echo(xml_escape($link['link_url'])); ?>" target="_new"><?php echo($this->image->getImage($link['link_image_id'], 'link', array('title' => $link['link_name'], 'alt' => $link['link_name']))); ?></a></td>
					<td><?php echo $link['link_name']; ?><br /><div style="overflow: hidden; font-size: x-small;"><?php echo(xml_escape($link['link_url'])); ?></div></td>
					<td>
						<a href="/office/links/promote/<?php echo($link['link_id']); ?>" title="Promote this link" onclick="return confirm('Are you sure you want to promote this link? This operation cannot be undun.');"><img src="/images/icons/tick.png" /></a><br />
						<a href="/office/links/reject/<?php echo($link['link_id']); ?>" title="Reject this link" onclick="return confirm('Are you sure you want to reject this link? This will cause the link to be removed from this list, and cannot be undun.');"><img src="/images/icons/cross.png" />
					</td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<div style="width: 45%; height: 300px; overflow: auto; float: right; margin-right: 2%;">
			<table width="98%">
				<?php foreach($officiallinks as $link) { ?>
					<tr>
						<td><a href="<?php echo(xml_escape($link['link_url'])); ?>" target="_new"><?php echo($this->image->getImage($link['link_image_id'], 'link', array('title' => $link['link_name'], 'alt' => $link['link_name']))); ?></a></td>
						<td><?php echo(xml_escape($link['link_name'])); ?><br /><div style="overflow: hidden; font-size: x-small;"><?php echo(xml_escape($link['link_url'])); ?></div></td>
						<td><a href="/office/links/edit/<?php echo(xml_escape($link['link_id'])); ?>" title="Edit this link">Edit</a></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</div>