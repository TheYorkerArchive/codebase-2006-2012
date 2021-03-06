<?php
// Must echo through PHP in case short tags is turned on
echo('<?xml version="1.0" encoding="UTF-8"?>');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="<?php echo(xml_escape($description)); ?>" />
	<meta name="keywords" content="<?php echo(xml_escape($keywords)); ?>" />

	<title>The Yorker - <?php
		// FIXME: backwards compatibility, remove when all pages are shown with titles
		if(isset($head_title)) {
			echo(xml_escape($head_title));
		} else {
			echo('no pagename');
		}
	?></title>

	<link rel="shortcut icon" href="/images/yorker.ico" />
	<link rel="alternate" type="application/rss+xml" title="The Yorker - Campus News" href="/news/rss" />


	<link href="/stylesheets/new.css" rel="stylesheet" type="text/css" />
	<!--[if lte IE 6]><link href="/stylesheets/new-ie6fix.css" rel="stylesheet" type="text/css" /><![endif]-->

	<?php
	if (isset($extra_css)) {
		echo('<link href="'.$extra_css.'" rel="stylesheet" type="text/css" />'."\n");
	}
	?>

	<?php
		// Get common javascript
		include('top_script.php');
	?>

</head>

<body onload="onLoadHandler()" onunload="onUnloadHandler()">
	<div id="Header">
		<div id="HeaderMenu">
			<span style="color: #999999; font-size: 0.9em">
			<?php
			// Set by GenerateToplinks in mainframe_helper
			if (isset($toplinks)) {
				foreach ($toplinks as $link) {
					if (is_string($link)) {
						echo('<span style="color: #000000;">'.xml_escape($link).'</span> | ');
					} elseif (is_array($link)) {
						echo('<a href="'.xml_escape($link[1]).'">'.xml_escape($link[0]).'</a> | ');
					}
				}
			}
			?>
			<a href="<?php echo($this->config->item('static_web_address')); ?>/pdf/advertisewithus.pdf">advertise with us</a> |
			<a href="/about/">about us</a> |
			<a href="/pages/join_our_team/">join us</a> |
			<a href="/faq/">FAQs</a>
			</span>
		</div>

		<div id="TopBanner">
			<h1 id="TopBannerName">
				<a href="/"><img src="/images/prototype/header/header_Layer-1.gif" width="300" height="108" alt="The Yorker"/></a>
			</h1>
			<div id="TopBannerText">
				<p>
					<?php echo(xml_escape($vipinfo['name'])); ?><br />
					<?php echo(xml_escape($vipinfo['organisation'])); ?>
				</p>
			</div>
		</div>
	</div>

	<div id="Page">
		<div id="NavigationColumn">
			<form id="searchbox_003080001858553066416:dyddjbcpdlc" action="http://www.google.com/search">
				<fieldset>
					<input type="hidden" name="cx" value="003080001858553066416:dyddjbcpdlc" />
					<input type="hidden" name="cof" value="FORID:0" />
				</fieldset>
				<fieldset id="SearchBox">
					<input name="q" type="text" size="40" value="Search for..." onfocus="inputFocus(this);" onblur="inputBlur(this);" />
				</fieldset>
			</form>
			<div id="NavigationMenu">
				<!-- Nasty "first" class used as IE6 doesn't have :first-child -->
				<ul class="first">
					<li class="first"><a href="<?php echo(site_url('viparea')); ?>">VIP Area Home</a></li>
				</ul>
				<ul>
					<li class="first"><a href="<?php echo(vip_url('directory/information')); ?>">Directory Entry</a></li>
					<li><a href="<?php echo(vip_url('calendar')); ?>">Manage Events</a></li>
					<li><a href="<?php echo(vip_url('notices')); ?>">Manage Notices</a></li>
					<li><a href="<?php echo(vip_url('members')); ?>">Manage Members</a></li>
				</ul>
				<ul>
					<li class="first"><a href="<?php echo(vip_url('account')); ?>">Settings</a></li>
					<li><a href="<?php echo(vip_url('contactpr')); ?>">Contact PR Rep</a></li>
				</ul>
				<?php
				if (isset($extra_menu_buttons) && !empty($extra_menu_buttons)) {
					echo('<ul>');
					foreach ($extra_menu_buttons as $key => $button) {
						echo('<li'.(!$key ? ' class="first"':'').'>');
						if (is_string($button)) {
							echo(xml_escape($button));
						} else {
							echo('<a href="'.xml_escape($button[1]).'">'.xml_escape($button[0]).'</a>');
						}
						echo('</li>');
					}
					echo('</ul>');
				}
				?>
			</div>
		</div>

		<div id="MainBodyPane">
			<h1 id="PageTitle">
				<?php
				if(isset($body_title)) {
					echo(xml_escape($body_title)."\n");
				} else {
					echo('no pagename'."\n");
				}
				if(isset($paged_edit_url) && NULL !== $paged_edit_url) {
					echo('<a href="'.$paged_edit_url.'">[edit]</a>');
				}
				?>
			</h1>

<!-- BEGIN generated content -->
<?php
	// Navigation bar
	if (isset($content['navbars']) && is_array($content['navbars'])) {
		foreach ($content['navbars'] as $navbar) {
			$navbar->Load();
		}
	} elseif (isset($content['navbar'])) {
		$content['navbar']->Load();
	}

	// Display each message
	foreach ($messages as $message) {
		// Display the message
		$message->Load();
	}

	// Display the main content
	$content[0]->Load();
?>
<!-- END generated content -->

		</div>
	</div>


	<?php
		// Get common footer
		include('footer.php');
	?>

</body>
</html>
