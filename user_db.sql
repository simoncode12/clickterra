-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 30 Jun 2025 pada 07.11
-- Versi server: 11.4.7-MariaDB-deb12
-- Versi PHP: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `ad_formats`
--

CREATE TABLE `ad_formats` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `ad_formats`
--

INSERT INTO `ad_formats` (`id`, `name`, `status`) VALUES
(1, 'Banner', 1),
(2, 'Native', 1),
(3, 'Video', 1),
(4, 'Pop', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `advertiser_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ad_format_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `serve_on_internal` tinyint(1) NOT NULL DEFAULT 1,
  `allow_external_rtb` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','paused','completed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `campaigns`
--

INSERT INTO `campaigns` (`id`, `advertiser_id`, `category_id`, `ad_format_id`, `name`, `serve_on_internal`, `allow_external_rtb`, `status`, `created_at`) VALUES
(2, 2, 2, 1, 'banner', 1, 1, 'active', '2025-06-29 09:43:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `campaign_stats`
--

CREATE TABLE `campaign_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `creative_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `device` varchar(50) DEFAULT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `cost` decimal(12,6) NOT NULL DEFAULT 0.000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `campaign_stats`
--

INSERT INTO `campaign_stats` (`id`, `campaign_id`, `creative_id`, `zone_id`, `country`, `os`, `browser`, `device`, `stat_date`, `impressions`, `clicks`, `cost`) VALUES
(1, 2, 2, 145855, 'Indonesia', 'UNKNOWN', 'UNKNOWN', 'Mobile', '2025-06-29', 17, 0, 0.000000),
(18, 2, 2, 11705777, 'Australia', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 6, 0, 0.000000),
(19, 2, 2, 11705777, 'United Kingdom', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 43, 0, 0.000000),
(20, 2, 2, 11705777, 'USA', 'iOS iOs 17', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 8, 0, 0.000000),
(22, 2, 2, 11714734, 'USA', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(23, 2, 2, 11705777, 'USA', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 9, 0, 0.000000),
(24, 2, 2, 11714734, 'USA', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 11, 0, 0.000000),
(26, 2, 2, 11705777, 'USA', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 41, 0, 0.000000),
(27, 2, 2, 11685610, 'USA', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 4, 0, 0.000000),
(28, 2, 2, 11714734, 'Japan', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 4, 0, 0.000000),
(29, 2, 2, 11705780, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 5, 0, 0.000000),
(31, 2, 2, 11685610, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 5, 0, 0.000000),
(32, 2, 2, 11705777, 'Japan', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 16, 0, 0.000000),
(34, 2, 2, 11705780, 'United Kingdom', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 22, 0, 0.000000),
(37, 2, 2, 11705780, 'USA', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 12, 0, 0.000000),
(39, 2, 2, 11714734, 'USA', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 13, 0, 0.000000),
(40, 2, 2, 11705780, 'United Kingdom', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 4, 0, 0.000000),
(42, 2, 2, 11714734, 'United Kingdom', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 18, 0, 0.000000),
(46, 2, 2, 11705777, 'USA', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 4, 0, 0.000000),
(48, 2, 2, 11705777, 'USA', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 17, 0, 0.000000),
(53, 2, 2, 11714734, 'United Kingdom', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 9, 0, 0.000000),
(57, 2, 2, 11705780, 'USA', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 5, 0, 0.000000),
(58, 2, 2, 11705780, 'Australia', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(59, 2, 2, 11685610, 'Japan', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 9, 0, 0.000000),
(61, 2, 2, 11705780, 'Australia', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(70, 2, 2, 11685610, 'Australia', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(72, 2, 2, 11705777, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 8, 0, 0.000000),
(74, 2, 2, 11685610, 'United Kingdom', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 9, 0, 0.000000),
(76, 2, 2, 11706422, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(81, 2, 2, 11705777, 'United Kingdom', 'iOS iOs 19', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(84, 2, 2, 11714734, 'USA', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(90, 2, 2, 11685610, 'United Kingdom', 'iOS iOs 19', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(92, 2, 2, 11705786, 'USA', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(96, 2, 2, 11705780, 'United Kingdom', 'iOS iOs 19', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(98, 2, 2, 11705780, 'United Kingdom', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(102, 2, 2, 11714734, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(105, 2, 2, 11705777, 'United Kingdom', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 8, 0, 0.000000),
(106, 2, 2, 11705777, 'USA', 'MacOSX Big Sur', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(108, 2, 2, 11714734, 'United Kingdom', 'iOS iOs 19', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(113, 2, 2, 11705780, 'United Kingdom', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 5, 0, 0.000000),
(115, 2, 2, 11685610, 'USA', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(116, 2, 2, 11705780, 'USA', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(120, 2, 2, 11714734, 'Australia', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(126, 2, 2, 11705786, 'USA', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(143, 2, 2, 11705780, 'Japan', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(145, 2, 2, 11705777, 'Japan', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(148, 2, 2, 11705780, 'Japan', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(165, 2, 2, 11705780, 'United Kingdom', 'iOS iOs 17', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(168, 2, 2, 11705786, 'United Kingdom', 'Android Android 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(171, 2, 2, 11714734, 'USA', 'iOS iOs 17', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(179, 2, 2, 11685610, 'United Kingdom', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(180, 2, 2, 11705777, 'Australia', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 10, 0, 0.000000),
(181, 2, 2, 11705777, 'United Kingdom', 'Android Android 14', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(183, 2, 2, 11705777, 'Japan', 'Android Android 14', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 4, 0, 0.000000),
(187, 2, 2, 11705783, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(197, 2, 2, 11685610, 'USA', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(198, 2, 2, 11705786, 'United Kingdom', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(200, 2, 2, 11705786, 'Japan', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(211, 2, 2, 11705780, 'Australia', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 4, 0, 0.000000),
(216, 2, 2, 11685610, 'USA', 'iOS iOs 15', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(224, 2, 2, 11705780, 'USA', 'iOS iOs 17', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(245, 2, 2, 11685610, 'Australia', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 6, 0, 0.000000),
(253, 2, 2, 11705780, 'Australia', 'Android Android 15', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(288, 2, 2, 11705777, 'United Kingdom', 'Linux', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(289, 2, 2, 11705777, 'USA', 'iOS iOs 15', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(293, 2, 2, 11714734, 'Australia', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(299, 2, 2, 11705786, 'Australia', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(317, 2, 2, 11705786, 'Indonesia', 'Android Android 12', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(323, 2, 2, 11714734, 'Japan', 'Android Android 14', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(336, 2, 2, 11705780, 'USA', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(364, 2, 2, 11705780, 'Japan', 'Android Android 14', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(378, 2, 2, 11705777, 'United Kingdom', 'Android Android 15', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 9, 0, 0.000000),
(389, 2, 2, 11705777, 'Japan', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 3, 0, 0.000000),
(401, 2, 2, 11705780, 'Japan', 'MacOSX Catalina', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(403, 2, 2, 11705780, 'United Kingdom', 'Android Android 15', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 9, 0, 0.000000),
(423, 2, 2, 11685610, 'USA', 'Windows Windows 10', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(424, 2, 2, 11705777, 'Australia', 'Android Android 13', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 1, 0, 0.000000),
(432, 2, 2, 11705777, 'Australia', 'iOS iOs 18', 'UNKNOWN', 'UNKNOWN', '2025-06-29', 2, 0, 0.000000),
(436, 2, 2, 145855, 'Indonesia', 'UNKNOWN', 'UNKNOWN', 'Mobile', '2025-06-30', 4, 0, 0.000032);

-- --------------------------------------------------------

--
-- Struktur dari tabel `campaign_targeting`
--

CREATE TABLE `campaign_targeting` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `countries` text DEFAULT NULL,
  `browsers` text DEFAULT NULL,
  `devices` text DEFAULT NULL,
  `os` text DEFAULT NULL,
  `connection_types` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `campaign_targeting`
--

INSERT INTO `campaign_targeting` (`id`, `campaign_id`, `countries`, `browsers`, `devices`, `os`, `connection_types`) VALUES
(2, 2, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `status`, `created_at`) VALUES
(1, 'Mainstream', 1, '2025-06-28 20:14:36'),
(2, 'Adult', 1, '2025-06-28 20:14:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `creatives`
--

CREATE TABLE `creatives` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `creative_type` enum('image','script') NOT NULL DEFAULT 'image',
  `bid_model` enum('cpc','cpm') NOT NULL,
  `bid_amount` decimal(10,4) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `landing_url` varchar(2048) DEFAULT NULL,
  `script_content` text DEFAULT NULL,
  `sizes` varchar(255) NOT NULL COMMENT 'e.g., "300x250" or "all" for RTB',
  `status` enum('active','paused') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `creatives`
--

INSERT INTO `creatives` (`id`, `campaign_id`, `name`, `creative_type`, `bid_model`, `bid_amount`, `image_url`, `landing_url`, `script_content`, `sizes`, `status`, `created_at`) VALUES
(2, 2, '300x250', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e2\" data-zoneid=\"5548370\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:10:44'),
(3, 2, '728x90', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e2\" data-zoneid=\"5548372\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:11:22'),
(4, 2, '160x600', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e2\" data-zoneid=\"5548374\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:11:53'),
(5, 2, '300x500', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e2\" data-zoneid=\"5548378\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:12:37'),
(6, 2, '900x250', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e2\" data-zoneid=\"5548376\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:13:05'),
(7, 2, '300x100', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e10\" data-zoneid=\"5548388\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:13:40'),
(8, 2, '300x50', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e10\" data-zoneid=\"5548390\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:14:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `geo_browsers`
--

CREATE TABLE `geo_browsers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `geo_browsers`
--

INSERT INTO `geo_browsers` (`id`, `name`) VALUES
(43, 'Aloha Browser'),
(44, 'Amazon Silk'),
(34, 'Avant Browser'),
(19, 'Avast Secure Browser'),
(15, 'Baidu Browser'),
(41, 'Bingbot'),
(26, 'BlackBerry Browser'),
(9, 'Brave'),
(1, 'Chrome'),
(14, 'Chromium'),
(50, 'CM Browser'),
(16, 'Coc Coc'),
(21, 'Comodo Dragon'),
(24, 'Dolphin'),
(42, 'DuckDuckGo Privacy Browser'),
(4, 'Edge'),
(20, 'Epic'),
(35, 'Falkon'),
(2, 'Firefox'),
(47, 'FreeU'),
(40, 'Googlebot'),
(37, 'IceDragon'),
(29, 'Iceweasel'),
(7, 'Internet Explorer'),
(36, 'K-Meleon'),
(46, 'Kiwi Browser'),
(27, 'Konqueror'),
(32, 'Lynx'),
(12, 'Maxthon'),
(39, 'Microsoft WebView2'),
(33, 'Midori'),
(54, 'Min Browser'),
(25, 'Nokia Browser'),
(5, 'Opera'),
(38, 'Opera GX'),
(52, 'Otter Browser'),
(30, 'Pale Moon'),
(45, 'Phoenix Browser'),
(13, 'Puffin'),
(17, 'QQ Browser'),
(53, 'QuteBrowser'),
(3, 'Safari'),
(6, 'Samsung Internet'),
(28, 'Seamonkey'),
(49, 'Slimjet'),
(18, 'Sogou Explorer'),
(22, 'SRWare Iron'),
(51, 'Superbird'),
(48, 'Tenta Browser'),
(23, 'Tor Browser'),
(8, 'UC Browser'),
(10, 'Vivaldi'),
(31, 'Waterfox'),
(55, 'Wave Browser'),
(11, 'Yandex Browser');

-- --------------------------------------------------------

--
-- Struktur dari tabel `geo_connections`
--

CREATE TABLE `geo_connections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `geo_connections`
--

INSERT INTO `geo_connections` (`id`, `name`) VALUES
(3, '2G'),
(4, '3G'),
(5, '4G'),
(6, '5G'),
(13, 'Bluetooth'),
(9, 'Cable'),
(2, 'Cellular'),
(12, 'Dial-up'),
(8, 'DSL'),
(7, 'Ethernet'),
(10, 'Fiber'),
(14, 'LoRaWAN'),
(15, 'Powerline'),
(11, 'Satellite'),
(16, 'Unknown'),
(1, 'WiFi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `geo_countries`
--

CREATE TABLE `geo_countries` (
  `id` int(11) NOT NULL,
  `iso_alpha_3_code` varchar(3) NOT NULL,
  `iso_alpha_2_code` varchar(2) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `geo_countries`
--

INSERT INTO `geo_countries` (`id`, `iso_alpha_3_code`, `iso_alpha_2_code`, `name`) VALUES
(1, 'AFG', 'AF', 'Afghanistan'),
(2, 'ALB', 'AL', 'Albania'),
(3, 'DZA', 'DZ', 'Algeria'),
(4, 'AND', 'AD', 'Andorra'),
(5, 'AGO', 'AO', 'Angola'),
(6, 'AIA', 'AI', 'Anguilla'),
(7, 'ATA', 'AQ', 'Antarctica'),
(8, 'ATG', 'AG', 'Antigua and Barbuda'),
(9, 'ARG', 'AR', 'Argentina'),
(10, 'ARM', 'AM', 'Armenia'),
(11, 'ABW', 'AW', 'Aruba'),
(12, 'AUS', 'AU', 'Australia'),
(13, 'AUT', 'AT', 'Austria'),
(14, 'AZE', 'AZ', 'Azerbaijan'),
(15, 'BHS', 'BS', 'Bahamas'),
(16, 'BHR', 'BH', 'Bahrain'),
(17, 'BGD', 'BD', 'Bangladesh'),
(18, 'BRB', 'BB', 'Barbados'),
(19, 'BLR', 'BY', 'Belarus'),
(20, 'BEL', 'BE', 'Belgium'),
(21, 'BLZ', 'BZ', 'Belize'),
(22, 'BEN', 'BJ', 'Benin'),
(23, 'BMU', 'BM', 'Bermuda'),
(24, 'BTN', 'BT', 'Bhutan'),
(25, 'BOL', 'BO', 'Bolivia'),
(26, 'BES', 'BQ', 'Bonaire, Sint Eustatius and Saba'),
(27, 'BIH', 'BA', 'Bosnia and Herzegovina'),
(28, 'BWA', 'BW', 'Botswana'),
(29, 'BVT', 'BV', 'Bouvet Island'),
(30, 'BRA', 'BR', 'Brazil'),
(31, 'IOT', 'IO', 'British Indian Ocean Territory'),
(32, 'BRN', 'BN', 'Brunei Darussalam'),
(33, 'BGR', 'BG', 'Bulgaria'),
(34, 'BFA', 'BF', 'Burkina Faso'),
(35, 'BDI', 'BI', 'Burundi'),
(36, 'CPV', 'CV', 'Cabo Verde'),
(37, 'KHM', 'KH', 'Cambodia'),
(38, 'CMR', 'CM', 'Cameroon'),
(39, 'CAN', 'CA', 'Canada'),
(40, 'CYM', 'KY', 'Cayman Islands'),
(41, 'CAF', 'CF', 'Central African Republic'),
(42, 'TCD', 'TD', 'Chad'),
(43, 'CHL', 'CL', 'Chile'),
(44, 'CHN', 'CN', 'China'),
(45, 'CXR', 'CX', 'Christmas Island'),
(46, 'CCK', 'CC', 'Cocos (Keeling) Islands'),
(47, 'COL', 'CO', 'Colombia'),
(48, 'COM', 'KM', 'Comoros'),
(49, 'COG', 'CG', 'Congo'),
(50, 'COD', 'CD', 'Congo, Democratic Republic of the'),
(51, 'COK', 'CK', 'Cook Islands'),
(52, 'CRI', 'CR', 'Costa Rica'),
(53, 'CIV', 'CI', 'Côte d\'Ivoire'),
(54, 'HRV', 'HR', 'Croatia'),
(55, 'CUB', 'CU', 'Cuba'),
(56, 'CUW', 'CW', 'Curaçao'),
(57, 'CYP', 'CY', 'Cyprus'),
(58, 'CZE', 'CZ', 'Czechia'),
(59, 'DNK', 'DK', 'Denmark'),
(60, 'DJI', 'DJ', 'Djibouti'),
(61, 'DMA', 'DM', 'Dominica'),
(62, 'DOM', 'DO', 'Dominican Republic'),
(63, 'ECU', 'EC', 'Ecuador'),
(64, 'EGY', 'EG', 'Egypt'),
(65, 'SLV', 'SV', 'El Salvador'),
(66, 'GNQ', 'GQ', 'Equatorial Guinea'),
(67, 'ERI', 'ER', 'Eritrea'),
(68, 'EST', 'EE', 'Estonia'),
(69, 'SWZ', 'SZ', 'Eswatini'),
(70, 'ETH', 'ET', 'Ethiopia'),
(71, 'FLK', 'FK', 'Falkland Islands (Malvinas)'),
(72, 'FRO', 'FO', 'Faroe Islands'),
(73, 'FJI', 'FJ', 'Fiji'),
(74, 'FIN', 'FI', 'Finland'),
(75, 'FRA', 'FR', 'France'),
(76, 'GUF', 'GF', 'French Guiana'),
(77, 'PYF', 'PF', 'French Polynesia'),
(78, 'ATF', 'TF', 'French Southern Territories'),
(79, 'GAB', 'GA', 'Gabon'),
(80, 'GMB', 'GM', 'Gambia'),
(81, 'GEO', 'GE', 'Georgia'),
(82, 'DEU', 'DE', 'Germany'),
(83, 'GHA', 'GH', 'Ghana'),
(84, 'GIB', 'GI', 'Gibraltar'),
(85, 'GRC', 'GR', 'Greece'),
(86, 'GRL', 'GL', 'Greenland'),
(87, 'GRD', 'GD', 'Grenada'),
(88, 'GLP', 'GP', 'Guadeloupe'),
(89, 'GUM', 'GU', 'Guam'),
(90, 'GTM', 'GT', 'Guatemala'),
(91, 'GGY', 'GG', 'Guernsey'),
(92, 'GIN', 'GN', 'Guinea'),
(93, 'GNB', 'GW', 'Guinea-Bissau'),
(94, 'GUY', 'GY', 'Guyana'),
(95, 'HTI', 'HT', 'Haiti'),
(96, 'HMD', 'HM', 'Heard Island and McDonald Islands'),
(97, 'VAT', 'VA', 'Holy See'),
(98, 'HND', 'HN', 'Honduras'),
(99, 'HKG', 'HK', 'Hong Kong'),
(100, 'HUN', 'HU', 'Hungary'),
(101, 'ISL', 'IS', 'Iceland'),
(102, 'IND', 'IN', 'India'),
(103, 'IDN', 'ID', 'Indonesia'),
(104, 'IRN', 'IR', 'Iran'),
(105, 'IRQ', 'IQ', 'Iraq'),
(106, 'IRL', 'IE', 'Ireland'),
(107, 'IMN', 'IM', 'Isle of Man'),
(108, 'ISR', 'IL', 'Israel'),
(109, 'ITA', 'IT', 'Italy'),
(110, 'JAM', 'JM', 'Jamaica'),
(111, 'JPN', 'JP', 'Japan'),
(112, 'JEY', 'JE', 'Jersey'),
(113, 'JOR', 'JO', 'Jordan'),
(114, 'KAZ', 'KZ', 'Kazakhstan'),
(115, 'KEN', 'KE', 'Kenya'),
(116, 'KIR', 'KI', 'Kiribati'),
(117, 'PRK', 'KP', 'Korea (Democratic People\'s Republic of)'),
(118, 'KOR', 'KR', 'Korea (Republic of)'),
(119, 'KWT', 'KW', 'Kuwait'),
(120, 'KGZ', 'KG', 'Kyrgyzstan'),
(121, 'LAO', 'LA', 'Lao People\'s Democratic Republic'),
(122, 'LVA', 'LV', 'Latvia'),
(123, 'LBN', 'LB', 'Lebanon'),
(124, 'LSO', 'LS', 'Lesotho'),
(125, 'LBR', 'LR', 'Liberia'),
(126, 'LBY', 'LY', 'Libya'),
(127, 'LIE', 'LI', 'Liechtenstein'),
(128, 'LTU', 'LT', 'Lithuania'),
(129, 'LUX', 'LU', 'Luxembourg'),
(130, 'MAC', 'MO', 'Macao'),
(131, 'MDG', 'MG', 'Madagascar'),
(132, 'MWI', 'MW', 'Malawi'),
(133, 'MYS', 'MY', 'Malaysia'),
(134, 'MDV', 'MV', 'Maldives'),
(135, 'MLI', 'ML', 'Mali'),
(136, 'MLT', 'MT', 'Malta'),
(137, 'MHL', 'MH', 'Marshall Islands'),
(138, 'MTQ', 'MQ', 'Martinique'),
(139, 'MRT', 'MR', 'Mauritania'),
(140, 'MUS', 'MU', 'Mauritius'),
(141, 'MYT', 'YT', 'Mayotte'),
(142, 'MEX', 'MX', 'Mexico'),
(143, 'FSM', 'FM', 'Micronesia (Federated States of)'),
(144, 'MDA', 'MD', 'Moldova'),
(145, 'MCO', 'MC', 'Monaco'),
(146, 'MNG', 'MN', 'Mongolia'),
(147, 'MNE', 'ME', 'Montenegro'),
(148, 'MSR', 'MS', 'Montserrat'),
(149, 'MAR', 'MA', 'Morocco'),
(150, 'MOZ', 'MZ', 'Mozambique'),
(151, 'MMR', 'MM', 'Myanmar'),
(152, 'NAM', 'NA', 'Namibia'),
(153, 'NRU', 'NR', 'Nauru'),
(154, 'NPL', 'NP', 'Nepal'),
(155, 'NLD', 'NL', 'Netherlands'),
(156, 'NCL', 'NC', 'New Caledonia'),
(157, 'NZL', 'NZ', 'New Zealand'),
(158, 'NIC', 'NI', 'Nicaragua'),
(159, 'NER', 'NE', 'Niger'),
(160, 'NGA', 'NG', 'Nigeria'),
(161, 'NIU', 'NU', 'Niue'),
(162, 'NFK', 'NF', 'Norfolk Island'),
(163, 'MKD', 'MK', 'North Macedonia'),
(164, 'MNP', 'MP', 'Northern Mariana Islands'),
(165, 'NOR', 'NO', 'Norway'),
(166, 'OMN', 'OM', 'Oman'),
(167, 'PAK', 'PK', 'Pakistan'),
(168, 'PLW', 'PW', 'Palau'),
(169, 'PSE', 'PS', 'Palestine, State of'),
(170, 'PAN', 'PA', 'Panama'),
(171, 'PNG', 'PG', 'Papua New Guinea'),
(172, 'PRY', 'PY', 'Paraguay'),
(173, 'PER', 'PE', 'Peru'),
(174, 'PHL', 'PH', 'Philippines'),
(175, 'PCN', 'PN', 'Pitcairn'),
(176, 'POL', 'PL', 'Poland'),
(177, 'PRT', 'PT', 'Portugal'),
(178, 'PRI', 'PR', 'Puerto Rico'),
(179, 'QAT', 'QA', 'Qatar'),
(180, 'REU', 'RE', 'Réunion'),
(181, 'ROU', 'RO', 'Romania'),
(182, 'RUS', 'RU', 'Russian Federation'),
(183, 'RWA', 'RW', 'Rwanda'),
(184, 'BLM', 'BL', 'Saint Barthélemy'),
(185, 'SHN', 'SH', 'Saint Helena, Ascension and Tristan da Cunha'),
(186, 'KNA', 'KN', 'Saint Kitts and Nevis'),
(187, 'LCA', 'LC', 'Saint Lucia'),
(188, 'MAF', 'MF', 'Saint Martin (French part)'),
(189, 'SPM', 'PM', 'Saint Pierre and Miquelon'),
(190, 'VCT', 'VC', 'Saint Vincent and the Grenadines'),
(191, 'WSM', 'WS', 'Samoa'),
(192, 'SMR', 'SM', 'San Marino'),
(193, 'STP', 'ST', 'Sao Tome and Principe'),
(194, 'SAU', 'SA', 'Saudi Arabia'),
(195, 'SEN', 'SN', 'Senegal'),
(196, 'SRB', 'RS', 'Serbia'),
(197, 'SYC', 'SC', 'Seychelles'),
(198, 'SLE', 'SL', 'Sierra Leone'),
(199, 'SGP', 'SG', 'Singapore'),
(200, 'SXM', 'SX', 'Sint Maarten (Dutch part)'),
(201, 'SVK', 'SK', 'Slovakia'),
(202, 'SVN', 'SI', 'Slovenia'),
(203, 'SLB', 'SB', 'Solomon Islands'),
(204, 'SOM', 'SO', 'Somalia'),
(205, 'ZAF', 'ZA', 'South Africa'),
(206, 'SGS', 'GS', 'South Georgia and the South Sandwich Islands'),
(207, 'SSD', 'SS', 'South Sudan'),
(208, 'ESP', 'ES', 'Spain'),
(209, 'LKA', 'LK', 'Sri Lanka'),
(210, 'SDN', 'SD', 'Sudan'),
(211, 'SUR', 'SR', 'Suriname'),
(212, 'SJM', 'SJ', 'Svalbard and Jan Mayen'),
(213, 'SWE', 'SE', 'Sweden'),
(214, 'CHE', 'CH', 'Switzerland'),
(215, 'SYR', 'SY', 'Syrian Arab Republic'),
(216, 'TWN', 'TW', 'Taiwan'),
(217, 'TJK', 'TJ', 'Tajikistan'),
(218, 'TZA', 'TZ', 'Tanzania'),
(219, 'THA', 'TH', 'Thailand'),
(220, 'TLS', 'TL', 'Timor-Leste'),
(221, 'TGO', 'TG', 'Togo'),
(222, 'TKL', 'TK', 'Tokelau'),
(223, 'TON', 'TO', 'Tonga'),
(224, 'TTO', 'TT', 'Trinidad and Tobago'),
(225, 'TUN', 'TN', 'Tunisia'),
(226, 'TUR', 'TR', 'Turkey'),
(227, 'TKM', 'TM', 'Turkmenistan'),
(228, 'TCA', 'TC', 'Turks and Caicos Islands'),
(229, 'TUV', 'TV', 'Tuvalu'),
(230, 'UGA', 'UG', 'Uganda'),
(231, 'UKR', 'UA', 'Ukraine'),
(232, 'ARE', 'AE', 'United Arab Emirates'),
(233, 'GBR', 'GB', 'United Kingdom'),
(234, 'USA', 'US', 'United States of America'),
(235, 'UMI', 'UM', 'United States Minor Outlying Islands'),
(236, 'URY', 'UY', 'Uruguay'),
(237, 'UZB', 'UZ', 'Uzbekistan'),
(238, 'VUT', 'VU', 'Vanuatu'),
(239, 'VEN', 'VE', 'Venezuela'),
(240, 'VNM', 'VN', 'Viet Nam'),
(241, 'VGB', 'VG', 'Virgin Islands (British)'),
(242, 'VIR', 'VI', 'Virgin Islands (U.S.)'),
(243, 'WLF', 'WF', 'Wallis and Futuna'),
(244, 'ESH', 'EH', 'Western Sahara'),
(245, 'YEM', 'YE', 'Yemen'),
(246, 'ZMB', 'ZM', 'Zambia'),
(247, 'ZWE', 'ZW', 'Zimbabwe');

-- --------------------------------------------------------

--
-- Struktur dari tabel `geo_devices`
--

CREATE TABLE `geo_devices` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `geo_devices`
--

INSERT INTO `geo_devices` (`id`, `name`) VALUES
(13, 'Augmented Reality'),
(11, 'Automotive'),
(1, 'Desktop'),
(8, 'E-reader'),
(5, 'Feature Phone'),
(6, 'Game Console'),
(17, 'IoT Device'),
(2, 'Mobile'),
(15, 'Printer'),
(16, 'Robot'),
(9, 'Set-top Box'),
(10, 'Smart Display'),
(14, 'Smart Speaker'),
(4, 'Smart TV'),
(3, 'Tablet'),
(18, 'Unknown'),
(12, 'Virtual Reality'),
(7, 'Wearable');

-- --------------------------------------------------------

--
-- Struktur dari tabel `geo_os`
--

CREATE TABLE `geo_os` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `geo_os`
--

INSERT INTO `geo_os` (`id`, `name`) VALUES
(32, 'AIX'),
(4, 'Android'),
(51, 'Android TV'),
(13, 'Arch Linux'),
(30, 'Bada'),
(21, 'BlackBerry OS'),
(11, 'CentOS'),
(18, 'Chrome OS'),
(8, 'Debian'),
(38, 'Deepin'),
(47, 'DOS'),
(35, 'DragonFly BSD'),
(37, 'elementary OS'),
(9, 'Fedora'),
(27, 'Fire OS'),
(14, 'FreeBSD'),
(56, 'FreeRTOS'),
(36, 'Gentoo'),
(48, 'Haiku OS'),
(20, 'HarmonyOS'),
(33, 'HP-UX'),
(5, 'iOS'),
(6, 'iPadOS'),
(34, 'IRIX'),
(19, 'KaiOS'),
(3, 'Linux'),
(2, 'macOS'),
(29, 'MeeGo'),
(49, 'MINIX'),
(15, 'NetBSD'),
(16, 'OpenBSD'),
(12, 'openSUSE'),
(54, 'OpenWrt'),
(31, 'QNX'),
(50, 'Raspbian'),
(10, 'Red Hat'),
(55, 'RouterOS'),
(28, 'Sailfish OS'),
(17, 'Solaris'),
(24, 'Symbian'),
(25, 'Tizen'),
(52, 'tvOS'),
(7, 'Ubuntu'),
(57, 'UNIX'),
(53, 'watchOS'),
(26, 'webOS'),
(1, 'Windows'),
(41, 'Windows 10'),
(40, 'Windows 11'),
(46, 'Windows 2000'),
(43, 'Windows 7'),
(42, 'Windows 8'),
(23, 'Windows Mobile'),
(22, 'Windows Phone'),
(44, 'Windows Vista'),
(45, 'Windows XP'),
(39, 'Zorin OS');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rtb_supply_sources`
--

CREATE TABLE `rtb_supply_sources` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Relasi ke tabel users dengan role publisher',
  `name` varchar(255) NOT NULL,
  `supply_key` varchar(32) NOT NULL,
  `status` enum('pending','active','paused') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `rtb_supply_sources`
--

INSERT INTO `rtb_supply_sources` (`id`, `user_id`, `name`, `supply_key`, `status`, `created_at`) VALUES
(1, 3, 'Pub1', 'd7e5c78ba93684631bd8ae92edad737d', 'active', '2025-06-29 11:04:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `sites`
--

INSERT INTO `sites` (`id`, `user_id`, `category_id`, `url`, `status`, `created_at`) VALUES
(1, 3, 2, 'https://www.hornylust.com', 'approved', '2025-06-29 07:59:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ssp_partners`
--

CREATE TABLE `ssp_partners` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `endpoint_url` varchar(255) NOT NULL,
  `partner_key` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `ssp_partners`
--

INSERT INTO `ssp_partners` (`id`, `name`, `endpoint_url`, `partner_key`, `created_at`) VALUES
(1, 'Exoclick', 'http://rtb.exoclick.com/rtb.php?idzone=5128252&fid=e573a1c2a656509b0112f7213359757be76929c7', '', '2025-06-29 09:59:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('admin','advertiser','publisher') NOT NULL,
  `revenue_share` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `revenue_share`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$3D/hJUXfQnw148ZLifhgDO3CNVxbx7BtXNja8sZVj47EUoB7npq4u', 'admin@adserver.com', 'admin', 0, 'active', '2025-06-28 13:33:04'),
(2, 'Ad1', '$2y$10$pf/.glSSAviSo1KqSW0L5uLf5gn8OWE.n26kSmrp7Kyv6LLNSi10W', 'ari513270@gmail.com', 'advertiser', 0, 'active', '2025-06-29 07:58:22'),
(3, 'Pub1', '$2y$10$3yP5QZIImi2FbGn21f9IsOOtC/7fUprCw5s1nnMM.JkqOdII6K80.', 'webpublhiser@gmail.com', 'publisher', 30, 'active', '2025-06-29 07:58:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `zones`
--

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `size` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `ad_formats`
--
ALTER TABLE `ad_formats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advertiser_id` (`advertiser_id`),
  ADD KEY `fk_campaign_category` (`category_id`),
  ADD KEY `ad_format_id` (`ad_format_id`);

--
-- Indeks untuk tabel `campaign_stats`
--
ALTER TABLE `campaign_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_unique_stat_full` (`stat_date`,`campaign_id`,`creative_id`,`zone_id`,`country`,`os`,`browser`,`device`),
  ADD KEY `stats_lookup` (`campaign_id`,`stat_date`);

--
-- Indeks untuk tabel `campaign_targeting`
--
ALTER TABLE `campaign_targeting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `campaign_id` (`campaign_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `creatives`
--
ALTER TABLE `creatives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Indeks untuk tabel `geo_browsers`
--
ALTER TABLE `geo_browsers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `geo_connections`
--
ALTER TABLE `geo_connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `geo_countries`
--
ALTER TABLE `geo_countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `iso_alpha_3_code` (`iso_alpha_3_code`),
  ADD UNIQUE KEY `iso_alpha_2_code` (`iso_alpha_2_code`);

--
-- Indeks untuk tabel `geo_devices`
--
ALTER TABLE `geo_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `geo_os`
--
ALTER TABLE `geo_os`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `rtb_supply_sources`
--
ALTER TABLE `rtb_supply_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supply_key` (`supply_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `ssp_partners`
--
ALTER TABLE `ssp_partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `partner_key` (`partner_key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ad_formats`
--
ALTER TABLE `ad_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `campaign_stats`
--
ALTER TABLE `campaign_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=440;

--
-- AUTO_INCREMENT untuk tabel `campaign_targeting`
--
ALTER TABLE `campaign_targeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `creatives`
--
ALTER TABLE `creatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `geo_browsers`
--
ALTER TABLE `geo_browsers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT untuk tabel `geo_connections`
--
ALTER TABLE `geo_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `geo_countries`
--
ALTER TABLE `geo_countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT untuk tabel `geo_devices`
--
ALTER TABLE `geo_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `geo_os`
--
ALTER TABLE `geo_os`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT untuk tabel `rtb_supply_sources`
--
ALTER TABLE `rtb_supply_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ssp_partners`
--
ALTER TABLE `ssp_partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`advertiser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_campaign_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `campaign_targeting`
--
ALTER TABLE `campaign_targeting`
  ADD CONSTRAINT `campaign_targeting_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `creatives`
--
ALTER TABLE `creatives`
  ADD CONSTRAINT `creatives_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sites`
--
ALTER TABLE `sites`
  ADD CONSTRAINT `sites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sites_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `zones`
--
ALTER TABLE `zones`
  ADD CONSTRAINT `zones_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
