<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	require_once( CONNECTION_PATH );
	
	

	class PDF_Estimates_SQL extends Connection{
	
		/**
		 * Gets all the info about a system that an estimate's PDF might need
		 */
		protected function SQL_getEstimateData( $id ){
			$sql = "SELECT *
					FROM `estimates`
					WHERE `id_estimate` = '{$id}'";
			return $this->query($sql, 'row');
		}
		
		protected function SQL_getEstimateDetail( $id ){
			/* Do not alter the order of fields, new ones overwrite wrong ones */
			$sql = "SELECT	`pc`.*,
							`pe`.*,
							`p`.*,
							`ed`.`price`,
							`ed`.`amount`
					FROM `estimates_detail` `ed`
					JOIN `_products` `p` USING (`id_product`)
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					WHERE `id_estimate` = '{$id}'";
			return $this->query($sql, 'named', 'id_product');
		}
		
		/**
		 * Gets all the info about a customer that an estimate's PDF might need
		 */
		protected function SQL_getCustomer( $id ){
			$sql = "SELECT	`c`.*,
							`cc`.`name` AS 'contact',
							`lc`.`location`
					FROM `customers` `c`
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					LEFT JOIN `customers_contacts` `cc` USING (`id_customer`)
					WHERE `id_customer` = '{$id}'";
			return $this->query($sql, 'row');
		}
		
		public function getInstallPlan( $id ){
			$sql = "SELECT	`ep`.*,
							IFNULL(`pe`.`code`, `p`.`name`) AS 'name'
					FROM `estimates_plan` `ep`
					LEFT JOIN `_products` `p` USING (`id_product`)
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					WHERE `ep`.`id_estimate` = '{$id}'";
			return $this->query($sql, 'array');
		}
		
		/**
		 * Gets all the info about a system that an estimate's PDF might need
		 */
		protected function SQL_getSystem( $id ){
			$sql = "SELECT *
					FROM `systems`
					WHERE `id_system` = '{$id}'";
			return $this->query($sql, 'row');
		}
		
	}