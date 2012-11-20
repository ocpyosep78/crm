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


	class SQL_Config extends Connection{

		public function getPermitsByProfiles($id, $filter='%'){
			$sql = "SELECT	IF(`pbp`.`id_profile`, 1, 0) AS 'enabled',
							`p2`.`name` AS 'module',
							`p`.*
					FROM `_permissions` `p`
					LEFT JOIN `_permissions_by_profile` `pbp`
						ON (`pbp`.`code` = `p`.`code` AND `pbp`.`id_profile` = '{$id}')
					LEFT JOIN `_pages` `g` ON (`g`.`code` = `p`.`code`)
					LEFT JOIN `_permissions` `p2` ON (`p2`.`code` = `g`.`module`)
					WHERE (ISNULL(`pbp`.`id_profile`) OR `pbp`.`id_profile` = '{$id}')
					AND `p`.`type` LIKE '{$filter}'
					ORDER BY `name`";
			return $this->query($sql, 'array');
		}

		public function getPermitsByUsers($id, $filter=array()){
			$sql = "SELECT	IF(`id_profile`, 1, 0) AS 'enabled',
							`p`.*
					FROM `_permissions` `p`
					LEFT JOIN `_permissions_by_profile` `pbp` USING (`code`)
					WHERE `pbp`.`id_profile` = '{$id}')
					AND {$this->array2filter($filter)}
					GROUP BY `code`
					ORDER BY `name`";
			return $this->query($sql, 'array');
		}

		public function addProfilePermission($id, $code){
			$sql = "INSERT IGNORE INTO `_permissions_by_profile`
					(`code`, `id_profile`)
					VALUES('{$code}', '{$id}')";
			return $this->modify( $sql );
		}

		public function removeProfilePermission($id, $code){
			$sql = "DELETE FROM `_permissions_by_profile`
					WHERE `code` = '{$code}'
					AND `id_profile` = '{$id}'
					LIMIT 1";
			return $this->modify( $sql );
		}

		public function addUserPermission($id, $code)
		{
			throw new Exception('Not implemented yet');

			$sql = "INSERT IGNORE INTO `_permissions_by_user`
					(`code`, `user`, `type`)
					VALUES('{$code}', '{$id}', 'add')";
			return $this->modify( $sql );
		}

		public function removeUserPermission($id, $code)
		{
			throw new Exception('Not implemented yet');

			$sql = "DELETE FROM `_permissions_by_user`
					WHERE `code` = '{$code}'
					AND `id_profile` = '{$id}'
					LIMIT 1";
			return $this->modify( $sql );
		}

	}