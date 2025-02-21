CREATE DATABASE IF NOT EXISTS tabulation_system;

USE tabulation_system;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Judge') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE evaluation_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    criteria TEXT NOT NULL,
    percentage INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE evaluation_criteria 
MODIFY category VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
MODIFY criteria TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
