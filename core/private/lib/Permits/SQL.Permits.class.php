<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/*
	Methods that use Connection's modify() method, return an AnswerSQL object.
	
	This object contains the following public attributes (note to self: outdated list):
		* msg	/> A string personalized message (defaults to '')
		* code	/> Either true or false
		* rows	/> mysql_affected_rows() returned by the query
		
	The personalized msg is set by assigning a string to each property, using:
		* ->setErrMsg( 'error message to print if query fails' );
		* ->setOkMsg( 'success message to print if query succeeds' );
		
*/
	
	require_once( CONNECTION_PATH );


	class pSQL extends Connection{
		

/***************
** Q U E R Y   M E T H O D S
****************
** (SELECT)
***************/

		protected function getPermits(){
			$sql = "SELECT *
					FROM `_permissions`";
			return $this->query($sql, 'named', 'code');
		}

		protected function getModules(){
			$sql = "SELECT	`m`.*,
							`p`.`name`
					FROM `_modules` `m`
					LEFT JOIN `_permissions` `p` USING( `code` )
					ORDER BY `order`";
			return $this->query($sql, 'named', 'code');
		}

		protected function getPages(){
			$sql = "SELECT	`g`.*,
							`a`.`area`,
							`p`.`name`
					FROM `_pages` `g`
					LEFT JOIN `_permissions` `p` USING( `code` )
					LEFT JOIN `_areas` `a` USING( `id_area` )
					ORDER BY `a`.`order`, `g`.`order`";
			return $this->query($sql, 'named', 'code');
		}

		protected function getAreas(){
			$sql = "SELECT *
					FROM `_areas`
					ORDER BY `order`";
			return $this->query($sql, 'named', 'id_area');
		}
		
		protected function getUserPermits($profile, $user){
			$sql = "SELECT `p`.*
					FROM `_permissions` `p`
					LEFT JOIN `_permissions_by_profile` `pbp` USING( `code` )
					LEFT JOIN `_permissions_by_user` `pbu` USING( `code` )
					WHERE(`pbu`.`user` = '{$user}' && `pbu`.`type` = 'add')
					OR(`pbp`.`id_profile` = '{$profile}' && IFNULL(`pbu`.`type`, 'add') <> 'sub')";
			return $this->query($sql, 'named', 'code');
		}
		
		protected function getUserModules($profile, $user){
			$sql = "SELECT	`m`.*,
							`p`.`name`
					FROM `_modules` `m`
					LEFT JOIN `_permissions` `p` USING( `code` )
					LEFT JOIN `_permissions_by_profile` `pbp` USING( `code` )
					LEFT JOIN `_permissions_by_user` `pbu` USING( `code` )
					WHERE(`pbu`.`user` = '{$user}' && `pbu`.`type` = 'add')
					OR(`pbp`.`id_profile` = '{$profile}' && IFNULL(`pbu`.`type`, 'add') <> 'sub')
					ORDER BY `m`.`order`";
			return $this->query($sql, 'named', 'code');
		}

		protected function getUserPages($profile, $user){
			$sql = "SELECT	`g`.*,
							`a`.`area`,
							`p`.`name`
					FROM `_pages` `g`
					LEFT JOIN `_permissions` `p` USING( `code` )
					LEFT JOIN `_permissions_by_profile` `pbp` USING( `code` )
					LEFT JOIN `_permissions_by_user` `pbu` USING( `code` )
					LEFT JOIN `_areas` `a` USING( `id_area` )
					WHERE(`pbu`.`user` = '{$user}' && `pbu`.`type` = 'add')
					OR(`pbp`.`id_profile` = '{$profile}' && IFNULL(`pbu`.`type`, 'add') <> 'sub')
					ORDER BY `a`.`order`, `g`.`order`";
			return $this->query($sql, 'named', 'code');
		}
		

/***************
** M O D I F Y   M E T H O D S
****************
** (INSERT, UPDATE)
***************/
			

/***************
** M O D I F Y   M E T H O D S
****************
** (DELETE)
***************/
		
	}

?>