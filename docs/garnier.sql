SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `garnier` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `garnier` ;

-- -----------------------------------------------------
-- Table `garnier`.`photo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `garnier`.`photo` ;

CREATE  TABLE IF NOT EXISTS `garnier`.`photo` (
  `photo_id` INT NOT NULL ,
  `path` VARCHAR(45) NULL ,
  `user_id` INT NULL ,
  `vote` INT NULL ,
  `datetime` INT NULL ,
  PRIMARY KEY (`photo_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `garnier`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `garnier`.`user` ;

CREATE  TABLE IF NOT EXISTS `garnier`.`user` (
  `user_id` INT NOT NULL ,
  `nickname` VARCHAR(45) NULL ,
  `password` VARCHAR(45) NULL ,
  `from` VARCHAR(45) NULL ,
  `email` VARCHAR(45) NULL ,
  `tel` VARCHAR(45) NULL ,
  `datetime` DATETIME NULL ,
  PRIMARY KEY (`user_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `garnier`.`vote`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `garnier`.`vote` ;

CREATE  TABLE IF NOT EXISTS `garnier`.`vote` (
  `user_id` INT NOT NULL ,
  `photo_id` VARCHAR(45) NULL ,
  `datetime` DATETIME NULL ,
  PRIMARY KEY (`user_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
