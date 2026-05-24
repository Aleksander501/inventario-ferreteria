-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 23-05-2026 a las 19:36:15
-- Versión del servidor: 10.3.39-MariaDB-0ubuntu0.20.04.2
-- Versión de PHP: 7.4.3-4ubuntu2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `1nventario_santalucia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(150) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `modulo` varchar(50) NOT NULL,
  `accion` enum('CREATE','UPDATE','DELETE','ENTRADA','SALIDA','REPORTE') NOT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `registro_id` bigint(20) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `campos_modificados` varchar(255) DEFAULT NULL,
  `valor_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valor_anterior`)),
  `valor_nuevo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valor_nuevo`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `usuario_id`, `nombre_usuario`, `fecha_hora`, `modulo`, `accion`, `tabla_afectada`, `registro_id`, `descripcion`, `campos_modificados`, `valor_anterior`, `valor_nuevo`, `ip_address`, `user_agent`) VALUES
(35, 1, 'Alejandro', '2025-10-21 00:38:00', 'Empresa', 'UPDATE', 'empresa', 1, 'Actualizó datos de la empresa', 'nombre_empresa', '{\"id_empresa\":1,\"nombre_empresa\":\"Ferretería Santa Lucía\",\"telefono\":\"2220-2220\",\"sitioWeb\":\"www.ferreteria-santa-lucia.com\",\"direccion\":\"Santiago Nonualco, Departamento de La Paz\",\"logo\":\"vistas\\/img\\/plantilla\\/logo410.png\",\"icono\":\"vistas\\/img\\/icono\\/icono.png\"}', '{\"id_empresa\":1,\"nombre_empresa\":\"Ferretería Santa Lucía - Disensa\",\"telefono\":\"2220-2220\",\"sitioWeb\":\"www.ferreteria-santa-lucia.com\",\"direccion\":\"Santiago Nonualco, Departamento de La Paz\",\"logo\":\"vistas\\/img\\/plantilla\\/logo410.png\",\"icono\":\"vistas\\/img\\/icono\\/icono.png\"}', NULL, NULL),
(36, NULL, 'Francisco Nuila', '2025-10-21 20:44:02', 'Entradas', 'ENTRADA', 'entradas_productos', 72, 'Entrada de 10 al producto INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W', 'stock', '{\"stock\":1}', '{\"stock\":11}', NULL, NULL),
(37, NULL, 'Francisco Nuila', '2025-10-21 20:46:50', 'Entradas', 'REPORTE', 'entradas_productos', NULL, 'Generó PDF de entradas de productos (total: 2) por Francisco Nuila', 'total_registros', '{\"total_registros\":2}', NULL, NULL, NULL),
(38, NULL, 'Francisco Nuila', '2025-10-22 12:50:55', 'Salidas', 'SALIDA', 'salidasp', 62, 'Salida de 7 del producto INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W (tipo: reposicion)', 'stock', '{\"stock\":11}', '{\"stock\":4}', NULL, NULL),
(39, NULL, 'Francisco Nuila', '2025-10-22 12:52:05', 'Salidas', 'SALIDA', 'salidasp', 63, 'Salida de 1 del producto Cemento Fortaleza (tipo: reposicion)', 'stock', '{\"stock\":1}', '{\"stock\":0}', NULL, NULL),
(40, NULL, 'Francisco Nuila', '2025-10-22 13:00:15', 'Reportes', 'REPORTE', 'productos', NULL, 'Generó reporte PDF de Productos (total: 11)', NULL, NULL, NULL, NULL, NULL),
(41, 1, 'Alejandro', '2025-10-22 19:03:11', 'Categorias', 'REPORTE', 'categorias', NULL, 'Generó PDF de categorías (total: 10) por Alejandro', 'total_registros', '{\"total_registros\":10}', NULL, NULL, NULL),
(42, 1, 'Alejandro', '2025-10-22 19:12:23', 'Categorias', 'CREATE', 'categorias', 44, 'Creó categoría Prueba categoria', 'id_categorias, nombre_categoria', NULL, '{\"id_categorias\":44,\"nombre_categoria\":\"Prueba categoria\"}', NULL, NULL),
(43, 1, 'Alejandro', '2025-10-22 19:12:38', 'Categorias', 'REPORTE', 'categorias', NULL, 'Generó PDF de categorías (total: 11) por Alejandro', 'total_registros', '{\"total_registros\":11}', NULL, NULL, NULL),
(44, NULL, 'Francisco Nuila', '2025-10-22 19:35:15', 'Salidas', 'SALIDA', 'salidasp', 64, 'Salida de 2 del producto INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W (tipo: venta)', 'stock', '{\"stock\":4}', '{\"stock\":2}', NULL, NULL),
(45, NULL, 'Francisco Nuila', '2025-10-22 19:35:28', 'Inventario', 'UPDATE', 'productos', 7, 'Actualizó producto INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba', 'nombre', '{\"upc_producto\":\"255200\",\"nombre\":\"INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W\",\"marca\":\"Aguila\",\"nombre_proveedor\":\"EMTOP Tools El Salvador\",\"tipo_proveedor\":\"Eléctricos e iluminación\",\"categoria\":\"Material Eléctrico\",\"descripcion_producto\":\"Interruptor y Toma Corriente 1006-W\",\"unidad_medida\":\"Pieza\",\"precio_compra\":1.25,\"precio_venta1\":3,\"precio_venta2\":2.75,\"precio_venta3\":2.25,\"stock\":2}', '{\"upc_producto\":\"255200\",\"nombre\":\"INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba\",\"marca\":\"Aguila\",\"nombre_proveedor\":\"EMTOP Tools El Salvador\",\"tipo_proveedor\":\"Eléctricos e iluminación\",\"categoria\":\"Material Eléctrico\",\"descripcion_producto\":\"Interruptor y Toma Corriente 1006-W\",\"unidad_medida\":\"Pieza\",\"precio_compra\":1.25,\"precio_venta1\":3,\"precio_venta2\":2.75,\"precio_venta3\":2.25,\"stock\":2}', NULL, NULL),
(46, 1, 'Alejandro', '2025-10-23 19:09:17', 'Empresa', 'UPDATE', 'empresa', 1, 'Actualizó datos de la empresa', 'nombre_empresa', '{\"id_empresa\":1,\"nombre_empresa\":\"Ferretería Santa Lucía - Disensa\",\"telefono\":\"2220-2220\",\"sitioWeb\":\"www.ferreteria-santa-lucia.com\",\"direccion\":\"Santiago Nonualco, Departamento de La Paz\",\"logo\":\"vistas\\/img\\/plantilla\\/logo410.png\",\"icono\":\"vistas\\/img\\/icono\\/icono.png\"}', '{\"id_empresa\":1,\"nombre_empresa\":\"Ferretería Santa Lucía\",\"telefono\":\"2220-2220\",\"sitioWeb\":\"www.ferreteria-santa-lucia.com\",\"direccion\":\"Santiago Nonualco, Departamento de La Paz\",\"logo\":\"vistas\\/img\\/plantilla\\/logo410.png\",\"icono\":\"vistas\\/img\\/icono\\/icono.png\"}', NULL, NULL),
(47, 1, 'Alejandro', '2025-10-23 19:09:21', 'Usuarios', 'REPORTE', 'usuarios', NULL, 'Generó PDF de usuarios (total: 5) por Alejandro', 'total_registros', '{\"total_registros\":5}', NULL, NULL, NULL),
(48, 1, 'Alejandro', '2025-10-23 19:09:27', 'Usuarios', 'DELETE', 'usuarios', 28, 'Eliminó usuario Adiel Enoc (Enoc)', 'id_usuario, nombre, usuario, correo, perfil', '{\"id_usuario\":28,\"nombre\":\"Adiel Enoc\",\"usuario\":\"Enoc\",\"correo\":\"adiel110489enoc@gmail.com\",\"perfil\":\"Administrador\"}', NULL, NULL, NULL),
(49, 1, 'Alejandro', '2025-10-26 10:47:44', 'Proveedores', 'REPORTE', 'proveedores', NULL, 'Generó PDF de proveedores (total: 10) por Alejandro', 'total_registros', '{\"total_registros\":10}', NULL, NULL, NULL),
(50, 1, 'Alejandro', '2025-10-27 18:04:20', 'Inventario', 'DELETE', 'productos', 38, 'Eliminó producto PruebaProducto', 'upc_producto, nombre, marca, nombre_proveedor, tipo_proveedor, categoria, descripcion_producto, unidad_medida, precio_compra, precio_venta1, precio_venta2, precio_venta3, stock', '{\"upc_producto\":\"00002\",\"nombre\":\"PruebaProducto\",\"marca\":\"Truper\",\"nombre_proveedor\":\"Truper\",\"tipo_proveedor\":\"Pinturas y recubrimientos\",\"categoria\":\"Decorativos y Acabados\",\"descripcion_producto\":\"DescripcionPruebaProducto\",\"unidad_medida\":\"Caja\",\"precio_compra\":1,\"precio_venta1\":10,\"precio_venta2\":11,\"precio_venta3\":11,\"stock\":1}', NULL, NULL, NULL),
(51, 1, 'Alejandro', '2025-10-27 18:08:46', 'Inventario', 'CREATE', 'productos', 39, 'Creó producto PruebaProducto', 'upc_producto, nombre, marca, nombre_proveedor, tipo_proveedor, categoria, descripcion_producto, unidad_medida, precio_compra, precio_venta1, precio_venta2, precio_venta3, stock', NULL, '{\"upc_producto\":\"1252\",\"nombre\":\"PruebaProducto\",\"marca\":\"Truper\",\"nombre_proveedor\":\"Truper\",\"tipo_proveedor\":\"Pinturas y recubrimientos\",\"categoria\":\"Artículos del Hogar\",\"descripcion_producto\":\"DescripcionPruebaProducto\",\"unidad_medida\":\"Unidad\",\"precio_compra\":10,\"precio_venta1\":10,\"precio_venta2\":10,\"precio_venta3\":10,\"stock\":1}', NULL, NULL),
(52, 1, 'Alejandro', '2025-10-27 18:09:45', 'Proveedores', 'CREATE', 'proveedores', 34, 'Creó proveedor prueba', 'id_proveedor, nombre_proveedor, tipo_proveedor, correo, telefono, direccion', NULL, '{\"id_proveedor\":34,\"nombre_proveedor\":\"prueba\",\"tipo_proveedor\":\"Materiales de construcción\",\"correo\":\"fn@gmail.com\",\"telefono\":\"+503 7864-5465\",\"direccion\":\"pruebapruebaprueba\"}', NULL, NULL),
(53, 1, 'Alejandro', '2025-10-27 18:21:48', 'Proveedores', 'REPORTE', 'proveedores', NULL, 'Generó PDF de proveedores (total: 11) por Alejandro', 'total_registros', '{\"total_registros\":11}', NULL, NULL, NULL),
(54, 1, 'Alejandro', '2025-10-27 18:26:59', 'Inventario', 'CREATE', 'productos', 40, 'Creó producto asdasd', 'upc_producto, nombre, marca, nombre_proveedor, tipo_proveedor, categoria, descripcion_producto, unidad_medida, precio_compra, precio_venta1, precio_venta2, precio_venta3, stock', NULL, '{\"upc_producto\":\"20000\",\"nombre\":\"asdasd\",\"marca\":\"asdasd\",\"nombre_proveedor\":\"Materiales Progreso El Salvador\",\"tipo_proveedor\":\"Pinturas y recubrimientos\",\"categoria\":\"Herramientas Electricas y Accesorios\",\"descripcion_producto\":\"asdasd\",\"unidad_medida\":\"Litro\",\"precio_compra\":10,\"precio_venta1\":10,\"precio_venta2\":10,\"precio_venta3\":10,\"stock\":10}', NULL, NULL),
(55, 1, 'Alejandro', '2025-10-27 18:27:17', 'Inventario', 'DELETE', 'productos', 40, 'Eliminó producto asdasd', 'upc_producto, nombre, marca, nombre_proveedor, tipo_proveedor, categoria, descripcion_producto, unidad_medida, precio_compra, precio_venta1, precio_venta2, precio_venta3, stock', '{\"upc_producto\":\"20000\",\"nombre\":\"asdasd\",\"marca\":\"asdasd\",\"nombre_proveedor\":\"Materiales Progreso El Salvador\",\"tipo_proveedor\":\"Pinturas y recubrimientos\",\"categoria\":\"Herramientas Electricas y Accesorios\",\"descripcion_producto\":\"asdasd\",\"unidad_medida\":\"Litro\",\"precio_compra\":10,\"precio_venta1\":10,\"precio_venta2\":10,\"precio_venta3\":10,\"stock\":10}', NULL, NULL, NULL),
(56, 1, 'Alejandro', '2025-10-27 18:27:41', 'Inventario', 'DELETE', 'productos', 39, 'Eliminó producto PruebaProducto', 'upc_producto, nombre, marca, nombre_proveedor, tipo_proveedor, categoria, descripcion_producto, unidad_medida, precio_compra, precio_venta1, precio_venta2, precio_venta3, stock', '{\"upc_producto\":\"1252\",\"nombre\":\"PruebaProducto\",\"marca\":\"Truper\",\"nombre_proveedor\":\"Truper\",\"tipo_proveedor\":\"Pinturas y recubrimientos\",\"categoria\":\"Artículos del Hogar\",\"descripcion_producto\":\"DescripcionPruebaProducto\",\"unidad_medida\":\"Unidad\",\"precio_compra\":10,\"precio_venta1\":10,\"precio_venta2\":10,\"precio_venta3\":10,\"stock\":1}', NULL, NULL, NULL),
(57, 1, 'Alejandro', '2025-10-27 18:43:13', 'Inventario', 'CREATE', 'productos', 41, 'Creó producto auxiliar', 'upc_producto, nombre, marca, nombre_proveedor, tipo_proveedor, categoria, descripcion_producto, unidad_medida, precio_compra, precio_venta1, precio_venta2, precio_venta3, stock', NULL, '{\"upc_producto\":\"1252\",\"nombre\":\"auxiliar\",\"marca\":\"Generico\",\"nombre_proveedor\":\"Ferretería Corinca S.A. de C.V.\",\"tipo_proveedor\":\"Cemento y agregados\",\"categoria\":\"Artículos del Hogar\",\"descripcion_producto\":\"DescripcionPruebaProducto\",\"unidad_medida\":\"Unidad\",\"precio_compra\":10,\"precio_venta1\":10,\"precio_venta2\":10,\"precio_venta3\":10,\"stock\":10}', NULL, NULL),
(58, 1, 'Alejandro', '2025-11-04 23:06:26', 'Usuarios', 'DELETE', 'usuarios', 30, 'Eliminó usuario Edwin Bautista (edwin alexander)', 'id_usuario, nombre, usuario, correo, perfil', '{\"id_usuario\":30,\"nombre\":\"Edwin Bautista\",\"usuario\":\"edwin alexander\",\"correo\":\"alexanderbautista501@gmail.com\",\"perfil\":\"Administrador\"}', NULL, NULL, NULL),
(59, 1, 'Alejandro', '2025-11-04 23:06:31', 'Usuarios', 'DELETE', 'usuarios', 26, 'Eliminó usuario Francisco Nuila Fermán (FNuilaF)', 'id_usuario, nombre, usuario, correo, perfil', '{\"id_usuario\":26,\"nombre\":\"Francisco Nuila Fermán\",\"usuario\":\"FNuilaF\",\"correo\":\"nuilaferman.dev@gmail.com\",\"perfil\":\"Administrador\"}', NULL, NULL, NULL),
(60, 1, 'Alejandro', '2025-11-04 23:06:35', 'Usuarios', 'DELETE', 'usuarios', 16, 'Eliminó usuario Francisco Nuila (Usuario Nuila)', 'id_usuario, nombre, usuario, correo, perfil', '{\"id_usuario\":16,\"nombre\":\"Francisco Nuila\",\"usuario\":\"Usuario Nuila\",\"correo\":\"grupo4.ppi.2025@gmail.com\",\"perfil\":\"Auxiliar\"}', NULL, NULL, NULL),
(61, 1, 'Alejandro', '2025-11-04 23:07:18', 'Usuarios', 'CREATE', 'usuarios', 31, 'Creó usuario Usuario Auxiliar (Auxiliar)', 'id_usuario, nombre, usuario, correo, perfil, password', NULL, '{\"id_usuario\":31,\"nombre\":\"Usuario Auxiliar\",\"usuario\":\"Auxiliar\",\"correo\":\"ejemplo@gmail.com\",\"perfil\":\"Auxiliar\",\"password\":\"[set]\"}', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categorias` int(11) NOT NULL,
  `nombre_categoria` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `nombre_categoria`, `fecha`) VALUES
(2, 'Herramientas Electricas y Accesorios', '2025-10-09 03:13:33'),
(4, 'Artículos del Hogar', '2025-09-17 22:42:06'),
(5, 'Ferretería y accesorios', '2025-10-14 03:32:03'),
(11, 'Equipos de Protección Personal', '2025-09-24 11:05:50'),
(14, 'Material Eléctrico', '2025-10-03 09:48:37'),
(17, 'Decorativos y Acabados', '2025-10-01 23:40:41'),
(18, 'Materiales de Construcción', '2025-10-08 02:56:54'),
(19, 'PVC y Accesorios Hidráulicos', '2025-10-14 03:32:22'),
(34, 'Herramientas Manuales', '2025-10-03 10:15:02'),
(37, 'xyzz8', '2025-10-21 04:15:31'),
(44, 'Prueba categoria', '2025-10-23 01:12:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `telefono` text NOT NULL,
  `sitioWeb` text NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `logo` text NOT NULL,
  `icono` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre_empresa`, `telefono`, `sitioWeb`, `direccion`, `logo`, `icono`, `fecha`) VALUES
(1, 'Ferretería Santa Lucía', '2220-2220', 'www.ferreteria-santa-lucia.com', 'Santiago Nonualco, Departamento de La Paz', 'vistas/img/plantilla/logo410.png', 'vistas/img/icono/icono.png', '2025-10-24 01:09:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas_productos`
--

CREATE TABLE `entradas_productos` (
  `id_entradap` int(11) NOT NULL,
  `nombre_proveedor` varchar(255) NOT NULL,
  `tipo_proveedor` varchar(255) NOT NULL,
  `nombreProducto` varchar(255) NOT NULL,
  `entradap` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `entradas_productos`
--

INSERT INTO `entradas_productos` (`id_entradap`, `nombre_proveedor`, `tipo_proveedor`, `nombreProducto`, `entradap`, `fecha`) VALUES
(71, 'EMTOP Tools El Salvador', 'Eléctricos e iluminación', 'INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba', 1, '2025-10-23 01:35:28'),
(72, 'EMTOP Tools El Salvador', 'Eléctricos e iluminación', 'INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba', 10, '2025-10-23 01:35:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacionesstock`
--

CREATE TABLE `notificacionesstock` (
  `id` int(11) NOT NULL,
  `idproducto` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `valorStock` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `notificacionesstock`
--

INSERT INTO `notificacionesstock` (`id`, `idproducto`, `stock`, `valorStock`, `fecha`) VALUES
(42, 7, 4, 0, '2025-10-22 18:51:07'),
(43, 13, 0, 0, '2025-10-22 18:52:23'),
(44, 7, 2, 0, '2025-10-26 15:19:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` char(16) NOT NULL,
  `verifier_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `requested_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `selector`, `verifier_hash`, `expires_at`, `used_at`, `requested_ip`) VALUES
(45, 32, '1ad2e3908f4f0e53', 'cea18d7e3c2f5a52752d2c2ff5b8eabed333106d9271f9293681d2fed02bfb17', '2026-05-15 22:59:42', '2026-05-15 22:32:22', '127.0.0.1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `upc_producto` varchar(12) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `nombre_proveedor` varchar(255) NOT NULL,
  `tipo_proveedor` varchar(255) NOT NULL,
  `categoria` varchar(255) NOT NULL,
  `descripcion_producto` varchar(255) NOT NULL,
  `unidad_medida` varchar(255) NOT NULL,
  `precio_compra` float NOT NULL,
  `precio_venta1` int(11) NOT NULL,
  `precio_venta2` float NOT NULL,
  `precio_venta3` float NOT NULL,
  `stock` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `upc_producto`, `nombre`, `marca`, `nombre_proveedor`, `tipo_proveedor`, `categoria`, `descripcion_producto`, `unidad_medida`, `precio_compra`, `precio_venta1`, `precio_venta2`, `precio_venta3`, `stock`, `fecha`) VALUES
(7, '255200', 'INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba', 'Aguila', 'EMTOP Tools El Salvador', 'Eléctricos e iluminación', 'Material Eléctrico', 'Interruptor y Toma Corriente 1006-W', 'Pieza', 1.25, 3, 2.75, 2.25, 2, '2025-10-23 01:35:28'),
(8, '78585', 'CHORRO PLASTICO DE 1/2 TRUPER', 'Truper', 'Distribuidora Aguila S.A. de C.V.', 'Pinturas y recubrimientos', 'PVC y Accesorios Hidráulicos', 'Grifo plástico', 'Unidad', 2, 4, 3.25, 3, 0, '2025-10-14 09:29:36'),
(9, '552288', 'LENTES DE SEGURIDAD GRISES TRUPER', 'Truper', 'Suministros Industriales Herrera', 'Cemento y agregados', 'PVC y Accesorios Hidráulicos', 'Lentes de seguridad', 'Unidad', 5, 8, 7.5, 7, 0, '2025-10-14 09:30:07'),
(10, '996623', 'PIOCHA 5LB CON MANGO TRUPER', 'Truper', 'Distribuidora Aguila S.A. de C.V.', 'Fontanería y tuberías', 'Herramientas Manuales', 'Piocha de acero con mango', 'Unidad', 10, 15, 14, 13.5, 1, '2025-10-08 21:13:40'),
(11, '45655', 'PULIDORA 4-1/2\'\' PROFESIONAL TRUPER ', 'Truper', 'Materiales Progreso El Salvador', 'Seguridad industrial y equipo de protección', 'Herramientas Electricas y Accesorios', 'Pulidora angular 4-1/2', 'Unidad', 20, 25, 24, 23.5, 1, '2025-10-09 03:13:33'),
(12, '78985', 'BROCHA EXPERT DE 3\'\'', 'Expert', 'Distribuidora ABRO El Salvador', 'Seguridad industrial y equipo de protección', 'Herramientas Manuales', 'Brocha de cerdas para pintar', 'Unidad', 5, 8, 7, 6.5, 0, '2025-10-16 01:16:02'),
(13, '12325', 'Cemento Fortaleza', 'Fortaleza', 'Ferretería Corinca S.A. de C.V.', 'Eléctricos e iluminación', 'Material Eléctrico', 'Cemento gris Fortaleza', 'Saco', 10, 15, 14, 13.5, 0, '2025-10-22 18:52:05'),
(14, '74547', 'BLOQUE COMERCIAL ENTERO 10X20X40', 'Generico', 'Reflex Deco', 'Pinturas y recubrimientos', 'Herramientas Manuales', 'Bloque comercial 15x20x40', 'Unidad', 2.25, 4, 3.5, 3, 16, '2025-10-21 03:33:09'),
(24, '15158', 'LAMPARA PLATILLO 30 WATTS GENERICO 30 W', 'Generico', 'Ferretería Corinca S.A. de C.V.', 'Vidrios y aluminios', 'Material Eléctrico', 'LAMPARA PLATILLO 30 WATTS GENERICO 30 W', 'Unidad', 15, 15, 15, 15, 1, '2025-10-08 21:15:04'),
(33, '12521', 'Cinta Métrica', 'Truper', 'Truper', 'Herramientas y tornillería', 'Herramientas Manuales', 'Cinta Métrica 5 Mtos color amarillo', 'Unidad', 10, 10, 1, 10, 4, '2025-10-14 09:27:37'),
(41, '1252', 'auxiliar', 'Generico', 'Ferretería Corinca S.A. de C.V.', 'Cemento y agregados', 'Artículos del Hogar', 'DescripcionPruebaProducto', 'Unidad', 10, 10, 10, 10, 10, '2025-10-28 00:43:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(255) NOT NULL,
  `tipo_proveedor` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(14) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_proveedor`, `tipo_proveedor`, `correo`, `telefono`, `direccion`, `fecha`) VALUES
(12, 'Ferretería Corinca S.A. de C.V.', 'Cemento y agregados', 'ventas@corinca.com.sv', '+503 2214-5600', 'Blvd. Constitución, San Salvador, El Salvador', '2025-10-13 23:07:43'),
(13, 'Materiales Progreso El Salvador', 'Pinturas y recubrimientos', 'clientes@cementoprogreso.com.sv', '+503 2235-7800', 'Km 9 Carretera Troncal del Norte, Apopa, El Salvador', '2025-10-13 23:07:54'),
(14, 'Truper', 'Herramientas y tornillería', 'ventas@distribuidoratruper.com.sv', '+503 2251-8900', 'Col. Médica, Calle Gabriela Mistral, San Salvador', '2025-10-21 04:22:20'),
(15, 'EMTOP Tools El Salvador2.0', 'Fontanería y tuberías', 'pedidos@emtop.com.sv', '+503 2285-4321', 'Alameda Juan Pablo II #215, San Salvador', '2025-10-13 23:08:14'),
(16, 'Amanco Wavin El Salvador', 'Eléctricos e iluminación', 'contacto@amancowavin.com.sv', '+503 2505-2000', 'Km 16 ½ Carretera a Santa Ana, San Juan Opico, La Libertad', '2025-10-13 23:08:21'),
(17, 'Volteck El Salvador', 'Seguridad industrial y equipo de protección', 'soporte@fosetvolteck.com.sv', '+503 2229-4505', '27 Av. Sur #412, San Salvador', '2025-10-13 23:08:31'),
(18, 'Distribuidora ABRO El Salvador', 'Maderas y derivados', 'info@abro.com.sv', '+503 2240-6784', '3a Calle Pte. y 9a Av. Nte., San Salvador', '2025-10-13 23:08:41'),
(19, 'Reflex Deco', 'Pinturas y recubrimientos', 'ventas@reflexdeco.com.sv', '+503 2216-7450', 'Km 6 ½ Carretera a Comalapa, San Marcos, San Salvador', '2025-10-13 23:08:54'),
(20, 'Distribuidora Aguila S.A. de C.V.', 'Fontanería y tuberías', 'ventas@aguila.com.sv', '+503 2250-9808', '10a Av. Norte, San Salvador', '2025-10-13 23:09:06'),
(26, 'Suministros Industriales Herrera', 'Herramientas y tornillería', 'contacto@siherra.com', '+503 2222-1111', 'Av. Central #45, San Salvador', '2025-10-13 23:09:13'),
(34, 'prueba', 'Materiales de construcción', 'fn@gmail.com', '+503 7864-5465', 'pruebapruebaprueba', '2025-10-28 00:09:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salidasp`
--

CREATE TABLE `salidasp` (
  `id_salidap` int(11) NOT NULL,
  `categoriap` varchar(255) NOT NULL,
  `nombreProducto` varchar(255) NOT NULL,
  `salidap` int(11) NOT NULL,
  `tipo_salida` varchar(20) NOT NULL,
  `descripcion_salida` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `salidasp`
--

INSERT INTO `salidasp` (`id_salidap`, `categoriap`, `nombreProducto`, `salidap`, `tipo_salida`, `descripcion_salida`, `fecha`) VALUES
(62, 'Material Eléctrico', 'INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba', 7, 'reposicion', '', '2025-10-23 01:35:28'),
(63, 'Material Eléctrico', 'Cemento Fortaleza', 1, 'reposicion', '', '2025-10-22 18:52:05'),
(64, 'Material Eléctrico', 'INTERRUPTOR Y TOMA CORRIENTE AGUILA 1006-W prueba', 2, 'venta', '', '2025-10-23 01:35:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `correo` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(255) NOT NULL,
  `perfil` varchar(50) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `usuario`, `correo`, `password`, `perfil`, `fecha`) VALUES
(1, 'Alejandro', 'admon1234', 'alefrman17@gmail.com', '$2y$10$twcOV5ytav/3ZrHrnPR4yeworCSHJiKMxGku.8m2Sp41V/heKEksG', 'Administrador', '2025-10-15 01:36:21'),
(31, 'Usuario Auxiliar', 'Auxiliar', 'ejemplo@gmail.com', '$2y$10$1HXohFOGkD0RhbaOmNiIz.8n2RIt/M.MfjPWHrBergzwkUgG6JfNS', 'Auxiliar', '2025-11-05 05:07:18'),
(32, 'Edwin Alexander', 'edwin', 'alexanderbautista501@gmail.com', '$2y$10$VzEtwqWr/oL2ZFQQY1xT9ODPVIE6QW6dil9yXVMpu0uuq0qap28Sa', 'Administrador', '2026-05-15 22:32:22');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_fecha` (`fecha_hora`),
  ADD KEY `idx_user` (`usuario_id`),
  ADD KEY `idx_modulo` (`modulo`),
  ADD KEY `idx_accion` (`accion`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categorias`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `entradas_productos`
--
ALTER TABLE `entradas_productos`
  ADD PRIMARY KEY (`id_entradap`);

--
-- Indices de la tabla `notificacionesstock`
--
ALTER TABLE `notificacionesstock`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_selector` (`selector`),
  ADD KEY `idx_user_expires` (`user_id`,`expires_at`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `salidasp`
--
ALTER TABLE `salidasp`
  ADD PRIMARY KEY (`id_salidap`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uniq_usuarios_correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `entradas_productos`
--
ALTER TABLE `entradas_productos`
  MODIFY `id_entradap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `notificacionesstock`
--
ALTER TABLE `notificacionesstock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `salidasp`
--
ALTER TABLE `salidasp`
  MODIFY `id_salidap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_resets_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
