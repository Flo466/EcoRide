-- Create database if it does not exist
CREATE DATABASE IF NOT EXISTS `ecoride`;

-- Use the ecoride database
USE `ecoride`;

-- Temporarily disable foreign key checks for table creation
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables
DROP TABLE IF EXISTS `carpooling_user`;
DROP TABLE IF EXISTS `review`;
DROP TABLE IF EXISTS `carpooling`;
DROP TABLE IF EXISTS `car`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `brand`;

--
-- Table: `brand`
--
CREATE TABLE `brand` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table: `user`
--
CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(180) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `roles` JSON NOT NULL,
    `last_name` VARCHAR(50) DEFAULT NULL,
    `first_name` VARCHAR(50) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `birth_date` DATETIME DEFAULT NULL,
    `photo` LONGBLOB,
    `photo_mime_type` VARCHAR(50) DEFAULT NULL,
    `user_name` VARCHAR(50) NOT NULL,
    `credits` INT NOT NULL,
    `api_token` VARCHAR(255) NOT NULL,
    `is_driver` BOOLEAN NOT NULL DEFAULT FALSE,
    `used_car_id` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB;

--
-- Table: `car`
--
CREATE TABLE `car` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `model` VARCHAR(50) NOT NULL,
    `color` VARCHAR(50) DEFAULT NULL,
    `licence_plate` VARCHAR(50) NOT NULL,
    `energy` VARCHAR(50) NOT NULL,
    `first_registration_date` DATE DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `pets_allowed` BOOLEAN NOT NULL DEFAULT FALSE,
    `seats` INT NOT NULL,
    `user_id` INT NOT NULL,
    `brand_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table: `carpooling`
--
CREATE TABLE `carpooling` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `departure_date` DATE NOT NULL,
    `departure_time` TIME NOT NULL,
    `departure_place` VARCHAR(50) NOT NULL,
    `arrival_date` DATE NOT NULL,
    `arrival_time` TIME NOT NULL,
    `arrival_place` VARCHAR(50) NOT NULL,
    `seat_count` INT NOT NULL,
    `price_per_person` DOUBLE NOT NULL,
    `is_eco` BOOLEAN NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `status` VARCHAR(255) NOT NULL,
    `car_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table: `carpooling_user`
--
CREATE TABLE `carpooling_user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `is_driver` BOOLEAN NOT NULL,
    `user_id` INT NOT NULL,
    `carpooling_id` INT NOT NULL,
    `is_cancelled` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_User_Carpooling` (`user_id`, `carpooling_id`)
) ENGINE=InnoDB;

--
-- Table: `review`
--
CREATE TABLE `review` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comment` TEXT NOT NULL,
    `ratting` INT NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `user_id` INT NOT NULL,
    `reviewed_user_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Add foreign key constraints after all tables are created
ALTER TABLE `car`
ADD CONSTRAINT `FK_Car_User` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
ADD CONSTRAINT `FK_Car_Brand` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`);

ALTER TABLE `carpooling`
ADD CONSTRAINT `FK_Carpooling_Car` FOREIGN KEY (`car_id`) REFERENCES `car` (`id`);

ALTER TABLE `carpooling_user`
ADD CONSTRAINT `FK_CarpoolingUser_User` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `FK_CarpoolingUser_Carpooling` FOREIGN KEY (`carpooling_id`) REFERENCES `carpooling` (`id`);

ALTER TABLE `review`
ADD CONSTRAINT `FK_Review_User` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
ADD CONSTRAINT `FK_Review_ReviewedUser` FOREIGN KEY (`reviewed_user_id`) REFERENCES `user` (`id`);

ALTER TABLE `user`
ADD CONSTRAINT `FK_User_UsedCar` FOREIGN KEY (`used_car_id`) REFERENCES `car` (`id`);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;