-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2026-05-13 14:54:40
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
-- 建立時間： 2026-05-13 12:52:31
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
--
-- 傾印資料表的資料 `ai_tools`
--

INSERT INTO `ai_tools` (`tool_ID`, `CID`, `name`, `description`, `website_url`, `logo_url`, `video_url`, `pricing_plans`) VALUES
(1, 1, 'ChatGPT', 'OpenAI 開發的多功能 AI 對話助手，支援文字生成、程式撰寫、圖像生成與語音互動', 'https://chatgpt.com', 'https://www.google.com/s2/favicons?domain=openai.com&sz=128', 'https://www.youtube.com/watch?v=sL1BNTU-4PI', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版（美國含廣告）\",\"features\":[\"GPT-5.3 有限存取\",\"每5小時10則訊息\",\"有限圖像生成\"]},{\"name\":\"Go\",\"price\":\"8\",\"description\":\"輕量付費版（含廣告）\",\"features\":[\"更高訊息額度\",\"檔案上傳\",\"圖像生成\"]},{\"name\":\"Plus\",\"price\":\"20\",\"description\":\"個人進階版（無廣告）\",\"features\":[\"完整 GPT-5.5 存取\",\"Deep Research 每月10次\",\"Sora 影片生成\",\"Agent Mode\"]},{\"name\":\"Pro\",\"price\":\"100\",\"description\":\"重度使用者方案\",\"features\":[\"Plus 5倍使用額度\",\"GPT-5.5 Pro 模型存取\",\"優先處理\"]},{\"name\":\"Business\",\"price\":\"25\",\"description\":\"企業團隊版（每人/月）\",\"features\":[\"團隊工作空間\",\"資料不參與模型訓練\",\"SOC 2 與 SAML SSO\"]}]}'),
(2, 1, 'Claude', 'Anthropic 開發的 AI 助手，擅長長文分析、程式開發，以安全性與準確性著稱', 'https://claude.ai', 'https://www.google.com/s2/favicons?domain=anthropic.com&sz=128', 'https://www.youtube.com/watch?v=114H9EWKfzM', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費基本版\",\"features\":[\"Claude Sonnet 有限存取\",\"基本對話功能\"]},{\"name\":\"Pro\",\"price\":\"20\",\"description\":\"個人進階版\",\"features\":[\"Claude Sonnet 4.6 完整存取\",\"更高使用額度\",\"Projects 功能\",\"優先回應\"]},{\"name\":\"Max\",\"price\":\"100\",\"description\":\"重度使用者方案\",\"features\":[\"Pro 5倍使用額度\",\"1M Token 超長上下文\",\"優先存取最新模型\"]},{\"name\":\"Team\",\"price\":\"30\",\"description\":\"團隊版（每人/月）\",\"features\":[\"所有 Pro 功能\",\"協作工作空間\",\"管理員控制台\",\"資料不參與訓練\"]}]}'),
(3, 1, 'Gemini', 'Google 開發的多模態 AI 助手，深度整合 Gmail、Docs 等 Google Workspace 服務', 'https://gemini.google.com', 'https://www.google.com/s2/favicons?domain=gemini.google.com&sz=128', 'https://www.youtube.com/watch?v=dHknQINzPdg&t=830s&pp=ygUNZ2VtaW5pIOS7i-e0uQ%3D%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"Gemini 2.0 Flash 存取\",\"基本對話功能\",\"整合 Google 搜尋\"]},{\"name\":\"Advanced\",\"price\":\"19.99\",\"description\":\"Google One AI Premium\",\"features\":[\"Gemini 2.0 Ultra 存取\",\"2TB Google 雲端空間\",\"整合 Gmail、Docs、Drive\",\"Deep Research 功能\"]}]}'),
(4, 2, 'Midjourney', '業界頂尖的 AI 圖像生成工具，以高品質藝術風格與精細畫面著稱', 'https://midjourney.com', 'https://www.google.com/s2/favicons?domain=midjourney.com&sz=128', 'https://www.youtube.com/watch?v=uxYxQmyw3Ys&pp=ygURTWlkam91cm5leSDku4vntLnSBwkJ3woBhyohjO8%3D', '{\"status\":\"Paid\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Basic\",\"price\":\"10\",\"description\":\"入門方案\",\"features\":[\"約200張快速生成/月\",\"商業使用授權\"]},{\"name\":\"Standard\",\"price\":\"30\",\"description\":\"標準方案（最受歡迎）\",\"features\":[\"約900張快速生成/月\",\"無限 Relax 模式\",\"商業使用授權\"]},{\"name\":\"Pro\",\"price\":\"60\",\"description\":\"專業方案\",\"features\":[\"約1800張快速生成/月\",\"Stealth 私密模式\",\"商業使用授權\"]},{\"name\":\"Mega\",\"price\":\"120\",\"description\":\"大量生成方案\",\"features\":[\"約3600張快速生成/月\",\"Stealth 私密模式\",\"最高生成額度\"]}]}'),
(5, 2, 'Adobe Firefly', 'Adobe 出品的 AI 圖像生成工具，以商業授權安全與整合 Creative Cloud 為最大優勢', 'https://firefly.adobe.com', 'https://www.google.com/s2/favicons?domain=firefly.adobe.com&sz=128', 'https://www.youtube.com/watch?v=Kji0SkeSVhw&pp=ygUUQWRvYmUgRmlyZWZseSDku4vntLk%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費體驗版\",\"features\":[\"有限生成積分\",\"標準圖像生成功能\"]},{\"name\":\"Standard\",\"price\":\"9.99\",\"description\":\"入門付費版\",\"features\":[\"2,000 進階積分/月\",\"無限標準圖像生成\",\"商業授權安全\"]},{\"name\":\"Pro\",\"price\":\"19.99\",\"description\":\"個人進階版\",\"features\":[\"4,000 進階積分/月\",\"Adobe Express Premium\",\"Photoshop 網頁版存取\"]},{\"name\":\"Premium\",\"price\":\"199.99\",\"description\":\"高產量專業版\",\"features\":[\"50,000 進階積分/月\",\"AI 影片生成\",\"音訊翻譯功能\"]}]}'),
(6, 2, 'DALL-E 3', 'OpenAI 開發的 AI 圖像生成模型，以精準理解文字描述並轉換為圖像著稱', 'https://openai.com/dall-e-3', 'https://www.google.com/s2/favicons?domain=openai.com&sz=128', 'https://www.youtube.com/watch?v=Wav60DMzP74&pp=ygUOREFMTC1FIDPku4vntLk%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free（via ChatGPT）\",\"price\":\"0\",\"description\":\"透過 ChatGPT 免費版有限使用\",\"features\":[\"有限圖像生成次數\",\"基本解析度輸出\"]},{\"name\":\"Plus（via ChatGPT）\",\"price\":\"20\",\"description\":\"透過 ChatGPT Plus 使用\",\"features\":[\"更高圖像生成額度\",\"高解析度輸出\",\"ChatGPT Images 2.0 功能\"]},{\"name\":\"API\",\"price\":\"0.04\",\"description\":\"按張計費（1024×1024）\",\"features\":[\"每張 $0.04 起\",\"彈性按需使用\",\"支援程式整合\"]}]}'),
(7, 3, 'Runway', 'AI 影片生成與剪輯平台，支援文字/圖片轉影片，廣泛應用於創意影片製作', 'https://runwayml.com', 'https://www.google.com/s2/favicons?domain=runwayml.com&sz=128', 'https://www.youtube.com/watch?v=JImyvx5Ij_U&t=45s&pp=ygUNUnVud2F5IOS7i-e0uQ%3D%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費基本版\",\"features\":[\"125 生成積分\",\"基本影片生成\",\"含浮水印\"]},{\"name\":\"Standard\",\"price\":\"15\",\"description\":\"標準個人版\",\"features\":[\"625 積分/月\",\"無浮水印\",\"Gen-3 Alpha 存取\"]},{\"name\":\"Pro\",\"price\":\"35\",\"description\":\"專業版\",\"features\":[\"2250 積分/月\",\"商業使用授權\",\"進階影片功能\"]}]}'),
(8, 3, 'CapCut', '字節跳動出品的 AI 影片剪輯工具，支援自動字幕、AI 配音與多種創意特效', 'https://capcut.com', 'https://www.google.com/s2/favicons?domain=capcut.com&sz=128', 'https://www.youtube.com/watch?v=z5hL76aKCKk&pp=ygUNQ2FwQ3V0IOS7i-e0uQ%3D%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"基本剪輯功能\",\"1080p 匯出\",\"基本 AI 功能\"]},{\"name\":\"Standard\",\"price\":\"7.99\",\"description\":\"標準版\",\"features\":[\"4K 匯出\",\"雲端同步\",\"進階 AI 特效\",\"移除浮水印\"]},{\"name\":\"Pro\",\"price\":\"9.99\",\"description\":\"專業版\",\"features\":[\"所有 Standard 功能\",\"更多 AI 功能\",\"優先處理速度\",\"進階模板\"]}]}'),
(9, 3, 'Kling AI', '快手出品的頂尖 AI 影片生成工具，以擬真人體動作與角色一致性著稱，2026年影片生成 ELO 排名第一', 'https://kling.ai', 'https://www.google.com/s2/favicons?domain=kling.ai&sz=128', 'https://www.youtube.com/watch?v=M01bDnMfLz4&pp=ygUPS2xpbmcgQUkg5LuL57S5', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"每日66積分\",\"720p 輸出\",\"含浮水印\",\"無音訊生成\"]},{\"name\":\"Standard\",\"price\":\"6.99\",\"description\":\"入門付費版\",\"features\":[\"每月660積分\",\"1080p 輸出\",\"商業使用授權\",\"移除浮水印\"]},{\"name\":\"Pro\",\"price\":\"25.99\",\"description\":\"專業版\",\"features\":[\"每月3,000積分\",\"Kling 2.6 & 3.0 模型存取\",\"原生音訊生成\",\"優先處理佇列\"]},{\"name\":\"Premier\",\"price\":\"64.99\",\"description\":\"高產量方案\",\"features\":[\"每月8,000積分\",\"所有 Pro 功能\",\"Kling 3.0 早期存取\",\"最高優先處理\"]}]}'),
(10, 4, 'GitHub Copilot', 'GitHub 官方 AI 程式助手，整合於主流 IDE，支援多語言程式碼補全與對話式開發', 'https://github.com/features/copilot', 'https://www.google.com/s2/favicons?domain=github.com&sz=128', 'https://www.youtube.com/watch?v=QG1E0SCqqW8&list=PLlrxD0HtieHgr23PS05FIncnih4dH9Na5', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費基本版\",\"features\":[\"每月2000次程式碼補全\",\"基本 Copilot Chat\",\"支援主流 IDE\"]},{\"name\":\"Pro\",\"price\":\"10\",\"description\":\"個人進階版\",\"features\":[\"無限程式碼補全\",\"完整 Copilot Chat\",\"多模型支援\"]},{\"name\":\"Pro+\",\"price\":\"39\",\"description\":\"個人旗艦版\",\"features\":[\"Pro 5倍以上使用額度\",\"最新旗艦模型優先存取\",\"進階代理人功能\"]},{\"name\":\"Business\",\"price\":\"19\",\"description\":\"團隊版（每人/月）\",\"features\":[\"組織管理控制台\",\"企業安全功能\",\"使用量統計\"]}]}'),
(11, 4, 'Cursor', 'AI 驅動的程式碼編輯器（基於 VS Code），支援全程式庫上下文理解與多模型切換', 'https://cursor.com', 'https://www.google.com/s2/favicons?domain=cursor.com&sz=128', 'https://www.youtube.com/watch?v=ocMOZpuAMw4', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Hobby\",\"price\":\"0\",\"description\":\"免費評估版\",\"features\":[\"每月2000次程式碼補全\",\"50次進階模型請求\",\"完整編輯器功能\"]},{\"name\":\"Pro\",\"price\":\"20\",\"description\":\"個人開發者首選\",\"features\":[\"無限 Tab 補全\",\"每月500次快速進階請求\",\"Agent Mode 存取\"]},{\"name\":\"Pro+\",\"price\":\"60\",\"description\":\"重度使用者方案\",\"features\":[\"Pro 3倍使用額度\",\"所有進階模型存取\"]},{\"name\":\"Teams\",\"price\":\"40\",\"description\":\"團隊版（每人/月）\",\"features\":[\"共用對話與規則\",\"集中帳單管理\",\"SAML SSO 支援\"]}]}'),
(12, 4, 'Tabnine', '企業級 AI 程式碼助手，以支援本地部署與嚴格資料隱私保護為核心優勢', 'https://tabnine.com', 'https://www.google.com/s2/favicons?domain=tabnine.com&sz=128', 'https://www.youtube.com/watch?v=vFBb6CYp6mw&pp=ygUUdGFibmluZSBpbnRyb2R1Y3Rpb24%3D', '{\"status\":\"Paid\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Code Assistant\",\"price\":\"39\",\"description\":\"企業程式碼助手（每人/月，需年繳）\",\"features\":[\"無限程式碼補全\",\"支援本地/私有雲部署\",\"不儲存程式碼資料\",\"多 IDE 支援\"]},{\"name\":\"Agentic Platform\",\"price\":\"59\",\"description\":\"企業代理人平台（每人/月，需年繳）\",\"features\":[\"Code Assistant 全部功能\",\"AI 代理人自動化工作流\",\"進階程式碼審查功能\",\"企業級 SLA\"]}]}'),
(13, 5, 'ElevenLabs', '頂尖 AI 語音生成平台，支援高擬真語音複製、多語言文字轉語音與即時配音', 'https://elevenlabs.io', 'https://www.google.com/s2/favicons?domain=elevenlabs.io&sz=128', 'https://www.youtube.com/watch?v=lgvWO4tOBqs&pp=ygUXRWxldmVuTGFicyBpbnRyb2R1Y3Rpb27SBwkJ3goBhyohjO8%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費體驗版\",\"features\":[\"每月10,000積分（約10分鐘）\",\"基本文字轉語音\",\"不含商業授權\"]},{\"name\":\"Starter\",\"price\":\"5\",\"description\":\"入門付費版\",\"features\":[\"每月30,000積分\",\"商業使用授權\",\"語音複製功能\"]},{\"name\":\"Creator\",\"price\":\"22\",\"description\":\"創作者方案\",\"features\":[\"每月100,000積分\",\"高品質語音輸出\",\"API 存取\",\"29種以上語言\"]}]}'),
(14, 5, 'Murf AI', '專業 AI 配音平台，提供200+種語音、35種以上語言，支援影片時間軸配音編輯', 'https://murf.ai', 'https://www.google.com/s2/favicons?domain=murf.ai&sz=128', 'https://www.youtube.com/watch?v=EQ3IYVbyVmc&pp=ygUHTXVyZiBBSQ%3D%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費試用版\",\"features\":[\"10分鐘語音試聽\",\"不可下載\",\"不含商業授權\"]},{\"name\":\"Creator\",\"price\":\"29\",\"description\":\"創作者方案\",\"features\":[\"每月2小時語音生成\",\"200+種語音\",\"商業使用授權\",\"可下載音檔\"]},{\"name\":\"Business\",\"price\":\"99\",\"description\":\"商業團隊方案\",\"features\":[\"每月8小時語音生成\",\"30種以上語言\",\"協作功能\",\"優先渲染\"]}]}'),
(15, 5, 'Suno', 'AI 音樂生成平台，輸入文字描述或歌詞即可自動生成完整歌曲（含人聲與樂器）', 'https://suno.com', 'https://www.google.com/s2/favicons?domain=suno.com&sz=128', 'https://www.youtube.com/watch?v=Xv_cpCtN4yE&t=5s&pp=ygUFc3VubyA%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"每日50積分（約5首歌）\",\"個人非商業使用\",\"MP3 下載\"]},{\"name\":\"Pro\",\"price\":\"8\",\"description\":\"個人付費版\",\"features\":[\"每月2500積分（約250首歌）\",\"商業使用授權\",\"優先生成佇列\"]},{\"name\":\"Premier\",\"price\":\"24\",\"description\":\"專業版\",\"features\":[\"每月10000積分（約1000首歌）\",\"商業使用授權\",\"最高音質輸出\"]}]}'),
(16, 6, 'Gamma', 'AI 驅動的簡報與網頁製作工具，輸入文字提示即可生成精美簡報，支援多種匯出格式', 'https://gamma.app', 'https://www.google.com/s2/favicons?domain=gamma.app&sz=128', 'https://www.youtube.com/watch?v=k6YmDzt-bVA&pp=ygUFR2FtbWE%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費基本版\",\"features\":[\"400 AI 積分\",\"基本主題樣板\",\"可匯出 PDF\"]},{\"name\":\"Plus\",\"price\":\"10\",\"description\":\"個人進階版\",\"features\":[\"無限 AI 生成\",\"移除 Gamma 浮水印\",\"自訂品牌顏色與 Logo\",\"匯出 PowerPoint\"]},{\"name\":\"Pro\",\"price\":\"20\",\"description\":\"專業版\",\"features\":[\"進階 AI 模型\",\"自訂網域（最多10個）\",\"進階分析報告\",\"API 存取\"]}]}'),
(17, 6, 'Beautiful.ai', 'AI 智慧版面自動排版的簡報工具，根據內容自動調整佈局，提升簡報設計效率', 'https://beautiful.ai', 'https://www.google.com/s2/favicons?domain=beautiful.ai&sz=128', 'https://www.youtube.com/watch?v=eX_xmSJVGqQ&pp=ygUMQmVhdXRpZnVsLmFp', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費試用版\",\"features\":[\"有限簡報數量\",\"基本 AI 排版功能\",\"線上分享\"]},{\"name\":\"Pro\",\"price\":\"12\",\"description\":\"個人進階版\",\"features\":[\"無限簡報數量\",\"完整 AI 排版\",\"自訂品牌套件\",\"PDF 與 PPT 匯出\"]},{\"name\":\"Team\",\"price\":\"40\",\"description\":\"團隊版（每人/月）\",\"features\":[\"所有 Pro 功能\",\"共用品牌模板\",\"協作編輯\",\"管理員控制台\"]}]}'),
(18, 6, 'Canva AI', 'Canva 整合 AI 的設計平台，支援 AI 圖像生成、文字轉設計與簡報一鍵生成', 'https://canva.com', 'https://www.google.com/s2/favicons?domain=canva.com&sz=128', 'https://www.youtube.com/watch?v=eMUnZnYT81I', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"基本設計功能\",\"有限 AI 生成次數\",\"數千種免費模板\"]},{\"name\":\"Pro\",\"price\":\"15\",\"description\":\"個人進階版\",\"features\":[\"無限 AI 圖像生成\",\"品牌套件\",\"Background Remover\",\"100GB 雲端空間\"]},{\"name\":\"Teams\",\"price\":\"10\",\"description\":\"團隊版（每人/月，最低2人）\",\"features\":[\"所有 Pro 功能\",\"協作設計空間\",\"團隊模板管理\",\"進階分析\"]}]}'),
(19, 7, 'Notion AI', 'Notion 整合的 AI 功能，可在文件中直接進行摘要、翻譯、資料庫自動填充與 AI 代理人', 'https://notion.so', 'https://www.google.com/s2/favicons?domain=notion.so&sz=128', 'https://www.youtube.com/watch?v=mNhVKQ7LHno&pp=ygUJTm90aW9uIEFJ', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版（含有限 AI 試用）\",\"features\":[\"無限頁面\",\"基本協作功能\",\"有限 AI 功能試用\"]},{\"name\":\"Plus\",\"price\":\"10\",\"description\":\"小型團隊版（每人/月）\",\"features\":[\"無限檔案上傳\",\"版本記錄（30天）\",\"自訂網域\"]},{\"name\":\"Business\",\"price\":\"20\",\"description\":\"成長團隊版（每人/月，含完整 AI）\",\"features\":[\"無限 AI 功能\",\"私人工作空間\",\"90天版本記錄\",\"進階分析\"]}]}'),
(20, 7, 'Julius AI', 'AI 資料分析工具，支援上傳 CSV/Excel 後以自然語言提問，自動生成圖表與洞察', 'https://julius.ai', 'https://www.google.com/s2/favicons?domain=julius.ai&sz=128', 'https://www.youtube.com/watch?v=7C_K-VSYgnU&pp=ygUJSnVsaXVzIEFJ', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"每日有限查詢次數\",\"CSV 資料上傳\",\"基本圖表生成\"]},{\"name\":\"Pro\",\"price\":\"20\",\"description\":\"個人進階版\",\"features\":[\"無限查詢次數\",\"多種資料來源整合\",\"進階視覺化圖表\",\"匯出報告\"]},{\"name\":\"Team\",\"price\":\"40\",\"description\":\"團隊版（每人/月）\",\"features\":[\"所有 Pro 功能\",\"共用工作空間\",\"協作分析\",\"優先支援\"]}]}'),
(21, 7, 'Perplexity AI', 'AI 搜尋與研究平台，結合即時網路搜尋與 AI 生成答案，提供有引用來源的深度研究', 'https://perplexity.ai', 'https://www.google.com/s2/favicons?domain=perplexity.ai&sz=128', 'https://www.youtube.com/watch?v=5GD9agWsfAg&pp=ygUNUGVycGxleGl0eSBBSQ%3D%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"基本 AI 搜尋\",\"有限 Pro 搜尋次數（每4小時5次）\",\"標準語言模型\"]},{\"name\":\"Pro\",\"price\":\"20\",\"description\":\"個人進階版\",\"features\":[\"無限 Pro 搜尋\",\"GPT-5、Claude、Gemini 模型切換\",\"Deep Research 功能\",\"檔案上傳分析\"]},{\"name\":\"Enterprise\",\"price\":\"40\",\"description\":\"企業版（每人/月）\",\"features\":[\"所有 Pro 功能\",\"資料不參與訓練\",\"SSO 企業登入\",\"管理員控制台\"]}]}'),
(22, 8, 'DeepL', '以高品質神經機器翻譯著稱的翻譯平台，支援30種以上語言，翻譯準確度領先業界', 'https://deepl.com', 'https://www.google.com/s2/favicons?domain=deepl.com&sz=128', 'https://www.youtube.com/watch?v=GdLVIS00cmw&pp=ygUFZGVlcGw%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"每次最多5000字\",\"每月1份文件翻譯\",\"有限語言支援\"]},{\"name\":\"Starter\",\"price\":\"10.49\",\"description\":\"個人入門版\",\"features\":[\"每月300,000字翻譯\",\"每月3份文件翻譯\",\"資料不參與訓練\",\"30+語言支援\"]},{\"name\":\"Advanced\",\"price\":\"34.49\",\"description\":\"進階版（每人/月）\",\"features\":[\"更高字數限制\",\"每月10份文件翻譯\",\"詞彙表功能\",\"API 整合\"]},{\"name\":\"Ultimate\",\"price\":\"68.99\",\"description\":\"旗艦版（每人/月）\",\"features\":[\"最高字數限制\",\"無限文件翻譯\",\"進階詞彙表\",\"優先支援\"]}]}'),
(23, 8, 'Microsoft Translator', 'Microsoft 出品的 AI 翻譯服務，支援100種以上語言，深度整合 Office、Teams 與 Edge 瀏覽器', 'https://translator.microsoft.com', 'https://www.google.com/s2/favicons?domain=translator.microsoft.com&sz=128', 'https://www.youtube.com/watch?v=DxNwTzecc7Y&pp=ygUUTWljcm9zb2Z0IFRyYW5zbGF0b3I%3D', '{\"status\":\"Free\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"完全免費使用\",\"features\":[\"支援100種以上語言\",\"文字、語音、圖片翻譯\",\"整合 Word、PowerPoint、Teams\",\"即時對話翻譯\"]},{\"name\":\"API Free\",\"price\":\"0\",\"description\":\"開發者免費額度\",\"features\":[\"每月200萬字元免費\",\"REST API 存取\",\"自訂翻譯模型\"]},{\"name\":\"API Standard\",\"price\":\"10\",\"description\":\"API 付費方案（每100萬字元）\",\"features\":[\"無使用上限\",\"企業級 SLA\",\"Azure 整合\"]}]}'),
(24, 8, 'Reverso', 'AI 翻譯平台，結合翻譯、例句語境與單字學習，全球超過5億用戶，深受語言學習者喜愛', 'https://reverso.net', 'https://www.google.com/s2/favicons?domain=reverso.net&sz=128', 'https://www.youtube.com/watch?v=QHMwLch3Fmw&pp=ygUQcmV2ZXJzbyB0cmFuc2xhdNIHCQneCgGHKiGM7w%3D%3D', '{\"status\":\"Freemium\",\"last_check\":\"2026-04-30\",\"currency\":\"USD\",\"plans\":[{\"name\":\"Free\",\"price\":\"0\",\"description\":\"免費版\",\"features\":[\"基本翻譯功能\",\"例句語境查詢\",\"15種語言支援\",\"每日有限次數\"]},{\"name\":\"Premium\",\"price\":\"9.99\",\"description\":\"個人進階版\",\"features\":[\"無限翻譯次數\",\"完整例句資料庫\",\"語音發音功能\",\"無廣告體驗\",\"跨裝置同步\"]}]}');

-- --------------------------------------------------------

--
-- 資料表結構 `categories`
--
-- 建立時間： 2026-05-13 12:52:31
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
--
-- 傾印資料表的資料 `categories`
--

INSERT INTO `categories` (`CID`, `name`, `description`) VALUES
(1, '文字生成', '協助撰寫文案內容'),
(2, '圖像生成', '生成視覺圖像'),
(3, '影片製作', '支援影片生成或剪輯'),
(4, '程式開發', '協助撰寫、除錯或解釋程式碼'),
(5, '語音生成', '語音或仿聲音生成'),
(6, '簡報設計', '生成簡報內容與版面'),
(7, '資料整理', '協助處理、分析或數據'),
(8, '翻譯語言', '語言翻譯或學習輔助');

-- --------------------------------------------------------

--
-- 資料表結構 `tags`
--
-- 建立時間： 2026-05-13 12:52:31
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
-- 建立時間： 2026-05-13 12:52:31
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
--
-- 傾印資料表的資料 `tool_reviews`
--

INSERT INTO `tool_reviews` (`review_ID`, `UID`, `tool_ID`, `stars`, `comment`, `comment_time`) VALUES
(1, 1, 1, 5, ' ', '2026-05-06 23:49:26'),
(2, 1, 2, 5, ' ', '2026-05-06 23:49:26'),
(3, 1, 3, 5, ' ', '2026-05-06 23:49:26'),
(4, 1, 4, 5, ' ', '2026-05-06 23:49:26'),
(5, 1, 5, 5, ' ', '2026-05-06 23:49:26'),
(6, 1, 6, 5, ' ', '2026-05-06 23:49:26'),
(7, 1, 7, 5, ' ', '2026-05-06 23:49:26'),
(8, 1, 8, 5, ' ', '2026-05-06 23:49:26'),
(9, 1, 9, 5, ' ', '2026-05-06 23:49:26'),
(10, 1, 10, 5, ' ', '2026-05-06 23:49:26'),
(11, 1, 11, 5, ' ', '2026-05-06 23:49:26'),
(12, 1, 12, 5, ' ', '2026-05-06 23:49:26'),
(13, 1, 13, 5, ' ', '2026-05-06 23:49:26'),
(14, 1, 14, 5, ' ', '2026-05-06 23:49:26'),
(15, 1, 15, 5, ' ', '2026-05-06 23:49:26'),
(16, 1, 16, 5, ' ', '2026-05-06 23:49:26'),
(17, 1, 17, 5, ' ', '2026-05-06 23:49:26'),
(18, 1, 18, 5, ' ', '2026-05-06 23:49:26'),
(19, 1, 19, 5, ' ', '2026-05-06 23:49:26'),
(20, 1, 20, 5, ' ', '2026-05-06 23:49:26'),
(21, 1, 21, 5, ' ', '2026-05-06 23:49:26'),
(22, 1, 22, 5, ' ', '2026-05-06 23:49:26'),
(23, 1, 23, 5, ' ', '2026-05-06 23:49:26'),
(24, 1, 24, 5, '（僅給予星級評分）', '2026-05-06 23:54:42'),
(34, 2, 24, 3, 'aa', '2026-05-06 23:55:01');

-- --------------------------------------------------------

--
-- 資料表結構 `tool_tag_map`
--
-- 建立時間： 2026-05-13 12:52:31
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
-- 建立時間： 2026-05-13 12:52:31
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
--
-- 傾印資料表的資料 `user`
--

INSERT INTO `user` (`UID`, `username`, `password_hash`, `role`, `system_prompt`, `created_at`) VALUES
(1, '使用者', '$2y$10$1fyk/JxOvcm3CIbZtI2QRuFlO54wwWyxqfGNwtyj2yl2ZILz/cPci', 0, NULL, '2026-05-06 23:49:09'),
(2, 'aa', '$2y$10$lAlkvp.5akPn/v726hA9cOqWcpiFdRN61slwjhwgx4WPhZCVE1DiG', 0, '對話都加上我是可愛的小助手', '2026-05-06 23:49:46');

-- --------------------------------------------------------

--
-- 資料表結構 `user_likes`
--
-- 建立時間： 2026-05-13 12:52:31
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
  MODIFY `tool_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `categories`
--
ALTER TABLE `categories`
  MODIFY `CID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tags`
--
ALTER TABLE `tags`
  MODIFY `TID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tool_reviews`
--
ALTER TABLE `tool_reviews`
  MODIFY `review_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user`
--
ALTER TABLE `user`
  MODIFY `UID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
