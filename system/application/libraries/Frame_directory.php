<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file Frame_directory.php
 * @author James Hogan (jh559@cs.york.ac.uk)
 * @brief Shortcut library for using the directory frame.
 */

// Load the Frames library
$CI = &get_instance();
$CI->load->library('frames');

/// Directory frame library class.
/**
 * Automatically loads the Frame_navbar library.
 *
 * Load the library from the controller constructor (you'll probably want to
 *	load main_frame as well):
 * @code
 *	// Load the directory frame
 *	$this->load->library('frame_directory');
 * @endcode
 *
 * You can then refer to it as $this->frame_directory in order to SetContent().
 * The view can then be loaded using Load() (including the content view
 *	specified using SetContent()) or by setting it as content in another frame
 *	such as main_frame and loading that.
 *
 * Example of usage from a controller function:
 * @code
 *	// Set up the directory view
 *	$directory_view = $this->frames->view('directory/directory', $data);
 *	
 *	// Set up the navigation bar
 *	$navbar = $this->frame_directory->GetNavbar();
 *	// Navigation_bar setup code
 *
 *	// Set up the directory frame
 *	$this->frame_directory->SetPage('members');
 *	$this->frame_directory->SetOrganisation('The Yorker');
 *	$this->frame_directory->SetContent($directory_view);
 *	
 *	// Set up the public frame
 *	$this->main_frame->SetTitle($page_title);
 *	$this->main_frame->SetContent($this->frame_directory);
 *	
 *	// Load the public frame view (which will load the directory frame which
 *	// will load the contents)
 *	$this->main_frame->Load();
 * @endcode
 */
class Frame_directory extends FramesFrame
{
	/// disable adverts for the directory frame
	protected $mHasAdverts = FALSE;
	
	/// Default constructor.
	function __construct()
	{
		parent::__construct('directory/directory_frame.php');
	}
	
	/// Set the organisation data.
	/**
	 * @param $OrganisationData array Organisation data with the following fields:
	 *	- 'name'
	 */
	function SetOrganisation($OrganisationData)
	{
		$this->SetData('organisation', $OrganisationData);
	}
}

?>