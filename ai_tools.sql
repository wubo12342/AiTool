-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2026-03-25 17:48:22
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `ai_tools`
--
CREATE DATABASE IF NOT EXISTS `ai_tools` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ai_tools`;

-- --------------------------------------------------------

--
-- 資料表結構 `ai_tools`
--
-- 建立時間： 2026-03-25 16:24:15
--

DROP TABLE IF EXISTS `ai_tools`;
CREATE TABLE `ai_tools` (
  `tool_ID` int(11) NOT NULL,
  `CID` int(11) DEFAULT NULL COMMENT '所屬分類 ID',
  `name` varchar(100) NOT NULL COMMENT '工具名稱',
  `description` text DEFAULT NULL COMMENT '工具功能簡介',
  `website_url` varchar(255) DEFAULT NULL COMMENT '官網連結',
  `logo_url` varchar(255) DEFAULT NULL COMMENT 'Logo 圖片連結',
  `video_url` varchar(255) DEFAULT NULL COMMENT '介紹影片連結',
  `pricing_plans` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '格式: {"status": "Freemium", "last_check": "2026-03-25", "plans": [...]}' CHECK (json_valid(`pricing_plans`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `ai_tools`:
--   `CID`
--       `categories` -> `CID`
--

--
-- 資料表新增資料前，先清除舊資料 `ai_tools`
--

TRUNCATE TABLE `ai_tools`;
-- --------------------------------------------------------

--
-- 資料表結構 `categories`
--
-- 建立時間： 2026-03-25 16:17:17
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `CID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '分類名稱 (如: 圖像生成、程式助手)',
  `description` text DEFAULT NULL COMMENT '分類詳細描述'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `categories`:
--

--
-- 資料表新增資料前，先清除舊資料 `categories`
--

TRUNCATE TABLE `categories`;
-- --------------------------------------------------------

--
-- 資料表結構 `tags`
--
-- 建立時間： 2026-03-25 16:16:19
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `TID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '標籤名 (如: 開源, 免費, 支援中文)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `tags`:
--

--
-- 資料表新增資料前，先清除舊資料 `tags`
--

TRUNCATE TABLE `tags`;
-- --------------------------------------------------------

--
-- 資料表結構 `tool_reviews`
--
-- 建立時間： 2026-03-25 16:42:50
--

DROP TABLE IF EXISTS `tool_reviews`;
CREATE TABLE `tool_reviews` (
  `review_ID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `tool_ID` int(11) NOT NULL,
  `stars` tinyint(4) NOT NULL COMMENT '1-5星評分',
  `comment` text DEFAULT NULL COMMENT '使用者心得',
  `comment_time` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `tool_reviews`:
--   `UID`
--       `user` -> `UID`
--   `tool_ID`
--       `ai_tools` -> `tool_ID`
--

--
-- 資料表新增資料前，先清除舊資料 `tool_reviews`
--

TRUNCATE TABLE `tool_reviews`;
-- --------------------------------------------------------

--
-- 資料表結構 `tool_tag_map`
--
-- 建立時間： 2026-03-25 16:31:59
--

DROP TABLE IF EXISTS `tool_tag_map`;
CREATE TABLE `tool_tag_map` (
  `tool_ID` int(11) NOT NULL,
  `TID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `tool_tag_map`:
--   `tool_ID`
--       `ai_tools` -> `tool_ID`
--   `TID`
--       `tags` -> `TID`
--

--
-- 資料表新增資料前，先清除舊資料 `tool_tag_map`
--

TRUNCATE TABLE `tool_tag_map`;
-- --------------------------------------------------------

--
-- 資料表結構 `user`
--
-- 建立時間： 2026-03-25 16:04:27
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `UID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL COMMENT '帳號',
  `password_hash` varchar(255) NOT NULL COMMENT '加密密碼',
  `role` tinyint(4) DEFAULT 0 COMMENT '0:一般用戶, 1:管理員, 9:超級管理員',
  `system_prompt` text DEFAULT NULL COMMENT '使用者個人化設定：讓 AI 知道你的背景、說話風格或偏好',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `user`:
--

--
-- 資料表新增資料前，先清除舊資料 `user`
--

TRUNCATE TABLE `user`;
-- --------------------------------------------------------

--
-- 資料表結構 `user_likes`
--
-- 建立時間： 2026-03-25 16:38:40
--

DROP TABLE IF EXISTS `user_likes`;
CREATE TABLE `user_likes` (
  `UID` int(11) NOT NULL,
  `tool_ID` int(11) NOT NULL,
  `liked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表的關聯 `user_likes`:
--   `UID`
--       `user` -> `UID`
--   `tool_ID`
--       `ai_tools` -> `tool_ID`
--

--
-- 資料表新增資料前，先清除舊資料 `user_likes`
--

TRUNCATE TABLE `user_likes`;
--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `ai_tools`
--
ALTER TABLE `ai_tools`
  ADD PRIMARY KEY (`tool_ID`),
  ADD KEY `fk_tool_category` (`CID`);

--
-- 資料表索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`CID`);

--
-- 資料表索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`TID`),
  ADD UNIQUE KEY `name` (`name`);

--
-- 資料表索引 `tool_reviews`
--
ALTER TABLE `tool_reviews`
  ADD PRIMARY KEY (`review_ID`),
  ADD UNIQUE KEY `unique_user_review` (`UID`,`tool_ID`),
  ADD KEY `tool_ID` (`tool_ID`);

--
-- 資料表索引 `tool_tag_map`
--
ALTER TABLE `tool_tag_map`
  ADD PRIMARY KEY (`tool_ID`,`TID`),
  ADD KEY `TID` (`TID`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 資料表索引 `user_likes`
--
ALTER TABLE `user_likes`
  ADD PRIMARY KEY (`UID`,`tool_ID`),
  ADD KEY `tool_ID` (`tool_ID`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `ai_tools`
--
ALTER TABLE `ai_tools`
  MODIFY `tool_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `categories`
--
ALTER TABLE `categories`
  MODIFY `CID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tags`
--
ALTER TABLE `tags`
  MODIFY `TID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tool_reviews`
--
ALTER TABLE `tool_reviews`
  MODIFY `review_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user`
--
ALTER TABLE `user`
  MODIFY `UID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `ai_tools`
--
ALTER TABLE `ai_tools`
  ADD CONSTRAINT `fk_tool_category` FOREIGN KEY (`CID`) REFERENCES `categories` (`CID`) ON DELETE SET NULL;

--
-- 資料表的限制式 `tool_reviews`
--
ALTER TABLE `tool_reviews`
  ADD CONSTRAINT `tool_reviews_ibfk_1` FOREIGN KEY (`UID`) REFERENCES `user` (`UID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tool_reviews_ibfk_2` FOREIGN KEY (`tool_ID`) REFERENCES `ai_tools` (`tool_ID`) ON DELETE CASCADE;

--
-- 資料表的限制式 `tool_tag_map`
--
ALTER TABLE `tool_tag_map`
  ADD CONSTRAINT `tool_tag_map_ibfk_1` FOREIGN KEY (`tool_ID`) REFERENCES `ai_tools` (`tool_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tool_tag_map_ibfk_2` FOREIGN KEY (`TID`) REFERENCES `tags` (`TID`) ON DELETE CASCADE;

--
-- 資料表的限制式 `user_likes`
--
ALTER TABLE `user_likes`
  ADD CONSTRAINT `user_likes_ibfk_1` FOREIGN KEY (`UID`) REFERENCES `user` (`UID`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_likes_ibfk_2` FOREIGN KEY (`tool_ID`) REFERENCES `ai_tools` (`tool_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
