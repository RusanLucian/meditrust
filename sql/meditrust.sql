-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: mart. 18, 2026 la 04:00 AM
-- Versiune server: 10.4.32-MariaDB
-- Versiune PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Bază de date: `meditrust`
--

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `patient_id`, `appointment_date`, `status`, `notes`, `created_at`) VALUES
(1, 3, 1, '2026-02-26 04:06:00', 'cancelled', '', '2026-02-24 11:04:07'),
(2, 3, 1, '2026-03-10 10:00:00', '', 'Consultation', '2026-02-24 14:30:53'),
(3, 4, 1, '2026-03-15 14:30:00', '', 'Check-up', '2026-02-24 14:30:53'),
(4, 5, 1, '2026-03-20 09:00:00', '', 'Dermatology', '2026-02-24 14:30:53'),
(5, 6, 1, '2026-03-25 11:00:00', '', 'Urology', '2026-02-24 14:30:53'),
(6, 7, 1, '2026-02-28 15:00:00', 'completed', 'Pediatrics', '2026-02-24 14:30:53'),
(7, 8, 1, '2026-03-05 13:00:00', '', 'Orthopedics', '2026-02-24 14:30:53'),
(8, 10, 1, '2026-03-12 10:30:00', '', 'Endocrinology', '2026-02-24 14:30:53'),
(9, 11, 1, '2026-03-18 16:00:00', '', 'Cardiology', '2026-02-24 14:30:53'),
(10, 12, 1, '2026-03-22 09:30:00', '', 'Gynecology', '2026-02-24 14:30:53'),
(11, 14, 1, '2026-03-30 14:00:00', '', 'Rheumatology', '2026-02-24 14:30:53'),
(12, 9, 45, '2026-12-03 14:15:00', 'scheduled', '', '2026-03-11 16:15:41'),
(13, 9, 45, '2026-12-03 14:15:00', 'scheduled', '', '2026-03-11 16:22:35');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `info_doctori`
--

CREATE TABLE `info_doctori` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialty_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `info_doctori`
--

INSERT INTO `info_doctori` (`id`, `user_id`, `specialty_id`, `bio`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 'Specialist în neurologie cu 15 ani de experiență. Tratez migrene, epilepsie și boli neurodegenerative.', 'avatars/doctor_3.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(2, 4, 2, 'Expert în oftalmologie și chirurgie oculară. Ofer tratamente pentru miopie, hipermetropie și cataractă.', 'avatars/doctor_4.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(3, 5, 5, 'Dermatolog cu certificări internaționale. Specialista în acnee, psoriazis și tratamente estetice.', 'avatars/doctor_5.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(4, 6, 7, 'Urolog cu experiență în tratamentele urologice și androgenetica. Sunt disponibil pentru consultații urgente.', 'avatars/doctor_6.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(5, 7, 8, 'Pediatru dedicată sănătății copiilor. Ofer consultații complete și program de vaccinare personalizat.', 'avatars/doctor_7.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(6, 8, 3, 'Ortoped specialist în traumatologie și chirurgie ortopedică. Tratez fracturile, luxații și boli articulare.', 'avatars/doctor_8.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(7, 9, 9, 'Psiholog clinician cu specialitate în psihoterapie. Ofer suport pentru anxietate, depresie și probleme relaționale.', 'avatars/doctor_9.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(8, 10, 10, 'Endocrinolog specialist în diabet și boli metabolice. Ghidez pacienții către o viață sănătoasă.', 'avatars/doctor_10.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(9, 11, 1, 'Specialist în cardiologie cu 12 ani de experiență. Tratez aritmii și insuficiență cardiaca.', 'avatars/doctor_11.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(10, 12, 11, 'Ginecolog cu certificări europene. Ofer consultații complete și planificare reproductivă.', 'avatars/doctor_12.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(11, 13, 12, 'Chirurg general cu specialitate în chirurgie laparoscopica. Expert în intervenții minim invazive.', 'avatars/doctor_13.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(12, 14, 13, 'Reumatolog specialist în boli autoimune și inflamatorii. Tratez artrita și lupusul.', 'avatars/doctor_14.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(13, 15, 14, 'Pneumolog cu experiență în astm și boli pulmonare cronice. Ofer tratamente personalizate.', 'avatars/doctor_15.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(14, 16, 15, 'ORL specialist în rinită alergică și probleme de auz. Ofer audiometrie și tratamente moderne.', 'avatars/doctor_16.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(15, 17, 16, 'Gastroenterolog specialist în endoscopie și tratarea problemelor digestive. Sunt disponibil pentru urgențe.', 'avatars/doctor_17.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(16, 18, 2, 'Oftalmolog cu expertiza în chirurgia refractivă (LASIK, PRK). Tratez miopia si astigmatismul.', 'avatars/doctor_18.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(17, 19, 17, 'Neurochirurg specialist în tumori cerebrale și patologie spinală. Expert în chirurgie minimalist.', 'avatars/doctor_19.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(18, 20, 18, 'Oncolog specialist în cancer de sân și colo-rectal. Ofer tratamente și suport psihologic.', 'avatars/doctor_20.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18'),
(19, 21, 1, 'Cardiolog cu experiență în boli de inimă și vasculare. Tratez pacienți cu afecțiuni cardiace complexe.', 'avatars/doctor_21.jpg', '2026-03-18 00:41:18', '2026-03-18 00:41:18');

-- --------------------------------------------------------

--
-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,

  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),

  `communication` int(1) NOT NULL DEFAULT 5,
  `professionalism` int(1) NOT NULL DEFAULT 5,
  `punctuality` int(1) NOT NULL DEFAULT 5,
  `empathy` int(1) NOT NULL DEFAULT 5,
  `recommendation` int(1) NOT NULL DEFAULT 5,

  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `reviews`
--

INSERT INTO `reviews`
(`id`, `doctor_id`, `patient_id`,
`rating`,
`communication`,
`professionalism`,
`punctuality`,
`empathy`,
`recommendation`,
`comment`,
`created_at`) VALUES

(21, 3, 1, 5, 5,5,5,5,5,
'Medic foarte profesionist! Ma simt mult mai bine. Recomand!',
'2026-02-24 13:37:32'),

(22, 4, 1, 5, 5,5,5,5,5,
'Oftalmolog de top! Foarte competent si amabil.',
'2026-02-24 13:37:32'),

(23, 5, 1, 5, 5,5,5,5,5,
'Dermatolog exceptional! Probleme rezolvate rapid.',
'2026-02-24 13:37:32'),

(24, 6, 1, 4, 4,4,4,4,4,
'Medic bun, consulta utila.',
'2026-02-24 13:37:32'),

(25, 7, 1, 5, 5,5,5,5,5,
'Pediatru grozav! Foarte atenta cu copiii.',
'2026-02-24 13:37:32'),

(26, 8, 1, 4, 4,4,4,4,4,
'Ortoped profesionist! Recomand.',
'2026-02-24 13:37:32'),

(27, 10, 1, 5, 5,5,5,5,5,
'Endocrinolog excelent! Foarte dedicat.',
'2026-02-24 13:37:32'),

(28, 11, 1, 5, 5,5,5,5,5,
'Cardiolog de TOP! Super profesionist.',
'2026-02-24 13:37:32'),

(29, 12, 1, 4, 4,4,4,4,4,
'Ginecolog bun! Consulta detaliata.',
'2026-02-24 13:37:32'),

(30, 14, 1, 5, 5,5,5,5,5,
'Reumatolog exceptional! Foarte empatic.',
'2026-02-24 13:37:32');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `specialties`
--

CREATE TABLE `specialties` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `specialties`
--

INSERT INTO `specialties` (`id`, `name`, `description`) VALUES
(1, 'Cardiologie', 'Specialist în boli de inimă și vasculare. Tratez aritmii și insuficiență cardiaca.'),
(2, 'Oftalmologie', 'Expert în oftalmologie și chirurgie oculară. Tratez miopie, hipermetropie și cataractă.'),
(3, 'Ortopedie', 'Specialist în traumatologie și chirurgie ortopedică. Tratez fracturile, luxații și boli articulare.'),
(4, 'Neurologie', 'Specialist în neurologie. Tratez migrene, epilepsie și boli neurodegenerative.'),
(5, 'Dermatologie', 'Dermatolog cu certificări internaționale. Specialista în acnee, psoriazis și tratamente estetice.'),
(6, 'Stomatologie', 'Specialist în boli și tratamente dentare. Ofer stomatologie generală și estetică.'),
(7, 'Urologie', 'Specialist în tratamentele urologice și androgenetica. Disponibil pentru consultații urgente.'),
(8, 'Pediatrie', 'Pediatru dedicat sănătății copiilor. Ofer consultații complete și program de vaccinare personalizat.'),
(9, 'Psihologie', 'Psiholog clinician cu specialitate în psihoterapie. Ofer suport pentru anxietate, depresie și probleme relaționale.'),
(10, 'Endocrinologie', 'Endocrinolog specialist în diabet și boli metabolice. Ghidez pacienții către o viață sănătoasă.'),
(11, 'Ginecologie', 'Ginecolog cu certificări europene. Ofer consultații complete și planificare reproductivă.'),
(12, 'Chirurgie', 'Chirurg general cu specialitate în chirurgie laparoscopica. Expert în intervenții minim invazive.'),
(13, 'Reumatologie', 'Specialist în boli autoimune și inflamatorii. Tratez artrita și lupusul.'),
(14, 'Pneumologie', 'Specialist în astm și boli pulmonare cronice. Ofer tratamente personalizate.'),
(15, 'ORL', 'Specialist în rinită alergică și probleme de auz. Ofer audiometrie și tratamente moderne.'),
(16, 'Gastroenterologie', 'Specialist în endoscopie și tratarea problemelor digestive. Disponibil pentru urgențe.'),
(17, 'Neurochirurgie', 'Specialist în tumori cerebrale și patologie spinală. Expert în chirurgie minimalist.'),
(18, 'Oncologie', 'Specialist în cancer de sân și colo-rectal. Ofer tratamente și suport psihologic.');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_type` enum('patient','doctor','admin') DEFAULT 'patient',
  `address` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `user_type`, `address`, `dob`, `created_at`) VALUES
(1, 'Pacient Demo', 'test@example.com', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0712345678', 'patient', NULL, NULL, '2026-02-14 17:17:45'),
(3, 'Dr. Maria Ionescu', 'maria.ionescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0723456789', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(4, 'Dr. Alexandru Petrescu', 'alexandru.petrescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0734567890', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(5, 'Dr. Elena Vasilescu', 'elena.vasilescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0745678901', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(6, 'Dr. Cristian Dumitru', 'cristian.dumitru@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0756789012', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(7, 'Dr. Mihaela Stancu', 'mihaela.stancu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0767890123', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(8, 'Dr. Daniel Gheorghe', 'daniel.gheorghe@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0778901234', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(9, 'Dr. Roxana Moldovan', 'roxana.moldovan@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0789012345', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(10, 'Dr. Andrei Stanciu', 'andrei.stanciu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0790123456', 'doctor', NULL, NULL, '2026-02-17 13:41:03'),
(11, 'Dr. Teodor Badescu', 'teodor.badescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0701234567', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(12, 'Dr. Floriana Oprea', 'floriana.oprea@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0712345678', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(13, 'Dr. Vlad Stoicu', 'vlad.stoicu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0723456789', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(14, 'Dr. Ioana Marinescu', 'ioana.marinescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0734567890', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(15, 'Dr. Gheorghe Toma', 'gheorghe.toma@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0745678901', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(16, 'Dr. Catalina Visalau', 'catalina.visalau@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0756789012', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(17, 'Dr. Stefan Marin', 'stefan.marin@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0767890123', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(18, 'Dr. Adriana Popescu', 'adriana.popescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0778901234', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(19, 'Dr. Nicola Dimitrescu', 'nicola.dimitrescu@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0789012345', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(20, 'Dr. Roxana Ionita', 'roxana.ionita@meditrust.ro', '$2y$10$CQNHLrQct9vpH8Riwoje4.yeF09XVm4PnG1TcCACNDgUB9lPsiPZ2', '0790123456', 'doctor', NULL, NULL, '2026-02-21 13:31:28'),
(21, 'Dr. Ion Popescu', 'ion.popescu@meditrust.ro', '$2y$10$jWBELdcKy9ZxjzsfUakAlOS833XPConGzl77DH/pKVfSWmuSA188q', '0721123456', 'doctor', NULL, NULL, '2026-02-21 16:10:16'),
(42, 'admin', 'admin@meditrust.com', '$2y$10$a3nH.ge3nn.CZA6A31n7puf..60PD7GxS9tpQf08uAw52LuOqaIGO', NULL, 'admin', NULL, NULL, '2026-03-11 15:32:29'),
(45, 'Ion Popescu', 'ion@example.com', '$2y$10$NvierKRW7mdlafaRrFznG.5J98.3PO/kchPhVnK56xXsHT2JKa8Mu', '+40 123 456 7890', 'patient', 'Str. Principală 123', '1990-01-01', '2026-03-11 16:02:46');

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexuri pentru tabele `info_doctori`
--
ALTER TABLE `info_doctori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `specialty_id` (`specialty_id`);

--
-- Indexuri pentru tabele `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexuri pentru tabele `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pentru tabele `info_doctori`
--
ALTER TABLE `info_doctori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pentru tabele `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pentru tabele `specialties`
--
ALTER TABLE `specialties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pentru tabele `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
