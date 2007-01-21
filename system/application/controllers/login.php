<?php

class Login extends Controller
{

	function __construct()
	{
		parent::Controller();
		
		// Load the public frame
		$this->load->library('frame_public');
	}

	function index()
	{
		$data = array(
			'test' => 'I set this variable from the controller!',
		);
		
		// Set up the public frame
		$this->frame_public->SetTitle('Log in');
		$this->frame_public->SetContentSimple('login/login', $data);
		
		// Load the public frame view (which will load the content view)
		$this->frame_public->Load();
	}
	function resetpassword()
	{
		$data = array(
			'test' => 'I set this variable from the controller!',
		);
		
		// Set up the public frame
		$this->frame_public->SetTitle('Reset My Password');
		$this->frame_public->SetContentSimple('login/resetpassword', $data);
		
		// Load the public frame view (which will load the content view)
		$this->frame_public->Load();
	}
	function register()
	{
		$data = array(
			'test' => 'I set this variable from the controller!',
		);
		
		// Set up the public frame
		$this->frame_public->SetTitle('Register An Account');
		$this->frame_public->SetContentSimple('login/register', $data);
		
		// Load the public frame view (which will load the content view)
		$this->frame_public->Load();
	}
}
?>
