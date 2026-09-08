CREATE TABLE IF NOT EXISTS `#__decaroprotocol_registers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `code` varchar(64) NOT NULL,
  `description` text NULL,
  `numbering_mode` varchar(20) NOT NULL DEFAULT 'yearly',
  `active` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime NULL,
  `created` datetime NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `idx_active_ordering` (`active`,`ordering`),
  KEY `idx_checked_out` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__decaroprotocol_register_counters` (
  `register_id` int unsigned NOT NULL,
  `protocol_year` smallint unsigned NOT NULL,
  `last_number` int unsigned NOT NULL DEFAULT 0,
  `updated` datetime NULL,
  PRIMARY KEY (`register_id`,`protocol_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__decaroprotocol_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `register_id` int unsigned NOT NULL,
  `protocol_number` int unsigned NULL,
  `protocol_year` smallint unsigned NULL,
  `protocolled_at` datetime NULL,
  `protocolled_by` int unsigned NOT NULL DEFAULT 0,
  `direction` varchar(16) NOT NULL DEFAULT 'incoming',
  `subject` varchar(500) NOT NULL,
  `document_date` date NULL,
  `external_reference` varchar(255) NOT NULL DEFAULT '',
  `sender` text NULL,
  `recipients` text NULL,
  `notes` text NULL,
  `status` varchar(24) NOT NULL DEFAULT 'draft',
  `checked_out` int unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime NULL,
  `created` datetime NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_protocol_identity` (`register_id`,`protocol_year`,`protocol_number`),
  KEY `idx_register` (`register_id`),
  KEY `idx_status` (`status`),
  KEY `idx_direction` (`direction`),
  KEY `idx_protocolled_at` (`protocolled_at`),
  KEY `idx_checked_out` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__decaroprotocol_audit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` int unsigned NOT NULL,
  `event` varchar(64) NOT NULL,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `data_json` mediumtext NULL,
  PRIMARY KEY (`id`),
  KEY `idx_record_created` (`record_id`,`created`),
  KEY `idx_event` (`event`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__decaroprotocol_registers` (`title`,`code`,`description`,`numbering_mode`,`active`,`ordering`)
SELECT 'Registro ufficiale','official','Registro ufficiale iniziale per Entrata, Uscita e Interna.','yearly',1,1
WHERE NOT EXISTS (SELECT 1 FROM `#__decaroprotocol_registers` WHERE `code`='official');
