<?php
/**
 * This controller deals with the input from feedback forms.
 *
 * @author Richard Ingle (ri504)
 *
 */

class Feedback extends Controller {

	function __construct()
	{
		parent::Controller();
	}

	function index()
	{
		if (!CheckPermissions('public', FALSE)) return;

		$this->load->model('feedback_model','feedback_model');

		$redirect_path = $this->input->post('r_redirecturl', '');
		$page_title = $this->input->post('a_pagetitle');
		$author_name = $this->input->post('a_authorname');
		$author_email = $this->input->post('a_authoremail');
		$rating = $this->input->post('a_rating');
		$feedback_text = $this->input->post('a_feedbacktext');
		$article_heading = $this->input->post('a_articleheading');
		$antispam = $this->input->post('email');

		$include_browser_info = ($this->input->post('a_browser_info') == '1');

		$this->load->library('user_agent');

		$rating_converstion = array( '1' => 'What\'s this for?',
									 '2' => 'Good idea - but what does it do?',
									 '3' => 'Useful.. I guess.',
									 '4' => 'Great idea, and easy to use!',
									 '5' => 'Amazing!!' );

		if (array_key_exists($rating,$rating_converstion)) {
			$rating = $rating_converstion[$rating];
		} else {
			$rating = 'None';
		}

		if (FALSE !== $feedback_text) {
			if ($feedback_text != '') {
				if($article_heading) {
					$feedback_text = 'Article: '.$article_heading."\n\n".$feedback_text;
				}
				if ($antispam === '' && !preg_match('/viagra|phentermine|orgasm|<\/a>|<a\s+href/i', $feedback_text)) {
					$this->feedback_model->AddNewFeedback($page_title,
						$author_name, $author_email,
						$rating, $feedback_text, 'http://'.$_SERVER['SERVER_NAME'].$redirect_path);
		
						$to = $this->pages_model->GetPropertyText('feedback_email', true);
						$from = (strpos($author_email, '@') ? $author_email : 'noreply@theyorker.co.uk');
						$subject = "The Yorker: Site Feedback";
						$message =
'Name: '.$author_name.'
Email: '.$author_email.'
';

if ($include_browser_info)
{
$message .='
Browser: '.$this->agent->browser().'
Version: '.$this->agent->version().'
Platform: '.$this->agent->platform().'
';
}
$message .='
Page Title: '.$page_title.'
Page URL: http://'.$_SERVER['SERVER_NAME'].$redirect_path.'

Rating: '.$rating.'

'.$feedback_text.'
';

					$this->load->helper('yorkermail');
					try {
						yorkermail($to,$subject,$message,$from);
						$this->messages->AddMessage('success',
							'You have successfully left feedback, thanks for your thoughts.' );
					} catch (Exception $e) {
						$this->messages->AddMessage('error',
							'You have successfully left feedback, thanks for your thoughts. However there was a problem sending this feedback by e-mail, so we might take a while to respond. '.$e->getMessage() );
					}
				} else {
					$this->messages->AddMessage('error',
						'Your feedback looks like spam. Please do not include any HTML code.'
					);
				}
			} else {
				$this->messages->AddMessage('error',
					'Please ensure that you have enterred some feedback text before submitting.');
			}
		} else {
			$this->messages->AddMessage('error',
				'To leave feedback use the feedback form at the bottom of each page.');
		}

		if ($redirect_path === '/')
			$redirect_path = '';
		redirect($redirect_path);
	}
}
?>
