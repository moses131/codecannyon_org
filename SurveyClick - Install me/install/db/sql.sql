CREATE TABLE `{{prefix}}admin_actions` (
  `id` int(11) NOT NULL,
  `by_user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `to_user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}alerts` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `open` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}answers` (
  `id` int(11) NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL,
  `result` int(11) UNSIGNED NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL,
  `question` int(11) UNSIGNED DEFAULT NULL,
  `value` int(11) UNSIGNED DEFAULT NULL,
  `value2` int(11) UNSIGNED DEFAULT NULL,
  `value3` int(11) UNSIGNED DEFAULT NULL,
  `value_str` text DEFAULT NULL,
  `value_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'website',
  `parent` int(11) UNSIGNED DEFAULT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `lang` varchar(20) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}category_pages` (
  `category` int(11) UNSIGNED DEFAULT NULL,
  `page` int(11) UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}collectors` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `slug` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `setting` text DEFAULT NULL,
  `cpa` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `lpoints` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}collector_options` (
  `collector` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `value` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}countries` (
  `id` bigint(20) NOT NULL,
  `iso_3166` varchar(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  `hour_format` int(2) NOT NULL DEFAULT 24,
  `date_format` varchar(5) NOT NULL DEFAULT 'm/d/y',
  `timezone` text NOT NULL,
  `firstday` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `language` varchar(15) NOT NULL DEFAULT 'en_US',
  `mformat` varchar(10) NOT NULL DEFAULT '%s %a',
  `mseparator` varchar(5) NOT NULL DEFAULT '.-,',
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{{prefix}}countries` (`id`, `iso_3166`, `name`, `hour_format`, `date_format`, `timezone`, `firstday`, `language`, `mformat`, `mseparator`, `date`) VALUES
(1, 'af', 'Afghanistan', 24, 'm/d/y', 'Asia/Kabul', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:31'),
(2, 'ax', 'Åland Islands', 24, 'm/d/y', 'Europe/Mariehamn', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:31'),
(3, 'al', 'Albania', 24, 'm/d/y', 'Europe/Tirane', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:31'),
(4, 'dz', 'Algeria', 24, 'm/d/y', 'Africa/Algiers', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:31'),
(5, 'as', 'American Samoa', 24, 'm/d/y', 'Pacific/Pago_Pago', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:31'),
(6, 'ad', 'Andorra', 24, 'm/d/y', 'Europe/Andorra', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:31'),
(7, 'ao', 'Angola', 24, 'm/d/y', 'Africa/Luanda', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(8, 'ai', 'Anguilla', 24, 'm/d/y', 'America/Anguilla', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(9, 'aq', 'Antarctica', 24, 'm/d/y', 'Antarctica/Casey,Antarctica/Davis,Antarctica/DumontDUrville,Antarctica/Mawson,Antarctica/McMurdo,Antarctica/Palmer,Antarctica/Rothera,Antarctica/Syowa,Antarctica/Troll,Antarctica/Vostok', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(10, 'ag', 'Antigua and Barbuda', 24, 'm/d/y', 'America/Antigua', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(11, 'ar', 'Argentina', 24, 'm/d/y', 'America/Argentina/Buenos_Aires,America/Argentina/Catamarca,America/Argentina/Cordoba,America/Argentina/Jujuy,America/Argentina/La_Rioja,America/Argentina/Mendoza,America/Argentina/Rio_Gallegos,America/Argentina/Salta,America/Argentina/San_Juan,America/Argentina/San_Luis,America/Argentina/Tucuman,America/Argentina/Ushuaia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(12, 'am', 'Armenia', 24, 'm/d/y', 'Asia/Yerevan', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(13, 'aw', 'Aruba', 24, 'm/d/y', 'America/Aruba', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(14, 'au', 'Australia', 24, 'm/d/y', 'Antarctica/Macquarie,Australia/Adelaide,Australia/Brisbane,Australia/Broken_Hill,Australia/Currie,Australia/Darwin,Australia/Eucla,Australia/Hobart,Australia/Lindeman,Australia/Lord_Howe,Australia/Melbourne,Australia/Perth,Australia/Sydney', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(15, 'at', 'Austria', 24, 'm/d/y', 'Europe/Vienna', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(16, 'az', 'Azerbaijan', 24, 'm/d/y', 'Asia/Baku', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(17, 'bs', 'Bahamas', 24, 'm/d/y', 'America/Nassau', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(18, 'bh', 'Bahrain', 24, 'm/d/y', 'Asia/Bahrain', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(19, 'bd', 'Bangladesh', 24, 'm/d/y', 'Asia/Dhaka', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(20, 'bb', 'Barbados', 24, 'm/d/y', 'America/Barbados', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(21, 'by', 'Belarus', 24, 'm/d/y', 'Europe/Minsk', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(22, 'be', 'Belgium', 24, 'm/d/y', 'Europe/Brussels', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(23, 'bz', 'Belize', 24, 'm/d/y', 'America/Belize', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(24, 'bj', 'Benin', 24, 'm/d/y', 'Africa/Porto-Novo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(25, 'bm', 'Bermuda', 24, 'm/d/y', 'Atlantic/Bermuda', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(26, 'bt', 'Bhutan', 24, 'm/d/y', 'Asia/Thimphu', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(27, 'bo', 'Bolivia, Plurinational State of', 24, 'm/d/y', 'America/La_Paz', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(28, 'bq', 'Bonaire, Sint Eustatius and Saba', 24, 'm/d/y', 'America/Kralendijk', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(29, 'ba', 'Bosnia and Herzegovina', 24, 'm/d/y', 'Europe/Sarajevo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(30, 'bw', 'Botswana', 24, 'm/d/y', 'Africa/Gaborone', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(31, 'bv', 'Bouvet Island', 24, 'm/d/y', '', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(32, 'br', 'Brazil', 24, 'm/d/y', 'America/Araguaina,America/Bahia,America/Belem,America/Boa_Vista,America/Campo_Grande,America/Cuiaba,America/Eirunepe,America/Fortaleza,America/Maceio,America/Manaus,America/Noronha,America/Porto_Velho,America/Recife,America/Rio_Branco,America/Santarem,America/Sao_Paulo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(33, 'io', 'British Indian Ocean Territory', 24, 'm/d/y', 'Indian/Chagos', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(34, 'bn', 'Brunei Darussalam', 24, 'm/d/y', 'Asia/Brunei', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(35, 'bg', 'Bulgaria', 24, 'm/d/y', 'Europe/Sofia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(36, 'bf', 'Burkina Faso', 24, 'm/d/y', 'Africa/Ouagadougou', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(37, 'bi', 'Burundi', 24, 'm/d/y', 'Africa/Bujumbura', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(38, 'kh', 'Cambodia', 24, 'm/d/y', 'Asia/Phnom_Penh', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(39, 'cm', 'Cameroon', 24, 'm/d/y', 'Africa/Douala', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(40, 'ca', 'Canada', 24, 'm/d/y', 'America/Atikokan,America/Blanc-Sablon,America/Cambridge_Bay,America/Creston,America/Dawson,America/Dawson_Creek,America/Edmonton,America/Fort_Nelson,America/Glace_Bay,America/Goose_Bay,America/Halifax,America/Inuvik,America/Iqaluit,America/Moncton,America/Nipigon,America/Pangnirtung,America/Rainy_River,America/Rankin_Inlet,America/Regina,America/Resolute,America/St_Johns,America/Swift_Current,America/Thunder_Bay,America/Toronto,America/Vancouver,America/Whitehorse,America/Winnipeg,America/Yellowknife', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(41, 'cv', 'Cape Verde', 24, 'm/d/y', 'Atlantic/Cape_Verde', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(42, 'ky', 'Cayman Islands', 24, 'm/d/y', 'America/Cayman', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(43, 'cf', 'Central African Republic', 24, 'm/d/y', 'Africa/Bangui', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(44, 'td', 'Chad', 24, 'm/d/y', 'Africa/Ndjamena', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(45, 'cl', 'Chile', 24, 'm/d/y', 'America/Punta_Arenas,America/Santiago,Pacific/Easter', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(46, 'cn', 'China', 24, 'm/d/y', 'Asia/Shanghai,Asia/Urumqi', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(47, 'cx', 'Christmas Island', 24, 'm/d/y', 'Indian/Christmas', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(48, 'cc', 'Cocos (Keeling) Islands', 24, 'm/d/y', 'Indian/Cocos', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(49, 'co', 'Colombia', 24, 'm/d/y', 'America/Bogota', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(50, 'km', 'Comoros', 24, 'm/d/y', 'Indian/Comoro', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(51, 'cg', 'Congo', 24, 'm/d/y', 'Africa/Brazzaville', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(52, 'cd', 'Congo, the Democratic Republic of the', 24, 'm/d/y', 'Africa/Kinshasa,Africa/Lubumbashi', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(53, 'ck', 'Cook Islands', 24, 'm/d/y', 'Pacific/Rarotonga', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(54, 'cr', 'Costa Rica', 24, 'm/d/y', 'America/Costa_Rica', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(55, 'ci', "Côte d'Ivoire", 24, 'm/d/y', 'Africa/Abidjan', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(56, 'hr', 'Croatia', 24, 'm/d/y', 'Europe/Zagreb', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(57, 'cu', 'Cuba', 24, 'm/d/y', 'America/Havana', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(58, 'cw', 'Curaçao', 24, 'm/d/y', 'America/Curacao', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(59, 'cy', 'Cyprus', 24, 'm/d/y', 'Asia/Famagusta,Asia/Nicosia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(60, 'cz', 'Czech Republic', 24, 'm/d/y', 'Europe/Prague', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(61, 'dk', 'Denmark', 24, 'm/d/y', 'Europe/Copenhagen', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(62, 'dj', 'Djibouti', 24, 'm/d/y', 'Africa/Djibouti', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(63, 'dm', 'Dominica', 24, 'm/d/y', 'America/Dominica', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(64, 'do', 'Dominican Republic', 24, 'm/d/y', 'America/Santo_Domingo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(65, 'ec', 'Ecuador', 24, 'm/d/y', 'America/Guayaquil,Pacific/Galapagos', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(66, 'eg', 'Egypt', 24, 'm/d/y', 'Africa/Cairo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(67, 'sv', 'El Salvador', 24, 'm/d/y', 'America/El_Salvador', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(68, 'gq', 'Equatorial Guinea', 24, 'm/d/y', 'Africa/Malabo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(69, 'er', 'Eritrea', 24, 'm/d/y', 'Africa/Asmara', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(70, 'ee', 'Estonia', 24, 'm/d/y', 'Europe/Tallinn', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(71, 'et', 'Ethiopia', 24, 'm/d/y', 'Africa/Addis_Ababa', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(72, 'fk', 'Falkland Islands (Malvinas)', 24, 'm/d/y', 'Atlantic/Stanley', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(73, 'fo', 'Faroe Islands', 24, 'm/d/y', 'Atlantic/Faroe', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(74, 'fj', 'Fiji', 24, 'm/d/y', 'Pacific/Fiji', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(75, 'fi', 'Finland', 24, 'm/d/y', 'Europe/Helsinki', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(76, 'fr', 'France', 24, 'm/d/y', 'Europe/Paris', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(77, 'gf', 'French Guiana', 24, 'm/d/y', 'America/Cayenne', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(78, 'pf', 'French Polynesia', 24, 'm/d/y', 'Pacific/Gambier,Pacific/Marquesas,Pacific/Tahiti', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(79, 'tf', 'French Southern Territories', 24, 'm/d/y', 'Indian/Kerguelen', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(80, 'ga', 'Gabon', 24, 'm/d/y', 'Africa/Libreville', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(81, 'gm', 'Gambia', 24, 'm/d/y', 'Africa/Banjul', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(82, 'ge', 'Georgia', 24, 'm/d/y', 'Asia/Tbilisi', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(83, 'de', 'Germany', 24, 'm/d/y', 'Europe/Berlin,Europe/Busingen', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(84, 'gh', 'Ghana', 24, 'm/d/y', 'Africa/Accra', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(85, 'gi', 'Gibraltar', 24, 'm/d/y', 'Europe/Gibraltar', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(86, 'gr', 'Greece', 24, 'm/d/y', 'Europe/Athens', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(87, 'gl', 'Greenland', 24, 'm/d/y', 'America/Danmarkshavn,America/Nuuk,America/Scoresbysund,America/Thule', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(88, 'gd', 'Grenada', 24, 'm/d/y', 'America/Grenada', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(89, 'gp', 'Guadeloupe', 24, 'm/d/y', 'America/Guadeloupe', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(90, 'gu', 'Guam', 24, 'm/d/y', 'Pacific/Guam', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(91, 'gt', 'Guatemala', 24, 'm/d/y', 'America/Guatemala', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(92, 'gg', 'Guernsey', 24, 'm/d/y', 'Europe/Guernsey', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(93, 'gn', 'Guinea', 24, 'm/d/y', 'Africa/Conakry', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(94, 'gw', 'Guinea-Bissau', 24, 'm/d/y', 'Africa/Bissau', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(95, 'gy', 'Guyana', 24, 'm/d/y', 'America/Guyana', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(96, 'ht', 'Haiti', 24, 'm/d/y', 'America/Port-au-Prince', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(97, 'hm', 'Heard Island and McDonald Islands', 24, 'm/d/y', '', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(98, 'va', 'Holy See (Vatican City State)', 24, 'm/d/y', 'Europe/Vatican', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(99, 'hn', 'Honduras', 24, 'm/d/y', 'America/Tegucigalpa', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(100, 'hk', 'Hong Kong', 24, 'm/d/y', 'Asia/Hong_Kong', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(101, 'hu', 'Hungary', 24, 'm/d/y', 'Europe/Budapest', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(102, 'is', 'Iceland', 24, 'm/d/y', 'Atlantic/Reykjavik', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(103, 'in', 'India', 24, 'm/d/y', 'Asia/Kolkata', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(104, 'id', 'Indonesia', 24, 'm/d/y', 'Asia/Jakarta,Asia/Jayapura,Asia/Makassar,Asia/Pontianak', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(105, 'ir', 'Iran, Islamic Republic of', 24, 'm/d/y', 'Asia/Tehran', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(106, 'iq', 'Iraq', 24, 'm/d/y', 'Asia/Baghdad', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(107, 'ie', 'Ireland', 24, 'm/d/y', 'Europe/Dublin', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(108, 'im', 'Isle of Man', 24, 'm/d/y', 'Europe/Isle_of_Man', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(109, 'il', 'Israel', 24, 'm/d/y', 'Asia/Jerusalem', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(110, 'it', 'Italy', 24, 'm/d/y', 'Europe/Rome', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(111, 'jm', 'Jamaica', 24, 'm/d/y', 'America/Jamaica', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(112, 'jp', 'Japan', 24, 'm/d/y', 'Asia/Tokyo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(113, 'je', 'Jersey', 24, 'm/d/y', 'Europe/Jersey', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(114, 'jo', 'Jordan', 24, 'm/d/y', 'Asia/Amman', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(115, 'kz', 'Kazakhstan', 24, 'm/d/y', 'Asia/Almaty,Asia/Aqtau,Asia/Aqtobe,Asia/Atyrau,Asia/Oral,Asia/Qostanay,Asia/Qyzylorda', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(116, 'ke', 'Kenya', 24, 'm/d/y', 'Africa/Nairobi', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(117, 'ki', 'Kiribati', 24, 'm/d/y', 'Pacific/Enderbury,Pacific/Kiritimati,Pacific/Tarawa', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(118, 'kp', "Korea, Democratic People's Republic of", 24, 'm/d/y', 'Asia/Pyongyang', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(119, 'kr', 'Korea, Republic of', 24, 'm/d/y', 'Asia/Seoul', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(120, 'kw', 'Kuwait', 24, 'm/d/y', 'Asia/Kuwait', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(121, 'kg', 'Kyrgyzstan', 24, 'm/d/y', 'Asia/Bishkek', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(122, 'la', "Lao People's Democratic Republic", 24, 'm/d/y', 'Asia/Vientiane', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(123, 'lv', 'Latvia', 24, 'm/d/y', 'Europe/Riga', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(124, 'lb', 'Lebanon', 24, 'm/d/y', 'Asia/Beirut', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(125, 'ls', 'Lesotho', 24, 'm/d/y', 'Africa/Maseru', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(126, 'lr', 'Liberia', 24, 'm/d/y', 'Africa/Monrovia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(127, 'ly', 'Libya', 24, 'm/d/y', 'Africa/Tripoli', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(128, 'li', 'Liechtenstein', 24, 'm/d/y', 'Europe/Vaduz', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(129, 'lt', 'Lithuania', 24, 'm/d/y', 'Europe/Vilnius', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(130, 'lu', 'Luxembourg', 24, 'm/d/y', 'Europe/Luxembourg', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(131, 'mo', 'Macao', 24, 'm/d/y', 'Asia/Macau', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(132, 'mk', 'Macedonia, the Former Yugoslav Republic of', 24, 'm/d/y', 'Europe/Skopje', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(133, 'mg', 'Madagascar', 24, 'm/d/y', 'Indian/Antananarivo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(134, 'mw', 'Malawi', 24, 'm/d/y', 'Africa/Blantyre', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(135, 'my', 'Malaysia', 24, 'm/d/y', 'Asia/Kuala_Lumpur,Asia/Kuching', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(136, 'mv', 'Maldives', 24, 'm/d/y', 'Indian/Maldives', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(137, 'ml', 'Mali', 24, 'm/d/y', 'Africa/Bamako', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(138, 'mt', 'Malta', 24, 'm/d/y', 'Europe/Malta', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(139, 'mh', 'Marshall Islands', 24, 'm/d/y', 'Pacific/Kwajalein,Pacific/Majuro', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(140, 'mq', 'Martinique', 24, 'm/d/y', 'America/Martinique', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(141, 'mr', 'Mauritania', 24, 'm/d/y', 'Africa/Nouakchott', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(142, 'mu', 'Mauritius', 24, 'm/d/y', 'Indian/Mauritius', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(143, 'yt', 'Mayotte', 24, 'm/d/y', 'Indian/Mayotte', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(144, 'mx', 'Mexico', 24, 'm/d/y', 'America/Bahia_Banderas,America/Cancun,America/Chihuahua,America/Hermosillo,America/Matamoros,America/Mazatlan,America/Merida,America/Mexico_City,America/Monterrey,America/Ojinaga,America/Tijuana', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(145, 'fm', 'Micronesia, Federated States of', 24, 'm/d/y', 'Pacific/Chuuk,Pacific/Kosrae,Pacific/Pohnpei', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(146, 'md', 'Moldova, Republic of', 24, 'm/d/y', 'Europe/Chisinau', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(147, 'mc', 'Monaco', 24, 'm/d/y', 'Europe/Monaco', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(148, 'mn', 'Mongolia', 24, 'm/d/y', 'Asia/Choibalsan,Asia/Hovd,Asia/Ulaanbaatar', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(149, 'me', 'Montenegro', 24, 'm/d/y', 'Europe/Podgorica', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(150, 'ms', 'Montserrat', 24, 'm/d/y', 'America/Montserrat', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(151, 'ma', 'Morocco', 24, 'm/d/y', 'Africa/Casablanca', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(152, 'mz', 'Mozambique', 24, 'm/d/y', 'Africa/Maputo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(153, 'mm', 'Myanmar', 24, 'm/d/y', 'Asia/Yangon', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(154, 'na', 'Namibia', 24, 'm/d/y', 'Africa/Windhoek', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(155, 'nr', 'Nauru', 24, 'm/d/y', 'Pacific/Nauru', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(156, 'np', 'Nepal', 24, 'm/d/y', 'Asia/Kathmandu', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(157, 'nl', 'Netherlands', 24, 'm/d/y', 'Europe/Amsterdam', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(158, 'nc', 'New Caledonia', 24, 'm/d/y', 'Pacific/Noumea', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(159, 'nz', 'New Zealand', 24, 'm/d/y', 'Pacific/Auckland,Pacific/Chatham', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(160, 'ni', 'Nicaragua', 24, 'm/d/y', 'America/Managua', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(161, 'ne', 'Niger', 24, 'm/d/y', 'Africa/Niamey', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(162, 'ng', 'Nigeria', 24, 'm/d/y', 'Africa/Lagos', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(163, 'nu', 'Niue', 24, 'm/d/y', 'Pacific/Niue', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(164, 'nf', 'Norfolk Island', 24, 'm/d/y', 'Pacific/Norfolk', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(165, 'mp', 'Northern Mariana Islands', 24, 'm/d/y', 'Pacific/Saipan', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(166, 'no', 'Norway', 24, 'm/d/y', 'Europe/Oslo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(167, 'om', 'Oman', 24, 'm/d/y', 'Asia/Muscat', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(168, 'pk', 'Pakistan', 24, 'm/d/y', 'Asia/Karachi', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(169, 'pw', 'Palau', 24, 'm/d/y', 'Pacific/Palau', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(170, 'ps', 'Palestine, State of', 24, 'm/d/y', 'Asia/Gaza,Asia/Hebron', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(171, 'pa', 'Panama', 24, 'm/d/y', 'America/Panama', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(172, 'pg', 'Papua New Guinea', 24, 'm/d/y', 'Pacific/Bougainville,Pacific/Port_Moresby', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(173, 'py', 'Paraguay', 24, 'm/d/y', 'America/Asuncion', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(174, 'pe', 'Peru', 24, 'm/d/y', 'America/Lima', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(175, 'ph', 'Philippines', 24, 'm/d/y', 'Asia/Manila', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(176, 'pn', 'Pitcairn', 24, 'm/d/y', 'Pacific/Pitcairn', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(177, 'pl', 'Poland', 24, 'm/d/y', 'Europe/Warsaw', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(178, 'pt', 'Portugal', 24, 'm/d/y', 'Atlantic/Azores,Atlantic/Madeira,Europe/Lisbon', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(179, 'pr', 'Puerto Rico', 24, 'm/d/y', 'America/Puerto_Rico', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(180, 'qa', 'Qatar', 24, 'm/d/y', 'Asia/Qatar', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(181, 're', 'Réunion', 24, 'm/d/y', 'Indian/Reunion', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(182, 'ro', 'Romania', 24, 'm/d/y', 'Europe/Bucharest', 1, 'ro_RO', '%a %s', '.-,', '2022-09-13 00:16:32'),
(183, 'ru', 'Russian Federation', 24, 'm/d/y', 'Asia/Anadyr,Asia/Barnaul,Asia/Chita,Asia/Irkutsk,Asia/Kamchatka,Asia/Khandyga,Asia/Krasnoyarsk,Asia/Magadan,Asia/Novokuznetsk,Asia/Novosibirsk,Asia/Omsk,Asia/Sakhalin,Asia/Srednekolymsk,Asia/Tomsk,Asia/Ust-Nera,Asia/Vladivostok,Asia/Yakutsk,Asia/Yekaterinburg,Europe/Astrakhan,Europe/Kaliningrad,Europe/Kirov,Europe/Moscow,Europe/Samara,Europe/Saratov,Europe/Ulyanovsk,Europe/Volgograd', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(184, 'rw', 'Rwanda', 24, 'm/d/y', 'Africa/Kigali', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(185, 'bl', 'Saint Barthélemy', 24, 'm/d/y', 'America/St_Barthelemy', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(186, 'sh', 'Saint Helena, Ascension and Tristan da Cunha', 24, 'm/d/y', 'Atlantic/St_Helena', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(187, 'kn', 'Saint Kitts and Nevis', 24, 'm/d/y', 'America/St_Kitts', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(188, 'lc', 'Saint Lucia', 24, 'm/d/y', 'America/St_Lucia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(189, 'mf', 'Saint Martin (French part)', 24, 'm/d/y', 'America/Marigot', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(190, 'pm', 'Saint Pierre and Miquelon', 24, 'm/d/y', 'America/Miquelon', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(191, 'vc', 'Saint Vincent and the Grenadines', 24, 'm/d/y', 'America/St_Vincent', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(192, 'ws', 'Samoa', 24, 'm/d/y', 'Pacific/Apia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(193, 'sm', 'San Marino', 24, 'm/d/y', 'Europe/San_Marino', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(194, 'st', 'Sao Tome and Principe', 24, 'm/d/y', 'Africa/Sao_Tome', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(195, 'sa', 'Saudi Arabia', 24, 'm/d/y', 'Asia/Riyadh', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(196, 'sn', 'Senegal', 24, 'm/d/y', 'Africa/Dakar', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(197, 'rs', 'Serbia', 24, 'm/d/y', 'Europe/Belgrade', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(198, 'sc', 'Seychelles', 24, 'm/d/y', 'Indian/Mahe', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(199, 'sl', 'Sierra Leone', 24, 'm/d/y', 'Africa/Freetown', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(200, 'sg', 'Singapore', 24, 'm/d/y', 'Asia/Singapore', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(201, 'sx', 'Sint Maarten (Dutch part)', 24, 'm/d/y', 'America/Lower_Princes', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(202, 'sk', 'Slovakia', 24, 'm/d/y', 'Europe/Bratislava', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(203, 'si', 'Slovenia', 24, 'm/d/y', 'Europe/Ljubljana', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(204, 'sb', 'Solomon Islands', 24, 'm/d/y', 'Pacific/Guadalcanal', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(205, 'so', 'Somalia', 24, 'm/d/y', 'Africa/Mogadishu', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(206, 'za', 'South Africa', 24, 'm/d/y', 'Africa/Johannesburg', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(207, 'gs', 'South Georgia and the South Sandwich Islands', 24, 'm/d/y', 'Atlantic/South_Georgia', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(208, 'ss', 'South Sudan', 24, 'm/d/y', 'Africa/Juba', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(209, 'es', 'Spain', 24, 'm/d/y', 'Africa/Ceuta,Atlantic/Canary,Europe/Madrid', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(210, 'lk', 'Sri Lanka', 24, 'm/d/y', 'Asia/Colombo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(211, 'sd', 'Sudan', 24, 'm/d/y', 'Africa/Khartoum', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(212, 'sr', 'Suriname', 24, 'm/d/y', 'America/Paramaribo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(213, 'sj', 'Svalbard and Jan Mayen', 24, 'm/d/y', 'Arctic/Longyearbyen', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(214, 'sz', 'Swaziland', 24, 'm/d/y', 'Africa/Mbabane', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(215, 'se', 'Sweden', 24, 'm/d/y', 'Europe/Stockholm', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(216, 'ch', 'Switzerland', 24, 'm/d/y', 'Europe/Zurich', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(217, 'sy', 'Syrian Arab Republic', 24, 'm/d/y', 'Asia/Damascus', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(218, 'tw', 'Taiwan, Province of China', 24, 'm/d/y', 'Asia/Taipei', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(219, 'tj', 'Tajikistan', 24, 'm/d/y', 'Asia/Dushanbe', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(220, 'tz', 'Tanzania, United Republic of', 24, 'm/d/y', 'Africa/Dar_es_Salaam', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(221, 'th', 'Thailand', 24, 'm/d/y', 'Asia/Bangkok', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(222, 'tl', 'Timor-Leste', 24, 'm/d/y', 'Asia/Dili', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(223, 'tg', 'Togo', 24, 'm/d/y', 'Africa/Lome', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(224, 'tk', 'Tokelau', 24, 'm/d/y', 'Pacific/Fakaofo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(225, 'to', 'Tonga', 24, 'm/d/y', 'Pacific/Tongatapu', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(226, 'tt', 'Trinidad and Tobago', 24, 'm/d/y', 'America/Port_of_Spain', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(227, 'tn', 'Tunisia', 24, 'm/d/y', 'Africa/Tunis', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(228, 'tr', 'Turkey', 24, 'm/d/y', 'Europe/Istanbul', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(229, 'tm', 'Turkmenistan', 24, 'm/d/y', 'Asia/Ashgabat', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(230, 'tc', 'Turks and Caicos Islands', 24, 'm/d/y', 'America/Grand_Turk', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(231, 'tv', 'Tuvalu', 24, 'm/d/y', 'Pacific/Funafuti', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(232, 'ug', 'Uganda', 24, 'm/d/y', 'Africa/Kampala', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(233, 'ua', 'Ukraine', 24, 'm/d/y', 'Europe/Kiev,Europe/Simferopol,Europe/Uzhgorod,Europe/Zaporozhye', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(234, 'ae', 'United Arab Emirates', 24, 'm/d/y', 'Asia/Dubai', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(235, 'gb', 'United Kingdom', 24, 'm/d/y', 'Europe/London', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(236, 'us', 'United States', 12, 'm/d/y', 'America/Adak,America/Anchorage,America/Boise,America/Chicago,America/Denver,America/Detroit,America/Indiana/Indianapolis,America/Indiana/Knox,America/Indiana/Marengo,America/Indiana/Petersburg,America/Indiana/Tell_City,America/Indiana/Vevay,America/Indiana/Vincennes,America/Indiana/Winamac,America/Juneau,America/Kentucky/Louisville,America/Kentucky/Monticello,America/Los_Angeles,America/Menominee,America/Metlakatla,America/New_York,America/Nome,America/North_Dakota/Beulah,America/North_Dakota/Center,America/North_Dakota/New_Salem,America/Phoenix,America/Sitka,America/Yakutat,Pacific/Honolulu', 0, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(237, 'um', 'United States Minor Outlying Islands', 24, 'm/d/y', 'Pacific/Midway,Pacific/Wake', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(238, 'uy', 'Uruguay', 24, 'm/d/y', 'America/Montevideo', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(239, 'uz', 'Uzbekistan', 24, 'm/d/y', 'Asia/Samarkand,Asia/Tashkent', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(240, 'vu', 'Vanuatu', 24, 'm/d/y', 'Pacific/Efate', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(241, 've', 'Venezuela, Bolivarian Republic of', 24, 'm/d/y', 'America/Caracas', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(242, 'vn', 'Viet Nam', 24, 'm/d/y', 'Asia/Ho_Chi_Minh', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(243, 'vg', 'Virgin Islands, British', 24, 'm/d/y', 'America/Tortola', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(244, 'vi', 'Virgin Islands, U.S.', 24, 'm/d/y', 'America/St_Thomas', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(245, 'wf', 'Wallis and Futuna', 24, 'm/d/y', 'Pacific/Wallis', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(246, 'eh', 'Western Sahara', 24, 'm/d/y', 'Africa/El_Aaiun', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(247, 'ye', 'Yemen', 24, 'm/d/y', 'Asia/Aden', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(248, 'zm', 'Zambia', 24, 'm/d/y', 'Africa/Lusaka', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32'),
(249, 'zw', 'Zimbabwe', 24, 'm/d/y', 'Africa/Harare', 1, 'en_US', '%s %a', '.-,', '2022-09-13 00:16:32');

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}deleted_surveys` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL DEFAULT 0,
  `survey` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}favorites` (
  `id` int(11) NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}invoices` (
  `id` int(11) UNSIGNED NOT NULL,
  `number` varchar(255) NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `summary` text NOT NULL,
  `bill_to` text NOT NULL,
  `tax` double(16,2) UNSIGNED NOT NULL,
  `subtotal` double(16,2) UNSIGNED NOT NULL,
  `total` double(16,2) UNSIGNED NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}labels` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(1) NOT NULL DEFAULT 'A',
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}label_items` (
  `id` int(11) NOT NULL,
  `label` int(11) UNSIGNED DEFAULT NULL,
  `result` int(11) UNSIGNED DEFAULT NULL,
  `checked` tinyint(1) UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}media` (
  `id` int(11) NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `type_id` int(11) UNSIGNED DEFAULT NULL,
  `owner` int(11) UNSIGNED DEFAULT NULL,
  `mtype` varchar(50) NOT NULL DEFAULT '',
  `ftype` varchar(50) DEFAULT NULL,
  `src` text NOT NULL,
  `size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `extension` varchar(6) NOT NULL,
  `server` varchar(15) NOT NULL,
  `deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}meta` (
  `type` tinyint(1) UNSIGNED NOT NULL,
  `type_id` int(11) UNSIGNED NOT NULL,
  `meta_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}options` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{{prefix}}options` (`name`, `content`) VALUES
('admin_theme_name', 'default'),
('auto_approve_surveys', '1'),
('auto_a_sbr', '0'),
('auto_a_sfs', '0'),
('auto_a_sps', '0'),
('comm_cpa', '30'),
('min_cpa', '0.3'),
('min_cpa_self', '0.1'),
('default_plan', '{\"name\":\"Free\",\"surveys\":15,\"responses\":50,\"questions\":10,\"collectors\":1,\"tmembers\":0,\"rbrand\":false,\"space\":100}'),
('def_country', 'us'),
('def_language', 'en_US'),
('deposit_min', '20'),
('email_type', ''),
('femail_verify', '0'),
('front_end_favicon', ''),
('invoicing_settings', ''),
('kyc_settings', NULL),
('mail_smtp', NULL),
('mail_stmt', NULL),
('meta_tag_desc', NULL),
('meta_tag_keywords', NULL),
('meta_tag_title', NULL),
('paypal_client_id', NULL),
('paypal_secret', NULL),
('stripe_secret_key', NULL),
('recaptcha_key', ''),
('recaptcha_secret', ''),
('ref_system', NULL),
('site_url', '{{account_URL}}'),
('subnotif_expired', NULL),
('subnotif_will_expire', NULL),
('survey_limit', '0'),
('survey_u_limit', '0'),
('terms_page', '3'),
('theme_name', 'Default'),
('to_Default', NULL),
('unvu_post_survey', '0'),
('unvu_respond_survey', '0'),
('website_desc', NULL),
('website_name', 'SurveyClick'),
('withdraw_min', '20');

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}pages` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'website',
  `slug` varchar(30) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `thumb` text DEFAULT NULL,
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `template` varchar(50) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `lu_user` int(11) UNSIGNED NOT NULL,
  `relation` int(11) UNSIGNED DEFAULT NULL,
  `lang` varchar(20) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}plans` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `sur` int(11) NOT NULL,
  `res_p_sur` int(11) NOT NULL,
  `que_p_sur` int(11) NOT NULL,
  `col` int(11) NOT NULL,
  `tm` int(11) NOT NULL,
  `r_brand` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `space` int(11) NOT NULL,
  `price` double(10,2) UNSIGNED NOT NULL,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 2,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}plan_offers` (
  `id` int(11) UNSIGNED NOT NULL,
  `plan` int(11) UNSIGNED NOT NULL,
  `min_months` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `price` double(10,2) UNSIGNED NOT NULL,
  `starts` datetime NOT NULL DEFAULT current_timestamp(),
  `expires` datetime DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}questions` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` varchar(30) NOT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `position` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `step` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `required` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 2,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}q_cond` (
  `id` int(11) UNSIGNED NOT NULL,
  `question` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `value` varchar(255) NOT NULL,
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}q_options` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `type_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `position` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice` int(11) UNSIGNED NOT NULL,
  `number` varchar(255) NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `summary` text NOT NULL,
  `bill_to` text NOT NULL,
  `tax` double(16,2) UNSIGNED NOT NULL,
  `subtotal` double(16,2) UNSIGNED NOT NULL,
  `total` double(16,2) UNSIGNED NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}results` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED DEFAULT NULL,
  `visitor` varchar(255) DEFAULT NULL,
  `survey` int(11) UNSIGNED NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `results` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `commission` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `commission_bonus` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `commission_p` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `lpoints` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `collector` int(11) UNSIGNED DEFAULT NULL,
  `country` varchar(5) DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `exp` datetime DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}result_vars` (
  `id` int(11) UNSIGNED NOT NULL,
  `result` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `var` varchar(50) NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}saved` (
  `id` int(11) NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `collector` int(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}saved_reports` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `temp_pos` tinyint(1) UNSIGNED DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `options` text DEFAULT NULL,
  `result` longtext NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `session` varchar(255) NOT NULL DEFAULT '',
  `valid` varchar(100) NULL,
  `expiration` datetime NOT NULL,
  `conf` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}shared_reports` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `report` int(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}shop_cart` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED DEFAULT NULL,
  `item` int(11) UNSIGNED DEFAULT NULL,
  `qt` int(3) UNSIGNED NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}shop_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `country` varchar(3) DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}shop_category_items` (
  `category` int(11) UNSIGNED DEFAULT NULL,
  `item` int(11) UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}shop_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `media` int(11) UNSIGNED DEFAULT NULL,
  `stock` int(3) UNSIGNED DEFAULT NULL,
  `price` double(10,2) UNSIGNED NOT NULL,
  `purchases` int(11) UNSIGNED DEFAULT NULL,
  `country` varchar(3) DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}shop_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `total` double(10,2) UNSIGNED NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}step_cond` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `step` int(11) UNSIGNED NOT NULL,
  `action` int(2) UNSIGNED NOT NULL,
  `action_id` int(11) UNSIGNED NOT NULL,
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `emsg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}step_questions` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL,
  `main` tinyint(1) UNSIGNED DEFAULT NULL,
  `action` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `action_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `emsg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}subscriptions` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `plan` int(11) UNSIGNED NOT NULL,
  `expiration` datetime DEFAULT NULL,
  `autorenew` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `rcount` int(3) UNSIGNED NOT NULL DEFAULT 1,
  `addm` tinyint(1) UNSIGNED DEFAULT NULL,
  `last_renew` datetime NOT NULL DEFAULT current_timestamp(),
  `paid` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `token` varchar(255) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}surveys` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `team` int(11) DEFAULT NULL,
  `lu_user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `avatar` int(11) UNSIGNED DEFAULT NULL,
  `category` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `ent_done` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `ent_target` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `budget` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `budget_bonus` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `spent` double(16,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `spent_c` double(16,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `questions` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `template` varchar(15) NOT NULL DEFAULT 't1',
  `autovalid` tinyint(1) NOT NULL DEFAULT 1,
  `last_answer` datetime DEFAULT NULL,
  `approved` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}survey_attachments` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `response` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `question` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `media` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}survey_dashboard` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `type_id` int(11) UNSIGNED DEFAULT NULL,
  `position` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `setting` text DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}survey_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}survey_meta` (
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `lcm_user` int(11) UNSIGNED DEFAULT NULL,
  `lcm` datetime NOT NULL DEFAULT current_timestamp(),
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}teams_actions` (
  `id` int(11) UNSIGNED NOT NULL,
  `team` int(11) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}teams_chat` (
  `id` int(11) UNSIGNED NOT NULL,
  `team` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}teams_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `team` int(11) UNSIGNED NOT NULL,
  `perm` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `perms` text DEFAULT NULL,
  `approved` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `inviter` int(11) UNSIGNED DEFAULT NULL,
  `last_action_chat` datetime DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}transactions` (
  `id` int(11) UNSIGNED NOT NULL,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `amount` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `amount_with_c` double(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `license` varchar(255) DEFAULT NULL,
  `transactionId` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL DEFAULT '',
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `birthday` date DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `dfname` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `avatar` int(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `balance` double(20,2) NOT NULL DEFAULT 0.00,
  `bonus` double(20,2) NOT NULL DEFAULT 0.00,
  `lpoints` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `ip_addr` varchar(255) NOT NULL DEFAULT '',
  `last_action` datetime NOT NULL DEFAULT current_timestamp(),
  `fail_attempts` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `valid` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `ban` datetime DEFAULT NULL,
  `country` varchar(3) DEFAULT NULL,
  `lcc` date DEFAULT NULL,
  `perm` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `surveyor` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `team` tinyint(1) UNSIGNED DEFAULT NULL,
  `verified` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `lang` varchar(15) DEFAULT NULL,
  `f_hour` varchar(2) NOT NULL DEFAULT '24',
  `f_date` varchar(5) NOT NULL DEFAULT 'm/d/y',
  `tz` varchar(50) DEFAULT NULL,
  `fdweek` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `trans` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `twosv` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `refId` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}user_intents` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}user_options` (
  `user` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}user_stats` (
  `user` int(11) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT '',
  `value` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}user_vouchers` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED DEFAULT 0,
  `voucher_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `uses` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}usr_surveys` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `survey` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `team` int(11) UNSIGNED DEFAULT NULL,
  `last_change` datetime DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}verif_codes` (
  `user` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `code` varchar(15) NOT NULL DEFAULT '',
  `checks` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `expiration` datetime NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `{{prefix}}vouchers` (
  `id` int(11) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `amount` double(8,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `a_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `user` int(11) UNSIGNED DEFAULT NULL,
  `limit` int(3) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `expiration` datetime DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--

ALTER TABLE `{{prefix}}admin_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `by_user` (`by_user`),
  ADD KEY `to_user` (`to_user`),
  ADD KEY `date` (`date`);

--

ALTER TABLE `{{prefix}}alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`,`open`) USING BTREE;

--

ALTER TABLE `{{prefix}}answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey` (`survey`,`type`,`result`) USING BTREE,
  ADD KEY `question` (`question`,`value`,`value2`,`value3`) USING BTREE;

--

ALTER TABLE `{{prefix}}categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`,`lang`) USING BTREE,
  ADD KEY `parent` (`parent`),
  ADD KEY `type` (`type`);
ALTER TABLE `{{prefix}}categories` ADD FULLTEXT KEY `name` (`name`);

--

ALTER TABLE `{{prefix}}category_pages`
  ADD UNIQUE KEY `category` (`category`,`page`),
  ADD KEY `page` (`page`);

--

ALTER TABLE `{{prefix}}collectors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`) USING BTREE,
  ADD KEY `survey` (`survey`),
  ADD KEY `type` (`type`,`visible`) USING BTREE,
  ADD KEY `cpa` (`cpa`,`lpoints`);

--

ALTER TABLE `{{prefix}}collector_options`
  ADD KEY `collector` (`collector`),
  ADD KEY `type` (`type`,`value`);

--

ALTER TABLE `{{prefix}}countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iso_3166` (`iso_3166`);
ALTER TABLE `{{prefix}}countries` ADD FULLTEXT KEY `name` (`name`);

--

ALTER TABLE `{{prefix}}deleted_surveys`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `survey` (`survey`),
  ADD KEY `date` (`date`);

--

ALTER TABLE `{{prefix}}favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`survey`),
  ADD KEY `survey` (`survey`);

--

ALTER TABLE `{{prefix}}invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `user` (`user`);

--

ALTER TABLE `{{prefix}}labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey` (`survey`);

--

ALTER TABLE `{{prefix}}label_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `result` (`result`,`label`) USING BTREE,
  ADD KEY `label` (`label`,`checked`);

--

ALTER TABLE `{{prefix}}media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`,`type_id`),
  ADD KEY `deleted` (`deleted`);

--

ALTER TABLE `{{prefix}}meta`
  ADD UNIQUE KEY `type` (`type`,`type_id`,`meta_id`),
  ADD KEY `value` (`value`(768)) USING BTREE;

--

ALTER TABLE `{{prefix}}options`
  ADD PRIMARY KEY (`name`);

--

ALTER TABLE `{{prefix}}pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`,`lang`),
  ADD KEY `type` (`type`),
  ADD KEY `relation` (`relation`);
ALTER TABLE `{{prefix}}pages` ADD FULLTEXT KEY `title` (`title`);

--

ALTER TABLE `{{prefix}}plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visible` (`visible`);

--

ALTER TABLE `{{prefix}}plan_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan` (`plan`,`min_months`,`starts`,`expires`) USING BTREE,
  ADD KEY `expires` (`expires`);

--

ALTER TABLE `{{prefix}}questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey_2` (`survey`,`step`),
  ADD KEY `survey` (`survey`,`position`) USING BTREE;
ALTER TABLE `{{prefix}}questions` ADD FULLTEXT KEY `title` (`title`);

--

ALTER TABLE `{{prefix}}q_cond`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question` (`question`) USING BTREE;

--

ALTER TABLE `{{prefix}}q_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`,`type_id`);

--

ALTER TABLE `{{prefix}}receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `user` (`user`),
  ADD KEY `invoice` (`invoice`);

--

ALTER TABLE `{{prefix}}results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`survey`),
  ADD UNIQUE KEY `visitor` (`visitor`,`survey`),
  ADD KEY `survey` (`survey`),
  ADD KEY `status` (`status`),
  ADD KEY `collector` (`collector`),
  ADD KEY `date` (`date`),
  ADD KEY `exp` (`exp`);

--

ALTER TABLE `{{prefix}}result_vars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `result` (`result`,`type`,`var`) USING BTREE;

--

ALTER TABLE `{{prefix}}saved`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`survey`),
  ADD KEY `survey` (`survey`);

--

ALTER TABLE `{{prefix}}saved_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`survey`,`user`,`temp_pos`) USING BTREE;

--

ALTER TABLE `{{prefix}}sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session` (`session`),
  ADD KEY `user` (`user`);

--

ALTER TABLE `{{prefix}}shared_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report` (`report`,`user`) USING BTREE;

--

ALTER TABLE `{{prefix}}shop_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`item`),
  ADD KEY `date` (`date`);

--

ALTER TABLE `{{prefix}}shop_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country` (`country`),
  ADD KEY `status` (`status`);
ALTER TABLE `{{prefix}}shop_categories` ADD FULLTEXT KEY `name` (`name`);

--

ALTER TABLE `{{prefix}}shop_category_items`
  ADD UNIQUE KEY `category` (`category`,`item`),
  ADD KEY `item` (`item`);

--

ALTER TABLE `{{prefix}}shop_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `country` (`country`);
ALTER TABLE `{{prefix}}shop_items` ADD FULLTEXT KEY `name` (`name`);

--

ALTER TABLE `{{prefix}}shop_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`,`status`) USING BTREE,
  ADD KEY `status` (`status`);

--

ALTER TABLE `{{prefix}}step_cond`
  ADD PRIMARY KEY (`id`),
  ADD KEY `step` (`survey`,`step`) USING BTREE;

--

ALTER TABLE `{{prefix}}step_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey` (`survey`) USING BTREE;

--

ALTER TABLE `{{prefix}}subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`),
  ADD KEY `last_renew` (`last_renew`) USING BTREE,
  ADD KEY `plan` (`plan`),
  ADD KEY `expiration` (`expiration`) USING BTREE,
  ADD KEY `date` (`date`);

--

ALTER TABLE `{{prefix}}surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `category` (`category`),
  ADD KEY `user` (`user`),
  ADD KEY `type` (`type`) USING BTREE,
  ADD KEY `date` (`date`),
  ADD KEY `team` (`team`),
  ADD KEY `last_answer` (`last_answer`);
ALTER TABLE `{{prefix}}surveys` ADD FULLTEXT KEY `name` (`name`);

--

ALTER TABLE `{{prefix}}survey_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey` (`survey`,`response`,`question`) USING BTREE;

--

ALTER TABLE `{{prefix}}survey_dashboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey` (`survey`,`user`) USING BTREE;

--

ALTER TABLE `{{prefix}}survey_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey` (`survey`);

--

ALTER TABLE `{{prefix}}survey_meta`
  ADD UNIQUE KEY `survey` (`survey`,`meta_key`);

--

ALTER TABLE `{{prefix}}teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`) USING BTREE;
ALTER TABLE `{{prefix}}teams` ADD FULLTEXT KEY `name` (`name`);

--

ALTER TABLE `{{prefix}}teams_actions`
  ADD PRIMARY KEY (`id`);

--

ALTER TABLE `{{prefix}}teams_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team` (`team`),
  ADD KEY `date` (`date`);

--

ALTER TABLE `{{prefix}}teams_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`team`) USING BTREE,
  ADD KEY `team` (`team`,`approved`) USING BTREE;

--

ALTER TABLE `{{prefix}}transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactionId` (`transactionId`),
  ADD KEY `date` (`date`),
  ADD KEY `user` (`user`) USING BTREE,
  ADD KEY `survey` (`survey`) USING BTREE,
  ADD KEY `type` (`type`);
ALTER TABLE `{{prefix}}transactions` ADD FULLTEXT KEY `license` (`license`);

--

ALTER TABLE `{{prefix}}users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `last_action` (`last_action`),
  ADD KEY `date` (`date`),
  ADD KEY `surveyor` (`surveyor`),
  ADD KEY `ban` (`ban`),
  ADD KEY `team` (`team`) USING BTREE,
  ADD KEY `refId` (`refId`);
ALTER TABLE `{{prefix}}users` ADD FULLTEXT KEY `password` (`password`);
ALTER TABLE `{{prefix}}users` ADD FULLTEXT KEY `full_name` (`full_name`);

--

ALTER TABLE `{{prefix}}user_intents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`,`user`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE;

--

ALTER TABLE `{{prefix}}user_options`
  ADD UNIQUE KEY `option` (`user`,`name`) USING BTREE;

--

ALTER TABLE `{{prefix}}user_stats`
  ADD UNIQUE KEY `user` (`user`,`type`);

--

ALTER TABLE `{{prefix}}user_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`,`voucher_id`,`uses`),
  ADD KEY `voucher_id` (`voucher_id`);

--

ALTER TABLE `{{prefix}}usr_surveys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`survey`),
  ADD KEY `user_2` (`user`,`last_change`) USING BTREE,
  ADD KEY `user_3` (`user`,`team`),
  ADD KEY `survey` (`survey`) USING BTREE;

--

ALTER TABLE `{{prefix}}verif_codes`
  ADD UNIQUE KEY `user` (`user`,`type`),
  ADD KEY `expiration` (`expiration`);

--

ALTER TABLE `{{prefix}}vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `type` (`type`),
  ADD KEY `expiration` (`expiration`),
  ADD KEY `limit` (`limit`),
  ADD KEY `status` (`status`);
ALTER TABLE `{{prefix}}vouchers` ADD FULLTEXT KEY `code` (`code`);

--

ALTER TABLE `{{prefix}}admin_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}alerts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}collectors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}countries`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--

ALTER TABLE `{{prefix}}deleted_surveys`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}invoices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}labels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}label_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}pages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}plans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}plan_offers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}questions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}q_cond`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}q_options`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}results`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}result_vars`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}saved`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}saved_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}shared_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}shop_cart`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}shop_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}shop_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}shop_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}step_cond`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}step_questions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}subscriptions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}surveys`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}survey_attachments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}survey_dashboard`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}survey_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}teams_actions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}teams_chat`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}teams_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}transactions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}user_intents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}user_vouchers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}usr_surveys`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

ALTER TABLE `{{prefix}}vouchers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--

CREATE TRIGGER `{{prefix}}categories_delete` AFTER DELETE ON `{{prefix}}categories` FOR EACH ROW UPDATE `{{prefix}}surveys` SET `category` = 0 WHERE `category` = OLD.`id` ;

--

CREATE TRIGGER `{{prefix}}pages_delete` AFTER DELETE ON `{{prefix}}pages` FOR EACH ROW DELETE FROM `{{prefix}}category_pages` WHERE `page` = OLD.`id` ;

--

CREATE TRIGGER `{{prefix}}collectors_delete` AFTER DELETE ON `{{prefix}}collectors` FOR EACH ROW DELETE FROM `{{prefix}}collector_options` WHERE `collector` = OLD.`id` ;
CREATE TRIGGER `{{prefix}}collectors_insert` AFTER INSERT ON `{{prefix}}collectors` FOR EACH ROW UPDATE `{{prefix}}surveys` SET `status` = IF(`approved` > 0, 3, 2) WHERE `id` = NEW.`survey` AND `status` = 1 AND (`budget` + `budget_bonus`) > 0 AND `questions` > 0 ;

--

CREATE TRIGGER `{{prefix}}labels_delete` AFTER DELETE ON `{{prefix}}labels` FOR EACH ROW DELETE FROM `{{prefix}}label_items` WHERE `label` = OLD.`id` ;

--

CREATE TRIGGER `{{prefix}}plans_delete` AFTER DELETE ON `{{prefix}}plans` FOR EACH ROW DELETE FROM `{{prefix}}plan_offers` WHERE `plan` = OLD.`id` ;

--

CREATE TRIGGER `{{prefix}}questions_delete` AFTER DELETE ON `{{prefix}}questions` FOR EACH ROW BEGIN

## Answers
DELETE FROM `{{prefix}}answers` WHERE `question` = OLD.`id`;

## Media
UPDATE `{{prefix}}media` SET `deleted` = 1 WHERE `type` IN (1,2) AND `type_id` = OLD.`id`;

END ;

CREATE TRIGGER `{{prefix}}questions_insert` BEFORE INSERT ON `{{prefix}}questions` FOR EACH ROW BEGIN

## Update survey count
UPDATE `{{prefix}}surveys` SET `questions` = `questions` + 1 WHERE `id` = NEW.`survey`;

## Position

IF NEW.`position` = 0 THEN

SET NEW.`position` = (SELECT COUNT(`id`) + 1 FROM `{{prefix}}questions` WHERE `survey` = NEW.`survey` AND `visible` > 0);

END IF;

END ;

CREATE TRIGGER `{{prefix}}questions_update` AFTER UPDATE ON `{{prefix}}questions` FOR EACH ROW BEGIN

IF OLD.`visible` = 2 AND NEW.`visible` < 2 THEN

## Update new survey count
UPDATE `{{prefix}}surveys` SET `questions` = `questions` - 1 WHERE `id` = NEW.`survey`;

ELSEIF OLD.visible < 2 AND NEW.`visible` = 2 THEN

## Update old survey count
UPDATE `{{prefix}}surveys` SET `questions` = `questions` + 1 WHERE `id` = OLD.`survey`;

END IF;

END ;

--

CREATE TRIGGER `{{prefix}}results_delete` AFTER DELETE ON `{{prefix}}results` FOR EACH ROW BEGIN

## Answers
DELETE FROM `{{prefix}}answers` WHERE `survey` = OLD.`survey` AND `type` IS NOT NULL AND `result` = OLD.`id`;

## Label items
DELETE FROM `{{prefix}}label_items` WHERE `result` = OLD.`id`;

## Variables
DELETE FROM `{{prefix}}result_vars` WHERE `result` = OLD.`id`;

## Attachments
DELETE FROM `{{prefix}}survey_attachments` WHERE `survey` = OLD.`survey` AND `response` = OLD.`id`;

END ;

CREATE TRIGGER `{{prefix}}results_insert` BEFORE INSERT ON `{{prefix}}results` FOR EACH ROW BEGIN

IF NEW.`commission` > 0 OR NEW.`commission_bonus` > 0 THEN

UPDATE `{{prefix}}surveys` SET `budget` = `budget` - NEW.`commission`, `budget_bonus` = `budget_bonus` - NEW.`commission_bonus` WHERE `id` = NEW.`survey`;

END IF;

## update action in user surveys
UPDATE `{{prefix}}usr_surveys` SET `last_change` = NOW() WHERE `survey` = NEW.`survey`;

END ;

CREATE TRIGGER `{{prefix}}results_update` AFTER UPDATE ON `{{prefix}}results` FOR EACH ROW BEGIN

IF OLD.`status` != 3 AND NEW.`status` = 3 THEN

UPDATE `{{prefix}}surveys` SET `ent_done` = @done := `ent_done` + 1, `spent` = NEW.`commission` + NEW.`commission_bonus`, `spent_c` = NEW.`commission_p`, `status` = IF(@done >= `ent_target`, 5, `status`), `last_answer` = NOW() WHERE `id` = NEW.`survey`;

IF NEW.`user` IS NOT NULL THEN

INSERT INTO `{{prefix}}transactions` (`survey`, `user`, `type`, `amount`, `amount_with_c`, `status`) VALUES (NEW.`survey`, NEW.`user`, 6, (NEW.`commission` + NEW.`commission_bonus` - NEW.`commission_p`), (NEW.`commission` + NEW.`commission_bonus`), 2);

IF NEW.`lpoints` THEN

UPDATE `{{prefix}}users` SET `lpoints` = `lpoints` + NEW.`lpoints` WHERE `id` = NEW.`user`;

END IF;

END IF;

IF NEW.`commission_p` > 0 THEN

INSERT INTO `{{prefix}}transactions` (`survey`, `user`, `type`, `amount`, `status`) VALUES (NEW.`survey`, NEW.`user`, 8, NEW.`commission_p`, 2);

END IF;

END IF;

## update action in user surveys if the status changes

IF NEW.`status` != OLD.`status` THEN

UPDATE `{{prefix}}usr_surveys` SET `last_change` = NOW() WHERE `survey` = NEW.`survey`;

END IF;

END ;

--

CREATE TRIGGER `{{prefix}}shared_reports_insert` AFTER INSERT ON `{{prefix}}shared_reports` FOR EACH ROW BEGIN

SET @user = (SELECT `user` FROM `{{prefix}}saved_reports` WHERE `id` = NEW.`report`);

INSERT INTO `{{prefix}}alerts` (`user`, `text`) VALUES (NEW.`user`, JSON_OBJECT("type", "srep", "report", NEW.`report`, "user", @user));

END ;

--

CREATE TRIGGER `{{prefix}}shop_orders_insert` AFTER INSERT ON `{{prefix}}shop_orders` FOR EACH ROW IF NEW.`status` > 0 THEN

UPDATE `{{prefix}}users` SET `lpoints` = `lpoints` - NEW.`total` WHERE `id` = NEW.`user`;

END IF ;

CREATE TRIGGER `{{prefix}}shop_orders_update` AFTER UPDATE ON `{{prefix}}shop_orders` FOR EACH ROW IF OLD.`status` > 0 AND NEW.`status` = 0 THEN

UPDATE `{{prefix}}users` SET `lpoints` = `lpoints` + OLD.`total` WHERE `id` = OLD.`user`;

ELSEIF OLD.`status` = 0 AND NEW.`status` > 0 THEN

UPDATE `{{prefix}}users` SET `lpoints` = `lpoints` - OLD.`total` WHERE `id` = OLD.`user`;

END IF ;

--

CREATE TRIGGER `{{prefix}}surveys_delete` AFTER DELETE ON `{{prefix}}surveys` FOR EACH ROW BEGIN

## Survey history
DELETE FROM `{{prefix}}survey_history` WHERE `survey` = OLD.`id`;

## Survey meta
DELETE FROM `{{prefix}}survey_meta` WHERE `survey` = OLD.`id`;

## Survey attachments
DELETE FROM `survey_attachments` WHERE `survey` = OLD.`id`;

## Questions
DELETE FROM `{{prefix}}questions` WHERE `survey` = OLD.`id`;

## Results
DELETE FROM `results` WHERE `survey` = OLD.`id`;

## Answers
DELETE FROM `answers` WHERE `survey` = OLD.`id`;

## Labels
DELETE FROM `labels` WHERE `survey` = OLD.`id`;

## Collectors
DELETE FROM `collectors` WHERE `survey` = OLD.`id`;

## Steps
DELETE FROM `step_questions` WHERE `survey` = OLD.`id`;

## Conditions for steps
DELETE FROM `step_cond` WHERE `survey` = OLD.`id`;

END ;

CREATE TRIGGER `{{prefix}}surveys_insert` AFTER INSERT ON `{{prefix}}surveys` FOR EACH ROW BEGIN

INSERT INTO `{{prefix}}usr_surveys` (`user`, `survey`) VALUES (NEW.`user`, NEW.`id`);

INSERT INTO `{{prefix}}step_questions` (`name`, `survey`, `main`) VALUES ('Main', NEW.`id`, 1);

END ;

CREATE TRIGGER `{{prefix}}surveys_update` BEFORE UPDATE ON `{{prefix}}surveys` FOR EACH ROW BEGIN

#### STATUS CHANGED
IF OLD.`status` != NEW.`status` THEN

## Update change history
INSERT INTO `{{prefix}}survey_history` (`survey`, `user`, `type`, `value`) VALUES (NEW.`id`, NEW.`lu_user`, 1, JSON_OBJECT( "new", NEW.`status`, "old", OLD.`status`));

## Survey finished or it has been transfered, return the budget in case it exists
IF NEW.`status` = 5 AND (NEW.`budget` > 0 OR NEW.`budget_bonus` > 0) THEN

UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`budget`, `bonus` = `bonus` + NEW.`budget_bonus` WHERE `id` = OLD.`user`;

INSERT INTO `{{prefix}}transactions` (`survey`, `user`, `type`, `amount`, `status`) VALUES (NEW.`id`, OLD.`user`, 2, (NEW.`budget` + NEW.`budget_bonus`), 2);

SET NEW.`budget` = 0;
SET NEW.`budget_bonus` = 0;

END IF;

END IF;

#### STATUS IS WAITING FOR QUESTIONS
IF NEW.`status` = 1 AND NEW.`questions` > 0 THEN

## SET "Waiting approval" or "Paused" in case it is already approved. 
SET NEW.`status` = IF(NEW.`approved` > 0, 3, 2);

END IF;

#### IF STATUS IS "WAITING APPROVAL" CHANGE STATUS TO "PAUSED"
IF NEW.`status` = 2 AND OLD.`approved` = 0 AND NEW.`approved` > 0 THEN

SET NEW.`status` = 3;

END IF;

#### SURVEY DELETED
IF NEW.`status` = -1 AND OLD.`status` != -1 THEN

## This survey will be pending deletion
INSERT INTO `{{prefix}}deleted_surveys` (`user`, `survey`) VALUES (OLD.`user`, NEW.`id`);

## Permanently delete collaborators
DELETE FROM `{{prefix}}usr_surveys` WHERE `survey` = NEW.`id`;

## Delete from favorites
DELETE FROM `{{prefix}}favorites` WHERE `survey` = NEW.`id`;

## Delete from saved
DELETE FROM `{{prefix}}saved` WHERE `survey` = NEW.`id`;

END IF;

#### SURVEY RESTORED
IF NEW.`status` != -1 AND OLD.`status` = -1 THEN

## Restore to the same user
IF NEW.`user` = 0 THEN
SET NEW.`user` = (SELECT `user` FROM `{{prefix}}deleted_surveys` WHERE `survey` = NEW.`id` LIMIT 1);
END IF;

## Delete from pending deletion
DELETE FROM `{{prefix}}deleted_surveys` WHERE `survey` = NEW.`id`;

END IF;

#### SURVEY TRANSFERED
IF NEW.`user` != OLD.`user` AND NEW.`user` != 0 THEN

## Return owner budget
IF NEW.`budget` > 0 OR NEW.`budget_bonus` > 0 THEN

UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`budget`, `bonus` = `bonus` + NEW.`budget_bonus` WHERE `id` = OLD.`user`;

INSERT INTO `{{prefix}}transactions` (`survey`, `user`, `type`, `amount`, `status`) VALUES (NEW.`id`, OLD.`user`, 2, (NEW.`budget` + NEW.`budget_bonus`), 2);

SET NEW.`budget` = 0;
SET NEW.`budget_bonus` = 0;

END IF;

## Survey old history
DELETE FROM `{{prefix}}survey_history` WHERE `survey` = OLD.`id`;

## Survey new history
INSERT INTO `{{prefix}}survey_history` (`survey`, `user`, `type`, `value`) VALUES (NEW.`id`, NEW.`lu_user`, 2, JSON_OBJECT( "new", NEW.`user`, "old", NEW.`lu_user`));

## Survey meta
DELETE FROM `{{prefix}}survey_meta` WHERE `survey` = OLD.`id`;

## Dashboards
DELETE FROM `{{prefix}}survey_dashboard` WHERE `survey` = OLD.`id`;

## Permanently delete collaborators
DELETE FROM `{{prefix}}usr_surveys` WHERE `survey` = OLD.`id`;

## Asign to the new owner
INSERT INTO `{{prefix}}usr_surveys` (`user`, `survey`) VALUES (NEW.`user`, NEW.`id`);

## Delete from favorites
DELETE FROM `{{prefix}}favorites` WHERE `survey` = OLD.`id`;

END IF;

END ;

--

CREATE TRIGGER `{{prefix}}survey_attachments_delete` AFTER DELETE ON `{{prefix}}survey_attachments` FOR EACH ROW UPDATE `{{prefix}}media` SET `deleted` = 1 WHERE `id` = OLD.`media` ;

--

CREATE TRIGGER `{{prefix}}teams_delete` AFTER DELETE ON `{{prefix}}teams` FOR EACH ROW DELETE FROM `{{prefix}}teams_members` WHERE `team` = OLD.`id` ;
CREATE TRIGGER `{{prefix}}teams_insert` AFTER INSERT ON `{{prefix}}teams` FOR EACH ROW INSERT INTO `{{prefix}}teams_members` (`user`, `team`, `perm`, `approved`) VALUES (NEW.`user`, NEW.`id`, 2, 1) ;

--

CREATE TRIGGER `{{prefix}}teams_chat_insert` AFTER INSERT ON `{{prefix}}teams_chat` FOR EACH ROW UPDATE `{{prefix}}teams` SET `lcm_user` = NEW.`user`, `lcm` = NOW() WHERE `id` = NEW.`team` ;

--

CREATE TRIGGER `{{prefix}}team_members_delete` AFTER DELETE ON `{{prefix}}teams_members` FOR EACH ROW BEGIN

IF OLD.`approved` = 0 THEN

INSERT INTO `{{prefix}}teams_actions` (`team`, `text`) VALUES (OLD.`team`, JSON_OBJECT("type", "invc", "user", OLD.`user`));

ELSEIF OLD.`approved` > 0 THEN

INSERT INTO `{{prefix}}teams_actions` (`team`, `text`) VALUES (OLD.`team`, JSON_OBJECT("type", "udel", "user", OLD.`user`));

DELETE FROM `{{prefix}}usr_surveys` WHERE `user` = OLD.`user` AND `team` = OLD.`team`;

UPDATE `{{prefix}}users` SET `team` = NULL WHERE `id` = OLD.`user` AND `team` = OLD.`team`;

END IF;

END ;

CREATE TRIGGER `{{prefix}}team_members_insert` AFTER INSERT ON `{{prefix}}teams_members` FOR EACH ROW BEGIN

IF NEW.`approved` = 0 THEN

INSERT INTO `{{prefix}}alerts` (`user`, `text`) VALUES (NEW.`user`, JSON_OBJECT("type", "tinv", "user", NEW.`user`, "team", NEW.`team`, "invited", NEW.`inviter`));

INSERT INTO `{{prefix}}teams_actions` (`team`, `text`) VALUES (NEW.`team`, JSON_OBJECT("type", "invs", "user", NEW.`user`,  "team", NEW.`team`, "invited", NEW.`inviter`));

END IF;

END ;

CREATE TRIGGER `{{prefix}}team_members_update` AFTER UPDATE ON `{{prefix}}teams_members` FOR EACH ROW BEGIN

IF OLD.approved = 0 AND NEW.approved = 1 THEN

INSERT INTO `{{prefix}}teams_actions` (`team`, `text`) VALUES (NEW.`team`, JSON_OBJECT("type", "inva", "user", NEW.`user`));

INSERT INTO `{{prefix}}usr_surveys` (`user`, `survey`, `team`) SELECT NEW.`user`, `id`, NEW.`team` FROM `{{prefix}}surveys` WHERE `team` = NEW.`team`; 

END IF;

END ;

--

CREATE TRIGGER `{{prefix}}transactions_insert` AFTER INSERT ON `{{prefix}}transactions` FOR EACH ROW BEGIN

#### 1: DEPOSIT: USES CUSTOM
IF NEW.`type` = 1 THEN

## Add to balance when approved
IF NEW.`status` = 2 THEN
UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`amount` WHERE `id` =  NEW.`user`;
END IF;

#### 2,3: SURVEY BUDGET: USES A FUNCTION

#### 4: WITHDRAW
ELSEIF NEW.`type` = 4 THEN

UPDATE `{{prefix}}users` SET `balance` = `balance` - NEW.`amount` WHERE `id` =  NEW.`user`;

## Send notification
IF NEW.`status` = 1 THEN
INSERT INTO `{{prefix}}alerts` (`user`, `text`) VALUES (NEW.`user`, JSON_OBJECT("type", "swit", "amount", NEW.`amount`, "transaction", NEW.`id`));
END IF;

#### 5: SUBSCRIPTION

#### 6: COMMISSION EARNED
ELSEIF NEW.`type` = 6 THEN

## Add to balance when approved
IF NEW.`status` = 2 THEN
UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`amount` WHERE `id` =  NEW.`user`;
END IF;

#### 7: VOUCHER APPLIED
ELSEIF NEW.`type` = 7 THEN

## Add to balance when approved
IF NEW.`status` = 2 THEN
UPDATE `{{prefix}}users` SET `bonus` = `bonus` + NEW.`amount` WHERE `id` =  NEW.`user`;
END IF;

#### 8: WITHDRAW CANCELED, CANNOT BE ADDED, ONLY UPDATED

END IF;

END ;

CREATE TRIGGER `{{prefix}}transactions_update` AFTER UPDATE ON `{{prefix}}transactions` FOR EACH ROW BEGIN

#### ONLY IF IS THE SAME TYPE
IF NEW.`type` = OLD.`type` THEN

#### ONLY IF STATUS CHANGED IN APPROVED
IF OLD.`status` != NEW.`status` THEN

#### 1: DEPOSIT
IF NEW.`type` = 1 THEN

## Request approved
IF NEW.`status` = 2 THEN
UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`amount` WHERE `id` =  NEW.`user`;
END IF;

#### 2,3: SURVEY BUDGET: USES A FUNCTION

#### 4: WITHDRAW
ELSEIF NEW.`type` = 4 THEN

## Request canceled
IF NEW.`status` = 0 THEN
UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`amount` WHERE `id` =  NEW.`user`;
## Approve request
ELSEIF NEW.`status` = 2 THEN
INSERT INTO `{{prefix}}alerts` (`user`, `text`) VALUES (NEW.`user`, JSON_OBJECT("type", "awit", "amount", NEW.`amount`, "transaction", NEW.`id`));
END IF;

#### 5: SUBSCRIPTION

#### 6: COMMISSION EARNED
ELSEIF NEW.`type` = 6 THEN

## Approved return the amount to the survey
IF NEW.`status` = 0 THEN
UPDATE `{{prefix}}surveys` SET `balance_bonus` = `balance_bonus` + NEW.`amount` WHERE `id` =  NEW.`survey`;
ELSEIF NEW.`status` = 2 THEN
UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`amount` WHERE `id` =  NEW.`user`;
END IF;

#### 7: VOUCHER APPLIED

#### 8: WITHDRAW
ELSEIF NEW.`type` = 8 THEN

#### Request canceled
IF NEW.`status` = 0 THEN
UPDATE `{{prefix}}users` SET `balance` = `balance` + NEW.`amount` WHERE `id` =  NEW.`user`;
ELSEIF NEW.`status` = 2 THEN
INSERT INTO `{{prefix}}alerts` (`user`, `text`) VALUES (NEW.`user`, JSON_OBJECT("type", "awit", "amount", NEW.`amount`, "transaction", NEW.`id`));
END IF;

END IF;

END IF;

END IF;

END ;

--

CREATE TRIGGER `{{prefix}}usr_surveys_delete` AFTER DELETE ON `{{prefix}}usr_surveys` FOR EACH ROW DELETE FROM `{{prefix}}survey_dashboard` WHERE `survey` = OLD.`survey` AND `user` = OLD.`user` ;

--

CREATE FUNCTION `add_voucher`(`IN_user` int(11) UNSIGNED, `IN_code` VARCHAR(50) CHARSET utf8) RETURNS int(11) unsigned
    NO SQL
BEGIN

DECLARE vid INT DEFAULT NULL;
DECLARE uvid INT DEFAULT NULL;

SELECT `v`.`id`, `uv`.`id` INTO vid, uvid FROM `{{prefix}}user_vouchers` `uv` RIGHT JOIN (SELECT * FROM `{{prefix}}vouchers` WHERE MATCH(`code`) AGAINST (IN_code IN BOOLEAN MODE) AND (`user` IS NULL OR `user` = IN_user) AND (`expiration` IS NULL OR `expiration` >= NOW()) AND `status` = 1) `v` ON `uv`.`user` = IN_user AND `uv`.`voucher_id` = v.id;

IF vid IS NOT NULL AND uvid IS NULL THEN

INSERT INTO `{{prefix}}user_vouchers` (`user`, `voucher_id`) VALUES (IN_user, vid);

RETURN LAST_INSERT_ID();

END IF;

RETURN FALSE;

END ;

--

CREATE FUNCTION `apply_voucher`(`IN_user` INT(11) UNSIGNED, `IN_vid` INT(11) UNSIGNED, `IN_amount` DOUBLE(8,2) UNSIGNED) RETURNS tinyint(1) unsigned
    NO SQL
BEGIN

DECLARE type INT UNSIGNED DEFAULT NULL;
DECLARE amount DOUBLE UNSIGNED DEFAULT 0.00;

SELECT `v`.`a_type`, `v`.`amount` INTO type, amount FROM `{{prefix}}vouchers` `v` RIGHT JOIN (SELECT * FROM `{{prefix}}user_vouchers` WHERE `id` = IN_vid) `uv` ON `v`.`id` = `uv`.`voucher_id` WHERE `uv`.`user` = IN_user AND (`v`.`user` IS NULL OR (`v`.`user` = `uv`.`user`)) AND (`v`.`expiration` IS NULL OR `v`.`expiration` >= NOW()) AND (`v`.`limit` IS NULL OR `v`.`limit` > `uv`.`uses`) AND `v`.`status` = 1;

IF type = 0 THEN

INSERT INTO `{{prefix}}transactions` (`user`, `type`, `amount`, `details`, `status`) VALUES (IN_user, 7, amount, JSON_OBJECT( "vid", IN_vid ), 2);

UPDATE `user_vouchers` SET `uses` = `uses` + 1 WHERE `user` = IN_user AND `id` = IN_vid;

RETURN TRUE;

ELSEIF type = 1 THEN

INSERT INTO `{{prefix}}transactions` (`user`, `type`, `amount`, `details`, `status`) VALUES (IN_user, 7, (IN_amount / 100 * amount), JSON_OBJECT( "vid", IN_vid ), 2);

UPDATE `{{prefix}}user_vouchers` SET `uses` = `uses` + 1 WHERE `user` = IN_user AND `id` = IN_vid;

RETURN TRUE;

END IF;

RETURN FALSE;

END ;

--

CREATE PROCEDURE `generate_results`(IN `survey` int(11) UNSIGNED)
    NO SQL
SELECT JSON_OBJECT('id', q.id, 'name', q.title, 'parent', q.parent, 'type', q.type, 'results', GROUP_CONCAT(JSON_OBJECT('id', qa.id, 'name', qa.title, 'count', (SELECT COUNT(id) FROM `{{prefix}}answers` WHERE `question` = `q`.`id` AND `answer` = `qa`.`id`)))) AS `question` FROM `q_answers` `qa` LEFT JOIN `{{prefix}}questions` `q` ON (`q`.`id` = `qa`.`question`) WHERE `q`.`survey` = survey GROUP BY `q`.`id` ORDER BY `qa`.`position` ;

--

CREATE FUNCTION `insert_result`(`id` int(11) UNSIGNED, `uid` int(11) UNSIGNED, `results` TEXT, `autovalid` TINYINT(1) UNSIGNED) RETURNS tinyint(1) unsigned
    NO SQL
BEGIN

SELECT `id`, `user`, `survey`, `status`, `exp` INTO @id, @user, @survey, @status, @exp FROM `{{prefix}}results` WHERE `id` = id;

## Wrong survey
IF @id IS NULL OR NOT @user <=> uid THEN

## Wrong survey
RETURN 0;

ELSE

## Check if expired
IF NOW() > @exp THEN
	RETURN 1;
END IF;

## insert answers
INSERT INTO `{{prefix}}answers` (`question`, `answer`) SELECT `q`.`id`, `qa`.`id` FROM `{{prefix}}questions` `q` LEFT JOIN `q_answers` `qa` ON `qa`.question = `q`.`id` WHERE FIND_IN_SET(results, `qa`.`id`);

## update result
UPDATE `{{prefix}}results` SET `results` = results, `status` = IF(autovalid = 0, 1, 2) WHERE `id` = id;

RETURN 2;

END IF;

END ;

--

CREATE PROCEDURE `send_alert`(IN `usr` int(11) UNSIGNED, IN `title` VARCHAR(255) CHARSET utf8mb4, IN `text` TEXT CHARSET utf8mb4)
    NO SQL
INSERT INTO `{{prefix}}alerts` (`user`, `text`) VALUES (usr, JSON_OBJECT("type", 0, "title", title, "text", text)) ;

--

CREATE FUNCTION `set_survey_budget`(`IN_survey` int(11) UNSIGNED, `IN_user` int(11) UNSIGNED, `IN_amount` DOUBLE(10,2)) RETURNS tinyint(1) unsigned
    NO SQL
BEGIN

DECLARE ubl DOUBLE DEFAULT NULL;
DECLARE ubb DOUBLE DEFAULT NULL;
DECLARE sbg DOUBLE DEFAULT NULL;
DECLARE sbb DOUBLE DEFAULT NULL;

SELECT `balance`, `bonus` FROM `{{prefix}}users` WHERE `id` = IN_user INTO ubl, ubb;

SELECT `budget`, `budget_bonus` FROM `{{prefix}}surveys` WHERE `id` = IN_survey INTO sbg, sbb;

IF ubl IS NULL OR sbg IS NULL THEN
RETURN FALSE;
ELSE

## deduct
IF IN_amount < 0 THEN

IF ABS(IN_amount) > (sbg + sbb) THEN
RETURN FALSE;
ELSE

SET @b_amount	= ABS(IN_amount);
SET @c_amount	= 0;

IF @b_amount > sbb THEN
SET @b_amount	= sbb;
SET @c_amount 	= ABS(IN_amount) - @b_amount;
END IF;

UPDATE `{{prefix}}users` SET `balance` = (`balance` + @c_amount), `bonus` = (`bonus` + @b_amount) WHERE `id` = IN_user;

UPDATE `{{prefix}}surveys` SET `budget` = (`budget` - @c_amount), `budget_bonus` = (`budget_bonus` - @b_amount) WHERE `id` = IN_survey;

INSERT INTO `{{prefix}}transactions` (`survey`, `user`, `type`, `amount`, `status`) VALUES (IN_survey, IN_user, 3, IN_amount, 2);

END IF;

# add
ELSEIF IN_amount > 0 THEN

IF IN_amount > (ubl + ubb) THEN
RETURN FALSE;
ELSE

SET @c_amount	= IN_amount;
SET @b_amount	= 0;

IF (ubl - IN_amount) < 0 THEN
SET @c_amount 	= ubl;
SET @b_amount	= IN_amount - @c_amount;
END IF;

UPDATE `{{prefix}}users` SET `balance` = (`balance` - @c_amount), `bonus` = (`bonus` - @b_amount) WHERE `id` = IN_user;

UPDATE `{{prefix}}surveys` SET `budget` = (`budget` + @c_amount), `budget_bonus` = (`budget_bonus` + @b_amount) WHERE `id` = IN_survey;

INSERT INTO `{{prefix}}transactions` (`survey`, `user`, `type`, `amount`, `status`) VALUES (IN_survey, IN_user, 3, IN_amount, 2);
END IF;

END IF;

RETURN TRUE;

END IF;

END ;

--

CREATE FUNCTION `generate_slug`() RETURNS varchar(6) CHARSET utf8mb4
    NO SQL
return concat(substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', floor(rand()*36+1), 1),
substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', floor(rand()*36+1), 1),
substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', floor(rand()*36+1), 1),
substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', floor(rand()*36+1), 1),
substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', floor(rand()*36+1), 1),
substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', floor(rand()*36+1), 1));

--

CREATE PROCEDURE `reorder_questions`(IN `IN_survey` INT(100) UNSIGNED, IN `IN_step` INT(100) UNSIGNED, IN `IN_position` INT(5) UNSIGNED, IN `IN_exc_id` INT(100) UNSIGNED)
    NO SQL
BEGIN

SET @posnow = IN_position;

UPDATE `{{prefix}}questions` SET `position` = (@posnow := @posnow+1) WHERE `id` != IN_exc_id AND `survey` = IN_survey AND `step` = IN_step AND `position` >= IN_position AND `visible` = 2;

END ;