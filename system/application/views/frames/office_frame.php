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

	<title><?php
		// FIXME: backwards compatibility, remove when all pages are shown with titles
		if(isset($head_title)) {
			echo(xml_escape($head_title));
		} else {
			echo('no pagename');
		}
	?> - The Yorker</title>

	<link rel="shortcut icon" href="/images/favicon.png" />
	<link rel="alternate" type="application/rss+xml" title="The Yorker - News" href="/feeds/news" />

	<link href="/stylesheets/v2.css" rel="stylesheet" type="text/css" />
	<link href="/stylesheets/office.css" rel="stylesheet" type="text/css" />

<?php include('top_script.php'); ?>

	<!--[if lte IE 7]><link href="/stylesheets/v2-iefix.css" rel="stylesheet" type="text/css" /><![endif]-->
	<!--[if lte IE 6]><link href="/stylesheets/v2-ie6fix.css" rel="stylesheet" type="text/css" /><![endif]-->

	<script type="text/javascript">
	tickerInit('BarNews');
<?php
if (!empty($ticker)) {
	// The first article is displayed by PHP so start from second article
	for ($x = 1; $x < count($ticker); $x++) {
		echo('	tickerAdd("' . xml_escape($ticker[$x]->headline) . '", "/' . xml_escape($ticker[$x]->section . '/' . $ticker[$x]->type . '/' . $ticker[$x]->id) . '");' . "\n");
	}
	echo('	tickerAdd("' . xml_escape($ticker[0]->headline) . '", "/' . xml_escape($ticker[0]->section . '/' . $ticker[0]->type . '/' . $ticker[0]->id) . '");' . "\n");
}
?>
	onLoadFunctions.push(tickerStart);
	</script>

</head>

<body onload="onLoadHandler()" onunload="onUnloadHandler()">
	<div id="Header">
		<h1 id="HeaderLogo"><a href="/"><img src="/images/version2/frame/logo.png" alt="The Yorker" /></a></h1>
		<div id="HeaderItems">
			<div id="HeaderMenu">
				<?php
				// Set by GenerateToplinks in mainframe_helper
				if (isset($toplinks)) {
					foreach ($toplinks as $link) {
						if (is_string($link)) {
							echo(xml_escape($link). ' | ');
						} elseif (is_array($link)) {
							echo('<a href="' . xml_escape($link[1]) . '">' . xml_escape($link[0]) . '</a> | ');
						}
					}
				}
				?>
				<a href="/account/">my account</a>
			</div>
			<div id="HeaderTime">
				<?php echo($date['time']); ?>
			</div>
			<div id="HeaderDate">
				<div id="HeaderDay">
					<?php echo($date['day']); ?>
				</div>
				<div id="HeaderWeek">
					Week <?php echo($date['week']); ?>
				</div>
			</div>
		</div>
	</div>

	<div id="Bar">
		<div id="BarDate">
			<?php echo($date['date'] . ' ' . $date['month']); ?>
		</div>
		<div id="BarSearch">
			<form id="searchbox_003080001858553066416:dyddjbcpdlc" action="http://www.google.com/search">
				<fieldset>
					<input type="hidden" name="cx" value="003080001858553066416:dyddjbcpdlc" />
					<input type="hidden" name="cof" value="FORID:0" />
					<input name="q" type="text" size="25" value="Search for..." onfocus="inputFocus(this);" onblur="inputBlur(this);" />
				</fieldset>
			</form>
		</div>
		<div id="BarTicker">
			<span id="BarLatest">latest news:</span>
			<span id="BarNews">
				<?php if (!empty($ticker)) { ?>
				<a href="/<?php echo(xml_escape($ticker[0]->section . '/' . $ticker[0]->type . '/' . $ticker[0]->id)); ?>">
					<?php echo(xml_escape($ticker[0]->headline)); ?>
				</a>
				<?php } ?>
			</span>
		</div>
	</div>

<?php
$menu = array(
	array('home', '/office', array('first')),
	array('articles', '/office/articles', array()),
	array('gallery', '/office/gallery', array()),
	array('business', '/office/business', array()),
	array('admin', '/admin', array('last'))
);
// Has a tab been set as selected?
$menu_style = '';
if (!empty($menu_tab)) {
        $menu_key = null;
        for ($x = 0; $x < count($menu); $x++) {
                if ($menu[$x][0] == $menu_tab) {
                        $menu_key = $x;
                        break;
                }
        }
        if ($menu_key !== null) {
                $menu[$menu_key][2][] = 'current';
                if (($menu_key - 1) > -1) {
                        $menu[$menu_key - 1][2][] = 'next';
                } else {
                        $menu_style = 'next';
                }
        }
}
?>
        <div id="Navigation">
                <ul id="Tabs"<?php if (!empty($menu_style)) echo(' class="' . $menu_style . '"'); ?>>
<?php foreach ($menu as $tab) { ?>
                        <li<?php if (!empty($tab[2])) echo(' class="' . implode(' ', $tab[2]) . '"'); ?>><a href="<?php echo($tab[1]); ?>"><?php echo($tab[0]); ?></a></li>
<?php } ?>
<?php foreach ($links as $link) { ?>
                        <li class="link"><?php echo($link); ?></li>
<?php } ?>
                </ul>
        </div>

<!--
			<li class="first next"><a href="/">Public Site</a></li>
			<li class="current"><a href="/office">Office</a></li>
			<li class="last"><a href="http://mail.theyorker.co.uk">Webmail</a></li>
-->

	<div id="Page">
		<div id="MainBodyPane">






<!--

			<div id="NavigationColumn">
				<div id="NavigationMenu">
<?php
function printMenu ($CI, $title, $links, $firstMenu = false) {
	$linkCount = count($links);
	for ($i = 0; $i < $linkCount; $i++) {
		if (!empty($links[$i][2]) && !$CI->permissions_model->hasUserPermission($links[$i][2])) {
			unset($links[$i]);
		}
	}
	if (count($links) == 0) return;
	echo('				<ul' . (($firstMenu) ? ' class="first"' : '') . '>'."\n");
	echo('					<li class="first">' . $title . '</li>'."\n");
	foreach ($links as $link) {
		echo('					<li><a href="' . $link[1] . '">' . $link[0] . '</a></li>'."\n");
	}
	echo('				</ul>'."\n");
}

printMenu($this, 'Office', array(
	array('Office Home', '/office', ''),
	array('Office Chat', '/office/irc', 'IRC_CHAT'),
	array('My Bylines', '/office/bylines', 'BYLINES_VIEW')
), true);

// Editor and Admins only
if (PermissionsSubset('editor', GetUserLevel())) {
	printMenu($this, 'Admin', array(
		array('Announcements', '/office/announcements', 'ANNOUNCEMENT_VIEW'),
		array('Permissions', '/admin/permissions', 'PERMISSIONS_VIEW'),
		array('Manage Team', '/office/manage/members', 'MANAGE'),
		array('Manage VIPs', '/office/vipmanager', 'VIPMANAGER_VIEW'),
		array('Content Schedule', '/office/news/contentschedule', 'ARTICLE_VIEW'),
		array('Change Live Article', '/office/news/scheduledlive', 'ARTICLE_VIEW'),
		array('Comment Moderation', '/office/moderator', 'COMMENT_MODERATE'),
		array('Page Properties', '/admin/pages', 'PAGES_VIEW'),
		array('Statistics', '/office/stats', 'STATS_VIEW'),
		array('Feedback', '/admin/feedback', 'FEEDBACK_VIEW'),
		array('Article Types', '/office/articletypes', 'ARTICLETYPES_VIEW'),
		array('Special Articles', '/office/specials', 'ARTICLE_VIEW'),
		array('Facebook Articles', '/office/ticker', 'ARTICLE_VIEW'),
		array('Advertising', '/office/advertising', 'ADVERTISING_VIEW'),
		array('Polls', '/office/polls', 'POLLS_VIEW')
	));
}

printMenu($this, 'Sections', array(
	array('Uni News', '/office/news/uninews', 'ARTICLE_VIEW'),
	array('Features', '/office/news/features', 'ARTICLE_VIEW'),
	array('Lifestyle', '/office/news/lifestyle', 'ARTICLE_VIEW'),
	array('Arts', '/office/news/arts', 'ARTICLE_VIEW'),
	array('Sport', '/office/news/sport', 'ARTICLE_VIEW'),
	array('Blogs', '/office/news/blogs', 'ARTICLE_VIEW'),
	array('Food', '/office/news/food', 'ARTICLE_VIEW'),
	array('Videocasts', '/office/news/videocasts', 'ARTICLE_VIEW'),
	array('News Comment', '/office/news/comment', 'ARTICLE_VIEW'),
	array('Podcasts', '/office/podcasts', 'ARTICLE_VIEW')
));

printMenu($this, 'Info + Reviews', array(
	array('Directory', '/office/prlist', ''),
	array('Food', '/office/reviewlist/foodreviews', ''),
	array('Drink', '/office/reviewlist/drinkreviews', ''),
	array('Review Tags', '/office/reviewtags', ''),
	array('Leagues', '/office/leagues', ''),
	array('PR System', '/office/pr/summary', ''),
	array('Campaigns', '/office/campaign', 'CAMPAIGN_VIEW'),
	array('Charities', '/office/charity', 'CHARITY_VIEW'),
	array('How Do I', '/office/howdoi', 'HOWDOI_VIEW'),
	array('Game Zone', '/office/games', 'GAMEZONE_VIEW')
));

printMenu($this, 'Photos', array(
	array('Photo Requests', '/office/photos', 'GALLERY_VIEW'),
	array('Gallery', '/office/gallery', 'GALLERY_VIEW'),
	array('Homepage Banners', '/office/banners', 'BANNERS_VIEW')
));

printMenu($this, 'Homepage', array(
	array('Quotes', '/office/quotes', 'QUOTES_VIEW'),
	array('Links', '/office/links', 'LINKS_VIEW'),
	array('Style Guide', '/office/guide', 'ARTICLE_VIEW')
));

?>
					<?php
					if (isset($extra_menu_buttons) && !empty($extra_menu_buttons)) {
						echo('<ul>');
						foreach ($extra_menu_buttons as $key => $button) {
							echo('<li'.(!$key ? ' class="first"':'').'>');
							if (is_string($button)) {
								echo($button);
							} else {
								echo('<a href="'.$button[1].'">'.$button[0].'</a>');
							}
							echo('</li>');
						}
						echo('</ul>');
					}
					?>
				</div>
			</div>


			<div id="ContentColumn">

-->





<!-- BEGIN generated content -->
<?php
	// TODO: check this works properly

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



<!--
			</div>
-->




			<div class="clear"></div>
		</div>
	</div>

	<?php include('footer.php'); ?>
</body>
</html>
