-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 03, 2026 at 04:12 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `el_motor`
--
CREATE DATABASE IF NOT EXISTS `el_motor` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE `el_motor`;

-- --------------------------------------------------------

--
-- Table structure for table `inventario`
--
-- Creation: Sep 03, 2026 at 12:59 PM
--

CREATE TABLE `inventario` (
  `id` int NOT NULL,
  `producto` varchar(150) COLLATE utf8mb4_spanish_ci NOT NULL,
  `cantidad` int NOT NULL DEFAULT '0',
  `precio` int NOT NULL DEFAULT '0',
  `descripcion` text COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `inventario`
--

INSERT INTO `inventario` (`id`, `producto`, `cantidad`, `precio`, `descripcion`) VALUES
(12, 'Aceite', 5, 3500, 'De motor Superplus, MechaMotors'),
(13, 'Ruedas', 0, 560000, 'De camión, genericas, Rodados Wang');

-- --------------------------------------------------------

--
-- Table structure for table `mensajes_contacto`
--
-- Creation: Aug 27, 2026 at 02:50 PM
--

CREATE TABLE `mensajes_contacto` (
  `id` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `mensaje` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `mensajes_contacto`
--

INSERT INTO `mensajes_contacto` (`id`, `nombre`, `email`, `mensaje`, `fecha`) VALUES
(3, 'Marcos Durán', 'marcosduran0098@gmail.com', 'Hola quería saber si hacen sndlkaldas', '2026-09-03 09:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `ordenes_trabajo`
--
-- Creation: Sep 03, 2026 at 02:50 PM
--

CREATE TABLE `ordenes_trabajo` (
  `id` int NOT NULL,
  `fecha` date NOT NULL,
  `cliente` varchar(150) COLLATE utf8mb4_spanish_ci NOT NULL,
  `vehiculo_marca` varchar(150) COLLATE utf8mb4_spanish_ci NOT NULL,
  `vehiculo_modelo` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `vehiculo_patente` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `diagnostico` text COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` enum('pendiente','en_progreso','completado') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'pendiente',
  `creado_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `ordenes_trabajo`
--

INSERT INTO `ordenes_trabajo` (`id`, `fecha`, `cliente`, `vehiculo_marca`, `vehiculo_modelo`, `vehiculo_patente`, `diagnostico`, `estado`, `creado_el`) VALUES
(1, '2026-09-03', 'Benjamín Ignacio López', 'Ford', 'Cronos', '234jsdkajbd', '214424234', 'completado', '2026-09-03 14:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `orden_detalles_repuestos`
--
-- Creation: Sep 03, 2026 at 02:35 PM
--

CREATE TABLE `orden_detalles_repuestos` (
  `id` int NOT NULL,
  `orden_id` int NOT NULL,
  `inventario_id` int NOT NULL,
  `cantidad_usada` int NOT NULL,
  `precio_unitario` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `orden_detalles_repuestos`
--

INSERT INTO `orden_detalles_repuestos` (`id`, `orden_id`, `inventario_id`, `cantidad_usada`, `precio_unitario`) VALUES
(1, 1, 13, 2, 560000);

-- --------------------------------------------------------

--
-- Table structure for table `orden_detalles_trabajos`
--
-- Creation: Sep 03, 2026 at 02:25 PM
--

CREATE TABLE `orden_detalles_trabajos` (
  `id` int NOT NULL,
  `orden_id` int NOT NULL,
  `descripcion_trabajo` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `precio_mano_obra` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `orden_detalles_trabajos`
--

INSERT INTO `orden_detalles_trabajos` (`id`, `orden_id`, `descripcion_trabajo`, `precio_mano_obra`) VALUES
(1, 1, 'Hay que ver', 123123);

-- --------------------------------------------------------

--
-- Table structure for table `turnos`
--
-- Creation: Aug 27, 2026 at 03:13 PM
-- Last update: Sep 03, 2026 at 01:04 AM
--

CREATE TABLE `turnos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `urgente` tinyint(1) NOT NULL DEFAULT '0',
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `turnos`
--

INSERT INTO `turnos` (`id`, `usuario_id`, `fecha`, `hora`, `descripcion`, `estado`, `urgente`, `telefono`) VALUES
(9, 9, '2026-09-03', '17:30:00', 'Hola quiero que me arreglen el guiño', 'aceptado', 0, '123123123'),
(11, 9, '2026-09-03', '09:00:00', 'Cambio de pastillas de freno', 'aceptado', 1, '12312421'),
(13, 9, '2026-10-03', '18:00:00', 'falskñlñkasd', 'aceptado', 1, '377592034\'280'),
(14, 9, '2026-09-13', '17:30:00', 'sadgfas', 'aceptado', 0, '1234534534');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--
-- Creation: Aug 27, 2026 at 12:09 PM
-- Last update: Sep 02, 2026 at 06:53 PM
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre_completo` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `es_admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `email`, `usuario`, `contrasena`, `es_admin`) VALUES
(9, 'Benjamín Ignacio López', 'lopezbenjamin831@gmail.com', 'benjaminlopez', '$2y$10$17YiA.vB22itt67ZcNYjT.1iWeduPRfmVe8Zv8UHcRkLJbO1koJ6u', 1),
(10, 'Marcos Durán', 'marcosduran0098@gmail.com', 'marcosduran', '$2y$10$kdTUIzHn4ngAEWdrFn/jHeVt3r6vGott7kGWm0k87jQ0pj5v1SOKy', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orden_detalles_repuestos`
--
ALTER TABLE `orden_detalles_repuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`),
  ADD KEY `inventario_id` (`inventario_id`);

--
-- Indexes for table `orden_detalles_trabajos`
--
ALTER TABLE `orden_detalles_trabajos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`);

--
-- Indexes for table `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orden_detalles_repuestos`
--
ALTER TABLE `orden_detalles_repuestos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orden_detalles_trabajos`
--
ALTER TABLE `orden_detalles_trabajos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orden_detalles_repuestos`
--
ALTER TABLE `orden_detalles_repuestos`
  ADD CONSTRAINT `orden_detalles_repuestos_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_trabajo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orden_detalles_repuestos_ibfk_2` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `orden_detalles_trabajos`
--
ALTER TABLE `orden_detalles_trabajos`
  ADD CONSTRAINT `orden_detalles_trabajos_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_trabajo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
