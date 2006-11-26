<?php
/**
 * This controller is the default.
 * It currently displays only the prototype review page, in the prototype student frame
 *
 * \author Richard Rout
 */
class Review extends Controller {

	/**
	* Displays prototype homepage, in the prototype student frame
	*/
	function index()
	{
		$this->load->view('reviews/review_frame');
	}

}
?>