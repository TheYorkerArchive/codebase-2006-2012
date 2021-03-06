<div id="RightColumn">
	<?php if (isset($main_text)) { ?>
		<h2 class="first">What's this?</h2>
		<div class="Entry">
			<?php echo $main_text; ?>
		</div>
	<?php } ?>
	<h2>Inline edit mode</h2>
	<div class="Entry">
		<?php echo($inline_edit_text); ?>
		<ul>
			<li><?php if ($this->pages_model->GetInlineEditMode()) {
				echo('<a href="'.site_url('admin/pages/inline/off').$this->uri->uri_string().'">Disable inline edit mode</a>');
			} else {
				echo('<a href="'.site_url('admin/pages/inline/on').$this->uri->uri_string().'">Enable inline edit mode</a>');
			} ?></li>
		</ul>
	</div>
</div>
<div id="MainColumn">
	<div class='BlueBox'>
		<h2>Custom pages</h2>

		<?php if ($permissions['custom_new']) { ?>
		<p><a href="/admin/pages/custom/new">Create a new custom page</a></p>
		<?php } ?>
		<p><?php
		foreach ($custom as $page) {
			$escaped_codename = xml_escape($page['codename']);
			echo('<a href="/pages/'.$escaped_codename.'">');
			echo($escaped_codename);
			echo('</a>');
			echo(' (');
			echo('<a href="/admin/pages/custom/edit/'.$escaped_codename.'">');
			echo('edit');
			echo('</a>');
			if ($permissions['custom_delete']) {
				echo ', <a href="/admin/pages/custom/delete/'.$escaped_codename.'">delete</a>';
			}
			echo ')';
			echo '<br />';
		}
		?></p>
	</div>

	<div class='BlueBox'>
		<h2>Pages</h2>
		<p><a href="/admin/pages/common">Common Properties</a></p>
		<?php if ($permissions['page_new']) { ?>
		<p><a href="/admin/pages/page/new">Create a new page</a></p>
		<?php } ?>

		<p><?php
		foreach ($pages as $page) {
			$escaped_codename = xml_escape($page['codename']);
			echo($escaped_codename);
			echo(' (');
			echo('<a href="/admin/pages/page/edit/'.$escaped_codename.'">');
			echo('edit');
			echo('</a>');
			if ($permissions['page_delete']) {
				echo(', <a href="/admin/pages/page/delete/'.$escaped_codename.'">delete</a>');
			}
			echo(')');
			echo('<br />');
		}
		?></p>
	</div>
</div>