-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 25-11-2024 a las 09:26:06
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `khonos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `khns_attendance_event`
--

CREATE TABLE `llx_attendance_event` (
  `rowid` bigint(20) UNSIGNED NOT NULL,
  `date_time_event` datetime NOT NULL,
  `event_location_ref` varchar(1024) DEFAULT NULL,
  `event_type` int(11) DEFAULT 1,
  `note` varchar(1024) DEFAULT NULL,
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fk_userid` int(11) NOT NULL,
  `fk_user_modification` int(11) DEFAULT NULL,
  `fk_third_party` int(11) DEFAULT NULL,
  `fk_task` int(11) DEFAULT NULL,
  `fk_project` int(11) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `llx_attendance_event`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `rowid` (`rowid`),
  ADD KEY `fk_ts_ae_user_idm` (`fk_user_modification`),
  ADD KEY `fk_ts_ae_user_id` (`fk_userid`),
  ADD KEY `fk_ts_ae_project_id` (`fk_project`),
  ADD KEY `fk_ts_ae_task` (`fk_task`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `khns_attendance_event`
--
ALTER TABLE `llx_attendance_event`
  MODIFY `rowid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=909;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `khns_attendance_event`
--
ALTER TABLE `llx_attendance_event`
  ADD CONSTRAINT `fk_ts_ae_project_id` FOREIGN KEY (`fk_project`) REFERENCES `llx_projet` (`rowid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ts_ae_task` FOREIGN KEY (`fk_task`) REFERENCES `llx_projet_task` (`rowid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ts_ae_user_id` FOREIGN KEY (`fk_userid`) REFERENCES `llx_user` (`rowid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ts_ae_user_idm` FOREIGN KEY (`fk_user_modification`) REFERENCES `llx_user` (`rowid`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
