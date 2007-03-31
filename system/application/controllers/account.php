<?php

/**
 *	@file	account.php
 *	@brief	My Account controller
 *	@author	James Hogan (jh559@cs.york.ac.uk)
 *	@author	Chris Travis (cdt502 - ctravis@gmail.com)
 */

/// Account controller.
class Account extends controller
{
	/// Default constructor
	function __construct()
	{
		parent::controller();
	}

	/**
	 *	@brief	Shows overview of a student's subscriptions and a menu for other account operations
	 */
	function index ()
	{
		/// Make sure users have necessary permissions to view this page
		if (!CheckPermissions('student')) return;

		$data['test'] = 'test';

		/// Get custom page content
		$this->pages_model->SetPageCode('account_home');

		/// Set up the main frame
		$this->main_frame->SetContentSimple('account/myaccount', $data);
		/// Set page title & load main frame with view
		$this->main_frame->Load();
	}

	/**
	 *	@brief	Allows setting of business card information
	 */
	function bcards()
	{
		/// Make sure users have necessary permissions to view this page
		if (!CheckPermissions('student')) return;

		$data['test'] = 'test';

		/// Get custom page content
		$this->pages_model->SetPageCode('account_bcards');

		/// Set up the main frame
		$this->main_frame->SetContentSimple('account/business_cards', $data);
		/// Set page title & load main frame with view
		$this->main_frame->Load();
	}

	/// Password related operations
	function password($option = '', $parameter = NULL)
	{
		static $handlers = array(
			'change' => '_password_change',
			'reset'  => '_password_reset',
		);

		if (array_key_exists($option, $handlers)) {
			if (NULL === $parameter) {
				$this->$handlers[$option]();
			} else {
				$this->$handlers[$option]($parameter);
			}
		} else {
			return show_404();
		}
	}

	/// Reset password
	protected function _password_reset($parameter = 'main')
	{
		if (!CheckPermissions('public')) return;

		$this->pages_model->SetPageCode('account_password_reset');

		$data = array();

		// Set up the public frame
		$this->main_frame->SetContentSimple('login/resetpassword', $data);

		// Load the public frame view (which will load the content view)
		$this->main_frame->Load();
	}

	/// Change password
	protected function _password_change($parameter = 'main')
	{
		static $handlers = array(
			'main'    => array('student', 'checkPassword', 'setPassword'),
			'office'  => array('editor', 'checkOfficePassword', 'setOfficePassword'),
		);

		if (!array_key_exists($parameter, $handlers)) {
			return show_404();
		}
		$permission_level = $handlers[$parameter][0];
		$password_checker = $handlers[$parameter][1];
		$password_setter  = $handlers[$parameter][2];

		if (!CheckPermissions($permission_level)) return;

		$this->pages_model->SetPageCode('account_password_change');

		// Check for post data for changing password
		$old_password = $this->input->post('oldpassword');
		if (is_string($old_password)) {
			$new_password = $this->input->post('newpassword');
			$confirm_password = $this->input->post('confirmpassword');

			$validation_errors = array();

			// Check existence of password fields.
			if (empty($old_password)) {
				$validation_errors[] = 'You must enter your current password. If you\'ve lost this you\'ll need to <a href="account/password/reset">reset</a> it first.';
			}
			if (empty($new_password)) {
				$validation_errors[] = 'You must enter your new password.';
			}
			if (empty($confirm_password)) {
				$validation_errors[] = 'You must confirm your new password by entering it again.';
			}

			if (empty($validation_errors)) {
				if ($new_password !== $confirm_password) {
					$validation_errors[] = 'The passwords entered do not match. Please try again, confirming your new password by entering it again.';
				} elseif ($old_password === $new_password) {
					$validation_errors[] = 'The new password is identical to the current password.';
				}

				if (empty($validation_errors)) {
					// Check old password
					if (!$this->user_auth->$password_checker($old_password)) {
						$validation_errors[] = 'Your current password was not entered correctly.';
					} else {
						try {
							$this->user_auth->$password_setter($new_password);
							$this->messages->AddMessage('success', 'Your password was successfully changed');
						} catch (Exception $e) {
							$validation_errors[] = $e->getMessage();
						}
					}
				}
			}


			if (!empty($validation_errors)) {
				$this->messages->AddMessage('error',
					'<p>There were problems with your passwords:'.
					'<ul><li>'.implode('</li><li>',$validation_errors).'</li></ul>'.
					'</p>');
			}
		}

		// Setup form
		$this->load->helper('form');
		$data = array(
			'main_text' => $this->pages_model->GetPropertyWikitext('main_text['.$parameter.']'),
			'change_password_target' => $this->uri->uri_string(),
		);

		// Load view
		$this->main_frame->SetContentSimple('account/password_change', $data);
		$this->main_frame->Load();
	}
}