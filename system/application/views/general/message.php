<?php
/*
if ($class === 'error')
	echo 'warning_box"><img src="/images/prototype/homepage/error.png" alt="Error" title="Error"';
elseif ($class === 'warning')
	echo 'warning_box"><img src="/images/prototype/homepage/warning.png" alt="Warning" title="Warning"';
elseif ($class === 'information')
	echo 'information_box"><img src="/images/prototype/homepage/information.png" alt="Information" title="Information"';
elseif ($class === 'success')
	echo 'information_box"><img src="/images/prototype/homepage/sucess.png" alt="Success" title="Success"';
else
	echo 'information_box"><img src="/images/prototype/homepage/questionmark.png" alt="Question" title="Question"';
*/
?>


<?php
echo('<div class="message_'.$class.'">'."\n");
echo('	<img src="/images/prototype/homepage/'.$class.'.png" alt="'.$class.'" />'."\n");
echo('	'.$text."\n");
echo('</div>'."\n");
?>