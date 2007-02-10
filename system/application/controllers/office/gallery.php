<?php

/// Office Gallery
/**
 * @author Nick Evans (nse500@cs.york.ac.uk)
 *
 */
define('PHOTOS_PERPAGE', 12);

 class Gallery extends Controller
{
	/**
	 * @brief Default constructor.
	 */
	function __construct() {
		parent::Controller();
		$this->load->helper(array('url', 'images'));
	}
	
	function index() {
		if (!CheckPermissions('office')) return;
		
		$this->pages_model->SetPageCode('office_gallery');
		$count = $this->db->get('photos')->num_rows();
		if ($count > PHOTOS_PERPAGE) {
			$this->load->library('pagination');
			
			$config['base_url'] = site_url('office/gallery/');
			$config['total_rows'] = $count;
			$config['per_page'] = PHOTOS_PERPAGE;
			$config['uri_segment'] = 3;
			
			$this->pagination->initialize($config);
			$pageNumbers = $this->pagination->create_links();
		} else {
			$pageNumbers = '';
		}
		$page = $this->uri->segment(3, 0);
		$photos = $this->db->get('photos', PHOTOS_PERPAGE, $page * PHOTOS_PERPAGE);
		
		$data = array(
			'main_text' => $this->pages_model->GetPropertyWikitext('main_text'),
			'photos' => $photos->result()
		);
		
		// Set up the center div for the gallery.
		$gallery_div = $this->frames->view('office/gallery/gallerythumbs');
		$gallery_div->AddData($data);

		// Set up the subview for gallery.
		$gallery_frame = $this->frames->frame('office/gallery/galleryframe');
		$gallery_frame->AddData($data);
		$gallery_frame->SetContent($gallery_div);

		// Set up the master frame.
		$this->main_frame->SetContent($gallery_frame);
		$this->main_frame->SetTitle('Photo Gallery');
	
		// Load the main frame
		$this->main_frame->Load();
	}
	
	function show()
	{
		if (!CheckPermissions('office')) return;
		
		$this->pages_model->SetPageCode('office_gallery');
		
		$data = array(
			'main_text' => $this->pages_model->GetPropertyWikitext('main_text'),
		);
		
		// Set up the center div for the gallery.
		$gallery_div = $this->frames->view('office/gallery/galleryimage');
		$gallery_div->AddData($data);

		// Set up the subview for gallery.
		$gallery_frame = $this->frames->frame('office/gallery/galleryframe');
		$gallery_frame->AddData($data);
		$gallery_frame->SetContent($gallery_div);

		// Set up the master frame.
		$this->main_frame->SetContent($gallery_frame);
		$this->main_frame->SetTitle('Photo Details');
	
		// Load the main frame
		$this->main_frame->Load();
	}
}

?>