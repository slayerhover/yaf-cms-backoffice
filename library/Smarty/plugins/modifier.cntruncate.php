<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     cntruncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
 
function smarty_modifier_cntruncate($string, $strlen = 20, $etc = '..', $keep_first_style = false)
{
	$str	= strip_tags($string);

	if ( mb_strlen($str,'utf-8') <= $strlen ) {
		return $string;
	}else{
		$rstr	=	mb_substr($str, 0, $strlen, 'utf-8');
		$rstr  .=	$etc;
		
		return $rstr;
	}
}