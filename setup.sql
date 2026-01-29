SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agenda`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `banner`
--

CREATE TABLE `banner` (
  `id_banner` int(11) NOT NULL,
  `banner` varchar(600) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `banner`
--

INSERT INTO `banner` (`id_banner`, `banner`) VALUES
(1, 'EM CASO DE NECESSIDADE LIGUE PARA A TELEFONISTA');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lista`
--

CREATE TABLE `lista` (
  `id_lista` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `ramal` varchar(16) DEFAULT NULL,
  `email` varchar(90) DEFAULT NULL,
  `setor` varchar(100) DEFAULT NULL,
  `secretaria` int(11) DEFAULT NULL,
  `acessos` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_tentativas`
--

CREATE TABLE `login_tentativas` (
  `id` int(99) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `datahora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_alteracoes`
--

CREATE TABLE `log_alteracoes` (
  `id` int(11) NOT NULL,
  `acao` varchar(50) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `id_lista` int(11) NOT NULL,
  `ramal` varchar(16) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `datahora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_importacoes`
--

CREATE TABLE `log_importacoes` (
  `id_log` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `inseridos` int(11) NOT NULL,
  `ignorados` int(11) NOT NULL,
  `data_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `secretarias`
--

CREATE TABLE `secretarias` (
  `id_secretaria` int(11) NOT NULL,
  `secretaria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `stats_diario`
--

CREATE TABLE `stats_diario` (
  `data` date NOT NULL,
  `acessos` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `stats_diario`
--

INSERT INTO `stats_diario` (`data`, `acessos`) VALUES
('2026-01-29', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `admin` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `senha`, `admin`) VALUES
(1, 'admin', '$2y$10$OSzVz6E6vdRVzhZW3jzS7u9DIJgt/s9MxoW6pBILcGu7JatFcCZJm', 's'),
(12, 'usuario', '$2y$10$f0GIdoCQCz5p1HiihlH0jen2lPe3kwWc9BkCMdDMaQdx2PGVYqK7W', 'n');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id_banner`);

--
-- Índices de tabela `lista`
--
ALTER TABLE `lista`
  ADD PRIMARY KEY (`id_lista`),
  ADD KEY `fk_secretaria` (`secretaria`);

--
-- Índices de tabela `login_tentativas`
--
ALTER TABLE `login_tentativas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `log_alteracoes`
--
ALTER TABLE `log_alteracoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `log_importacoes`
--
ALTER TABLE `log_importacoes`
  ADD PRIMARY KEY (`id_log`);

--
-- Índices de tabela `secretarias`
--
ALTER TABLE `secretarias`
  ADD PRIMARY KEY (`id_secretaria`);

--
-- Índices de tabela `stats_diario`
--
ALTER TABLE `stats_diario`
  ADD PRIMARY KEY (`data`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `banner`
--
ALTER TABLE `banner`
  MODIFY `id_banner` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `lista`
--
ALTER TABLE `lista`
  MODIFY `id_lista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `login_tentativas`
--
ALTER TABLE `login_tentativas`
  MODIFY `id` int(99) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `log_alteracoes`
--
ALTER TABLE `log_alteracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `log_importacoes`
--
ALTER TABLE `log_importacoes`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `secretarias`
--
ALTER TABLE `secretarias`
  MODIFY `id_secretaria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `lista`
--
ALTER TABLE `lista`
  ADD CONSTRAINT `fk_secretaria` FOREIGN KEY (`secretaria`) REFERENCES `secretarias` (`id_secretaria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;