-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 10, 2026 at 10:02 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `inventario`
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
(12, 'Aceite', 10, 3500, 'De motor Superplus, MechaMotors'),
(13, 'Ruedas', 6, 560000, 'De camión, genericas, Rodados Wang');

-- --------------------------------------------------------

--
-- Table structure for table `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `direccion` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `mensaje` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `mensajes_contacto`
--

INSERT INTO `mensajes_contacto` (`id`, `nombre`, `direccion`, `telefono`, `email`, `mensaje`, `fecha`) VALUES
(6, 'Jose Paz', 'San Martin 343', '3775 42-3789', 'josepaz43@gmail.com', 'Hola, quería consultar si les quedan pastillas de freno.', '2026-11-09 14:42:34'),
(7, 'Martín Gómez', 'Av. Eva Duarte 1240, Monte Caseros, Corrientes', '+54 9 3775 48-1234', 'martin.gomez91@gmail.com', 'Hola, quería consultarles si tienen disponibilidad la semana que viene para hacerle el cambio de aceite y filtros a un Renault Sandero 2020, y si me pueden pasar un presupuesto estimativo aproximado. Gracias!', '2026-11-10 06:54:23'),
(8, 'Valeria RIos', 'Calle Colón 450, Monte Caseros, Corrientes', '+54 9 3775 59-8821', 'valeria.rios.mc@hotmail.com', 'Hola, buenas tardes. Les escribo porque el aire acondicionado de mi auto no está enfriando casi nada y hace un zumbido raro cuando lo prendo. ¿Están tomando turnos para revisión esta semana? Saludos.', '2026-11-10 06:55:32'),
(9, 'Lucas Benítez', 'Gral. Paz 820, Monte Caseros, Corrientes', '+54 9 3775 62-4419', 'lucas.benitez@gmail.com', 'Hola, cómo están? Quería consultar si hacen trabajos de alineación y balanceo y si es con turno previo. Noto que el volante vibra un poco cuando voy en la ruta.', '2026-11-10 06:56:56'),
(10, 'Esteban Quito', 'San Martín 150, Monte Caseros, Corrientes', '+54 9 3775 41-9988', 'esteban.quito99@yahoo.com', 'Hola, disculpen la molestia, venden empanadas de carne cortada a cuchillo por docena?', '2026-11-10 06:58:40');

-- --------------------------------------------------------

--
-- Table structure for table `ordenes_trabajo`
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

-- --------------------------------------------------------

--
-- Table structure for table `orden_detalles_repuestos`
--

CREATE TABLE `orden_detalles_repuestos` (
  `id` int NOT NULL,
  `orden_id` int NOT NULL,
  `inventario_id` int NOT NULL,
  `cantidad_usada` int NOT NULL,
  `precio_unitario` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orden_detalles_trabajos`
--

CREATE TABLE `orden_detalles_trabajos` (
  `id` int NOT NULL,
  `orden_id` int NOT NULL,
  `descripcion_trabajo` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `precio_mano_obra` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabla_servicios`
--

CREATE TABLE `tabla_servicios` (
  `id` int NOT NULL,
  `servicio` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `precio` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `tabla_servicios`
--

INSERT INTO `tabla_servicios` (`id`, `servicio`, `precio`) VALUES
(1, 'Cambio de aceite y filtro', 46000),
(2, 'Revision general', 39000),
(3, 'Cambio de pastillas de freno', 80000),
(4, 'Alineacion y balanceo', 40000),
(5, 'Cambio de bateria', 25000),
(6, 'Cambio de bujias', 35000),
(7, 'Diagnostico computarizado', 30000),
(8, 'Servicios de embrague', 250000),
(9, 'Cambio de correa', 180000),
(10, 'Reparacion del sistema de refrigeracion', 90000);

-- --------------------------------------------------------

--
-- Table structure for table `turnos`
--

CREATE TABLE `turnos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `marca` varchar(25) COLLATE utf8mb4_spanish_ci NOT NULL,
  `modelo` varchar(25) COLLATE utf8mb4_spanish_ci NOT NULL,
  `patente` varchar(25) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `urgente` tinyint(1) NOT NULL DEFAULT '0',
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `turnos`
--

INSERT INTO `turnos` (`id`, `usuario_id`, `fecha`, `hora`, `marca`, `modelo`, `patente`, `descripcion`, `estado`, `urgente`, `telefono`) VALUES
(16, 13, '2026-11-18', '09:30:00', 'Chevrolet', 'Onix 1.4', 'AA 345 CD', 'Escucho un ruido como un chillido agudo cada vez que freno en los semáforos, y además siento que el auto vibra un poco cuando paso los 80 km/h en la ruta.', 'pendiente', 1, '+54 9 11 5543-8901'),
(17, 14, '2026-11-22', '14:00:00', 'Volkswagen', 'Gol Trend 1.6', 'AB 789 EF', 'A la mañana le cuesta arrancar, tengo que darle dos o tres veces a la llave hasta que prende, y la luz de la batería queda encendida unos segundos más en el tablero.', 'pendiente', 0, '+54 9 345 422-9834'),
(18, 15, '2026-11-25', '11:00:00', 'Ford', 'EcoSport 1.6', 'AD 123 GH', 'Cuando paso por lomos de burro o calles empedradas se escucha un golpeteo seco del lado del acompañante, como si algo estuviera flojo en la suspensión.', 'pendiente', 0, '+54 9 351 678-4321');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
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
(12, 'Admin', 'admin@gmail.com', 'admin', '$2y$10$sURvFnKyno2bPyzfgjg5CeIbgNXBsDKP6qNLjVrntin3.RYlFsuRC', 1),
(13, 'Jose Paz', 'josepaz42@gmail.com', 'josepaz', '$2y$10$eqbBNAa717.QCHYYIOUcJuF0K3Z541s9hjNoYa2wyM7EM73MGe1R6', 0),
(14, 'Marcos Garcia', 'garciamarcos997@gmail.com', 'marcos', '$2y$10$A7LTgczD7YQT/2.qFPOjAeva2pDF4Ds154c/lg5zjWLBNYoWDMxUq', 0),
(15, 'Griselda Torres', 'grisetorres@gmail.com', 'griselda', '$2y$10$gLIrcjuS.JdSK4KwhhMS2utfPdGRVwBVdMOl8M9i8E4.yQ70ZoeCa', 0);

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
-- Indexes for table `tabla_servicios`
--
ALTER TABLE `tabla_servicios`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orden_detalles_repuestos`
--
ALTER TABLE `orden_detalles_repuestos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orden_detalles_trabajos`
--
ALTER TABLE `orden_detalles_trabajos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tabla_servicios`
--
ALTER TABLE `tabla_servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
