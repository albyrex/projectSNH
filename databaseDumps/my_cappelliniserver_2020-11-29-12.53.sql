/*
 Navicat Premium Data Transfer

 Source Server         : root@localhost (no pass)
 Source Server Type    : MySQL
 Source Server Version : 100316
 Source Host           : localhost:3306
 Source Schema         : my_cappelliniserver

 Target Server Type    : MySQL
 Target Server Version : 100316
 File Encoding         : 65001

 Date: 29/11/2020 12:53:59
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for books
-- ----------------------------
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books`  (
  `id_book` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `author` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `price` decimal(10, 2) NOT NULL,
  PRIMARY KEY (`id_book`) USING BTREE,
  UNIQUE INDEX `title_author_unique`(`title`, `author`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of books
-- ----------------------------
INSERT INTO `books` VALUES (1, 'Il Mago dei Numeri', 'Hans Magnus Enzensberger', 10.00);
INSERT INTO `books` VALUES (2, 'Momo', 'Michael Ende', 11.95);
INSERT INTO `books` VALUES (3, 'Il Cavaliere Inesistente', 'Italo Calvino', 9.35);

-- ----------------------------
-- Table structure for password_recovery_requests
-- ----------------------------
DROP TABLE IF EXISTS `password_recovery_requests`;
CREATE TABLE `password_recovery_requests`  (
  `id_user` int(255) NOT NULL,
  `password_recovery_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  PRIMARY KEY (`id_user`) USING BTREE,
  CONSTRAINT `id_user_fk2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_recovery_requests
-- ----------------------------

-- ----------------------------
-- Table structure for payments
-- ----------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments`  (
  `id_user` int(11) NOT NULL,
  `id_book` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  PRIMARY KEY (`id_user`, `id_book`) USING BTREE,
  INDEX `id_book_fk`(`id_book`) USING BTREE,
  CONSTRAINT `id_book_fk` FOREIGN KEY (`id_book`) REFERENCES `books` (`id_book`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `id_user_fk` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of payments
-- ----------------------------
INSERT INTO `payments` VALUES (1, 1, 0);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id_user` int(255) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `username` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `admin` int(255) NOT NULL DEFAULT 0,
  `answers` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `verified_email` tinyint(4) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `consecutive_failed_login_count` int(11) NOT NULL DEFAULT 0,
  `failed_login_timestamp` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_user`) USING BTREE,
  UNIQUE INDEX `email_unique`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'user@user.com', 'user', '$2y$10$ijxKZM7aB88BUcbNbn8IYeLxUedWMc5oxYlTPU2sr.gNJXsD8v3A.', 1, '[\"USER\",\"USER\",\"USER\"]', 0, NULL, 0, 0);
INSERT INTO `users` VALUES (3, 'user2@user2.it', 'user2', '$2y$10$ijxKZM7aB88BUcbNbn8IYeLxUedWMc5oxYlTPU2sr.gNJXsD8v3A.', 0, '[\"ciao\",\"ciao\",\"ciao\"]', 0, NULL, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
