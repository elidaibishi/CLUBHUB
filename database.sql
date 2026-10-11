CREATE DATABASE IF NOT EXISTS clubhub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE clubhub;

CREATE TABLE IF NOT EXISTS participants (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `groups` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS participant_groups (
  participant_id INT UNSIGNED NOT NULL,
  group_id INT UNSIGNED NOT NULL,
  joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (participant_id, group_id),
  KEY idx_participant_groups_group_id (group_id),
  CONSTRAINT fk_participant_groups_participant
    FOREIGN KEY (participant_id) REFERENCES participants (id),
  CONSTRAINT fk_participant_groups_group
    FOREIGN KEY (group_id) REFERENCES `groups` (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS administrators (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_administrators_email (email)
) ENGINE=InnoDB;
