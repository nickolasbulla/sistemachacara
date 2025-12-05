-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/12/2025 às 23:27
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
(5, 'Augusto Magalhães', '2000-07-08', '(44) 98573 - 8625', '', 1),
(6, 'Ruan Rodanho', '1945-12-12', '(44) 99786 - 3235', 'teste', 1);

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
  `observacoes` text DEFAULT NULL,
  `valor_cobrado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_pago` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_usuario`, `nome_reserva`, `telefone_reserva`, `data_reserva`, `hora_inicio`, `hora_fim`, `id_ambiente`, `id_funcionario`, `observacoes`, `valor_cobrado`, `valor_pago`) VALUES
(15, 21, 'Pedro Filho', '(44) 98127 - 3722', '2025-12-13', '08:00:00', '23:00:00', 7, NULL, '', 0.00, 0.00),
(16, 21, 'Isabela Pereira', '(11) 92927 - 6364', '2025-12-13', '06:00:00', '18:00:00', 9, 1, '', 0.00, 0.00),
(17, 21, 'Saul Guimaraes', '(41) 92731 - 6236', '2025-12-20', '08:30:00', '16:00:00', 7, 1, 'média de 50 pesssoas\r\nnão vão usar a piscina', 1000.00, 0.00),
(18, 21, 'Pedro Floresta', '(44) 99377 - 6723', '2025-12-25', '06:00:00', '23:59:00', 7, 5, '', 0.00, 0.00),
(19, 24, 'Renan Lodi', '(11) 93838 - 7626', '2025-12-26', '09:30:00', '20:00:00', 9, 5, '', 0.00, 0.00),
(20, 21, 'Marcos Silva', '(44) 99932 - 2265', '2025-12-31', '16:00:00', '23:59:00', 7, 1, '', 100.00, 10.00),
(21, 21, 'Marcos Silva', '(44) 99932 - 2265', '2026-01-01', '00:00:00', '16:00:00', 7, NULL, '', 0.00, 0.00),
(22, 21, 'Juliano Ribeiro', '(44) 99826 - 7163', '2025-12-27', '06:00:00', '18:00:00', 9, 1, '', 0.00, 0.00),
(23, 21, 'Pedro Pascal', '(44) 98635 - 3252', '2025-12-28', '08:00:00', '23:00:00', 9, NULL, '', 0.00, 0.00),
(24, 21, 'Gimenes Gimaldo', '(44) 99364 - 6325', '2025-12-29', '08:30:00', '23:00:00', 7, 6, '', 0.00, 0.00),
(25, 21, 'Joao Pedro', '(44) 97926 - 3424', '2025-12-09', '12:00:00', '23:00:00', 7, 5, '', 0.00, 0.00),
(26, 21, 'Raul Castanha', '(44) 45345 - 3453', '2025-12-09', '13:00:00', '23:00:00', 9, NULL, '', 0.00, 0.00),
(27, 21, 'Carlos Miguel', '(43) 45345 - 3454', '2025-12-09', '08:00:00', '10:00:00', 7, NULL, '', 0.00, 0.00),
(28, 21, 'Reinaldo Mestre', '(44) 99783 - 2476', '2025-12-11', '12:00:00', '23:00:00', 9, 6, '', 1200.00, 800.00),
(29, 21, 'Rhuan Patriky', '(49) 92713 - 6122', '2026-01-14', '12:00:00', '23:00:00', 9, 6, '', 1200.00, 50.00),
(30, 21, 'Guilherme Rodrigues', '(43) 99239 - 2729', '2025-12-08', '12:00:00', '23:00:00', 9, 6, '', 1200.00, 560.53);

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
(24, 'Derick', 'derick', '$2y$10$T1X5zJ6IV3A7DzMUThXPk.bYro0ZpRy17O1yWFtta6B2RVSNMM1WW', 'reserveiro', '2001-12-12', '', '', 1),
(27, 'william pelissari', 'william', '$2y$10$fYB1XLc2hnWqxKNJYLM2iOZmai1U/22cAeqte1M1pIFHwFJnyP4v6', 'admin', '1935-12-12', '', '', 1);

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
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
