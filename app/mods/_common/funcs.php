<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
 
	
	function canEditEvent($user, $creator, $target=NULL){
	
		# 1. Edit own events (from or to), always granted
		if( $user == $creator || $user == $target ) return true;
		
		# 2. Developer and admin permission always granted
		if( getSes('id_profile') <= 2 ) return true;
		
		# 3. Groups of users who can edit eachother's events
		$groups[] = array('mantunez', 'rdelossantos', 'gperdomo', 'rferdinand', 'flaborde');
		foreach( $groups as $group ){
			if (in_array($user, $group))
			{
				if (in_array($creator, $group) || ($target && in_array($target, $group)))
				{
					return true;
				}
			}
		}
		
		# 4. Pairs of users where first one can edit second one's events
		$allowed = array(
//			array('thisOneCanEdit', 'thisOnesEvents'),
		);
		if( in_array(array($user, $creator), $allowed) ) return true;
		
		# None of the above
		return false;
			
	}