<?php

/**
 * smarty-gettext.php - Gettext support for smarty
 *
 * ------------------------------------------------------------------------- *
 * This library is free software; you can redistribute it and/or             *
 * modify it under the terms of the GNU Lesser General Public                *
 * License as published by the Free Software Foundation; either              *
 * version 2.1 of the License, or (at your option) any later version.        *
 *                                                                           *
 * This library is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         *
 * Lesser General Public License for more details.                           *
 *                                                                           *
 * You should have received a copy of the GNU Lesser General Public          *
 * License along with this library; if not, write to the Free Software       *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA *
 * ------------------------------------------------------------------------- *
 *
 * To register as a smarty block function named 't', use:
 *   $smarty->register_block('t', 'smarty_translate');
 *
 * @package	smarty-gettext
 * @version	$Id: smarty-gettext.php,v 1.1 2004/04/30 11:39:32 sagi Exp $
 * @link	http://smarty-gettext.sf.net/
 * @author	Sagi Bashari <sagi@boom.org.il>
 * @copyright 2004 Sagi Bashari
 */
 
/**
 * Smarty block function, provides gettext support for smarty.
 *
 * The block content is the text that should be translated.
 * 
 * Modified by dbind to allow sprintf arguments to be translated.
 * 
 * Accepts any number of %s holders. Parameters to replace %s are passed in
 * the opening block and must be within slashes if you want them translated.
 * Replacement occurs AFTER main sentence and params are already translated.
 *
 * To install and get it running, simply put this file inside Smarty plugins
 * directory. You might change name to block.whatever.php, as long as you use
 * {whateva} tags within templates: i.e. {whatever}Text to translate{/whateva}
 */
function smarty_block_t( $params , $text , &$smarty ){

	if( $text == '' || $text == '//' ) return '';	// Avoid attempting to translate empty strings
	$text = stripslashes( $text );
	
	// Set escape mode
	if( isset($params['escape']) ){
		$escape = $params['escape'];
		unset( $params['escape'] );
	}
	
	// Set plural version
	if( isset($params['plural']) && isset($params['count']) ){
		$plural = $params['plural'];
		$count = $params['count'];
		unset( $params['plural'] , $params['count'] );
	}
	
	// Use plural if required parameters are set
	if( isset($plural) ) $text = ngettext($text,$plural,$count);
	else $text = gettext($text);

	// run strarg if there are parameters
	if( count($params) ){
		$par = array();
		foreach( $params as $key => $val ){
			if( substr($val,0,1) != '/' || substr($val,-1) != '/' ) $par[] = $val;
			elseif( $val != '' && $val != '//' ) $par[] = gettext( substr($val,1,-1) );
			else $par[] = substr($val,1,-1);
		}
		$t = explode( '%s' , $text );
		$i = -1;
		$iT = count( $t );
		$iP = count( $par );
		while( $i++ && $i<$iT-1 && $i<$iP ) $t[$i] .= $par[$i];
		$text = implode('',$t);
	}

	if( !isset($escape) || $escape == 'html' ){								// html escape, default
		$text = nl2br( htmlspecialchars($text) );
	}elseif( isset($escape) && ($escape=='javascript' || $escape=='js') ){	// javascript escape
		$text = str_replace( '\'' , '\\\'' , stripslashes($text) );
	}

	return $text;
	
}

?>
