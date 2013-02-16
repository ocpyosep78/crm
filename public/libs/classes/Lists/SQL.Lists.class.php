<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/**
 * This SQL class specializes in object lists. I.e. products, users, customers...
 * It is an SQL Connection tool for class Lists (which extends this one).
 *
 * To present a list of items of a particular object, there's always 2 queries
 * required ($obj stands for that object's code):
 *	~ Lists: {$obj}List([array $filters])
 *		Returns a named array with all rows needed for a list (key for each row
 *		is the item's id). This is used for the item's tabular list.
 *	~ Hashes: {$obj}()
 *		Returns a 1-dimensional array, $id => $value, where $id is the item's
 *		id and $value. This one's used for combos to jump fast to a certain item,
 *		so $value should be descriptive (could be several fields concatenated).
 *		Note: Hashes are used in other pages, like {$obj}Info, edit{$obj}, etc.
 * It is the programmer's task to create these two queries and return meaningfull
 * results. Creating them right should be enough for this class' duties.
 *
 * See List class for additional info to get a list running.
 */

	require_once( CONNECTION_PATH );


	class SQL_Lists extends Connection{

/***************
** H A S H E S
***************/

		public function customers( $show='all' ){
			if( $show == 'customers' ) $statusFilter = 'NOT ISNULL(`since`)';
			elseif( $show == 'potential' ) $statusFilter = 'ISNULL(`since`)';
			else $statusFilter = '1';
			$sql = "SELECT	`id_customer`,
							`customer`
					FROM `customers`
					WHERE {$statusFilter}
					ORDER BY `customer`";
			return $this->asHash( $sql );
		}

		public function departments(){
			$sql = "SELECT	`id_department`,
							`department`
					FROM `_departments`";
			return $this->asHash( $sql );
		}

		public function estimates( $modifier='all' ){
			$conds = array('quotes' => '', 'estimates' => 'NOT');
			$cond = isset($conds[$modifier]) ? $conds[$modifier] : '1 OR';
			$sql = "SELECT	`id_estimate`,
							`estimate`
					FROM `estimates`
					WHERE {$cond} (
						ISNULL(`orderNumber`)
						OR ISNULL(`id_customer`)
						OR ISNULL(`id_system`)
					)
					ORDER BY `estimate`";
			return $this->asHash( $sql );
		}

		public function installers(){
			$sql = "SELECT	`id_installer`,
							IF(`company` <> '', `company`, `installer`) AS 'installer'
					FROM `installers`";
			return $this->asHash( $sql );
		}

		public function installs(){
			$sql = "SELECT	`si`.`id_sale`,
							CONCAT(
								DATE_FORMAT(`l`.`date`, '%d/%m/%Y - '),
								CONCAT(`c`.`customer`, ' (', `s`.`system`, ')')
							),
							`s`.*
					FROM `sales` `l`
					LEFT JOIN `sales_installs` `si` USING (`id_sale`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE `l`.`type` = 'install'
					ORDER BY `l`.`date`, `s`.`system`";
			return $this->asHash( $sql );
		}

		public function products( $type=NULL ){
			$typeCond = is_null($type) ? '1' : "`pc`.`type` = '{$type}'";
			$sql = "SELECT	`id_product` AS 'value',
							IF(ISNULL(`pe`.`id_product`),
								`p`.`name`,
								CONCAT('(', `pc`.`category`, ') ',
									CONCAT(`pe`.`code`,' - ', `p`.`name`, ' ', `pe`.`model`),
									`p`.`name`)) AS 'name'
					FROM `_products` `p`
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE {$typeCond}
					ORDER BY `name`";
			return $this->asHash( $sql );
		}

		public function product_categories( $type=NULL ){
			$typeCond = is_null($type) ? '1' : "`pc`.`type` = '{$type}'";
			$sql = "SELECT	`pc`.`id_category`,
							`pc`.`category`
					FROM `_product_categories` `pc`
					WHERE {$typeCond}
					ORDER BY `pc`.`category`";
			return $this->asHash( $sql );
		}

		public function profiles( $minProfile=1 ){
			$sql = "SELECT	`id_profile`,
							`profile`
					FROM `_profiles`
					WHERE `id_profile` >= '{$minProfile}'";
			return $this->asHash( $sql );
		}

		public function sales(){
			$sql = "SELECT	`l`.`id_sale`,
							CONCAT(
								'(', DATE_FORMAT(`l`.`date`, '%d/%m/%Y'), ') ',
								'Fact. ', `l`.`invoice`, ' - ',
								`c`.`customer`
							) AS 'info'
					FROM `sales` `l`
					LEFT JOIN `customers` `c` USING (`id_customer`)
					ORDER BY `l`.`date`, CONVERT(`l`.`invoice`, UNSIGNED INTEGER)";
			return $this->asHash( $sql );
		}

		public function sellers(){	/* same as ::users, with different indexes and sorting */
			$sql = "SELECT	`user`								AS 'seller',
							CONCAT(`name`, ' ', `lastName`)		AS 'seller'
					FROM `_users`
					ORDER BY `name`";
			return $this->asHash( $sql );
		}

		public function systems(){
			$sql = "SELECT	`id_system`,
							`system`
					FROM `systems`
					ORDER BY `system`";
			return $this->asHash( $sql );
		}

		public function techVisits(){
			$sql = "SELECT	`ss`.`id_sale`,
							CONCAT(
								DATE_FORMAT(`l`.`date`, '%d/%m/%Y'),
								IF(ISNULL(`ss`.`number`), ' (sin número)', CONCAT(' (visita #', `ss`.`number`, ')')),
								' - ', `c`.`customer`
							)
					FROM `sales` `l`
					LEFT JOIN `sales_services` `ss` USING (`id_sale`)
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE `l`.`type` = 'service'
					ORDER BY `l`.`date`";
			return $this->asHash( $sql );
		}

		public function technicians(){
			$sql = "SELECT	`u`.`user`,
							CONCAT(`u`.`name`, ' ', `u`.`lastName`) AS 'technician'
					FROM `_users` `u`
					JOIN `_departments` `d` USING (`id_department`)
					WHERE `d`.`department` = 'Técnica'";
			return $this->asHash( $sql );
		}

/***************
** L I S T S
***************/

		public function customersList($filters=array(), $show='all'){

			# Modifier specifics ($show)
			if( $show == 'customers' ) $statusFilter = 'AND NOT ISNULL(`since`)';
			elseif( $show == 'potential' ) $statusFilter = 'AND ISNULL(`since`)';
			else $statusFilter = '';
			# Handle possible name conflicts and composed fields
			$this->fixFilters($filters, array(
				'address' => '`c`.`address`',
				'phone' => '`c`.`phone`',
				'sellerName' => "CONCAT(`u`.`name`,' ',`u`.`lastName`)"
			));
			$sql = "SELECT	`c`.*,
							`u`.`name`		AS 'seller_name',
							`u`.`lastName`	AS 'seller_lastName',
							CONCAT(`u`.`name`,' ',`u`.`lastName`) AS 'sellerName',
							`lc`.*
					FROM `customers` `c`
					LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					WHERE {$this->array2filter($filters)}
					{$statusFilter}
					ORDER BY `c`.`customer`";
			return $this->asList($sql, 'id_customer');
		}

		public function estimatesList($filters=array(), $modifier='all', $compare='LIKE'){
			$conds = array('quotes' => '', 'estimates' => 'NOT');
			$cond = isset($conds[$modifier]) ? $conds[$modifier] : '1 OR';
			$sql = "SELECT	`e`.*,
							`c`.`customer`,
							`s`.`system`
					FROM `estimates` `e`
					LEFT JOIN `customers` `c` USING (`id_customer`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE {$this->array2filter($filters, 'AND', $compare)}
					AND ({$cond} (
						ISNULL(`e`.`orderNumber`)
						OR ISNULL(`e`.`id_customer`)
						OR ISNULL(`e`.`id_system`)
					))
					ORDER BY `orderNumber`";
			return $this->asList($sql, 'id_estimate');
		}

		public function installsList( $filters=array() ){
			# Handle possible name conflicts and composed fields
			$this->fixFilters($filters, array(
				'date'		=> "DATE_FORMAT(`l`.`date`, '%d/%m/%Y')",
			));
			$sql = "SELECT	`l`.*,
							`si`.*,
							DATE_FORMAT(`l`.`date`, '%d/%m/%Y') AS 'date',
							`t`.`installer`,
							`c`.`customer`,
							`s`.`system`
					FROM `sales` `l`
					LEFT JOIN `sales_installs` `si` USING (`id_sale`)
					LEFT JOIN `installers` `t` USING (`id_installer`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE `l`.`type` = 'install'
					AND {$this->array2filter($filters)}
					ORDER BY `l`.`date`";
			return $this->asList($sql, 'id_sale');
		}

		public function productsList($filters=array(), $type=NULL){
			# Modifier specifics ($type)
			$typeCond = is_null($type) ? '1' : "`pc`.`type` = '{$type}'";
			# Handle possible name conflicts and composed fields
			$this->fixFilters($filters, array(
				'code' => 'pe.code',
			));
			$sql = "SELECT	`p`.`id_product` AS 'id',
							`p`.*,
							`pe`.*,
							`pc`.*,
							`s`.`system`
					FROM `_products` `p`
					JOIN `_product_categories` `pc` USING (`id_category`)
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE {$typeCond}
					AND {$this->array2filter($filters)}
					ORDER BY `pc`.`category`, `pe`.`code`, `p`.`name`";
			return $this->asList($sql, 'id');
		}

		public function salesList($filters=array(), $modifier=NULL){
			$cond = isset($modifier) ? "`type` = '{$modifier}'" : '1';
			# Handle possible name conflicts and composed fields
			$this->fixFilters($filters, array(
				'date'		=> "DATE_FORMAT(`l`.`date`, '%d/%m/%Y')",
			));
			$sql = "SELECT	`l`.*,
							DATE_FORMAT(`l`.`date`, '%d/%m/%Y') AS 'date',
							`c`.`customer`
					FROM `sales` `l`
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE {$this->array2filter($filters)}
					AND `type` = '{$modifier}'
					ORDER BY `l`.`date`, CONVERT(`l`.`invoice`, UNSIGNED INTEGER)";
			return $this->asList($sql, 'id_sale');
		}

		public function techVisitsList( $filters=array() ){
			# Handle possible name conflicts and composed fields
			$this->fixFilters($filters, array(
				'number'	=> 'ss.number',
				'date'		=> "DATE_FORMAT(`l`.`date`, '%d/%m/%Y')",
			));
			$sql = "SELECT	`l`.`invoice`,
							`l`.`currency`,
							`l`.`cost`,
							`ss`.*,
							IFNULL(`ss`.`number`, '(sin número)') AS 'number',
							DATE_FORMAT(`l`.`date`, '%d/%m/%Y') AS 'date',
							CONCAT(`u`.`name`, ' ', `u`.`lastName`) AS 'technician',
							CONCAT(`ss`.`starts`, ' a ', `ss`.`ends`) AS 'period',
							`c`.`customer`,
							`c`.`number` AS 'custNumber'
					FROM `sales` `l`
					LEFT JOIN `sales_services` `ss` USING (`id_sale`)
					LEFT JOIN `_users` `u` ON (`ss`.`technician` = `u`.`user`)
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE `l`.`type` = 'service'
					AND {$this->array2filter($filters)}
					ORDER BY `l`.`date`";
			return $this->asList($sql, 'id_sale');
		}

		public function usersList( $filters=array() ){
			$this->fixFilters($filters, array(
				'fullName' => "CONCAT(`u`.`name`, ' ', `u`.`lastName`)",
			));
			$sql = "SELECT	`u`.*,
							`p`.`profile`,
							`d`.`department`,
							IF(`blocked`, 'Bloqueado', 'En uso') AS `blocked`,
							CONCAT(`u`.`name`, ' ', `u`.`lastName`) AS 'fullName',
							`blocked` AS `blockedCode`
					FROM `_users` `u`
					JOIN `_profiles` `p` USING (`id_profile`)
					JOIN `_departments` `d` USING (`id_department`)
					WHERE {$this->array2filter($filters)}
					AND {$this->array2filter($filters)}
					AND NOT `blocked`
					ORDER BY `u`.`user`";
			return $this->asList($sql, 'user');
		}

/***************
** S I M P L E   L I S T S
***************/

		public function customerContactsSL($filters, $custID){
			$sql = "SELECT *
					FROM `customers_contacts`
					WHERE `id_customer` = '{$custID}'";
			return $this->asList($sql, 'id_contact');
		}

		public function customerOwnersSL($filters, $custID){
			$sql = "SELECT *
					FROM `customers_owners`
					WHERE `id_customer` = '{$custID}'";
			return $this->asList($sql, 'id_owner');
		}

		public function notesSL( $filters=array() ){
			$sql = "SELECT	`n`.`id_note`,
							`n`.`type`,
							IF(`type` = 'technical', 'Técnica', 'Ventas') AS 'typeName',
							`n`.`note`,
							`n`.`user`,
							`n`.`by`,
							DATE_FORMAT(`n`.`date`, '%d/%m/%Y') AS 'date',
							`c`.`id_customer`,
							`c`.`customer`
					FROM `_notes` `n`
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE {$this->array2filter($filters)}";
			return $this->asList($sql, 'id_note');
		}

/***************
** REUSED LISTS (lists read through setting alternative sources, for filters or other reasons)
***************/

		private function byCustomer($filters, $id, $code, $modifier=NULL){
			return $this->{"{$code}List"}($filters + array('id_customer' => array($id, '=')), $modifier);
		}

		public function estimatesByCustomerList($filters, $id){
			return $this->byCustomer($filters, $id, 'estimates', 'all');
		}

		public function installsByCustomerList($filters, $id){
			return $this->byCustomer($filters, $id, 'installs');
		}

		public function notesByUserSL($filters, $id){
			return $this->notesSL($filters + array('user' => array($id, '=')));
		}

		public function notesByCustomerSL($filters, $ids){
			# $ids is a concatenation of customer ID and user ID (latter is optional)
			$idsArr = explode('__|__', $ids, 2);
			$idsFilter['id_customer'] = array($idsArr[0], '=');
			if( isset($idsArr[1]) ){
				$idsFilter['*literal'] = "(`user` = '{$idsArr[1]}' OR ISNULL(`user`))";
			}
			return $this->notesSL($filters + $idsFilter);
		}

		public function salesByCustomerList($filters, $id){
			return $this->byCustomer($filters, $id, 'sales', 'sale');
		}

		public function techVisitsByCustomerList($filters, $id){
			return $this->byCustomer($filters, $id, 'techVisits');
		}

		public function customersByUserList($filters, $id){
			return $this->customersList($filters + array('seller' => array($id, '=')));
		}

	}