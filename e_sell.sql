/*
 Navicat Premium Data Transfer

 Source Server         : opencart
 Source Server Type    : MySQL
 Source Server Version : 50730
 Source Host           : 192.168.0.136:3306
 Source Schema         : open_cart_origin

 Target Server Type    : MySQL
 Target Server Version : 50730
 File Encoding         : 65001

 Date: 14/07/2020 10:38:23
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for dealbao_order_related
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_order_related`;
CREATE TABLE `dealbao_order_related`
(
    `id`       int(11)                                                 NOT NULL AUTO_INCREMENT,
    `order_id` int(11)                                                 NOT NULL,
    `pay_sn`   varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_download_images
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_download_images`;
CREATE TABLE `dealbao_download_images`
(
    `id`          int(11)                                                 NOT NULL AUTO_INCREMENT,
    `origin_url`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `local_url`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `download_id` int(11)                                                 NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 308
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_download_group
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_download_group`;
CREATE TABLE `dealbao_download_group`
(
    `id`          int(11)                                         NOT NULL AUTO_INCREMENT,
    `download_id` int(11)                                         NOT NULL,
    `group_id`    int(11)                                         NOT NULL,
    `category_id` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `status`      int(3)                                          NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 5
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_download_goods
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_download_goods`;
CREATE TABLE `dealbao_download_goods`
(
    `id`                    int(11)                                                 NOT NULL AUTO_INCREMENT,
    `goods_name`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_ad_words`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `spec_name_compose`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `store_name`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `spec_name`             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `spec_value`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_attr`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_custom`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_body`            text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    `mobile_body`           text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    `language_id`           int(11)                                                 NOT NULL,
    `spu_language`          varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `spu`                   varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `goods_collect`         int(10)                                                 NOT NULL,
    `goods_click`           int(10)                                                 NOT NULL,
    `goods_salenum`         int(10)                                                 NOT NULL,
    `goods_image`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_state`           tinyint(3)                                              NOT NULL,
    `goods_stateremark`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_verify`          tinyint(3)                                              NOT NULL,
    `goods_verifyremark`    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_selltime`        char(10) CHARACTER SET utf8 COLLATE utf8_general_ci     NOT NULL,
    `goods_price`           decimal(10, 2)                                          NOT NULL,
    `goods_marketprice`     decimal(10, 2)                                          NOT NULL,
    `goods_promotion_price` decimal(10, 2)                                          NOT NULL,
    `goods_promotion_type`  tinyint(3)                                              NOT NULL,
    `goods_costprice`       decimal(10, 2)                                          NOT NULL,
    `goods_discount`        float                                                   NOT NULL,
    `goods_serial`          varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `goods_storage`         int(10)                                                 NOT NULL,
    `goods_source`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_storage_alarm`   int(10)                                                 NOT NULL,
    `goods_barcode`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `ladder_freight`        text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    `transport_id`          int(11)                                                 NOT NULL,
    `transport_title`       varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `goods_commend`         tinyint(3)                                              NOT NULL,
    `goods_freight`         decimal(10, 0)                                          NOT NULL,
    `goods_vat`             tinyint(3)                                              NOT NULL,
    `create_time`           char(10) CHARACTER SET utf8 COLLATE utf8_general_ci     NOT NULL,
    `update_time`           char(10) CHARACTER SET utf8 COLLATE utf8_general_ci     NOT NULL,
    `min_price`             decimal(10, 2)                                          NOT NULL,
    `max_price`             decimal(10, 2)                                          NOT NULL,
    `weight`                decimal(10, 0)                                          NOT NULL,
    `return_goods`          int(1)                                                  NOT NULL,
    `language_list`         varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `language_list_name`    varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `goods_type`            int(1)                                                  NOT NULL,
    `record_code`           varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `goods_length`          int(6)                                                  NOT NULL,
    `goods_wide`            int(6)                                                  NOT NULL,
    `goods_height`          int(6)                                                  NOT NULL,
    `sku_data`              text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    `images_more`           text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    `download_id`           int(11)                                                 NOT NULL,
    `download_status`       int(3)                                                  NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 15
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_download_history
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_download_history`;
CREATE TABLE `dealbao_download_history`
(
    `id`            int(11)                                                NOT NULL AUTO_INCREMENT,
    `bind_group_id` text CHARACTER SET utf8 COLLATE utf8_general_ci        NOT NULL,
    `create_time`   varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `update_time`   varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `status`        int(3)                                                 NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 3
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_language
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_language`;
CREATE TABLE `dealbao_language`
(
    `language_id` int(11)                                                 NOT NULL AUTO_INCREMENT,
    `name`        varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `code_three`  varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci   NULL     DEFAULT NULL COMMENT '语言编码3',
    `code`        varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci   NOT NULL,
    `locale`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `currency`    varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '货币',
    `image`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `sort_order`  int(3)                                                  NOT NULL DEFAULT 0,
    `status`      tinyint(1)                                              NOT NULL DEFAULT 1,
    PRIMARY KEY (`language_id`) USING BTREE,
    INDEX `name` (`name`) USING BTREE
) ENGINE = MyISAM
  AUTO_INCREMENT = 9
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_download_prosess
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_download_prosess`;
CREATE TABLE `dealbao_download_prosess`
(
    `id`              int(11)                                                NOT NULL AUTO_INCREMENT,
    `spu`             varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'origin',
    `website_spu`     varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'local',
    `sku`             varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'origin',
    `website_sku`     varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'local',
    `download_status` int(2)                                                 NOT NULL COMMENT 'status',
    `download_id`     int(11)                                                NOT NULL,
    `group_id`        int(11)                                                NOT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dealbao_category_mapping
-- ----------------------------
DROP TABLE IF EXISTS `dealbao_category_mapping`;
CREATE TABLE `dealbao_category_mapping`
(
    `mapping_id`  int(11)                                                NOT NULL AUTO_INCREMENT,
    `origin_id`   int(11)                                                NOT NULL,
    `local_id`    int(11)                                                NOT NULL,
    `create_time` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    PRIMARY KEY (`mapping_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- alert table open cart product
-- ----------------------------
ALTER TABLE `oc_product`
    ADD COLUMN `dealbao_status` enum ('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
    ADD COLUMN `dealbao_spu`    varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci    NOT NULL DEFAULT '',
    ADD COLUMN `dealbao_sku`    varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci    NOT NULL DEFAULT '';
