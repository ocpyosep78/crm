<?php
	
	$page = oNav()->getCurrentPage();
	
	$id = 'notes_';	/* Serves as prefix for several fields */
	
	$params = array(
		'name'		=> 'Nota',
		'plural'	=> 'Notas',
	);
	
	$fields['note'] = 'Mensaje';
	$fields['type'] = array('Tipo', 'type');
	if( strstr($page, 'users') ) $fields['customer'] = array('Cliente', 'id_customer');
	elseif( strstr($page, 'customers') ) $fields['visibility'] = array('Visibilidad', 'visibility');
	$fields['by'] = 'Creada / Editada por';
	$fields['date'] = 'Fecha';
	
	$noInput = array('by' => getSes('user'), 'date' => 'fecha/hora actual');
	
	$tools = array(
		'edit',
		'delete',
	);
	
	$postProcess = 'postProcessNotesData';
	
	/* TEMP */
	function postProcessNotesData( $data ){
	
		foreach( $data as &$row ){
			$row['visibility'] = !empty($row['user']) ? 'Privado' : 'Público';
		}
	
	}