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


	class SQL extends Connection{

		/****************************************
		***** T A B L E   I T E M   L I S T S
		****************************************/
		
		public function getCustomers($filters=array(), $show='all'){
			# Status filter
			if( $show == 'customers' ) $statusFilter = 'AND NOT ISNULL(`since`)';
			elseif( $show == 'potential' ) $statusFilter = 'AND ISNULL(`since`)';
			else $statusFilter = '';
			# Handle possible name conflicts and composed fields
			if( isset($filters['phone']) ) $filters['c.phone'] = $filters['phone'];
			if( isset($filters['seller']) ) $filters["CONCAT(name,' ',lastName)"] = $filters['seller'];
			if( isset($filters['contact']) ) $filters['cc.name'] = $filters['contact'];
			unset($filters['contact'], $filters['seller'], $filters['phone']);
			# We're done, let's query
			$sql = "SELECT	`c`.*,
							`u`.`name`		AS 'seller_name',
							`u`.`lastName`	AS 'seller_lastName',
							`lc`.*,
							(SELECT `name` FROM `customers_contacts`
							WHERE `id_customer` = `c`.`id_customer`
							LIMIT 1) AS `contact`
					FROM `customers` `c`
					LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					WHERE {$this->array2filter($filters)}
					{$statusFilter}
					ORDER BY `c`.`customer`";
			return $this->query($sql, 'array');
		}

		/****************************************
		***** U S E R S
		****************************************/
		
		public function getUser( $user ){
			$sql = "SELECT	`u`.*,
							`p`.`profile`,
							`d`.`department`
					FROM `_users` `u`
					JOIN `_profiles` `p` USING (`id_profile`)
					JOIN `_departments` `d` USING (`id_department`)
					WHERE `u`.`user` = '{$user}'";
			return $this->query($sql, 'row');
		}
		
		public function getLocations(){
			$sql = "SELECT	`id_location`,
							`location`
					FROM `_locations`
					ORDER BY `location`";
			return $this->query($sql, 'col');
		}

		/****************************************
		***** C U S T O M E R S
		****************************************/
		
		public function getCustomer( $cust ){
			$sql = "SELECT	`c`.*,
							`u`.`name`		AS 'seller_name',
							`u`.`lastName`	AS 'seller_lastName',
							`lc`.*,
							(SELECT `name` FROM `customers_contacts`
							WHERE `id_customer` = `c`.`id_customer`
							LIMIT 1) AS `contact`
					FROM `customers` `c`
					LEFT JOIN `_users` `u` ON (`u`.`user` = `c`.`seller`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					WHERE `c`.`id_customer` = '{$cust}'";
			return $this->query($sql, 'row');
		}

		/****************************************
		***** P R O D U C T S
		****************************************/
		
		public function getProduct( $id ){
			$sql = "SELECT	`p`.*,
							`pe`.*,
							`pc`.*,
							`s`.`system`,
							`p`.`id_product`
					FROM `_products` `p`
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE `p`.`id_product` = '{$id}'";
			return $this->query($sql, 'row');
		}
		
		public function getProductSuggest( $text ){
			$sql = "SELECT	`id_product` AS 'id',
							`pc`.`type`,
							IF(ISNULL(`pe`.`code`), `p`.`name`,
								CONCAT(`pe`.`code`,' - ', `p`.`name`, ' ', `pe`.`model`)) AS 'name',
							`p`.`price`
					FROM `_products` `p`
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					WHERE(IF(ISNULL(`pe`.`code`),
						`p`.`name`,
						CONCAT(`pe`.`code`,' - ', `p`.`name`, ' ', `pe`.`model`)) LIKE '%{$text}%')
					ORDER BY `type`, `name`
					LIMIT 0, 15";
			return $this->query($sql, 'array');
		}

		/****************************************
		***** E S T I M A T E S
		****************************************/
		
		public function isProductUsedInEstimates( $id ){
			$sql = "SELECT	`e`.`estimate`
					FROM `estimates_detail` `d`
					JOIN `estimates` `e` USING (`id_estimate`)
					WHERE `d`.`id_product` = '{$id}'";
			return $this->query($sql, 'col');
		}
		
		public function getEstimate( $id ){
			$sql = "SELECT	`e`.*,
							`c`.`customer`,
							`s`.`system`
					FROM `estimates` `e`
					LEFT JOIN `customers` `c` ON (`c`.`id_customer` = `e`.`id_customer`)
					LEFT JOIN `systems` `s` ON (`s`.`id_system` = `e`.`id_system`)
					WHERE `e`.`id_estimate` = '{$id}'";
			return $this->query($sql, 'row');
		}
		
		public function getEstimateDetail( $id ){
			$sql = "SELECT	`d`.`id_product` AS 'id',
							`pc`.`type`,
							`d`.`price`,
							`d`.`amount`,
							`pe`.`id_system`,
							IF(ISNULL(`pe`.`code`), `p`.`name`,
								CONCAT(`pe`.`code`,' - ', `p`.`name`, ' ', `pe`.`model`)) AS 'name'
					FROM `estimates_detail` `d`
					LEFT JOIN `_products` `p` USING (`id_product`)
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					WHERE `d`.`id_estimate` = '{$id}'";
			return $this->query($sql, 'array');
		}
		
		/**
		 */
		public function estimateProducts( $id ){
			$sql = "SELECT	`ed`.`id_product`,
							CONCAT('(', CAST(`ed`.`amount` AS CHAR), ') ',
								IF(ISNULL(`pe`.`id_product`),
									`p`.`name`,
									CONCAT('(', `pe`.`code`, ') ', `p`.`name`,' ', `pe`.`model`))) AS 'name'
					FROM `estimates_detail` `ed`
					LEFT JOIN `_products` `p` USING (`id_product`)
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					WHERE `pc`.`type` IN('products', 'materials')
					AND `ed`.`id_estimate` = '{$id}'
					ORDER BY `name`, `pc`.`type`";
			return $this->query($sql, 'named', 'id_product');
		}
		
		public function getInstallPlan( $id ){
			$sql = "SELECT	`ep`.*,
							`ep`.`id_product`,
							CONCAT('(', CAST(`ep`.`amount` AS CHAR), ') ',
								IF(ISNULL(`pe`.`id_product`),
									`p`.`name`,
									CONCAT('(', `pe`.`code`, ') ', `p`.`name`,' ', `pe`.`model`))) AS 'name'
					FROM `estimates_plan` `ep`
					LEFT JOIN `_products` `p` USING (`id_product`)
					LEFT JOIN `_product_extension` `pe` USING (`id_product`)
					LEFT JOIN `_product_categories` `pc` USING (`id_category`)
					WHERE `pc`.`type` IN('products', 'materials')
					AND `ep`.`id_estimate` = '{$id}'";
			return $this->query($sql, 'array');
		}
		
		public function getRemainingProducts($id_estimate, $id_product){
			$sql = "SELECT IFNULL(`ed`.`amount` - IFNULL(SUM(`ep`.`amount`), 0), 0)
					FROM `estimates_detail` `ed`
					LEFT JOIN `estimates_plan` `ep` USING (`id_estimate`, `id_product`)
					WHERE `id_estimate` = '{$id_estimate}'
					AND `id_product` = '{$id_product}'";
			return $this->query($sql, 'field');
		}
		
		public function removeEntryFromPlan( $id ){
			$sql = "DELETE FROM `estimates_plan`
					WHERE `id_plan` = '{$id}'";
			return $this->modify( $sql );
		}

		/****************************************
		***** T E C H N I C A L
		****************************************/
		
		public function getCustomersForService($filters=array()){
			$this->fixFilters($filters, array(
				'phone'		=> 'c.phone',
				'address'	=> "CONCAT(c.address, ' (', lc.location, ')')",
				'contact'	=> 'cc.name',
			));
			$sql = "SELECT	`c`.*,
							`cc`.`name` AS 'contact',
							CONCAT(c.address, ' (', lc.location, ')') AS 'address'
					FROM `customers` `c`
					LEFT JOIN `_locations` `lc` USING(`id_location`)
					LEFT JOIN (	SELECT `id_customer`, `name`
								FROM `customers_contacts` `cc`
								GROUP BY `id_customer`) `cc` USING (`id_customer`)
					WHERE {$this->array2filter($filters)}";
			return $this->query($sql, 'array');
		}
		
		public function getCustomerInstalls( $id ){
			$sql = "SELECT	`l`.`id_sale` AS 'onSale',
							`l`.`invoice`,
							`l`.`notes`,
							`s`.`system`,
							DATE_FORMAT(DATE_ADD(`l`.`date`, INTERVAL `l`.`warranty` MONTH),
								'%d-%m-%Y')			AS 'warrantyVoid',
							IF(DATE_ADD(`l`.`date`, INTERVAL `l`.`warranty` MONTH) < NOW(),
								1, 0)				AS 'void'
					FROM `sales` `l`
					LEFT JOIN `sales_installs` `si` USING (`id_sale`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE `l`.`type` IN ('install', 'sale')
					AND `l`.`id_customer` = '{$id}'";
			return $this->query($sql, 'array');
		}
		
		public function getInstallForNewService( $id ){
			$sql = "SELECT	'' AS 'invoice',		/* overwrite sales' invoice */
							`c`.*,
							`c`.`number` AS 'custNumber',
							`l`.*,
							CONCAT(`c`.`address`, ' (', `lc`.`location`, ')') AS 'address',
							`s`.*,
							`cc`.`name` AS 'contact'
					FROM `sales` `l`
					LEFT JOIN `customers` `c` USING (`id_customer`)
					LEFT JOIN (	SELECT `id_customer`, `name`
								FROM `customers_contacts` `cc`
								GROUP BY `id_customer`) `cc` USING (`id_customer`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					LEFT JOIN `sales_installs` `si` USING (`id_sale`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE `l`.`id_sale` = '{$id}'";
			return $this->query($sql, 'row');
		}
		
		public function getTechVisit( $id ){
			$sql = "SELECT	`l`.*,
							`c`.*,
							`c`.`number` AS 'custNumber',
							`ss`.*,
							CONCAT(`c`.`address`, ' (', `lc`.`location`, ')') AS 'address',
							`s`.`system`,
							CONCAT(`u`.`name`, ' ', `u`.`lastName`) AS 'technicianName'
					FROM `sales` `l`
					LEFT JOIN `sales_services` `ss` USING (`id_sale`)
					LEFT JOIN `_users` `u` ON (`ss`.`technician` = `u`.`user`)
					LEFT JOIN `customers` `c` USING (`id_customer`)
					LEFT JOIN `_locations` `lc` USING (`id_location`)
					LEFT JOIN `sales_installs` `si` USING (`id_sale`)
					LEFT JOIN `systems` `s` USING (`id_system`)
					WHERE `l`.`type` = 'service'
					AND `l`.`id_sale` = '{$id}'";
			return $this->query($sql, 'row');
		}

		/****************************************
		***** A G E N D A
		****************************************/
		
		public function getEventsInfo($id=NULL, $range=array(), $filters=array()){
			# Accept single days as range and format it correctly (same ini as end)
			if( is_string($range) ) $range = array('ini' => $range, 'end' => $range);
			# Users filter needs special treatment
			$usersFilter = !empty($filters['user'])
				? "(`e`.`target` = '{$filters['user']}' OR `e`.`creator` = '{$filters['user']}')"
				: 1;
			unset( $filters['user'] );
			# Other filters are regular
			$sql = "SELECT	`e`.*,
							`c`.`id_customer`,
							`c`.`customer`,
							IF(`er`.`id_event`, `er`.`comment`, '') AS 'closed',
							IF(`er`.`id_event`, `er`.`rescheduled`, '') AS 'rescheduled'";
			if( $id ) $sql .= ",
							`er`.`user` AS 'closedBy',
							`er`.`date` AS 'closedOn'";
			$sql .= "
					FROM `events` `e`
					LEFT JOIN `events_customers` `rec` USING (`id_event`)
					LEFT JOIN `customers` `c` ON (`c`.`id_customer` = `rec`.`id_customer`)
					LEFT JOIN `events_results` `er` ON (`er`.`id_event` = `e`.`id_event`)";
			$sql .= $id
				? "	WHERE `e`.`id_event` = '{$id}'"
				: "	WHERE `e`.`ini` BETWEEN '{$range['ini']}' AND DATE_ADD('{$range['end']}', INTERVAL 1 DAY)";
			$sql .= "
					AND {$this->array2filter($filters, 'AND', 'equals')}
					AND {$usersFilter}
					ORDER BY `e`.`ini`";
			return $this->query($sql, $id === NULL ? 'array' : 'row');
		}
		
		public function getUserEvents($id, $type=NULL){
			$conds = array(	'by'	=> "`e`.`creator` = '{$id}'",
							'for'	=> "`e`.`target` = '{$id}'");
			$cond = isset($conds[$type]) ? $conds[$type] : join(' OR ', $conds);
			$sql = "SELECT	`e`.*,
							'' AS `customer`,		/* for event.tpl widget */
							IF(`er`.`id_event`, `er`.`comment`, '') AS 'closed',
							IF(`er`.`id_event`, `er`.`rescheduled`, '') AS 'rescheduled'
					FROM `events` `e`
					LEFT JOIN `events_customers` `rec` USING (`id_event`)
					LEFT JOIN `events_results` `er` ON (`er`.`id_event` = `e`.`id_event`)
					WHERE {$cond}
					ORDER BY `e`.`ini` DESC";
			return $this->query($sql, 'array');
		}
		
		public function getCustomerEvents( $id ){
			$sql = "SELECT	`e`.*,
							'' AS `customer`,		/* for event.tpl widget */
							IF(`er`.`id_event`, `er`.`comment`, '') AS 'closed',
							IF(`er`.`id_event`, `er`.`rescheduled`, '') AS 'rescheduled'
					FROM `events` `e`
					LEFT JOIN `events_customers` `rec` USING (`id_event`)
					LEFT JOIN `events_results` `er` ON (`er`.`id_event` = `e`.`id_event`)
					WHERE `id_customer` = '{$id}'
					ORDER BY `e`.`ini` DESC";
			return $this->query($sql, 'array');
		}
		
		public function getEventEditions( $id ){
			$sql = "SELECT *
					FROM `events_edition`
					WHERE `id_event` = '{$id}'";
			return $this->query($sql, 'array');
		}
		

/***************
** M O D I F Y   M E T H O D S
****************
** (INSERT, UPDATE)
***************/
		
		public function saveLastAccessDate( $user ){
			$sql = "UPDATE `_users`
					SET `last_access` = CURRENT_TIMESTAMP
					WHERE `user` = '{$user}'";
			return $this->modify( $sql );
		}
		
		public function createUsers( $data ){
			$data['pass'] = md5( $data['pass'] );
			return $this->modify( $this->array2insSQL('_users', $data) );
		}
		
		public function editUsers( $data ){
			$assignments = $this->array2updSQL( $data );
			$sql = "UPDATE `_users`
					SET {$assignments}
					WHERE `user` = '{$data['user']}'
					AND `id_profile` >= '".getSes('id_profile')."'";
			return $this->modify( $sql );
		}
		
		public function blockUsers($user, $unblock=false){
			$block = intval(!$unblock);
			$sql = "UPDATE `_users`
					SET `blocked` = '{$block}'
					WHERE `user` = '{$user}'";
			return $this->modify( $sql );
		}
		
		public function editCustomers( $data ){
			$assignments = $this->array2updSQL( $data );
			$sql = "UPDATE `customers`
					SET {$assignments}
					WHERE `id_customer` = '{$data['id_customer']}'";
			return $this->modify( $sql );
		}
		
		public function createEstimates($data, $products){
			$this->BEGIN();		/* Start transaction, estimates require a detail to be correctly saved too */
			$ans1 = $this->modify( $this->array2insSQL('estimates', $data) );
			if( $ans1->error ) return $this->ROLLBACK( $ans1 );		/* Cancel transaction and return */
			foreach( $products as $product ) $product['id_estimate'] = $ans1->ID;
			$ans2 = $this->multipleInsert($products, 'estimates_detail');
			if( $ans2->error ) return $this->ROLLBACK( $ans2 );		/* Cancel transaction and return */
			return $this->COMMIT( $ans1 );							/* Return newly created estimate's ID */
		}
		
		public function updateEstimates($data, $products, $id){
			$this->BEGIN();
			# Update entry in table estimates
			$ans1 = $this->update($data, 'estimates', array('id_estimate'));
			if( $ans1->error ) return $this->ROLLBACK( $ans1 );
			# Remove all current details
			$ans2 = $this->delete('estimates_detail', array('id_estimate' => $id));
			if( $ans2->error ) return $this->ROLLBACK( $ans2 );
			# Save new detail
			foreach( $products as &$product ) $product['id_estimate'] = $id;
			$ans3 = $this->multipleInsert($products, 'estimates_detail');
			if( $ans3->error ) return $this->ROLLBACK( $ans3 );
			return $this->COMMIT( $ans1 );
		}
		
		public function createEvent($data, $info){
			$ans1 = $this->insert($data, 'events');
			if( !$ans1->error && $info['id_customer'] ){
				$this->setErrMsg('El evento fue guardado, pero no se pudo asociar al cliente.');
				$data = array('id_event' => $ans1->ID, 'id_customer' => $info['id_customer']);
				$ans2 = $this->insert($data, 'events_customers');
				if( $ans2->error ) return $ans2;
			}
			return $ans1;
		}
		
		public function editEvent($data, $info){
			$id = $data['id_event'] = $info['id_event'];
			$ans1 = $this->update($data, 'events', array('id_event'));
			if( !$ans1->error ){
				$this->delete('events_customers', array('id_event' => $id));
				if( $info['id_customer'] ){
					$this->setErrMsg('El evento fue guardado, pero no se pudo asociar al cliente.');
					$data = array('id_event' => $id, 'id_customer' => $info['id_customer']);
					$ans2 = $this->insert($data, 'events_customers');
					if( $ans2->error ) return $ans2;
				}
			}
			# Record edition (which event, by whom and on what date/time)
			$this->insert($info['lastEdit'], 'events_edition');
			return $ans1;
		}
		
		public function closeAgendaEvent( $data ){
			return $this->modify( $this->array2insSQL('events_results', $data) );
		}



/****************************************
***** L O G S   A N D   A L E R T S
****************************************/
		
		public function isAlertActive( $id ){
			$sql = "SELECT `inUse`
					FROM `alerts_types`
					WHERE `id_type` = '{$id}'";
			return $this->query($sql, 'field');
		}
		
		public function registerLog($table, $data){
			return $this->modify( $this->array2insSQL($table, $data) );
		}
		
		public function getMostRecentLog(){
			$sql = "SELECT MAX(`id_log`) FROM `logs`";
			return $this->query($sql, 'field');
		}
		
		public function removeOldAlerts($user, $keep=20){
			$sql = "SELECT `id_log`
					FROM `alerts_unread`
					WHERE `user` = '{$user}'
					ORDER BY `id_log` DESC
					LIMIT {$keep}";
			$newest = $this->query($sql, 'list');
			$sql = "DELETE FROM `alerts_unread`
					WHERE `user` = '{$user}'
					AND `id_log` NOT IN ({$newest})";
			return $this->modify( $sql );
		}
		
		public function removeOldLogs( $maxLogs=10000 ){
			$sql = "SELECT MAX(`id_log`)
					FROM `alerts_unread`";
			$max = (int)$this->query($sql, 'field') - $maxLogs;
			$sql = "DELETE FROM `logs`
					WHERE `id_log` < {$max}";
			return $this->modify( $sql );
		}
		
		/**
		 * Get all unread alerts from logs table, starting from the last
		 * alert read by the user, and dump it in table alerts_unread.
		 */
		public function updateUserAlerts( $user ){
			$sql = "INSERT IGNORE INTO `alerts_unread`
					SELECT	'{$user}' AS 'user',
							`id_log`
					FROM `logs`
					WHERE `user` <> '{$user}'
					AND `logType` IN (
						SELECT `id_type`
						FROM `alerts`
						WHERE `user` = '{$user}'
					)
					AND `id_log` > (
						SELECT `lastSeenLog`
						FROM `_users`
						WHERE `user` = '{$user}'
					)
					ORDER BY `id_log`";
			return $this->modify( $sql );
		}
		
		public function updateLastSeenLog($user, $lastSeenLog){
			$sql = "UPDATE `_users`
					SET `lastSeenLog` = '{$lastSeenLog}'
					WHERE `user` = '{$user}'";
			return $this->modify( $sql );
		}
		
		public function getUserAlerts($user, $startingAt=0){
			$sql = "SELECT * FROM(
						SELECT `l`.*
						FROM `alerts_unread` `au`
						JOIN `logs` `l` ON (`l`.`id_log` = `au`.`id_log`)
						WHERE `au`.`user` = '{$user}'
						AND `l`.`id_log` > '{$startingAt}'
						AND `l`.`logType` <> 'lastRead'
						ORDER BY `id_log` DESC
						LIMIT 100
					) `temp`
					ORDER BY `id_log`";
			return $this->query($sql, 'array');
		}
		
		public function removeAlert( $id ){
			$sql = "DELETE FROM `alerts_unread`
					WHERE `user` = '".getSes('user')."'
					AND `id_log` = '{$id}'";
			return $this->modify( $sql );
		}
		
		public function removeAllAlerts( $user ){
			$sql = "DELETE FROM `alerts_unread`
					WHERE `user` = '{$user}'";
			return $this->modify( $sql );
		}
			

/***************
** M O D I F Y   M E T H O D S
****************
** (DELETE)
***************/
		
		public function deleteUsers( $user ){
			$sql = "DELETE FROM `_users`
					WHERE `user` = '{$user}'
					LIMIT 1";
			return $this->modify( $sql );
		}
		
		public function deleteCustomers( $id ){
			$sql = "DELETE FROM `customers`
					WHERE `id_customer` = '{$id}'
					LIMIT 1";
			return $this->modify( $sql );
		}
		
		public function deleteProducts( $id ){
			$sql = "DELETE FROM `_products`
					WHERE `id_product` = '{$id}'
					LIMIT 1";
			return $this->modify( $sql );
		}
		
		public function deleteEstimates( $id ){
			$this->BEGIN();
			$sql = "DELETE FROM `estimates_detail`
					WHERE `id_estimate` = '{$id}'";
			$ans1 = $this->modify( $sql );
			if( $ans1->error ) return $this->ROLLBACK( $ans1 );
			$sql = "DELETE FROM `estimates_plan`
					WHERE `id_estimate` = '{$id}'";
			$ans2 = $this->modify( $sql );
			if( $ans2->error ) return $this->ROLLBACK( $ans2 );
			$sql = "DELETE FROM `estimates`
					WHERE `id_estimate` = '{$id}'
					LIMIT 1";
			$ans3 = $this->modify( $sql );
			if( $ans3->error ) return $this->ROLLBACK( $ans3 );
			return $this->COMMIT( $ans1 );
		}
		

/***************
** A P P   &   S E C U R I T Y
***************/

		public function getLastLoginLogout( $user ){
			$sql = "(	SELECT `objectID`, `date` FROM `logs`
						WHERE `logType` = 'loginLogout' AND `user` = '{$user}')
					UNION
					(	SELECT `objectID`, `date` FROM `logs_history`
						WHERE `logType` = 'loginLogout' AND `user` = '{$user}')
					ORDER BY `date` DESC
					LIMIT 1";
			return $this->query($sql, 'field');
		}
		
		public function attemptLogin($user, $pass){
			$sql = "SELECT	`u`.*,
							`p`.`profile`,
							`d`.`department`
					FROM `_users` `u`
					JOIN `_profiles` `p` USING (`id_profile`)
					JOIN `_departments` `d` USING (`id_department`)
					WHERE `u`.`user` = '{$user}'
					AND `u`.`pass` = MD5('{$pass}')";
			return $this->query($sql, 'row');
		}
		
	}

?>