CREATE TABLE IF NOT EXISTS `sprint` (
  `jira_id` bigint(20) unsigned NOT NULL,
  `everhour_id` VARCHAR(20) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `status` tinyint(2) unsigned NOT NULL,
  `position` tinyint(2) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`jira_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `issue` (
  `jira_id` bigint(20) unsigned NOT NULL,
  `everhour_id` VARCHAR(20) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `is_closed` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `sprint_jira_id` bigint(20) unsigned NOT NULL,
  `time_spent` bigint(20) unsigned DEFAULT NULL,
  `estimation` int(5) unsigned DEFAULT NULL,
  `user_id` int(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`jira_id`),
  CONSTRAINT `issue_milestone` FOREIGN KEY (`sprint_jira_id`) REFERENCES `sprint` (`jira_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `user` (
  `id` VARCHAR(20) NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `sprint_buffer` (
  `everhour_id` VARCHAR(20) NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`everhour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `issue_buffer` (
  `everhour_id` VARCHAR(20) NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `time_spent` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`everhour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `sprint` (`jira_id`, `name`, `status`)
VALUES (1, 'Management', 3), (2, 'Without Sprint', 4);

INSERT IGNORE INTO `issue` (`jira_id`, `name`, `sprint_jira_id`)
VALUES (1, 'Code review', 1), (2, 'Meeting', 1), (3, 'Management', 1), (4, 'Issues Discuss', 1);