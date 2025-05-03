-- MySQL Script modified for appropriate ON DELETE behavior
-- Cleaned and corrected version

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

DROP TABLE IF EXISTS `user_has_role`, `parameter`, `configuration`, `user_has_carpooling`, `carpooling`, `car`, `brand`, `user_has_review`, `review`, `role`, `user`;

CREATE TABLE `user` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `last_name` VARCHAR(50) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `password` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `address` VARCHAR(50) DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `photo` BLOB DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `credits` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`)
);

CREATE TABLE `role` (
  `role_id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`role_id`)
);

CREATE TABLE `review` (
  `review_id` INT NOT NULL AUTO_INCREMENT,
  `comment` VARCHAR(255) DEFAULT NULL,
  `rating` INT DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`review_id`)
);

CREATE TABLE `user_has_review` (
  `user_id` INT NOT NULL,
  `review_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `review_id`),
  CONSTRAINT `fk_user_has_review_user1`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_has_review_review1`
    FOREIGN KEY (`review_id`) REFERENCES `review` (`review_id`) ON DELETE CASCADE
);

CREATE TABLE `brand` (
  `brand_id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`brand_id`)
);

CREATE TABLE `car` (
  `car_id` INT NOT NULL AUTO_INCREMENT,
  `model` VARCHAR(50) DEFAULT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `licence_plate` VARCHAR(50) DEFAULT NULL,
  `energy` VARCHAR(50) DEFAULT NULL,
  `first_registration_date` DATE DEFAULT NULL,
  `user_id` INT NOT NULL,
  `brand_id` INT NOT NULL,
  PRIMARY KEY (`car_id`),
  CONSTRAINT `fk_car_user1`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_car_brand1`
    FOREIGN KEY (`brand_id`) REFERENCES `brand` (`brand_id`) ON DELETE SET NULL
);

CREATE TABLE `carpooling` (
  `carpooling_id` INT NOT NULL AUTO_INCREMENT,
  `departure_date` DATE DEFAULT NULL,
  `departure_time` TIME DEFAULT NULL,
  `departure_place` VARCHAR(50) DEFAULT NULL,
  `arrival_date` DATE DEFAULT NULL,
  `arrival_time` TIME DEFAULT NULL,
  `arrival_place` VARCHAR(50) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT NULL,
  `seat_count` INT DEFAULT NULL,
  `price_per_person` FLOAT DEFAULT NULL,
  `is_eco` TINYINT DEFAULT NULL,
  `car_id` INT NOT NULL,
  PRIMARY KEY (`carpooling_id`),
  CONSTRAINT `fk_carpooling_car1`
    FOREIGN KEY (`car_id`) REFERENCES `car` (`car_id`) ON DELETE CASCADE
);

CREATE TABLE `user_has_carpooling` (
  `user_user_id` INT NOT NULL,
  `carpooling_id` INT NOT NULL,
  PRIMARY KEY (`user_user_id`, `carpooling_id`),
  CONSTRAINT `fk_user_has_carpooling_user1`
    FOREIGN KEY (`user_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_has_carpooling_carpooling1`
    FOREIGN KEY (`carpooling_id`) REFERENCES `carpooling` (`carpooling_id`) ON DELETE CASCADE
);

CREATE TABLE `configuration` (
  `configuration_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`configuration_id`),
  CONSTRAINT `fk_configuration_user1`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
);

CREATE TABLE `parameter` (
  `parameter_id` INT NOT NULL AUTO_INCREMENT,
  `configuration_id` INT NOT NULL,
  PRIMARY KEY (`parameter_id`),
  CONSTRAINT `fk_parameter_configuration1`
    FOREIGN KEY (`configuration_id`) REFERENCES `configuration` (`configuration_id`) ON DELETE CASCADE
);

CREATE TABLE `user_has_role` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  CONSTRAINT `fk_user_has_role_user1`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_has_role_role1`
    FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE SET NULL
);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
