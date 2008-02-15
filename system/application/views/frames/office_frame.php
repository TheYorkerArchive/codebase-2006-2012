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
	<link href="/stylesheets/general.css" rel="stylesheet" type="text/css" />
	<link href="/stylesheets/stylesheet.css" rel="stylesheet" type="text/css" />

	<!--<link href="/stylesheets/new.css" rel="stylesheet" type="text/css" /> -->
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

<div style="width: 100%;" align="center">
<div style="width: 780px; text-align: left; background-color: #fff;">
	<div style="height: 22px; text-align: right;" class="HeaderMenu">
		<?php
		// Set by GenerateToplinks in mainframe_helper
		if (isset($toplinks)) {
			foreach ($toplinks as $link) {
				if (is_string($link)) {
					echo(xml_escape($link).' | ');
				} elseif (is_array($link)) {
					echo('<a class="HeaderLinks" href="'.xml_escape($link[1]).'">'.xml_escape($link[0]).'</a> | ');
				}
			}
		}
		?>
		<a class="HeaderLinks" href="/about/">about us</a> |
		<a class="HeaderLinks" href="/contact/">contact us</a> |
		<a class="HeaderLinks" href="/faq/">FAQs</a>
	</div>
	<div style="width: 780px; background-image:url(/images/prototype/header/homepage_bk.gif); background-repeat:repeat-x; height:108; float: left;">
		<div style="float: left;">
			<a href="/home/">
				<img src="/images/prototype/header/header_Layer-1.gif" width="300" height="108" alt="" border="0" />
			</a>
		</div>
		<a href="/logout/office">
		<div style="float: right; width: 100px; overflow: hidden; color: #FFFFFF; text-align: center; position: relative; top: 37px; height: 40px;">
			<span style="font-size: 18px; font-weight:bold; ">Leave<br />Office</span>
		</div>
		</a>
		<div style="float: right; width: 370px; overflow: hidden; color: #FFFFFF; text-align: center; position: relative; top: 37px; height: 40px;">
			<span style="font-size: 40px; font-weight:bold; ">Office</span>
		</div>
	</div>
	<div style="background-color: #fff;">
		<form name='site_search' action='/search/layout' method='post' style='display:inline; '>
		<div style='float: left; width: 120px; font-size: 10px; border: solid 1px #20c1f0; padding: 2px; margin: 0px; margin-left: 0px;'>
			<img src='/images/prototype/header/search.png' alt='Search' title='Search' style='float: left; padding-top: 1px;' />
			<input type="text" style="float: right; color: #20c1f0; font-size: 12px; width: 100px; border: 0; margin: 2px 0; padding: 0;" value="Search for..." onFocus="if (this.value==this.defaultValue) this.value=''" onBlur="if (this.value=='') this.value=this.defaultValue" />
		</div>
		</form>
		<div style="float: right; width: 645px; margin-bottom: 0px; background-color: #20c1f0; padding: 3px 0px 3px 5px; color: #fff; font-size: medium; font-weight: bold; height: 18px; " >
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
		</div>
	</div>
	<br style="clear: both;" />
	<div style="float: left; width: 120px; margin-top: 8px; margin-right: 5px; background-color: #fff;">
		<div class='officenavigation_title'>
			Office
		</div>
		<div class='officenavigation_item'>
			<a href='/office/'>Office Home</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/irc'>Office Chat</a>
		</div>
		<div class="officenavigation_item">
			<a href="/office/bylines/">Manage Bylines</a>
		</div>

<?php
	//editor and admins only
	if (PermissionsSubset('editor', GetUserLevel())){
?>
		<div class='officenavigation_title'>
			Admin
		</div>
		<div class='officenavigation_item'>
			<a href='/office/manage/members/'>Manage Team</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/vipmanager/'>Manage VIP's</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/contentschedule/'>Content Schedule</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/scheduledlive/'>Change Live Article</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/moderator/'>Comment Moderation</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/admin/pages'>Page Properties</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/stats'>Statistics</a>
		</div>		
		<div class='officenavigation_item'>
			<a href='/office/articletypes'>Article Types</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/specials'>Special Articles</a>
		</div>
		<div class="officenavigation_item">
			<a href="/office/ticker/">
				Facebook Articles
			</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/advertising'>Advertising</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/polls'>Polls</a>
		</div>
<?php
	}
?>

		<div class='officenavigation_title'>
			Sections
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/uninews/'>Uni News</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/features/'>Features</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/lifestyle/'>Lifestyle</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/arts/'>Arts</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/sport/'>Sport</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/blogs/'>Blogs</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/videocasts/'>Videocasts</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/news/comment/'>News Comment</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/photos/'>Photos</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/podcasts/'>Podcasts</a>
		</div>

		<div class='officenavigation_title'>
			Info + Reviews
		</div>
		<div class='officenavigation_item'>
			<a href='/office/prlist/'>Directory</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/reviewlist/food'>Food</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/reviewlist/drink'>Drink</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/reviewtags'>Review Tags</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/leagues'>Leagues</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/pr/summary'>PR System</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/campaign/'>Campaigns</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/charity/'>Charities</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/howdoi/'>How Do I</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/games/'>Game Zone</a>
		</div>
		<div class='officenavigation_item'>
			<a href='http://yorkipedia.theyorker.co.uk'>Yorkipedia</a>
		</div>
		<?php /*
		<div class='officenavigation_item'>
			<a href='/office/packages/' onclick="alert('Coming soon...'); return false;">Packages</a>
		</div>
		*/ ?>
		
		<div class='officenavigation_title'>
			Photos
		</div>
		<div class='officenavigation_item'>
			<a href='/office/gallery/'>Gallery</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/banners/'>Homepage Banners</a>
		</div>
		
		<div class='officenavigation_title'>
			Homepage
		</div>
		<div class='officenavigation_item'>
			<a href='/office/quotes/'>Quote Moderation</a>
		</div>
		
		<div class='officenavigation_item'>
			<a href='/office/guide/'>Style Guide</a>
		</div>
		<div class='officenavigation_item'>
			<a href='/office/links/'>Links</a>
		</div>
	</div>
	<div style="float: right; width: 650px; padding: 0px; margin-top: 0px; margin-bottom: 0px; margin-left: 5px; background-color: #fff;">
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
	?>

	</div>
	<div style="float: right; width: 650px; padding: 0px; margin-top: 8px; margin-bottom: 0px; margin-left: 5px; background-color: #fff;">
<?php
			// Display the main content
			$content[0]->Load();
?>
	</div>
</div>

</div>
<br style="clear: both;" />

<?php
	// Get common footer
	include('footer.php');
?>

</body>
</html>
