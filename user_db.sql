-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 29 Jun 2025 pada 13.32
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
  `country` varchar(5) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `device` varchar(50) DEFAULT NULL,
  `stat_date` date NOT NULL,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `cost` decimal(12,6) NOT NULL DEFAULT 0.000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
(2, 2, 'Indonesia,USA,United Kingdom,Australia,Japan', NULL, NULL, NULL, NULL);

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
(3, 'Pub1', '$2y$10$JxCdtS50dbLfWd9xk/kLGORsDUyQPtLlcrzKI7KCrYA8t0buUmv8W', 'webpublhiser@gmail.com', 'publisher', 50, 'active', '2025-06-29 07:58:47');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `campaign_targeting`
--
ALTER TABLE `campaign_targeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
