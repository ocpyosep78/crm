<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
	/* TEMP */
	function postProcessNotesData( $data ){
	
		foreach( $data as &$row ){
			$row['visibility'] = !empty($row['user']) ? 'Privado' : 'Público';
		}
	
	}

?>