CREATE TABLE IF NOT EXISTS `sprint` (
  `jira_id` bigint(20) unsigned NOT NULL,
  `everhour_id` VARCHAR(20) NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `status` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`jira_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `issue` (
  `jira_id` bigint(20) unsigned NOT NULL,
  `everhour_id` VARCHAR(20) NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL,
  `sprint_jira_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`jira_id`),
  CONSTRAINT `issue_milestone` FOREIGN KEY (`sprint_jira_id`) REFERENCES `sprint` (`jira_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;