<?php
/**
 * @file views/pages/error.php
 * @brief Simple view for displaying an error and back button.
 */
?>
<div class="BlueBox">
	<img align="left" src="<?php echo site_url('/images/prototype/homepage/error.png'); ?>" alt="error" width="30" height="30" />
	<?php
		echo(@$_wikitext);
		
		$CI = & get_instance();
		if (isset($try_login['_text']) && !$CI->user_auth->isLoggedIn) {
			echo('<p>You are not currently logged in. '.htmlentities($try_login['_text'],ENT_QUOTES,'UTF-8').'</p>');
			//echo('<p><a href="'.site_url('login/main'.$this->uri->uri_string()).'">Log in now</a></p>');
			echo(HtmlButtonLink(site_url('login/main'.$this->uri->uri_string()),'Log in'));
		}
		
		if (isset($return['_text']) && isset($referer)) {
			echo(HtmlButtonLink($referer, $return['_text']));
		}
	?>
</div>
