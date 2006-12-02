<?php

/**
 * @brief wikitext test controller.
 * @author James Hogan (jh559@cs.york.ac.uk)
 */
class Wikitext extends Controller {
	
	/**
	 * @brief Default constructor.
	 */
	function __construct()
	{
		parent::Controller();
		$this->load->helper('form');
		$this->load->library('wikiparser');
	}
	
	/**
	 * @brief Wikitest test page.
	 */
	function index()
	{
		// No POST data? just set wikitext to default string
		if (!array_key_exists('wikitext',$_POST)) {
			$_POST['wikitext'] =
					'==This is the yorker wikitext parser==' . "\n" .
					'Enter wikitext here:';
		}
		
		echo '<HTML><HEAD><TITLE>Wikitext preview</TITLE></HEAD><BODY>';
		
		// Put a preview at the top
		echo $this->wikiparser->parse($_POST['wikitext'],'wiki test');
		
		// Then have a form for changing the wikitext
		echo form_open('test/wikitext');
		
		$textarea_data = array(
				'name'        => 'wikitext',
				'id'          => 'wikitext',
				'value'       => $_POST['wikitext'],
				'rows'        => '10',
				'cols'        => '80',
				'style'       => 'width:80%',
			);
		echo form_textarea($textarea_data) . '<br/>';
		echo form_submit('submit', 'Preview') . '<br/>';
		echo form_close('') . '<br/>';
		
		
		echo '</BODY></HTML>';
	}
}
?>
