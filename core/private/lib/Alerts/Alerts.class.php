<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	class Alerts{
		
		private $user;
		private $logs;
		private $alerts;
		
		public function __construct( $user ){
		
			$this->user = $user;
		
		}
		
/**
 * A L E R T S   H A N D L I N G
 * Finding, saving and processing user alerts
 */
		
		public function browseLogs( $startingAt=0 ){
			
			$lastSeenLog = oSQL()->getMostRecentLog();
			oSQL()->updateUserAlerts( $this->user );
			oSQL()->updateLastSeenLog($this->user, $lastSeenLog);
			
			$this->logs = oSQL()->getUserAlerts($this->user, $startingAt);
			
		}
		
		public function processLogs(){

			foreach( $this->logs as $alert ){
				if( !($msg=$this->buildAlertMsgs($alert)) ) continue;
				$alerts[$alert['id_log']] = array(
					'id'	=> $alert['id_log'],
					'type'	=> $alert['logType'],
					'date'	=> date('(d-m-Y H:i:s) ', strtotime($alert['date'])),
					'msg'	=> $msg,
				);
			}
			
			$this->alerts = isset($alerts) ? $alerts : array();
			
		}
		
		public function getAlerts(){
		
			return $this->alerts;
		
		}
		
		/**
		 * Build an apropriate message for the user, according to the event type
		 * and other attributes. If a type is not specifically handled, a default
		 * message is built (not recommended).
		 */
		private function buildAlertMsgs( $alert ){
		
			$objID = $alert['objectID'];
			$user = $alert['user'];
			$extra = str_replace(array("\r\n", "\r", "\n"), " / ", $alert['extra']);
			
			switch( $alert['logType'] ){
				case 'loginLogout':
					$data = oSQL()->getUser( $user );
					if( $data['id_profile'] < getSes('id_profile') ) break;
					switch( $objID ){
						case 'in':
							$msg = "{$user} ha iniciado sesión.";
						break;
						case 'out':
							$opt = ($extra == 'timed out') ? ' (por inactividad)' : '';
							$msg = "La sesión de {$user} ha finalizado{$opt}";
						break;
					}
				break;
				case 'agendaEventCreated':
					$event = "<span class='linkLike' onclick='xajax_eventInfo({$objID})'>evento</span>";
					$msg = "{$user} ha creado un nuevo {$event} en la Agenda: '{$extra}'";
					$event = oSQL()->getEventsInfo($objID);
					if($event['target'] == getSes('user') && time() - strtotime($event['created']) < 30){
						addScript("xajax_eventInfo('{$objID}');");
					}
				break;
				case 'agendaEventEdited':
					$event = "<span class='linkLike' onclick='xajax_eventInfo({$objID})'>evento</span>";
					$msg = "{$user} ha editado un {$event} en la Agenda: '{$extra}'";
				break;
				case 'agendaEventClosed':
					$event = "<span class='linkLike' onclick='xajax_eventInfo({$objID})'>evento</span>";
					$msg = "{$user} ha cerrado un {$event} en la Agenda";
				break;
				default:
					$msg = "Usuario: {$user} | Evento: {$alert['logType']} | ID: {$objID}";
					if( $extra ) $msg .= " | Additional info: {$extra}";
				break;
			}
			
			return isset($msg) ? $msg : '';
			
		}
		
		
/**
 * A L E R T S   S E T T I N G S
 * Configuring user's alerts
 */
		
		public function getAlertTypes( $queryType ){
		
			switch( $queryType ){
				case 'selected': return $this->getSelected(); break;
				case 'unselected': return $this->getUnselected(); break;
				case 'available': return $this->getAvailable(); break;
				case 'unavailable': return $this->getUnavailable(); break;
				default: return $this->getAll(); break;
			}
		
		}
			
		private function getSelected(){
			
			
			
		}
		
		private function getUnselected(){
			
			
			
		}
		
		private function getAvailable(){
			
			
			
		}
		
		private function getUnavailable(){
			
			
			
		}
		
		private function getAll(){
			
			
			
		}
		
		private function getAllAlerts(){
			
		}
		
	}
	
?>