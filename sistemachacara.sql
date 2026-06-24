CREATE TABLE `ambientes` (
  `id_ambiente` int(11) NOT NULL,
  `nome_ambiente` varchar(100) NOT NULL,
  `capacidade` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `bloqueios` (
  `id_bloqueio` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `itens_vistoria` (
  `id_item_vistoria` int(11) NOT NULL,
  `nome_item` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `logs_acoes` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `acao` varchar(20) NOT NULL,
  `entidade` varchar(50) NOT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `detalhes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ocorrencias` (
  `id_ocorrencia` int(11) NOT NULL,
  `id_vistoria_resultado` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `id_item_vistoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `status` enum('aberta','resolvida') NOT NULL DEFAULT 'aberta',
  `data_hora_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome_reserva` varchar(100) NOT NULL,
  `telefone_reserva` varchar(20) NOT NULL,
  `data_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `id_ambiente` int(11) NOT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `valor_cobrado` decimal(10,2) NOT NULL,
  `valor_pago` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_permissao` enum('admin','reserveiro') NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id_usuario`, `nome_completo`, `login`, `senha`, `tipo_permissao`, `data_nascimento`, `telefone`, `observacoes`, `ativo`) VALUES
(1, 'Nickolas', 'nickolas', '$2y$10$fzt0DD14Yu.03LIBXKhaQufyov.MjKur9sTjKSurLpgHepq8DXjvi', 'admin', '2000-01-01', '00000000000', NULL, 1);

CREATE TABLE `vistoria_resultados` (
  `id_vistoria_resultado` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `id_item_vistoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `conforme` tinyint(1) NOT NULL DEFAULT 1,
  `descricao_problema` varchar(255) DEFAULT NULL,
  `data_hora_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `ambientes`
  ADD PRIMARY KEY (`id_ambiente`);

ALTER TABLE `bloqueios`
  ADD PRIMARY KEY (`id_bloqueio`),
  ADD KEY `fk_bloqueio_usuario` (`id_usuario`);

ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id_funcionario`);

ALTER TABLE `itens_vistoria`
  ADD PRIMARY KEY (`id_item_vistoria`),
  ADD UNIQUE KEY `uq_nome_item` (`nome_item`);

ALTER TABLE `logs_acoes`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `ocorrencias`
  ADD PRIMARY KEY (`id_ocorrencia`),
  ADD KEY `fk_ocorrencia_vr` (`id_vistoria_resultado`),
  ADD KEY `fk_ocorrencia_reserva` (`id_reserva`),
  ADD KEY `fk_ocorrencia_item` (`id_item_vistoria`),
  ADD KEY `fk_ocorrencia_usuario` (`id_usuario`);

ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_ambiente` (`id_ambiente`),
  ADD KEY `id_funcionario` (`id_funcionario`),
  ADD KEY `fk_reserva_usuario` (`id_usuario`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `login` (`login`);

ALTER TABLE `vistoria_resultados`
  ADD PRIMARY KEY (`id_vistoria_resultado`),
  ADD KEY `fk_vr_reserva` (`id_reserva`),
  ADD KEY `fk_vr_item` (`id_item_vistoria`),
  ADD KEY `fk_vr_usuario` (`id_usuario`);


ALTER TABLE `ambientes`
  MODIFY `id_ambiente` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bloqueios`
  MODIFY `id_bloqueio` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `itens_vistoria`
  MODIFY `id_item_vistoria` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `logs_acoes`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `ocorrencias`
  MODIFY `id_ocorrencia` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `vistoria_resultados`
  MODIFY `id_vistoria_resultado` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `bloqueios`
  ADD CONSTRAINT `fk_bloqueio_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `logs_acoes`
  ADD CONSTRAINT `logs_acoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `ocorrencias`
  ADD CONSTRAINT `fk_ocorrencia_item` FOREIGN KEY (`id_item_vistoria`) REFERENCES `itens_vistoria` (`id_item_vistoria`),
  ADD CONSTRAINT `fk_ocorrencia_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`),
  ADD CONSTRAINT `fk_ocorrencia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_ocorrencia_vr` FOREIGN KEY (`id_vistoria_resultado`) REFERENCES `vistoria_resultados` (`id_vistoria_resultado`);

ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_ambiente`) REFERENCES `ambientes` (`id_ambiente`),
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`);

ALTER TABLE `vistoria_resultados`
  ADD CONSTRAINT `fk_vr_item` FOREIGN KEY (`id_item_vistoria`) REFERENCES `itens_vistoria` (`id_item_vistoria`),
  ADD CONSTRAINT `fk_vr_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`),
  ADD CONSTRAINT `fk_vr_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;