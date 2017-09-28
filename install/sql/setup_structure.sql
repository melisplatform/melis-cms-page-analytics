-- MySQL Script generated by MySQL Workbench
-- Thu Sep 14 16:08:41 2017
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema melis-cms-page-analytics
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `melis_cms_page_analytics`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `melis_cms_page_analytics` ;

CREATE TABLE IF NOT EXISTS `melis_cms_page_analytics` (
  `ph_id` INT NOT NULL AUTO_INCREMENT,
  `ph_page_id` INT NOT NULL,
  `ph_session_id` VARCHAR(100) NOT NULL,
  `ph_date_visit` DATETIME NULL,
  PRIMARY KEY (`ph_id`, `ph_session_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `melis_cms_page_analytics_data`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `melis_cms_page_analytics_data` ;

CREATE TABLE IF NOT EXISTS `melis_cms_page_analytics_data` (
  `pad_id` INT NOT NULL AUTO_INCREMENT,
  `pad_current_analytics` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`pad_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `melis_cms_page_analytics_settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `melis_cms_page_analytics_settings` ;

CREATE TABLE IF NOT EXISTS `melis_cms_page_analytics_settings` (
  `pas_id` INT NOT NULL AUTO_INCREMENT,
  `pas_analytics` VARCHAR(100) NULL,
  `pas_settings` TEXT NULL,
  PRIMARY KEY (`pas_id`))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `melis_cms_page_analytics_data`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `melis_cms_page_analytics_data` (`pad_id`, `pad_current_analytics`) VALUES (1, 'melis_cms_page_analytics');

COMMIT;

