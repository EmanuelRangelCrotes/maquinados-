-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-06-2025 a las 18:51:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE `material` (
  `id_material` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `numero_proyecto` int(11) DEFAULT NULL,
  `fecha_pedido` date NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `material`
--

INSERT INTO `material` (`id_material`, `cantidad`, `descripcion`, `precio`, `numero_proyecto`, `fecha_pedido`, `id_usuario`) VALUES
(1, 45, 'Mouse para ratones', 999, 1, '2025-05-02', 3),
(2, 45, 'Mouse para ratones', 999, 1, '2025-05-02', 3),
(3, 60, 'Cables ethernet', 300, 2, '2025-05-02', 2),
(4, 50, 'Pantallas para laptos', 400, 3, '2025-05-02', 3),
(5, 45, 'Audifonos para pc', 300, 4, '2025-05-05', 3),
(6, 70, 'Discos duros ', 1399, 5, '2025-05-05', 3),
(7, 70, 'Discos duros para pc', 1399, 6, '2025-05-05', 3),
(8, 30, 'Teclados para computadoras', 400, 7, '2025-05-05', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedidos` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_productos` int(11) NOT NULL,
  `fecha_pedido` date NOT NULL DEFAULT current_timestamp(),
  `estatus` varchar(25) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedidos`, `id_usuario`, `id_productos`, `fecha_pedido`, `estatus`) VALUES
(1, 4, 1, '2025-06-11', 'pendiente'),
(2, 4, 1, '2025-06-11', 'pendiente'),
(3, 4, 1, '2025-06-11', 'pendiente'),
(4, 4, 2, '2025-06-11', 'pendiente'),
(5, 4, 3, '2025-06-11', 'pendiente'),
(6, 4, 3, '2025-06-11', 'pendiente'),
(7, 4, 1, '2025-06-11', 'pendiente'),
(8, 4, 2, '2025-06-11', 'pendiente'),
(9, 4, 2, '2025-06-11', 'pendiente'),
(10, 4, 4, '2025-06-11', 'pendiente'),
(11, 4, 3, '2025-06-11', 'pendiente'),
(12, 2, 3, '2025-06-11', 'pendiente'),
(13, 3, 4, '2025-06-11', 'pendiente'),
(14, 4, 1, '2025-06-11', 'pendiente'),
(15, 4, 5, '2025-06-11', 'pendiente'),
(16, 4, 1, '2025-06-11', 'pendiente'),
(17, 4, 1, '2025-06-11', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_productos` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `clase` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `unidad_medida` varchar(255) DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_productos`, `nombre`, `sku`, `clase`, `descripcion`, `unidad_medida`, `precio`, `id_usuario`) VALUES
(1, '1018 SOL 1/4 PLG X 3 PLG EF', 'mdmch6e7twc6', 'Perfl', 'Solera', 'PZ', 592.21, NULL),
(2, '1018 SOL 5/16 PLG X 1 PLG EF', '1266tdgdg', 'Perfil', 'Solera', 'PZ', 235.22, NULL),
(3, 'METALEX ULTRA DEEP BTE BLUE CHIP', 'mdmch6e7twc6', 'Pintura', 'Consumible', 'PZ', 1095.07, NULL),
(4, 'THINNER STD', '19udhd73', 'Consumible', 'Solvente', 'LT', 27.15, NULL),
(5, 'tornillos', 'eeee', 'material', 'tornillos de punta hueca', 'PZ', 130, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_usuario` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_usuario`, `name`, `email`, `password`) VALUES
(1, 'Marco Ivan Sanchez Anguiano', 'misael@12.com', '123456'),
(2, 'Marco', 'wsda@gmail.com', '$2y$10$3ymUPtbycyq8ecyK6HptreK6DIGE55OUwWaq/QCnwjHPhyAym8niq'),
(3, 'Marco Ivan Sanchez Anguiano', 'marco@gmail.com', '$2y$10$OuJJJoapIt/Ufma3YHbUAOQ9CjkYcIBus/Bfty0kC/6QXquepUUyu'),
(4, 'marco', 'chanchitofeliz@gmail.com', '$2y$10$/8i4GMHoKFU71KXwPGt..OCQbENxe9V2OgjfA4/X0hwtR57qk7MkC');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedidos`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_productos` (`id_productos`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_productos`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `material`
--
ALTER TABLE `material`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedidos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_productos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `material_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id_usuario`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id_usuario`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_productos`) REFERENCES `productos` (`id_productos`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
