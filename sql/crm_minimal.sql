SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;



DROP TABLE IF EXISTS `alerts`;
CREATE TABLE IF NOT EXISTS `alerts` (
  `user` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `id_type` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`user`,`id_type`),
  KEY `fk_user` (`user`),
  KEY `fk_id_type` (`id_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `alerts_types`;
CREATE TABLE IF NOT EXISTS `alerts_types` (
  `id_type` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `type` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  `inUse` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `alerts_types` (`id_type`, `type`, `inUse`) VALUES
('agendaEventClosed', 'Cierre de eventos en la Agenda', 1),
('agendaEventCreated', 'Creación de eventos en la Agenda', 1),
('agendaEventEdited', 'Edición de eventos en la Agenda', 1),
('loginLogout', 'Logueo y deslogueo de usuarios', 1);

DROP TABLE IF EXISTS `alerts_unread`;
CREATE TABLE IF NOT EXISTS `alerts_unread` (
  `user` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `id_log` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user`,`id_log`),
  KEY `fk_user2` (`user`),
  KEY `fk_au_id_log` (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id_customer` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(10) COLLATE latin1_spanish_ci DEFAULT NULL,
  `customer` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `legal_name` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `rut` varchar(15) COLLATE latin1_spanish_ci DEFAULT NULL,
  `address` varchar(50) COLLATE latin1_spanish_ci NOT NULL,
  `id_location` mediumint(8) unsigned NOT NULL,
  `phone` varchar(15) COLLATE latin1_spanish_ci NOT NULL,
  `email` varchar(60) COLLATE latin1_spanish_ci NOT NULL,
  `seller` varchar(20) COLLATE latin1_spanish_ci DEFAULT NULL,
  `since` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subscribed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_customer`),
  UNIQUE KEY `customer` (`customer`),
  UNIQUE KEY `number` (`number`),
  KEY `fk_c_id_location` (`id_location`),
  KEY `fk_c_seller` (`seller`),
  KEY `subscribed` (`subscribed`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=2 ;


DROP TABLE IF EXISTS `customers_contacts`;
CREATE TABLE IF NOT EXISTS `customers_contacts` (
  `id_customer` mediumint(8) unsigned NOT NULL,
  `name` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `phone` varchar(15) COLLATE latin1_spanish_ci NOT NULL,
  `email` varchar(60) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_customer`,`name`),
  KEY `fk_id_customer` (`id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `customers_owners`;
CREATE TABLE IF NOT EXISTS `customers_owners` (
  `id_customer` mediumint(8) unsigned NOT NULL,
  `docNum` int(10) unsigned DEFAULT NULL,
  `name` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `phone` varchar(15) COLLATE latin1_spanish_ci NOT NULL,
  `email` varchar(60) COLLATE latin1_spanish_ci NOT NULL,
  `address` varchar(50) COLLATE latin1_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_customer`,`name`),
  KEY `fk_co_id_customer` (`id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `estimates`;
CREATE TABLE IF NOT EXISTS `estimates` (
  `id_estimate` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderNumber` int(10) unsigned DEFAULT NULL,
  `estimate` varchar(50) COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `id_customer` mediumint(8) unsigned DEFAULT NULL,
  `id_system` smallint(5) unsigned DEFAULT NULL,
  `estimateDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estimate`),
  UNIQUE KEY `orderNumber` (`orderNumber`),
  KEY `fk_customer` (`id_customer`),
  KEY `fk_id_system` (`id_system`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `estimates_detail`;
CREATE TABLE IF NOT EXISTS `estimates_detail` (
  `id_estimate` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `amount` int(11) NOT NULL,
  UNIQUE KEY `ed_unique` (`id_estimate`,`id_product`),
  KEY `fk_ed_id_estimate` (`id_estimate`),
  KEY `fk_ed_id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `estimates_plan`;
CREATE TABLE IF NOT EXISTS `estimates_plan` (
  `id_plan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_estimate` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `amount` smallint(5) unsigned NOT NULL,
  `position` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_plan`),
  KEY `fk_ep_id_estimate` (`id_estimate`,`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci COMMENT='Plan de Obras (work plan)' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id_event` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(500) COLLATE latin1_spanish_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `target` varchar(20) COLLATE latin1_spanish_ci DEFAULT NULL,
  `creator` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ini` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` timestamp NULL DEFAULT NULL,
  `type` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_event`),
  KEY `starts` (`ini`),
  KEY `active` (`active`),
  KEY `fk_e_creator` (`creator`),
  KEY `fk_e_target` (`target`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `events_customers`;
CREATE TABLE IF NOT EXISTS `events_customers` (
  `id_event` int(10) unsigned NOT NULL,
  `id_customer` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id_event`,`id_customer`),
  KEY `fk_ec_id_event` (`id_event`),
  KEY `fk_ec_id_customer` (`id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `events_edition`;
CREATE TABLE IF NOT EXISTS `events_edition` (
  `id_event` int(10) unsigned NOT NULL,
  `by` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_event`,`on`),
  KEY `fk_ee_id_event` (`id_event`),
  KEY `fk_ee_by` (`by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `events_results`;
CREATE TABLE IF NOT EXISTS `events_results` (
  `id_event` int(10) unsigned NOT NULL,
  `user` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` varchar(500) COLLATE latin1_spanish_ci NOT NULL,
  `rescheduled` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_event_UNIQUE` (`id_event`),
  KEY `fk_er_user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `installers`;
CREATE TABLE IF NOT EXISTS `installers` (
  `id_installer` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `installer` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `phone` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `company` varchar(50) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_installer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logType` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `objectID` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `user` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `extra` varchar(300) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `logType` (`logType`),
  KEY `date` (`date`),
  KEY `fk_l_user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `logs_history`;
CREATE TABLE IF NOT EXISTS `logs_history` (
  `id_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logType` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `objectID` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `user` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `extra` varchar(300) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `logType` (`logType`),
  KEY `date` (`date`),
  KEY `fk_l_user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `id_sale` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('sale','install','service') COLLATE latin1_spanish_ci NOT NULL DEFAULT 'sale',
  `id_customer` mediumint(8) unsigned NOT NULL,
  `id_system` smallint(5) unsigned DEFAULT NULL,
  `invoice` varchar(7) COLLATE latin1_spanish_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `currency` enum('$','U$S') COLLATE latin1_spanish_ci DEFAULT NULL,
  `cost` int(11) DEFAULT NULL,
  `warranty` tinyint(3) unsigned DEFAULT NULL COMMENT 'months',
  `contact` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `notes` varchar(150) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_sale`),
  KEY `fk_l_id_customer` (`id_customer`),
  KEY `type` (`type`),
  KEY `invoice` (`invoice`),
  KEY `date` (`date`),
  KEY `contact` (`contact`),
  KEY `fk_l_id_system` (`id_system`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `sales_installs`;
CREATE TABLE IF NOT EXISTS `sales_installs` (
  `id_sale` int(10) unsigned NOT NULL,
  `id_installer` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id_sale`),
  KEY `fk_si_id_sale` (`id_sale`),
  KEY `fk_si_id_installer` (`id_installer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `sales_services`;
CREATE TABLE IF NOT EXISTS `sales_services` (
  `id_sale` int(10) unsigned NOT NULL,
  `onSale` int(10) unsigned DEFAULT NULL,
  `technician` varchar(20) COLLATE latin1_spanish_ci DEFAULT NULL,
  `number` mediumint(8) unsigned DEFAULT NULL,
  `starts` time DEFAULT NULL,
  `ends` time DEFAULT NULL,
  `reason` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  `outcome` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  `quality` enum('bad','regular','good','excellent') COLLATE latin1_spanish_ci DEFAULT NULL,
  `order` mediumint(9) DEFAULT NULL,
  `complete` tinyint(1) DEFAULT NULL,
  `ifIncomplete` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  `usedProducts` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  `pendingEstimate` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sale`),
  UNIQUE KEY `visitNumber_UNIQUE` (`number`),
  KEY `fk_t_id_sale` (`id_sale`),
  KEY `fk_t_technician` (`technician`),
  KEY `visitNumber` (`number`),
  KEY `visitDate` (`starts`),
  KEY `visitQuality` (`quality`),
  KEY `visitOrder` (`order`),
  KEY `complete` (`complete`),
  KEY `pendingEstimate` (`pendingEstimate`),
  KEY `fk_t_onSale` (`onSale`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `systems`;
CREATE TABLE IF NOT EXISTS `systems` (
  `id_system` smallint(6) unsigned NOT NULL,
  `code` varchar(10) COLLATE latin1_spanish_ci NOT NULL,
  `system` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_system`),
  UNIQUE KEY `productArea` (`system`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `systems` (`id_system`, `code`, `system`) VALUES
(1, 'cctv', 'Sistemas de CCTV'),
(2, 'fire', 'Detección de Incendios'),
(3, 'central', 'Centrales Telefónicas'),
(4, 'domotics', 'Domótica'),
(5, 'alarms', 'Sistema de Alarmas'),
(6, 'access', 'Controles de Acceso'),
(7, 'cable', 'Cableado Estructurado');

DROP TABLE IF EXISTS `_areas`;
CREATE TABLE IF NOT EXISTS `_areas` (
  `id_area` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `area` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (`id_area`),
  KEY `menu_order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `_areas` (`id_area`, `area`, `order`) VALUES
('', '', 0),
('agenda', 'Agenda', 100),
('config', 'Administración', 20),
('contact', 'Contacto', 80),
('global', 'CRM', 255),
('lists', 'Listados', 40);

DROP TABLE IF EXISTS `_departments`;
CREATE TABLE IF NOT EXISTS `_departments` (
  `id_department` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_department`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=8 ;

INSERT INTO `_departments` (`id_department`, `department`) VALUES
(1, 'Ventas'),
(2, 'Gerencia'),
(3, 'Técnica'),
(4, 'Ingeniería'),
(5, 'Coordinación'),
(6, 'Desarrollo'),
(7, 'Administración');

DROP TABLE IF EXISTS `_locations`;
CREATE TABLE IF NOT EXISTS `_locations` (
  `id_location` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_location`),
  UNIQUE KEY `location` (`location`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=48 ;

INSERT INTO `_locations` (`id_location`, `location`) VALUES
(1, 'Artigas'),
(2, 'Artigas - Bella Unión'),
(3, 'Canelones'),
(4, 'Canelones - Canelones'),
(5, 'Canelones - Costa de Oro'),
(6, 'Canelones - El Pinar'),
(7, 'Canelones - La Paz'),
(8, 'Canelones - Las Piedras'),
(9, 'Canelones - Pando'),
(10, 'Canelones - Tala'),
(11, 'Cerro Largo'),
(12, 'Cerro Largo - Melo'),
(13, 'Colonia'),
(14, 'Colonia - Colonia del Sacramento'),
(15, 'Colonia - Tarariras'),
(16, 'Durazno'),
(17, 'Durazno - Durazno'),
(18, 'Flores'),
(19, 'Flores - Flores'),
(20, 'Flores - Trinidad'),
(21, 'Florida'),
(22, 'Florida - Florida'),
(23, 'Florida - Sarandí Grande'),
(24, 'Lavalleja'),
(25, 'Lavalleja - Minas'),
(26, 'Maldonado'),
(27, 'Maldonado - Maldonado'),
(28, 'Maldonado - Punta del Este'),
(29, 'Montevideo'),
(30, 'Paysandú'),
(31, 'Paysandú - Paysandú'),
(32, 'Río Negro'),
(33, 'Río Negro - Río Negro'),
(34, 'Rivera'),
(35, 'Rivera - Rivera'),
(36, 'Rocha'),
(37, 'Rocha - Rocha'),
(38, 'Salto'),
(39, 'Salto - Salto'),
(40, 'San José'),
(41, 'San José - San José'),
(42, 'Soriano'),
(43, 'Soriano - Mercedes'),
(44, 'Tacuarembó'),
(45, 'Tacuarembó - Tacuarembó'),
(46, 'Treinta y Tres'),
(47, 'Treinta y Tres - Treinta y Tres');

DROP TABLE IF EXISTS `_modules`;
CREATE TABLE IF NOT EXISTS `_modules` (
  `code` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `order` (`order`),
  KEY `fk_module` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `_modules` (`code`, `order`) VALUES
('home', 1),
('users', 2),
('customers', 3),
('products', 4),
('estimates', 5),
('technical', 6),
('config', 7);

DROP TABLE IF EXISTS `_notes`;
CREATE TABLE IF NOT EXISTS `_notes` (
  `id_note` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `by` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `note` varchar(500) COLLATE latin1_spanish_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) COLLATE latin1_spanish_ci DEFAULT NULL,
  `id_customer` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_note`),
  KEY `fk_n_user` (`user`),
  KEY `fk_n_by` (`by`),
  KEY `date` (`date`),
  KEY `fk_n_id_customer` (`id_customer`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=10 ;


DROP TABLE IF EXISTS `_pages`;
CREATE TABLE IF NOT EXISTS `_pages` (
  `code` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `module` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `id_area` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `area` (`id_area`),
  KEY `fk_pages_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci COMMENT='Page''s module, area and menu order';

INSERT INTO `_pages` (`code`, `module`, `id_area`, `order`) VALUES
('agenda', 'home', 'global', 1),
('agendaDay', 'home', '', 0),
('config', 'config', '', 0),
('createCustomers', 'customers', 'config', 10),
('createEstimates', 'estimates', 'config', 255),
('createEvent', 'home', 'agenda', 0),
('createMaterials', 'products', 'config', 2),
('createOthers', 'products', 'config', 4),
('createProducts', 'products', 'config', 1),
('createServices', 'products', 'config', 3),
('createTechVisits', 'technical', 'config', 200),
('createUsers', 'users', 'config', 10),
('customers', 'customers', 'lists', 10),
('customersInfo', 'customers', '', 0),
('editAcc', 'home', 'config', 10),
('editAccInfo', 'home', 'config', 5),
('editCustomers', 'customers', '', 20),
('editEstimates', 'estimates', '', 2),
('editEvent', 'home', '', 0),
('editMaterial', 'products', '', 0),
('editProducts', 'products', '', 0),
('editTechVisits', 'technical', '', 210),
('editUsers', 'users', '', 20),
('estimatePDF', 'estimates', '', 0),
('estimates', 'estimates', 'lists', 2),
('estimatesInfo', 'estimates', '', 2),
('home', 'home', '', 0),
('installPlan', 'estimates', '', 0),
('installs', 'technical', 'lists', 20),
('materialInfo', 'products', '', 0),
('materials', 'products', 'lists', 2),
('others', 'products', 'lists', 4),
('potentialCustomers', 'customers', 'lists', 20),
('products', 'products', 'lists', 1),
('productsInfo', 'products', '', 0),
('quoteInfo', 'estimates', '', 0),
('quotes', 'estimates', 'lists', 255),
('registerSales', 'customers', 'config', 200),
('sales', 'customers', 'lists', 100),
('salesInfo', 'customers', '', 0),
('services', 'products', 'lists', 3),
('technical', 'technical', '', 200),
('techVisits', 'technical', 'lists', 10),
('techVisitsInfo', 'technical', '', 0),
('users', 'users', 'lists', 2),
('usersInfo', 'users', '', 0);

DROP TABLE IF EXISTS `_permissions`;
CREATE TABLE IF NOT EXISTS `_permissions` (
  `code` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `type` enum('module','page','permit') COLLATE latin1_spanish_ci NOT NULL DEFAULT 'permit',
  `name` varchar(120) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`code`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci COMMENT='Descriptions will usually be seen by users, write them right';

INSERT INTO `_permissions` (`code`, `type`, `name`) VALUES
('agenda', 'page', 'Mostrar Agenda'),
('agendaDay', 'page', 'Agenda: Detalle por Día'),
('blockUsers', 'permit', 'Bloquear Usuario'),
('chatActivity', 'permit', 'Supervisor Chat'),
('config', 'module', 'Configuración'),
('createCustomers', 'page', 'Nuevo Cliente'),
('createEstimates', 'page', 'Crear Presupuesto/Cotización'),
('createEvent', 'page', 'Nuevo Evento'),
('createMaterials', 'page', 'Ingresar Material'),
('createOthers', 'page', 'Ingresar Otros Productos'),
('createProducts', 'page', 'Ingresar Producto'),
('createServices', 'page', 'Ingresar Servicio'),
('createTechVisits', 'page', 'Nueva Visita Técnica'),
('createUsers', 'page', 'Registrar Nuevo Usuario'),
('customers', 'module', 'Clientes'),
('customersInfo', 'page', 'Información del Cliente'),
('deleteCustomers', 'permit', 'Eliminar Cliente'),
('deleteProducts', 'permit', 'Eliminar Artículo'),
('deleteUsers', 'permit', 'Eliminar Usuario'),
('delMaterial', 'permit', 'Eliminar Material'),
('editAcc', 'page', 'Cambiar Contraseña'),
('editAccInfo', 'page', 'Editar Datos Personales'),
('editCustomers', 'page', 'Editar Cliente'),
('editEstimates', 'page', 'Editar Presupuesto'),
('editEvent', 'page', 'Editar Evento'),
('editMaterial', 'page', 'Editar Material'),
('editProducts', 'page', 'Editar Producto'),
('editQuote', 'page', 'Editar Cotización'),
('editTechVisits', 'page', 'Editar Visita Técnica'),
('editUsers', 'page', 'Editar Usuario'),
('estimatePDF', 'page', 'Exportar Presupuestos en PDF'),
('estimates', 'module', 'Presupuestos'),
('estimatesInfo', 'page', 'Ver Presupuesto'),
('home', 'module', 'Inicio'),
('installPlan', 'page', 'Plan de Obras'),
('installs', 'page', 'Instalaciones'),
('materialInfo', 'page', 'Detalle de Material'),
('materials', 'page', 'Materiales'),
('others', 'page', 'Otros productos'),
('potentialCustomers', 'page', 'Posibles Clientes'),
('products', 'module', 'Productos'),
('productsInfo', 'page', 'Detalle de Producto'),
('quoteInfo', 'page', 'Detalle de Cotización'),
('quotes', 'page', 'Cotizaciones'),
('registerSales', 'page', 'Registrar Venta'),
('sales', 'page', 'Ventas'),
('salesInfo', 'page', 'Información de Venta'),
('services', 'page', 'Servicios'),
('subCfgProfiles', 'permit', 'Administrar Permisos por Perfil'),
('technical', 'module', 'Técnica'),
('techVisits', 'page', 'Visitas Técnicas'),
('techVisitsInfo', 'page', 'Detalle de Visita Técnica'),
('users', 'module', 'Usuarios'),
('usersInfo', 'page', 'Información de Usuario'),
('usersNotes', 'permit', 'Notas de Otros Usuarios');

DROP TABLE IF EXISTS `_permissions_by_profile`;
CREATE TABLE IF NOT EXISTS `_permissions_by_profile` (
  `code` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `id_profile` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`code`,`id_profile`),
  KEY `fk_code` (`code`),
  KEY `fk_pbp_profile` (`id_profile`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `_permissions_by_profile` (`code`, `id_profile`) VALUES
('agenda', 2),
('agendaDay', 2),
('blockUsers', 2),
('chatActivity', 2),
('config', 2),
('createCustomers', 2),
('createEstimates', 2),
('createEvent', 2),
('createMaterials', 2),
('createOthers', 2),
('createProducts', 2),
('createServices', 2),
('createTechVisits', 2),
('createUsers', 2),
('customers', 2),
('customersInfo', 2),
('deleteCustomers', 2),
('deleteProducts', 2),
('deleteUsers', 2),
('delMaterial', 2),
('editAcc', 2),
('editAccInfo', 2),
('editCustomers', 2),
('editEstimates', 2),
('editEvent', 2),
('editMaterial', 2),
('editProducts', 2),
('editQuote', 2),
('editTechVisits', 2),
('editUsers', 2),
('estimates', 2),
('estimatesInfo', 2),
('home', 2),
('installs', 2),
('materialInfo', 2),
('materials', 2),
('others', 2),
('potentialCustomers', 2),
('products', 2),
('productsInfo', 2),
('quoteInfo', 2),
('quotes', 2),
('sales', 2),
('services', 2),
('subCfgProfiles', 2),
('technical', 2),
('techVisits', 2),
('techVisitsInfo', 2),
('users', 2),
('usersInfo', 2),
('agenda', 3),
('agendaDay', 3),
('chatActivity', 3),
('createCustomers', 3),
('createEstimates', 3),
('createEvent', 3),
('createMaterials', 3),
('createOthers', 3),
('createProducts', 3),
('createServices', 3),
('createTechVisits', 3),
('customers', 3),
('customersInfo', 3),
('editAcc', 3),
('editAccInfo', 3),
('editCustomers', 3),
('editEstimates', 3),
('editEvent', 3),
('editMaterial', 3),
('editProducts', 3),
('editQuote', 3),
('editTechVisits', 3),
('estimatePDF', 3),
('estimates', 3),
('estimatesInfo', 3),
('home', 3),
('installPlan', 3),
('installs', 3),
('materialInfo', 3),
('materials', 3),
('potentialCustomers', 3),
('products', 3),
('productsInfo', 3),
('quoteInfo', 3),
('quotes', 3),
('sales', 3),
('technical', 3),
('techVisits', 3),
('techVisitsInfo', 3),
('agenda', 4),
('agendaDay', 4),
('createEvent', 4),
('customers', 4),
('customersInfo', 4),
('editAcc', 4),
('editAccInfo', 4),
('editEvent', 4),
('home', 4),
('agendaDay', 5),
('editAcc', 5),
('editAccInfo', 5),
('home', 5);

DROP TABLE IF EXISTS `_permissions_by_user`;
CREATE TABLE IF NOT EXISTS `_permissions_by_user` (
  `code` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `user` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `type` enum('add','sub') COLLATE latin1_spanish_ci NOT NULL DEFAULT 'add',
  PRIMARY KEY (`code`,`user`),
  KEY `fk_pbu_code` (`code`),
  KEY `fk_pbu_user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `_permissions_by_user` (`code`, `user`, `type`) VALUES
('createTechVisits', 'user', 'add'),
('editTechVisits', 'user', 'add'),
('technical', 'user', 'add'),
('techVisitsInfo', 'user', 'add');

DROP TABLE IF EXISTS `_products`;
CREATE TABLE IF NOT EXISTS `_products` (
  `id_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_category` varchar(40) COLLATE latin1_spanish_ci DEFAULT NULL,
  `name` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `price` decimal(8,2) unsigned NOT NULL,
  `description` varchar(240) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_product`),
  KEY `fk_p_id_category` (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `_product_categories`;
CREATE TABLE IF NOT EXISTS `_product_categories` (
  `id_category` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `category` varchar(80) COLLATE latin1_spanish_ci NOT NULL,
  `type` enum('products','materials','services','others') COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_category`),
  UNIQUE KEY `category` (`category`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

INSERT INTO `_product_categories` (`id_category`, `category`, `type`) VALUES
('cameras', 'Cámaras', 'products'),
('cards', 'Placas', 'products'),
('dvr', 'DVR', 'products'),
('geo', 'GeoVision', 'products'),
('gv', 'GV', 'products'),
('materials', 'Materiales', 'materials'),
('mics', 'Micrófonos', 'products'),
('others', 'Otros', 'others'),
('products', 'Productos sin Clasificar', 'products'),
('screens', 'Monitores', 'products'),
('servers', 'Servidores', 'products'),
('services', 'Servicios', 'services');

DROP TABLE IF EXISTS `_product_extension`;
CREATE TABLE IF NOT EXISTS `_product_extension` (
  `id_product` int(10) unsigned NOT NULL,
  `code` varchar(20) COLLATE latin1_spanish_ci NOT NULL,
  `trademark` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `model` varchar(40) COLLATE latin1_spanish_ci NOT NULL,
  `warranty` tinyint(3) unsigned NOT NULL,
  `id_system` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_product`),
  KEY `fk_pe_id_product` (`id_product`),
  KEY `fk_pe_id_system` (`id_system`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;


DROP TABLE IF EXISTS `_profiles`;
CREATE TABLE IF NOT EXISTS `_profiles` (
  `id_profile` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `profile` varchar(30) COLLATE latin1_spanish_ci NOT NULL,
  PRIMARY KEY (`id_profile`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=6 ;

INSERT INTO `_profiles` (`id_profile`, `profile`) VALUES
(1, 'Master'),
(2, 'Administrador'),
(3, 'Operador'),
(4, 'Usuario'),
(5, 'Invitado');

DROP TABLE IF EXISTS `_users`;
CREATE TABLE IF NOT EXISTS `_users` (
  `user` varchar(20) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `pass` varchar(32) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `id_profile` tinyint(3) unsigned NOT NULL,
  `name` varchar(40) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `lastName` varchar(40) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `phone` varchar(20) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `address` varchar(40) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `email` varchar(60) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `employeeNum` varchar(20) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `position` varchar(40) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '',
  `id_department` smallint(5) unsigned DEFAULT NULL,
  `last_access` timestamp NULL DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `lastSeenLog` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`),
  KEY `name` (`name`,`lastName`,`employeeNum`,`id_department`),
  KEY `fk_u_id_department` (`id_department`),
  KEY `fk_u_id_profile` (`id_profile`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `_users` (`user`, `pass`, `id_profile`, `name`, `lastName`, `phone`, `address`, `email`, `employeeNum`, `position`, `id_department`, `last_access`, `blocked`, `lastSeenLog`) VALUES
('admin', MD5('1234'), 2, 'TestingAcc', 'Admin', '555-55555', 'Testing 123', 'no.email@example.com', '103', 'Administrativa', 7, '2011-03-14 16:57:50', 0, 3),
('master', MD5('1234'), 1, 'TestingAcc', 'Master', '555-55555', 'Testing 123', 'no.email@example.com', '', 'Desarrollador (free-lance)', 6, '2011-03-14 16:36:36', 0, 1),
('operator', MD5('1234'), 3, 'TestingAcc', 'Operator', '555-55555', 'Testing 123', 'no.email@example.com', '101', 'Coordinadora', 5, '2011-03-14 16:37:43', 0, 0),
('user', MD5('1234'), 4, 'TestingAcc', 'User', '555-55555', 'Testing 123', 'no.email@example.com', '110', 'Técnico', 3, '2011-03-14 16:37:47', 0, 0);


ALTER TABLE `alerts`
  ADD CONSTRAINT `fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `alerts_types` (`id_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `alerts_unread`
  ADD CONSTRAINT `fk_au_id_log` FOREIGN KEY (`id_log`) REFERENCES `logs` (`id_log`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_au_user` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `customers`
  ADD CONSTRAINT `fk_c_id_location` FOREIGN KEY (`id_location`) REFERENCES `_locations` (`id_location`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_c_seller` FOREIGN KEY (`seller`) REFERENCES `_users` (`user`) ON UPDATE CASCADE;

ALTER TABLE `customers_contacts`
  ADD CONSTRAINT `fk_id_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `customers_owners`
  ADD CONSTRAINT `fk_co_id_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `estimates`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id_customer`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_id_system` FOREIGN KEY (`id_system`) REFERENCES `systems` (`id_system`) ON UPDATE CASCADE;

ALTER TABLE `estimates_detail`
  ADD CONSTRAINT `fk_ed_id_estimate` FOREIGN KEY (`id_estimate`) REFERENCES `estimates` (`id_estimate`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ed_id_product` FOREIGN KEY (`id_product`) REFERENCES `_products` (`id_product`) ON UPDATE CASCADE;

ALTER TABLE `estimates_plan`
  ADD CONSTRAINT `fk_ep_id_estimate` FOREIGN KEY (`id_estimate`, `id_product`) REFERENCES `estimates_detail` (`id_estimate`, `id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `events`
  ADD CONSTRAINT `fk_e_creator` FOREIGN KEY (`creator`) REFERENCES `_users` (`user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_e_target` FOREIGN KEY (`target`) REFERENCES `_users` (`user`) ON UPDATE CASCADE;

ALTER TABLE `events_customers`
  ADD CONSTRAINT `fk_ec_id_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id_customer`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ec_id_event` FOREIGN KEY (`id_event`) REFERENCES `events` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `events_edition`
  ADD CONSTRAINT `fk_ee_by` FOREIGN KEY (`by`) REFERENCES `_users` (`user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ee_id_event` FOREIGN KEY (`id_event`) REFERENCES `events` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `events_results`
  ADD CONSTRAINT `fk_er_id_event` FOREIGN KEY (`id_event`) REFERENCES `events` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_er_user` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON UPDATE CASCADE;

ALTER TABLE `logs`
  ADD CONSTRAINT `fk_l_user` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `logs_history`
  ADD CONSTRAINT `logs_history_ibfk_1` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sales`
  ADD CONSTRAINT `fk_l_id_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id_customer`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_l_id_system` FOREIGN KEY (`id_system`) REFERENCES `systems` (`id_system`) ON UPDATE CASCADE;

ALTER TABLE `sales_installs`
  ADD CONSTRAINT `fk_si_id_installer` FOREIGN KEY (`id_installer`) REFERENCES `installers` (`id_installer`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_si_id_sale` FOREIGN KEY (`id_sale`) REFERENCES `sales` (`id_sale`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sales_services`
  ADD CONSTRAINT `fk_t_id_sale` FOREIGN KEY (`id_sale`) REFERENCES `sales` (`id_sale`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_t_onSale` FOREIGN KEY (`onSale`) REFERENCES `sales` (`id_sale`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_t_technician` FOREIGN KEY (`technician`) REFERENCES `_users` (`user`) ON UPDATE CASCADE;

ALTER TABLE `_modules`
  ADD CONSTRAINT `fk_module` FOREIGN KEY (`code`) REFERENCES `_permissions` (`code`) ON UPDATE CASCADE;

ALTER TABLE `_notes`
  ADD CONSTRAINT `fk_n_user` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_n_id_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_n_by` FOREIGN KEY (`by`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `_pages`
  ADD CONSTRAINT `fk_area` FOREIGN KEY (`id_area`) REFERENCES `_areas` (`id_area`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_page` FOREIGN KEY (`code`) REFERENCES `_permissions` (`code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pages_module` FOREIGN KEY (`module`) REFERENCES `_modules` (`code`) ON UPDATE CASCADE;

ALTER TABLE `_permissions_by_profile`
  ADD CONSTRAINT `fk_pbp_code` FOREIGN KEY (`code`) REFERENCES `_permissions` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pbp_profile` FOREIGN KEY (`id_profile`) REFERENCES `_profiles` (`id_profile`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `_permissions_by_user`
  ADD CONSTRAINT `fk_pbu_code` FOREIGN KEY (`code`) REFERENCES `_permissions` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pbu_user` FOREIGN KEY (`user`) REFERENCES `_users` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `_products`
  ADD CONSTRAINT `fk_p_id_category` FOREIGN KEY (`id_category`) REFERENCES `_product_categories` (`id_category`) ON UPDATE CASCADE;

ALTER TABLE `_product_extension`
  ADD CONSTRAINT `fk_pe_id_product` FOREIGN KEY (`id_product`) REFERENCES `_products` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_id_system` FOREIGN KEY (`id_system`) REFERENCES `systems` (`id_system`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `_users`
  ADD CONSTRAINT `fk_u_id_department` FOREIGN KEY (`id_department`) REFERENCES `_departments` (`id_department`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_u_id_profile` FOREIGN KEY (`id_profile`) REFERENCES `_profiles` (`id_profile`) ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
