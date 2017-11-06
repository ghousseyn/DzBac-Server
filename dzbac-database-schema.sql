CREATE TABLE `banned_membres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `id_banned` int(11) NOT NULL,
  `android_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `banned_phones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_membre` int(11) NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `blocked_membres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_blocker` int(11) NOT NULL,
  `id_blocked` int(11) NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `content_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_content` int(11) NOT NULL,
  `id_membre` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `url_image` text COLLATE utf8mb4_unicode_ci COMMENT 'Lien pour une réponse en image',
  `audio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_delete` int(11) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `content_hits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_membre` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `content_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_content` int(11) NOT NULL,
  `id_membre` int(11) NOT NULL,
  `valeur` int(11) NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_membre` int(11) NOT NULL,
  `id_membre_del` int(11) DEFAULT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Aide, Sujet, Résumé',
  `secteur` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Matière',
  `url_presentation` longtext COLLATE utf8mb4_unicode_ci,
  `url` longtext COLLATE utf8mb4_unicode_ci,
  `url_video` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_delete` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL DEFAULT '0',
  `date_update` datetime DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `digits` (
  `digits_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_membre` int(11) NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`digits_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `following` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_followed` int(11) DEFAULT NULL,
  `id_follower` int(11) DEFAULT NULL,
  `is_following` tinyint(1) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `header` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_receveur` int(11) DEFAULT NULL,
  `sujet` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `unread_id_membre` int(11) DEFAULT NULL,
  `header_deleted` tinyint(4) DEFAULT '0',
  `date_creation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `membres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_google` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_code_app` int(11) DEFAULT NULL,
  `ip_adress` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` mediumtext COLLATE utf8mb4_unicode_ci,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'C''est la branche',
  `localisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` tinyint(2) DEFAULT NULL COMMENT 'Pour connaître le grade de l''utilisateur',
  `likes` int(11) DEFAULT '0',
  `level_contribution` tinyint(4) NOT NULL DEFAULT '0',
  `api_key` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokenCode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gcm_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `url_avatar` mediumtext COLLATE utf8mb4_unicode_ci,
  `url_background` mediumtext COLLATE utf8mb4_unicode_ci,
  `date_last_activity` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `date_creation` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_id` int(11) DEFAULT NULL,
  `id_membre` int(11) DEFAULT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci,
  `audio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_lu` int(11) NOT NULL DEFAULT '0',
  `new_message` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_last_view` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_membre` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_notifieur` int(11) NOT NULL,
  `id_receveur` int(11) NOT NULL,
  `id_notification_group` int(11) NOT NULL,
  `verb` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_supprime` int(11) NOT NULL DEFAULT '0',
  `new_notification` int(1) NOT NULL,
  `data` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `android_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_membre` int(11) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `signalements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_membre` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL,
  `date_deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tags` (`id`, `name`)
VALUES
	(1,'Scientifique'),
	(2,'Maths'),
	(3,'Maths technique'),
	(4,'Langue étrangère'),
	(5,'Gestion');
	
CREATE TABLE `tags_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_content` int(11) NOT NULL,
  `id_tag` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;