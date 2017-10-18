-- MySQL Script generated by MySQL Workbench
-- Wed Oct 18 16:38:30 2017
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
-- Table `melis_cms_page_analytics_data_settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `melis_cms_page_analytics_data_settings` ;

CREATE TABLE IF NOT EXISTS `melis_cms_page_analytics_data_settings` (
  `pads_id` INT NOT NULL AUTO_INCREMENT,
  `pads_site_id` INT NOT NULL,
  `pads_analytics_key` VARCHAR(100) NOT NULL,
  `pads_settings` LONGTEXT NULL,
  `pads_js_analytics` LONGTEXT NULL,
  PRIMARY KEY (`pads_id`))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `melis_cms_page_analytics_data`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `melis_cms_page_analytics_data` ;

CREATE TABLE IF NOT EXISTS `melis_cms_page_analytics_data` (
  `pad_id` INT NOT NULL AUTO_INCREMENT,
  `pad_site_id` INT NOT NULL,
  `pad_analytics_key` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`pad_id`))
  ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `melis_cms_page_analytics_data_settings`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `melis_cms_page_analytics_data_settings` (`pads_id`, `pads_site_id`, `pads_analytics_key`, `pads_settings`, `pads_js_analytics`) VALUES (DEFAULT, 1, 'melis_cms_page_analytics', NULL, 'console.log(\"Melis CMS Page Analytics\");');

COMMIT;


-- -----------------------------------------------------
-- Data for table `melis_cms_page_analytics_data`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `melis_cms_page_analytics_data` (`pad_id`, `pad_site_id`, `pad_analytics_key`) VALUES (1, 1, 'melis_cms_page_analytics');

COMMIT;

