CREATE TABLE `ambientes` (
  `id_ambiente` int(11) NOT NULL AUTO_INCREMENT,
  `nome_ambiente` varchar(100) NOT NULL,
  `capacidade` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_ambiente`)
)

CREATE TABLE `bloqueios` (
  `id_bloqueio` int(11) NOT NULL AUTO_INCREMENT,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `motivo` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_bloqueio`),
  KEY `fk_bloqueio_usuario` (`id_usuario`),
  CONSTRAINT `fk_bloqueio_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
)

CREATE TABLE `funcionarios` (
  `id_funcionario` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_funcionario`)
)

CREATE TABLE `itens_vistoria` (
  `id_item_vistoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome_item` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_item_vistoria`),
  UNIQUE KEY `uq_nome_item` (`nome_item`)
)

CREATE TABLE `logs_acoes` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `acao` varchar(20) NOT NULL,
  `entidade` varchar(50) NOT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `detalhes` text DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `logs_acoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
)

CREATE TABLE `ocorrencias` (
  `id_ocorrencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_vistoria_resultado` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `id_item_vistoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `status` enum('aberta','resolvida') NOT NULL DEFAULT 'aberta',
  `data_hora_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ocorrencia`),
  KEY `fk_ocorrencia_vr` (`id_vistoria_resultado`),
  KEY `fk_ocorrencia_reserva` (`id_reserva`),
  KEY `fk_ocorrencia_item` (`id_item_vistoria`),
  KEY `fk_ocorrencia_usuario` (`id_usuario`),
  CONSTRAINT `fk_ocorrencia_item` FOREIGN KEY (`id_item_vistoria`) REFERENCES `itens_vistoria` (`id_item_vistoria`),
  CONSTRAINT `fk_ocorrencia_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`),
  CONSTRAINT `fk_ocorrencia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `fk_ocorrencia_vr` FOREIGN KEY (`id_vistoria_resultado`) REFERENCES `vistoria_resultados` (`id_vistoria_resultado`)
)

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nome_reserva` varchar(100) NOT NULL,
  `telefone_reserva` varchar(20) NOT NULL,
  `data_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `id_ambiente` int(11) NOT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `valor_cobrado` decimal(10,2) NOT NULL,
  `valor_pago` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_reserva`),
  KEY `id_ambiente` (`id_ambiente`),
  KEY `id_funcionario` (`id_funcionario`),
  KEY `fk_reserva_usuario` (`id_usuario`),
  CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_ambiente`) REFERENCES `ambientes` (`id_ambiente`),
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`)
)

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_permissao` enum('admin','reserveiro') NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `login` (`login`)
)

CREATE TABLE `vistoria_resultados` (
  `id_vistoria_resultado` int(11) NOT NULL AUTO_INCREMENT,
  `id_reserva` int(11) NOT NULL,
  `id_item_vistoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `conforme` tinyint(1) NOT NULL DEFAULT 1,
  `descricao_problema` text DEFAULT NULL,
  `data_hora_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_vistoria_resultado`),
  KEY `fk_vr_reserva` (`id_reserva`),
  KEY `fk_vr_item` (`id_item_vistoria`),
  KEY `fk_vr_usuario` (`id_usuario`),
  CONSTRAINT `fk_vr_item` FOREIGN KEY (`id_item_vistoria`) REFERENCES `itens_vistoria` (`id_item_vistoria`),
  CONSTRAINT `fk_vr_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`),
  CONSTRAINT `fk_vr_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
)