<?php
	
	$page = oNav()->getCurrentPage();

	$id = 'notes_';	/* Serves as prefix for several fields */

	$params = array(
		'name'		=> 'Nota',
		'plural'	=> 'Notas',
	);
		
	$fields['note'] = 'Mensaje';
	if( strstr($page, 'users') ) $fields['customer'] = array('Cliente', 'id_customer');
	elseif( strstr($page, 'customers') ) $fields['visibility'] = array('Visibilidad', 'visibility');
	$fields['by'] = 'Creada / Editada por';
	$fields['date'] = 'Fecha';
	
	$noInput = array('by' => getSes('user'), 'date' => 'fecha/hora actual');
	
	$tools = array(
		'edit',
		'delete',
	);
	
	if( strstr($page, 'customers') ) $postProcess = 'postProcessNotesData';
	
?>