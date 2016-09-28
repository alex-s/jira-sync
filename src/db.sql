CREATE TABLE `milestone` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `issue` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `status` tinyint(1) unsignedNOT NULL,
  `milestone_id` bigint(20) unsigned NOT NULL
  PRIMARY KEY (`id`),
  CONSTRAINT `issue_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `milestone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
