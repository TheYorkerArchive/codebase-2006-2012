<?php $this->load->helper('form');
echo form_open('search/get'); ?>
	<p><label for="search">Search:</label> 
	<?php echo form_input(array('name'=>'search',
                            	'id'=>'search',
                            	'style'=>'font-size:10px;height: 19px;border-style:solid;border-color:#2DC6D7;border-width: 2px;')); ?></p>
	<p><?php echo form_submit('submit', 'Search'); ?></p>
<?php echo form_close(); ?>