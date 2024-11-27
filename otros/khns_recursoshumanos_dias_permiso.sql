-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 26-11-2024 a las 16:42:39
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `khns_recursoshumanos_dias_permiso` (
  `rowid` int(11) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  `fk_user_creat` int(11) NOT NULL,
  `fk_user_modif` int(11) DEFAULT NULL,
  `last_main_doc` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL,
  `fk_user_solicitado` int(11) NOT NULL,
  `date_solic` datetime NOT NULL,
  `date_solic_fin` datetime NOT NULL DEFAULT current_timestamp(),
  `fk_user_validador` int(11) DEFAULT NULL,
  `motivos` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indices de la tabla `khns_recursoshumanos_dias_permiso`
--
ALTER TABLE `khns_recursoshumanos_dias_permiso`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `khns_recursoshumanos_dias_permiso`
--
ALTER TABLE `khns_recursoshumanos_dias_permiso`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
