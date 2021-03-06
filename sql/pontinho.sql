-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Máquina: 127.0.0.1
-- Data de Criação: 21-Mar-2015 às 20:06
-- Versão do servidor: 5.5.32
-- versão do PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de Dados: `pontinho`
--
CREATE DATABASE IF NOT EXISTS `pontinho` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `pontinho`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `jogando`
--

CREATE TABLE IF NOT EXISTS `jogando` (
  `jogo` int(11) NOT NULL,
  `usuario_vez` int(11) NOT NULL,
  `cartas_mesa` varchar(255) NOT NULL DEFAULT '',
  `fichas` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`jogo`),
  KEY `usuario_vez` (`usuario_vez`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `jogando_participante`
--

CREATE TABLE IF NOT EXISTS `jogando_participante` (
  `jogo` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `cartas` varchar(255) NOT NULL,
  `fichas` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`jogo`,`usuario`),
  KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `jogo`
--

CREATE TABLE IF NOT EXISTS `jogo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `capacidade` tinyint(4) NOT NULL DEFAULT '4',
  `fichas` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `timeout` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario` (`usuario`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `jogo_participante`
--

CREATE TABLE IF NOT EXISTS `jogo_participante` (
  `jogo` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `resultado` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`jogo`,`usuario`),
  KEY `jogo_participante_ibfk_2` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `token`
--

CREATE TABLE IF NOT EXISTS `token` (
  `id` varchar(32) NOT NULL,
  `usuario` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `token`
--

INSERT INTO `token` (`id`, `usuario`, `timestamp`) VALUES
('9fea16eaad4fdb8a6ca8e0cadbfb7711', 1, '2015-03-21 02:11:44'),
('b12e7e3275c32a4e056b100c39a5f07e', 2, '2015-03-21 00:55:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE IF NOT EXISTS `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `senha` varchar(20) NOT NULL,
  `fichas` int(11) NOT NULL DEFAULT '20',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id`, `username`, `senha`, `fichas`) VALUES
(1, 'murilo', '1015745', 20),
(2, 'thalita', '12345', 20);

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `jogando`
--
ALTER TABLE `jogando`
  ADD CONSTRAINT `jogando_ibfk_1` FOREIGN KEY (`jogo`) REFERENCES `jogo` (`id`),
  ADD CONSTRAINT `jogando_ibfk_2` FOREIGN KEY (`usuario_vez`) REFERENCES `usuario` (`id`);

--
-- Limitadores para a tabela `jogando_participante`
--
ALTER TABLE `jogando_participante`
  ADD CONSTRAINT `jogando_participante_ibfk_1` FOREIGN KEY (`jogo`) REFERENCES `jogo` (`id`),
  ADD CONSTRAINT `jogando_participante_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`);

--
-- Limitadores para a tabela `jogo`
--
ALTER TABLE `jogo`
  ADD CONSTRAINT `jogo_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`);

--
-- Limitadores para a tabela `jogo_participante`
--
ALTER TABLE `jogo_participante`
  ADD CONSTRAINT `jogo_participante_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jogo_participante_ibfk_1` FOREIGN KEY (`jogo`) REFERENCES `jogo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
