<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	return array(
		'updatePermitsBy',
		'movePermit',
	);

	function updatePermitsBy($type, $id, $filter='%'){
	
		# Get list of permissions, add titles, and register it for Smarty
		$permits = oConfig()->{"getPermitsBy{$type}"}($id, $filter);
		
		foreach( $permits as &$permit ){
			$permit['title'] = "Código: {$permit['code']}";
			switch( $permit['type'] ){
				case 'page': $permit['title'] .= " | Módulo: {$permit['module']}"; break;
				default: break;
			}
		}
		oSmarty()->assign('permits', $permits);
		
		# Set current filter
		oSmarty()->assign('permitsFilter', $filter);
		oSmarty()->assign('permitsFilters', permitsFilters());
		
		# Fille both lists of permissions (assigned and unassigned)
		oSmarty()->assign('stat', 1);
		addAssign('permitsYes', 'innerHTML', oSmarty()->fetch('config/_permitsList.tpl'));
		oSmarty()->assign('stat', 0);
		addAssign('permitsNo', 'innerHTML', oSmarty()->fetch('config/_permitsList.tpl'));
	
		# Clear permissions, modules, pages, areas, from cache (to stay updated always)
		oPermits()->clear();
		
		return addScript('List.attachListEventHandlers();');
		
	}
	
	/**
	 * $type can be aither 'Profiles' or 'Users'
	 * $hadIt 0 means adding, $from 1 means removing permission
	 * $id is the ID of the given profile or user
	 * $code is the permission's ID
	 */
	function movePermit($type, $hadIt, $id, $code){
	
		switch( strtolower($type) ){
			case 'profiles':
				$ans = $hadIt
					? oConfig()->removeProfilePermission($id, $code)
					: oConfig()->addProfilePermission($id, $code);
			break;
			case 'users':
				$ans = $hadIt
					? oConfig()->removeUserPermission($id, $code)
					: oConfig()->addUserPermission($id, $code);
			break;
			default: return showStatus('No se reconoce el objeto asociado a los permisos.');
		}
	
		# Clear permissions, modules, pages, areas, from cache (to stay updated always)
		oPermits()->clear();
		
		return updatePermitsBy($type, $id, '%');
		
	}

?>