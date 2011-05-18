<?php
	
	require_once( CONNECTION_PATH );

	class Activity extends Connection{
		
		public function events( $type ){
		
			switch( $type ){
				case 'technical':
					$types = "'install', 'laststeps', 'remote', 'service', 'technical'";
					break;
				case 'sales':
					$types = "'incomes', 'delivery', 'invoice', 'travel', 'estimate', 'sales'";
					break;
				default:
					$types = '';
					break;
			}
			
			$sql = "SELECT	`e`.*,
							`ec`.`id_customer`,
							`c`.`customer`,
							`g`.`id` AS 'activity_entry',
							`g`.`model`,
							`g`.`uid`
					FROM `activity` `g`
					LEFT JOIN `events` `e` ON (`e`.`id_event` = `g`.`uid`)
					LEFT JOIN `events_customers` `ec` ON (`ec`.`id_event` = `e`.`id_event`)
					LEFT JOIN `customers` `c` ON (`c`.`id_customer` = `ec`.`id_customer`)
					WHERE NOT `g`.`handled`
					AND `g`.`model` = 'events'
					AND `e`.`type` IN ({$types})
					ORDER BY `e`.`ini`";
			return $this->query($sql, 'array');
			
		}
		
		public function notes( $type ){
		
			$sql = "SELECT	`n`.*,
							`c`.`customer`,
							`g`.`id` AS 'activity_entry',
							`g`.`uid`
					FROM `activity` `g`
					LEFT JOIN `_notes` `n` ON (`n`.`id_note` = `g`.`uid`)
					LEFT JOIN `customers` `c` ON (`c`.`id_customer` = `n`.`id_customer`)
					WHERE NOT `g`.`handled`
					AND `g`.`model` = 'notes'
					AND `n`.`type` = '{$type}'";
			return $this->query($sql, 'array');
			
		}
		
	}

?>