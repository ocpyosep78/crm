<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsModifier
 */
function smarty_modifier_date_locale($string, $format = SMARTY_RESOURCE_DATE_FORMAT, $default_date = '',$formatter='auto'){
    /**
    * Include the {@link shared.make_timestamp.php} plugin
    */
    require_once(SMARTY_PLUGINS_DIR . 'shared.make_timestamp.php');
    if ($string != '') {
        $timestamp = smarty_make_timestamp($string);
    } elseif ($default_date != '') {
        $timestamp = smarty_make_timestamp($default_date);
    } else {
        return;
    } 
	$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	$dias = array('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
	$months = array('January', 'February', 'March', 'April', 'May', 'June',
		'July', 'August', 'September', 'October', 'November', 'December');
	$meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
		'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre');
	$date = str_replace($days, $dias, date($format, $timestamp));
	$date = str_replace($months, $meses, $date);
	
	return $date;
	
}