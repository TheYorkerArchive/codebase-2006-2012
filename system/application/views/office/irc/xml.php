<?php
/**
 * @file views/office/irc/xml.php
 * @author James Hogan (jh559)
 * @param $Messages  Messages recieved.
 */
header('content-type: text/xml');

// Simple text fields, field => tag
$simple_fields = array(
	'sender',
	'to',
	'content',
	'names',
	'topic',
	'channel',
);

function write_xml($subtags, $label = NULL)
{
	if (NULL !== $label) {
		$attributes = '';
		if (is_array($subtags) && isset($subtags['_attr']) && is_array($subtags['_attr'])) {
			foreach ($subtags['_attr'] as $attribute => $value) {
				$attributes .= " $attribute=\"".htmlentities($value, ENT_QUOTES, 'UTF-8').'"';
			}
		}
		echo("<$label$attributes>");
	}
	if (is_array($subtags)) {
		foreach ($subtags as $tag => $content) {
			if (substr($tag, 0, 1) == '_') {
				continue;
			}
			if (is_numeric($tag)) {
				if (isset($content['_tag'])) {
					$tag = $content['_tag'];
				} else {
					$tag = NULL;
				}
			}
			write_xml($content, $tag);
		}
	} else {
		echo(htmlentities($subtags, ENT_QUOTES, 'utf-8'));
	}
	if (NULL !== $label) {
		echo("</$label>");
	}
}

?><<?php ?>?xml version="1.0" encoding="UTF-8"?><?php
?><irc><?php
	if (isset($Messages) && is_array($Messages)) {
		foreach ($Messages as $message) {
			echo('<msg type="'.htmlentities($message['type']).'"');
			if (isset($message['highlight'])) {
				echo(' highlight="1"');
			}
			echo('>');
			if (isset($message['received'])) {
				echo('<time>'.date('H:i', $message['received']).'</time>');
			}
			foreach ($simple_fields as $field) {
				if (isset($message[$field])) {
					write_xml($message[$field], $field);
				}
			}
			?></msg><?php
		}
	}
?></irc>