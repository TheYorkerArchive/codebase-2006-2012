<?php

class Dev extends Controller {

	function index()
	{
		SetupMainFrame('admin');
		if (CheckPermissions('admin')) {
			$op  = '<a href="/admin/dev/phpinfo">PHP information</a><br />';
			$op .= 'If you think this is wrong then email mg512<br />';
			$op .= 'Info dumps follow:<br /><pre>';
			exec('svn info', $ops);
			$op .= implode("\n",$ops);
			$op .= '<pre />';
			
			$this->main_frame->SetContent(new SimpleView($op));
		}
		$this->main_frame->SetTitle('Devr\'s Status page');
		$this->main_frame->Load();
	}
	
	function phpinfo()
	{
		if (CheckPermissions('admin')) {
			phpinfo();
		}
	}
}
?>
