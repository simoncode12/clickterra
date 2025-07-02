-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 02 Jul 2025 pada 11.37
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
(4, 'Pop', 1),
(5, 'Popunder', 1);

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
(2, 2, 2, 1, 'banner', 1, 1, 'active', '2025-06-29 09:43:42'),
(4, 2, 2, 1, 'Exoclick inter redprn', 1, 1, 'active', '2025-06-30 20:16:33'),
(5, 2, 2, 1, 'Adstort video', 1, 1, 'active', '2025-06-30 20:52:32'),
(6, 2, 2, 1, 'Exoclick Hotnxx inter', 1, 1, 'active', '2025-07-01 14:27:55'),
(7, 2, 2, 1, 'Exoclick Hotnxx video', 1, 1, 'active', '2025-07-01 14:31:26'),
(10, 2, 2, 3, 'video ron', 1, 1, 'active', '2025-07-01 16:11:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `campaign_stats`
--

CREATE TABLE `campaign_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `creative_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `ssp_partner_id` int(11) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `device` varchar(50) DEFAULT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `cost` decimal(18,10) NOT NULL DEFAULT 0.0000000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `campaign_stats`
--

INSERT INTO `campaign_stats` (`id`, `campaign_id`, `creative_id`, `zone_id`, `ssp_partner_id`, `country`, `os`, `browser`, `device`, `stat_date`, `impressions`, `clicks`, `cost`) VALUES
(1, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(2, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(3, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(4, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(5, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(6, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(7, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(8, -1, -1, 6, 1, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000014000),
(9, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(10, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(11, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(12, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(13, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(14, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(15, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(16, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(17, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(18, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(19, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(20, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(21, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(22, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(23, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(24, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(25, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(26, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(27, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(28, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(29, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(30, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(31, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(32, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(33, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(34, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(35, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(36, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(37, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(38, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(39, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(40, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(41, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(42, -1, -1, 6, 4, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000016122),
(43, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(44, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(45, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(46, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(47, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(48, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(49, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(50, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(51, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(52, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(53, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(54, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(55, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(56, -1, -1, 6, 1, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000014000),
(57, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(58, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(59, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(60, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(61, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(62, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(63, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(64, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(65, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(66, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(67, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(68, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(69, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(70, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(71, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(72, -1, -1, 6, 2, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000007700),
(73, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(74, -1, -1, 6, 1, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000063000),
(75, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(76, -1, -1, 6, 1, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000070000),
(77, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(78, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(79, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(80, -1, -1, 6, 1, '0', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000014000),
(81, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(82, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(83, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(84, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(85, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(86, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(87, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(88, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000),
(89, 2, 2, 6, NULL, 'NL', 'Unknown', 'Unknown', 'Desktop', '2025-07-02', 1, 0, 0.0000001000);

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
(2, 2, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi'),
(6, 4, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi'),
(7, 5, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi'),
(8, 6, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi'),
(9, 7, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi'),
(12, 10, 'AFG,ALB,DZA,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BES,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,CPV,KHM,CMR,CAN,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COD,COK,CRI,CIV,HRV,CUB,CUW,CYP,CZE,DNK,DJI,DMA,DOM,ECU,EGY,SLV,GNQ,ERI,EST,SWZ,ETH,FLK,FRO,FJI,FIN,FRA,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GGY,GIN,GNB,GUY,HTI,HMD,VAT,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,IMN,ISR,ITA,JAM,JPN,JEY,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MNE,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,NCL,NZL,NIC,NER,NGA,NIU,NFK,MKD,MNP,NOR,OMN,PAK,PLW,PSE,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROU,RUS,RWA,BLM,SHN,KNA,LCA,MAF,SPM,VCT,WSM,SMR,STP,SAU,SEN,SRB,SYC,SLE,SGP,SXM,SVK,SVN,SLB,SOM,ZAF,SGS,SSD,ESP,LKA,SDN,SUR,SJM,SWE,CHE,SYR,TWN,TJK,TZA,THA,TLS,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,UMI,USA,URY,UZB,VUT,VEN,VNM,VGB,VIR,WLF,ESH,YEM,ZMB,ZWE', 'Aloha Browser,Amazon Silk,Avant Browser,Avast Secure Browser,Baidu Browser,Bingbot,BlackBerry Browser,Brave,Chrome,Chromium,CM Browser,Coc Coc,Comodo Dragon,Dolphin,DuckDuckGo Privacy Browser,Edge,Epic,Falkon,Firefox,FreeU,Googlebot,IceDragon,Iceweasel,Internet Explorer,K-Meleon,Kiwi Browser,Konqueror,Lynx,Maxthon,Microsoft WebView2,Midori,Min Browser,Nokia Browser,Opera,Opera GX,Otter Browser,Pale Moon,Phoenix Browser,Puffin,QQ Browser,QuteBrowser,Safari,Samsung Internet,Seamonkey,Slimjet,Sogou Explorer,SRWare Iron,Superbird,Tenta Browser,Tor Browser,UC Browser,Vivaldi,Waterfox,Wave Browser,Yandex Browser', 'Augmented Reality,Automotive,Desktop,E-reader,Feature Phone,Game Console,IoT Device,Mobile,Printer,Robot,Set-top Box,Smart Display,Smart Speaker,Smart TV,Tablet,Unknown,Virtual Reality,Wearable', 'AIX,Android,Android TV,Arch Linux,Bada,BlackBerry OS,CentOS,Chrome OS,Debian,Deepin,DOS,DragonFly BSD,elementary OS,Fedora,Fire OS,FreeBSD,FreeRTOS,Gentoo,Haiku OS,HarmonyOS,HP-UX,iOS,iPadOS,IRIX,KaiOS,Linux,macOS,MeeGo,MINIX,NetBSD,OpenBSD,openSUSE,OpenWrt,QNX,Raspbian,Red Hat,RouterOS,Sailfish OS,Solaris,Symbian,Tizen,tvOS,Ubuntu,UNIX,watchOS,webOS,Windows,Windows 10,Windows 11,Windows 2000,Windows 7,Windows 8,Windows Mobile,Windows Phone,Windows Vista,Windows XP,Zorin OS', '2G,3G,4G,5G,Bluetooth,Cable,Cellular,Dial-up,DSL,Ethernet,Fiber,LoRaWAN,Powerline,Satellite,Unknown,WiFi');

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
(8, 2, '300x50', 'script', 'cpm', 0.0001, NULL, NULL, '<script async type=\"application/javascript\" src=\"https://a.magsrv.com/ad-provider.js\"></script> \r\n <ins class=\"eas6a97888e10\" data-zoneid=\"5548390\"></ins> \r\n <script>(AdProvider = window.AdProvider || []).push({\"serve\": {}});</script>', 'all', 'active', '2025-06-29 10:14:16'),
(9, 4, '300x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"300\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:17:10'),
(10, 4, '728x90', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"728\" height=\"90\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:19:34'),
(11, 4, '160x600', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"160\" height=\"600\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:20:20'),
(12, 4, '300x500', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"300\" height=\"500\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:20:39'),
(13, 4, '900x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"900\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:21:09'),
(14, 4, '300x100', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"300\" height=\"100\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:21:33'),
(15, 4, '300x50', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//redprn.com/ads/exointerintal.html\" width=\"300\" height=\"50\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:21:50'),
(16, 5, '300x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"300\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:53:39'),
(17, 5, '728x90', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"728\" height=\"90\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:54:06'),
(18, 5, '160x600', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"160\" height=\"600\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:54:25'),
(19, 5, '300x500', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"300\" height=\"500\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:54:41'),
(20, 5, '900x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"900\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:55:01'),
(21, 5, '300x100', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"300\" height=\"100\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:55:17'),
(22, 5, '300x50', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//mixlust.com/shorts/ads/video/ad.html\" width=\"300\" height=\"50\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-06-30 20:55:33'),
(30, 6, '300x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"300\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:28:15'),
(31, 6, '728x90', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"726\" height=\"90\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:28:49'),
(32, 6, '160x600', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"160\" height=\"600\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:29:08'),
(33, 6, '300x500', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"300\" height=\"500\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:29:34'),
(34, 6, '900x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"900\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:29:55'),
(35, 6, '300x100', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"300\" height=\"100\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:30:20'),
(36, 6, '300x50', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/exointer.html\" width=\"300\" height=\"50\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:30:34'),
(37, 7, '300x50', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"300\" height=\"50\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:32:29'),
(38, 7, '300x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"300\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:32:45'),
(39, 7, '728x90', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"728\" height=\"90\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:33:05'),
(40, 7, '160x600', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"160\" height=\"600\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:33:24'),
(41, 7, '300x500', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"300\" height=\"500\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:33:38'),
(42, 7, '900x250', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"900\" height=\"250\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:33:57'),
(43, 7, '300x100', 'script', 'cpm', 0.0001, NULL, NULL, '<iframe src=\"//hotnxx.com/ortb/adsvideo.html\" width=\"300\" height=\"100\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\"></iframe>', 'all', 'active', '2025-07-01 14:34:13');

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
-- Struktur dari tabel `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(255) NOT NULL,
  `account_details` text NOT NULL,
  `status` enum('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rtb_requests`
--

CREATE TABLE `rtb_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `supply_source_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `is_bid_sent` tinyint(1) NOT NULL DEFAULT 0,
  `winning_price_cpm` decimal(10,4) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `source_domain` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `rtb_requests`
--

INSERT INTO `rtb_requests` (`id`, `request_time`, `supply_source_id`, `zone_id`, `is_bid_sent`, `winning_price_cpm`, `country`, `source_domain`) VALUES
(1, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(2, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(3, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(4, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(5, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(6, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(7, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(8, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(9, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'gishancoma.com'),
(10, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(11, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'tundevelluckeed.com'),
(12, '2025-07-02 11:37:43', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(13, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(14, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(15, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(16, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(17, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(18, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(19, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(20, '2025-07-02 11:37:44', 4, 6, 1, 0.0014, 'NL', 'calirwizer.com'),
(21, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'purexxxhub.com'),
(22, '2025-07-02 11:37:44', 4, 6, 1, 0.0001, 'NL', 'bectfuladit.com'),
(23, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(24, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com'),
(25, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(26, '2025-07-02 11:37:45', 4, 6, 1, 0.0014, 'NL', 'exinkbeflatizer.com'),
(27, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(28, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(29, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(30, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(31, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'concommencheco.com'),
(32, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(33, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(34, '2025-07-02 11:37:45', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(35, '2025-07-02 11:37:46', 4, 6, 1, 0.0014, 'NL', 'sumibbilings.com'),
(36, '2025-07-02 11:37:46', 4, 6, 1, 0.0001, 'NL', 'tundevelluckeed.com'),
(37, '2025-07-02 11:37:46', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(38, '2025-07-02 11:37:46', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(39, '2025-07-02 11:37:46', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(40, '2025-07-02 11:37:46', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(41, '2025-07-02 11:37:46', 4, 6, 1, 0.0001, 'NL', 'purexxxhub.com'),
(42, '2025-07-02 11:37:47', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(43, '2025-07-02 11:37:47', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com'),
(44, '2025-07-02 11:37:47', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(45, '2025-07-02 11:37:47', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(46, '2025-07-02 11:37:47', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(47, '2025-07-02 11:37:47', 4, 6, 1, 0.0001, 'NL', 'bectfuladit.com'),
(48, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(49, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(50, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'gishancoma.com'),
(51, '2025-07-02 11:37:48', 4, 6, 1, 0.0245, 'NL', 'bralnesseds.com'),
(52, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'bectfuladit.com'),
(53, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(54, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(55, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com'),
(56, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(57, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(58, '2025-07-02 11:37:48', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(59, '2025-07-02 11:37:48', 4, 6, 1, 0.0560, 'NL', 'sticantforratic.com'),
(60, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(61, '2025-07-02 11:37:49', 4, 6, 1, 0.0014, 'NL', 'tundevelluckeed.com'),
(62, '2025-07-02 11:37:49', 4, 6, 1, 0.0014, 'NL', 'sticantforratic.com'),
(63, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(64, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'gishancoma.com'),
(65, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(66, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(67, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com'),
(68, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(69, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(70, '2025-07-02 11:37:49', 4, 6, 1, 0.0014, 'NL', 'sumibbilings.com'),
(71, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(72, '2025-07-02 11:37:49', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(73, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(74, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(75, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(76, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(77, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'gishancoma.com'),
(78, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com'),
(79, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(80, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(81, '2025-07-02 11:37:50', 4, 6, 1, 0.0001, 'NL', 'bectfuladit.com'),
(82, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(83, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(84, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(85, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(86, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(87, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(88, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(89, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(90, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(91, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'concommencheco.com'),
(92, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'tundevelluckeed.com'),
(93, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(94, '2025-07-02 11:37:51', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(95, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(96, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(97, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'concommencheco.com'),
(98, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(99, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(100, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(101, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(102, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'gishancoma.com'),
(103, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(104, '2025-07-02 11:37:52', 4, 6, 1, 0.0016, 'NL', 'sticantforratic.com'),
(105, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'askautiessios.com'),
(106, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(107, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'concommencheco.com'),
(108, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(109, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'concommencheco.com'),
(110, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(111, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(112, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(113, '2025-07-02 11:37:52', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com'),
(114, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(115, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'bectfuladit.com'),
(116, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'concommencheco.com'),
(117, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'tundevelluckeed.com'),
(118, '2025-07-02 11:37:53', 4, 6, 1, 0.0014, 'NL', 'amplaintiont.com'),
(119, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(120, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(121, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'purexxxhub.com'),
(122, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'purexxxhub.com'),
(123, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(124, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(125, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'habiculpize.com'),
(126, '2025-07-02 11:37:53', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(127, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(128, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'gishancoma.com'),
(129, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'tundevelluckeed.com'),
(130, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'askautiessios.com'),
(131, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(132, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'purexxxhub.com'),
(133, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(134, '2025-07-02 11:37:54', 4, 6, 1, 0.0008, 'NL', 'azinonistrine.com'),
(135, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(136, '2025-07-02 11:37:54', 4, 6, 1, 0.0063, 'NL', 'bectfuladit.com'),
(137, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'hometelaviss.com'),
(138, '2025-07-02 11:37:54', 4, 6, 1, 0.0070, 'NL', 'amplaintiont.com'),
(139, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(140, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(141, '2025-07-02 11:37:54', 4, 6, 1, 0.0001, 'NL', 'askautiessios.com'),
(142, '2025-07-02 11:37:54', 4, 6, 1, 0.0014, 'NL', 'gishancoma.com'),
(143, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'bralnesseds.com'),
(144, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'uncessinic.com'),
(145, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(146, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'amplaintiont.com'),
(147, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'sticantforratic.com'),
(148, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'exinkbeflatizer.com'),
(149, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'azinonistrine.com'),
(150, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'alaterizercock.com'),
(151, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'tundevelluckeed.com'),
(152, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'sumibbilings.com'),
(153, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'calirwizer.com'),
(154, '2025-07-02 11:37:55', 4, 6, 1, 0.0001, 'NL', 'nonandacoly.com');

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
  `default_zone_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `rtb_supply_sources`
--

INSERT INTO `rtb_supply_sources` (`id`, `user_id`, `name`, `supply_key`, `status`, `default_zone_id`, `created_at`) VALUES
(4, 3, 'Pub1', '65773139c3b927b7056953e6fadb345e', 'active', 6, '2025-06-30 09:09:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'site_logo', 'assets/img/logo.png'),
(2, 'site_favicon', 'assets/img/favicon.ico'),
(3, 'ad_server_domain', 'http://userpanel.clicterra.com'),
(4, 'rtb_handler_domain', 'http://userpanel.clicterra.com'),
(5, 'min_withdrawal_amount', '10.00'),
(6, 'payment_methods', 'PayPal\r\nBank Transfer\r\nUSDT');

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
(1, 3, 2, 'https://www.hornylust.com', 'approved', '2025-06-29 07:59:27'),
(5, 3, 1, 'https://rtb-partner.com/pub1', 'approved', '2025-06-30 09:09:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ssp_partners`
--

CREATE TABLE `ssp_partners` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `endpoint_url` varchar(255) NOT NULL,
  `vast_endpoint_url` varchar(255) DEFAULT NULL,
  `partner_key` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `ssp_partners`
--

INSERT INTO `ssp_partners` (`id`, `name`, `endpoint_url`, `vast_endpoint_url`, `partner_key`, `created_at`) VALUES
(1, 'Banner exo', 'http://rtb.exoclick.com/rtb.php?idzone=5128252&fid=e573a1c2a656509b0112f7213359757be76929c7', NULL, '', '2025-06-29 09:59:18'),
(2, 'Banner exo busty', 'http://rtb.exoclick.com/rtb.php?idzone=5123466&fid=b5677dfe2f4a21c7548abc927fac110aaa4b157b', NULL, '2e5ec2cd4a30e9f010e6e1bf1d87741d', '2025-06-30 12:45:40'),
(3, 'Banner exo fucboob', 'http://rtb.exoclick.com/rtb.php?idzone=5123472&fid=6e4bb66dceebaae013c1bdfcde873a0e6457cb81', NULL, 'e274eab6f170d354cd49dffdd499f222', '2025-06-30 12:46:22'),
(4, 'Banner exo cumshot', 'http://rtb.exoclick.com/rtb.php?idzone=5123470&fid=2e05dd2082bc5cebcc121e9645ab1bdd81ca2148', NULL, '1486f7e96d1955b4cae688c1278d8db7', '2025-06-30 12:46:58'),
(5, 'Exoclick Video sl', '', 'http://rtb.exoclick.com/rtb.php?idzone=5128256&fid=05e57889d0a6c09390e1b5854dfb94333a1cca12', 'ca832f673fddaf534fbe1cc7c0a9ee8d', '2025-07-01 09:45:35');

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
  `payout_method` varchar(100) DEFAULT NULL,
  `payout_details` text DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `revenue_share`, `payout_method`, `payout_details`, `referred_by`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$3D/hJUXfQnw148ZLifhgDO3CNVxbx7BtXNja8sZVj47EUoB7npq4u', 'admin@adserver.com', 'admin', 0, NULL, NULL, NULL, 'active', '2025-06-28 13:33:04'),
(2, 'Ad1', '$2y$10$pf/.glSSAviSo1KqSW0L5uLf5gn8OWE.n26kSmrp7Kyv6LLNSi10W', 'ari513270@gmail.com', 'advertiser', 0, NULL, NULL, NULL, 'active', '2025-06-29 07:58:22'),
(3, 'Pub1', '$2y$10$XbDr5qOTNg.CcKTrtzas5.XTqTW41NcHACqTOBY0OfCR0YlHSyiUa', 'webpublhiser@gmail.com', 'publisher', 50, 'USDT', 'TMMnac1jT2UTRzGDLHhFcHvX9R6G7cHXUL', NULL, 'active', '2025-06-29 07:58:47'),
(4, 'clickadnow', '$2y$10$POFPnGvOmXgwbWKSm6fREO116mRXdCm465XsPDsavYoMTnMFIugT.', 'info@clickadnow.com', 'publisher', 30, NULL, NULL, NULL, 'active', '2025-06-30 08:24:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `video_creatives`
--

CREATE TABLE `video_creatives` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bid_model` enum('cpm','cpc') NOT NULL DEFAULT 'cpm',
  `bid_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `status` enum('active','paused') NOT NULL DEFAULT 'active',
  `vast_type` enum('upload','hotlink','third_party') NOT NULL,
  `video_url` varchar(2048) DEFAULT NULL,
  `landing_url` varchar(2048) DEFAULT NULL,
  `duration` int(11) NOT NULL COMMENT 'In seconds',
  `impression_tracker` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `video_creatives`
--

INSERT INTO `video_creatives` (`id`, `campaign_id`, `name`, `bid_model`, `bid_amount`, `status`, `vast_type`, `video_url`, `landing_url`, `duration`, `impression_tracker`, `created_at`) VALUES
(1, 10, 'Exoclick Video', 'cpm', 0.0001, 'active', 'third_party', 'https://s.magsrv.com/v1/vast.php?idzone=5548380', NULL, 15, NULL, '2025-07-01 16:17:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `zones`
--

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `ad_format_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `size` varchar(50) NOT NULL,
  `external_tag_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `zones`
--

INSERT INTO `zones` (`id`, `site_id`, `ad_format_id`, `name`, `size`, `external_tag_id`, `created_at`) VALUES
(2, 1, 1, '300x250', '300x250', NULL, '2025-06-30 08:19:29'),
(6, 5, 1, 'Default RTB Zone (All Sizes)', 'all', NULL, '2025-06-30 09:09:14'),
(16, 1, 3, 'vast', 'responsive', NULL, '2025-07-01 15:56:05');

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
-- Indeks untuk tabel `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `rtb_requests`
--
ALTER TABLE `rtb_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lookup_idx` (`supply_source_id`,`request_time`);

--
-- Indeks untuk tabel `rtb_supply_sources`
--
ALTER TABLE `rtb_supply_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supply_key` (`supply_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

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
-- Indeks untuk tabel `video_creatives`
--
ALTER TABLE `video_creatives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Indeks untuk tabel `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `external_tag_id` (`external_tag_id`),
  ADD KEY `site_id` (`site_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ad_formats`
--
ALTER TABLE `ad_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `campaign_stats`
--
ALTER TABLE `campaign_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT untuk tabel `campaign_targeting`
--
ALTER TABLE `campaign_targeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `creatives`
--
ALTER TABLE `creatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

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
-- AUTO_INCREMENT untuk tabel `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rtb_requests`
--
ALTER TABLE `rtb_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT untuk tabel `rtb_supply_sources`
--
ALTER TABLE `rtb_supply_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `ssp_partners`
--
ALTER TABLE `ssp_partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `video_creatives`
--
ALTER TABLE `video_creatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
