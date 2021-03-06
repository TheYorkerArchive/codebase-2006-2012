<?php

class Policy extends Controller
{

	function __construct()
	{
		parent::Controller();
		$this->load->library('image');
	}

	/// Main page
	function index()
	{
		if (!CheckPermissions('public')) return;
		
		$this->pages_model->SetPageCode('our_policy');
		
		// Get the blocks array from page properties.
		$blocks = $this->pages_model->GetPropertyArray('blocks', array(
			// First index is [int]
			array('pre' => '[', 'post' => ']', 'type' => 'int'),
			// Second index is .string
			array('pre' => '.', 'type' => 'enum',
				'enum' => array(
					array('title',	'text'),
					array('blurb',	'wikitext'),
					array('image',	'text'),
				),
			),
		));
		if (FALSE === $blocks) {
			$blocks = array();
		}
		
		// Create data array.
		$data = array();
		$data['textblocks'] = array();
		
		// Process page properties.
		foreach ($blocks as $key => $block) {
			$data['textblocks'][] = array(
				'shorttitle'	=> str_replace(' ','_',$block['title']),
				'blurb'			=> $block['blurb'],
				'image'			=> strlen($block['image']) ? $this->image->getThumb($block['image'], 'medium') : NULL,
			);
		}
		
		// Load the main frame
		$this->main_frame->SetContentSimple('about/about', $data);
		$this->main_frame->Load();
	}
}
?>
