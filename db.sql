CREATE DATABASE IF NOT EXISTS tabulation_system;

USE tabulation_system;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Judge') NOT NULL,
  status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  passcode VARCHAR(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE evaluation_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    criteria TEXT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE evaluation_criteria 
MODIFY category VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
MODIFY criteria TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS officials_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    attempted_password VARCHAR(255) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    event_logo VARCHAR(255) DEFAULT NULL,
    event_banner VARCHAR(255) DEFAULT NULL
);

CREATE TABLE `score_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `judge_id` INT(11) NOT NULL,
  `judge_name` VARCHAR(255) NOT NULL,
  `participant_name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `criteria` VARCHAR(255) NOT NULL,
  `percentage` FLOAT DEFAULT NULL,
  `score` FLOAT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_score` (`judge_id`, `participant_name`, `category`, `criteria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
