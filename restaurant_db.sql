SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `restaurant` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `restaurant`;

CREATE TABLE `password_reset`
(
    `account_id`                      int(10) UNSIGNED NOT NULL,
    `password_reset_token`            varchar(255)     NOT NULL,
    `password_reset_token_expires_at` datetime         NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

CREATE TABLE `reservation`
(
    `reservation_id`      int(10) UNSIGNED NOT NULL,
    `account_id`          int(10) UNSIGNED NOT NULL,
    `table_id`            int(10) UNSIGNED NOT NULL,
    `date_of_reservation` datetime         NOT NULL,
    `number_of_people`    int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

CREATE TABLE `reservation_block`
(
    `block_id`  int(10) UNSIGNED NOT NULL,
    `starts_at` datetime         NOT NULL,
    `ends_at`   datetime         NOT NULL,
    `reason`    varchar(255) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

CREATE TABLE `tables_info`
(
    `table_id` int(10) UNSIGNED NOT NULL,
    `name`     varchar(255)     NOT NULL,
    `location` varchar(255)     NOT NULL,
    `capacity` int(10) UNSIGNED NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

CREATE TABLE `users`
(
    `account_id`                    int(10) UNSIGNED                  NOT NULL,
    `email`                         varchar(255)                      NOT NULL,
    `password_hash`                 varchar(255)                      NOT NULL,
    `created_at`                    datetime                          NOT NULL DEFAULT utc_timestamp(),
    `verfication_token_used_at`     datetime                                   DEFAULT NULL,
    `verification_token`            varchar(64)                       NOT NULL,
    `verification_token_expires_at` datetime                          NOT NULL,
    `role`                          enum ('client','manager','admin') NOT NULL DEFAULT 'client'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

ALTER TABLE `password_reset`
    ADD PRIMARY KEY (`account_id`);

ALTER TABLE `reservation`
    ADD PRIMARY KEY (`reservation_id`),
    ADD KEY `fk_user` (`account_id`),
    ADD KEY `fk_table` (`table_id`),
    ADD KEY `idx_reservation_table_date` (`table_id`, `date_of_reservation`);

ALTER TABLE `reservation_block`
    ADD PRIMARY KEY (`block_id`),
    ADD KEY `idx_reservation_block_dates` (`starts_at`, `ends_at`);

ALTER TABLE `tables_info`
    ADD PRIMARY KEY (`table_id`),
    ADD UNIQUE KEY `uq_tables_name` (`name`);

ALTER TABLE `users`
    ADD PRIMARY KEY (`account_id`),
    ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `reservation`
    MODIFY `reservation_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `reservation_block`
    MODIFY `block_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tables_info`
    MODIFY `table_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
    MODIFY `account_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_reset`
    ADD CONSTRAINT `fk_pw_reset_user` FOREIGN KEY (`account_id`) REFERENCES `users` (`account_id`) ON DELETE CASCADE;

ALTER TABLE `reservation`
    ADD CONSTRAINT `fk_table` FOREIGN KEY (`table_id`) REFERENCES `tables_info` (`table_id`),
    ADD CONSTRAINT `fk_user` FOREIGN KEY (`account_id`) REFERENCES `users` (`account_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;


INSERT INTO tables_info (table_id, name, location, capacity, is_active)
VALUES (1, 'Stolik 1', 'Sala główna', 2, 1),
       (2, 'Stolik 2', 'Sala główna', 2, 1),
       (3, 'Stolik 3', 'Sala główna', 4, 1),
       (4, 'Stolik 4', 'Sala główna', 4, 1),
       (5, 'Stolik 5', 'Sala główna', 4, 1),
       (6, 'Stolik 6', 'Sala główna', 6, 1),
       (7, 'Stolik 7', 'Sala główna', 6, 1),
       (8, 'Stolik 8', 'Taras', 2, 1),
       (9, 'Stolik 9', 'Taras', 4, 1),
       (10, 'Stolik 10', 'Taras', 8, 1);