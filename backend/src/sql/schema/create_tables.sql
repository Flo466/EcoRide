-- Create database if it does not exist
CREATE DATABASE IF NOT EXISTS `ecoride`;

-- Use the ecoride database
USE `ecoride`;

--
-- Table: `brand`
--
DROP TABLE IF EXISTS `brand`;
CREATE TABLE `brand` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(50) NOT NULL,
    PRIMARYLY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table: `car`
--
DROP TABLE IF EXISTS `car`;
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
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_Car_User` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
    CONSTRAINT `FK_Car_Brand` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`)
) ENGINE=InnoDB;

--
-- Table: `carpooling`
--
DROP TABLE IF EXISTS `carpooling`;
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
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_Carpooling_Car` FOREIGN KEY (`car_id`) REFERENCES `car` (`id`)
) ENGINE=InnoDB;

--
-- Table: `carpooling_user`
--
DROP TABLE IF EXISTS `carpooling_user`;
CREATE TABLE `carpooling_user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `is_driver` BOOLEAN NOT NULL,
    `user_id` INT NOT NULL,
    `carpooling_id` INT NOT NULL,
    `is_cancelled` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_User_Carpooling` (`user_id`, `carpooling_id`),
    CONSTRAINT `FK_CarpoolingUser_User` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_CarpoolingUser_Carpooling` FOREIGN KEY (`carpooling_id`) REFERENCES `carpooling` (`id`)
) ENGINE=InnoDB;

--
-- Table: `review`
--
DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comment` TEXT NOT NULL,
    `ratting` INT NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `user_id` INT NOT NULL,
    `reviewed_user_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_Review_User` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
    CONSTRAINT `FK_Review_ReviewedUser` FOREIGN KEY (`reviewed_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB;

--
-- Table: `user`
--
DROP TABLE IF EXISTS `user`;
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
    UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
    CONSTRAINT `FK_User_UsedCar` FOREIGN KEY (`used_car_id`) REFERENCES `car` (`id`)
) ENGINE=InnoDB;