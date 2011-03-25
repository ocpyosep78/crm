<?php

	$id = 'notes_';	/* Serves as prefix for several fields */

	$params = array(
		'name'		=> 'Nota',
		'plural'	=> 'Notas',
	);
		
	$fields = array(
		'note'	=> 'Nota',
		'by'	=> 'Escrita por',
		'date'	=> 'Fecha',
	);
	
	$noInput = array('by', 'date');
	
	$tools = array(
		'edit',
		'delete',
	);
	
?>