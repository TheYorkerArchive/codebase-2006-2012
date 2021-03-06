<?php

/**
 * @file escape_helper.php
 * @brief Simple functions for escaping.
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 */
 
/// Htmlentities shortcut.
/**
 * @param $text string The text to escape.
 * @param $quotes string Whether to encode quotes (e.g. may be false for javascript block).
 * @return string @a $text with xml bits escaped using htmlentities.
 */
function xml_escape($text, $quotes = true)
{
	return htmlentities($text, $quotes ? ENT_QUOTES : ENT_NOQUOTES, 'utf-8');
}

/// HtmlEntityDecode shortcut.
/**
 * @param $xml string The xml to decode.
 * @param $quotes string Whether to decode quotes (e.g. may be false for javascript block).
 * @return string @a $xml with xml entities decoded using html_entity_decode.
 */
function xml_unescape($xml, $quotes = true)
{
	return html_entity_decode($xml, $quotes ? ENT_QUOTES : ENT_NOQUOTES, 'utf-8');
}

/// Literalise a php value into javascript.
/**
 * @param $value string,int,bool,null The value to literalise.
 * @return string @a $value in javascript.
 */
function js_literalise($value)
{
	if (is_int($value) || is_float($value)) {
		return $value;
	}
	elseif (is_bool($value)) {
		return $value ? 'true' : 'false';
	}
	elseif (null === $value) {
		return 'null';
	}
	elseif (is_array($value)) {
		// represent arrays as hashes
		$result = '{';
		$comma = '';
		foreach ($value as $key => $item) {
			$result .= $comma.js_literalise($key).':'.js_literalise($item);
			$comma = ',';
		}
		$result .= '}';
		return $result;
	}
	else {
		return '\''.str_replace(
			array('\'',  '<?',       ']]>'),
			array('\\\'', '<\'+\'?', ']\'+\']>'),
			$value
		).'\'';
	}
}

/// Echo a block of javascript code in proper CDATA tags.
function js_block($code)
{
	return	"<script type=\"text/javascript\">\n".
			"// <![CDATA[\n".
			"$code\n".
			"// ]]>\n".
			"</script>";
}

?>
