-- Esquema completo y datos de ejemplo para Villa Luro Store
-- Versión 2.0 - Admin por ID=1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Borrado de tablas existentes para una instalación limpia
--
DROP TABLE IF EXISTS `pedido_items`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `carrito`;
DROP TABLE IF EXISTS `perfumes`;
DROP TABLE IF EXISTS `marcas`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `suscripciones`;
DROP TABLE IF EXISTS `usuarios`;

--
-- Estructura de tabla para la tabla `usuarios`
-- Se elimina la columna `rol`. El admin es el usuario con ID=1.
--
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `marcas`
--
CREATE TABLE `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `perfumes`
--
CREATE TABLE `perfumes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `categoria` enum('masculino','femenino','unisex') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_url` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `marca_id` (`marca_id`),
  CONSTRAINT `perfumes_ibfk_1` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `carrito`
--
CREATE TABLE `carrito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_perfume` (`usuario_id`,`perfume_id`),
  KEY `perfume_id` (`perfume_id`),
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`perfume_id`) REFERENCES `perfumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `pedidos`
--
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `nombre` varchar(255) NOT NULL,
  `direccion` text NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `pedido_items`
--
CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `perfume_id` (`perfume_id`),
  CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `suscripciones`
--
CREATE TABLE `suscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `password_resets`
--
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Datos de ejemplo
--
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`) VALUES (1, 'Admin', 'admin@villalurostore.com', '$2y$10$9vF7qgY.xYJg8X.wZ.uL9.wT.rS.qP.oN.m.L.k.J.iH.g.F.eD.c'); -- pass: admin
INSERT INTO `marcas` (`id`, `nombre`) VALUES (1, 'Chanel'), (2, 'Dior'), (3, 'Tom Ford'), (4, 'Gucci'), (5, 'Creed'), (6, 'Jo Malone London');
INSERT INTO `perfumes` (`id`, `nombre`, `marca_id`, `categoria`, `descripcion`, `precio`, `imagen_url`, `stock`) VALUES (1, 'Bleu de Chanel', 1, 'masculino', 'Aroma fresco y elegante.', 120.00, 'public/img/img1.webp', 10), (2, 'J\'adore', 2, 'femenino', 'Fragancia floral y sofisticada.', 135.00, 'public/img/img2.webp', 8), (3, 'Black Orchid', 3, 'unisex', 'Intenso y misterioso.', 150.00, 'public/img/img1.webp', 5), (4, 'Gucci Bloom', 4, 'femenino', 'Floral y moderno.', 110.00, 'public/img/img2.webp', 7), (5, 'Aventus', 5, 'masculino', 'Una fragancia audaz y contemporánea.', 325.00, 'public/img/img1.webp', 4), (6, 'Wood Sage & Sea Salt', 6, 'unisex', 'Escape a la costa barrida por el viento.', 98.00, 'public/img/img2.webp', 15), (7, 'Sauvage', 2, 'masculino', 'Composición radicalmente fresca.', 105.00, 'public/img/img1.webp', 12), (8, 'Coco Mademoiselle', 1, 'femenino', 'La esencia de una mujer libre y audaz.', 145.00, 'public/img/img2.webp', 9);

COMMIT;