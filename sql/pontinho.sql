-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Máquina: 127.0.0.1
-- Data de Criação: 15-Mar-2015 às 15:18
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

--
-- Extraindo dados da tabela `jogo`
--

INSERT INTO `jogo` (`id`, `capacidade`, `fichas`, `usuario`, `status`, `timeout`) VALUES
(6, 2, 2, 1, 0, 10);

-- --------------------------------------------------------

--
-- Estrutura da tabela `jogo_participante`
--

CREATE TABLE IF NOT EXISTS `jogo_participante` (
  `jogo` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `resultado` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`jogo`,`usuario`),
  KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `jogo_participante`
--

INSERT INTO `jogo_participante` (`jogo`, `usuario`, `resultado`) VALUES
(6, 1, 0);

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
('273fbc8de9597eff4918f40153b0c451', 1, '2015-03-15 03:03:22'),
('e485dee31bd00adf29862a059fc2c8b6', 2, '2015-03-15 03:28:17');

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
(1, 'murilo', '1015745', 18),
(2, 'thalita', '12345', 22);

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
  ADD CONSTRAINT `jogo_participante_ibfk_1` FOREIGN KEY (`jogo`) REFERENCES `jogo` (`id`),
  ADD CONSTRAINT `jogo_participante_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
