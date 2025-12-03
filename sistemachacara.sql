-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03/12/2025 às 23:09
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistemachacara`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `ambientes`
--

CREATE TABLE `ambientes` (
  `id_ambiente` int(11) NOT NULL,
  `nome_ambiente` varchar(100) NOT NULL,
  `capacidade` int(11) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ambientes`
--

INSERT INTO `ambientes` (`id_ambiente`, `nome_ambiente`, `capacidade`, `descricao`, `observacoes`, `ativo`) VALUES
(7, 'Inferior', 150, 'Ambiente amplo com capacidade para grandes eventos, equipado com mesas, cadeiras e iluminação decorativa. Ideal para aniversários, casamentos e confraternizações.', 'Não é permitido uso de fogos de artifício no local.', 1),
(9, 'Superior', 30, 'Ambiente menor, com piscina e churrasqueira.', 'Som alto não é permitido.', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id_funcionario`, `nome_completo`, `data_nascimento`, `telefone`, `observacoes`, `ativo`) VALUES
(1, 'Suelen Ribeiro', '1993-03-12', '(44) 99886 - 5223', 'não pode aos domingos', 1),
(5, 'Augusto Magalhães', '2000-07-08', '(44) 98573 - 8625', '', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome_reserva` varchar(100) NOT NULL,
  `telefone_reserva` varchar(20) DEFAULT NULL,
  `data_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `id_ambiente` int(11) NOT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `pago` tinyint(1) DEFAULT 0,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_usuario`, `nome_reserva`, `telefone_reserva`, `data_reserva`, `hora_inicio`, `hora_fim`, `id_ambiente`, `id_funcionario`, `pago`, `observacoes`) VALUES
(15, 21, 'Manuel Ribeiro', '(44) 98127 - 3722', '2025-12-13', '08:00:00', '23:00:00', 7, NULL, 0, ''),
(16, 21, 'Isabela Pereira', '(11) 92927 - 6364', '2025-12-13', '06:00:00', '18:00:00', 9, 1, 0, ''),
(17, 21, 'Saul', '(41) 92731 - 6236', '2025-12-20', '08:30:00', '16:00:00', 7, 1, 1, ''),
(18, 21, 'Pedro Floresta', '(44) 99377 - 6723', '2025-12-25', '06:00:00', '23:59:00', 7, 5, 0, ''),
(19, 24, 'Renan Lodi', '(11) 93838 - 7626', '2025-12-26', '09:30:00', '20:00:00', 9, 5, 0, ''),
(20, 21, 'Marcos Silva', '(44) 99932 - 2265', '2025-12-31', '16:00:00', '23:59:00', 7, 1, 0, ''),
(21, 21, 'Marcos Silva', '(44) 99932 - 2265', '2026-01-01', '00:00:00', '16:00:00', 7, NULL, 0, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_permissao` enum('admin','reserveiro') NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome_completo`, `login`, `senha`, `tipo_permissao`, `data_nascimento`, `telefone`, `observacoes`, `ativo`) VALUES
(21, 'nickolas', 'nickolas', '$2y$10$TGlkpABQs77FzUpd0VGy/uwy5rub9zidJ9kbKHNJnIwDNIIbh4Df.', 'admin', '2006-07-06', '(44) 99862 - 0278', 'admin', 1),
(24, 'Jorge Guzman', 'jorge', '$2y$10$C/elsamb2.uVOZBsX3sDr.Rg8yP2GRymGI7TgRLGzPv5m0bexOmr6', 'reserveiro', '2001-12-12', '', '', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ambientes`
--
ALTER TABLE `ambientes`
  ADD PRIMARY KEY (`id_ambiente`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id_funcionario`);

--
-- Índices de tabela `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_ambiente` (`id_ambiente`),
  ADD KEY `id_funcionario` (`id_funcionario`),
  ADD KEY `fk_reserva_usuario` (`id_usuario`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ambientes`
--
ALTER TABLE `ambientes`
  MODIFY `id_ambiente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_ambiente`) REFERENCES `ambientes` (`id_ambiente`),
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
