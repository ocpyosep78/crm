<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
 
	
	function canEditEvent($user, $creator){
		
		# Pairs of users, so that first one can edit second one's events in the Agenda
		$allowed = array(
			array('mantunez', 'rdelossantos'),
			array('rdelossantos', 'mantunez'),
		);
		
		# Keep in mind users still need editEvent permission granted in the first place
		return getSes('id_profile') <= 2 || $creator == $user
			|| in_array(array($user, $creator), $allowed);
			
	}

?>