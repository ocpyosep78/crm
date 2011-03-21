<?php
 
function smarty_function_append( $params , &$smarty ){

    if( !isset($params['var']) )
        return $smarty->_trigger_fatal_error("assign: missing 'var' parameter", E_USER_WARNING);
	if( !isset($params['values']) )
		return $smarty->_trigger_fatal_error("append: missing 'values' parameter", E_USER_WARNING);
	
	if( $smarty->get_template_vars($params['var']) == '' ) $smarty->assign($params['var'],array());
	$values = preg_split( "/'\s*,\s*'/", substr($params['values'],1,-1), NULL, PREG_SPLIT_NO_EMPTY );
	
	$output = '';
	foreach( $values as $key => $val ){
		$smarty->append( $params['var'] , preg_replace("/^[\s']+|[\s']+$/","",$val) );
	}
	
}

?>