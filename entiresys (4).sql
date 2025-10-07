-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 179.188.16.33
-- Generation Time: 06-Out-2025 às 21:35
-- Versão do servidor: 5.7.32-35-log
-- PHP Version: 5.6.40-0+deb8u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `entiresys`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `helpdesk_usuarios`
--

CREATE TABLE `helpdesk_usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `senha` varchar(255) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `helpdesk_usuarios`
--

INSERT INTO `helpdesk_usuarios` (`id`, `usuario`, `senha`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9'),
(2, 'wagner', 'e3cd764c5ebd63b824503d350ffe8d000a662995b9dd576ed23acb18ed41d95f'),
(4, 'wag', '6e75f2b67fc2de6283a35785ea665e47cc3702861075d04b4b7b8da5a41bdad8'),
(6, 'jayne', '8f3997327cd94a1b2a5e18b11e521393012f9b0cfc3b741fbee643e5b54ce0bd'),
(7, 'tertuliano', '68c98763739162de54ae003947ce57b57ae3312d1a18fc1380bd1648025e4f0c');

-- --------------------------------------------------------

--
-- Estrutura da tabela `scnp_nfs`
--

CREATE TABLE `scnp_nfs` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cnpj` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `nnf` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `data_rec` date DEFAULT NULL,
  `chave` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `dev` enum('SIM','NAO') COLLATE latin1_general_ci NOT NULL DEFAULT 'NAO',
  `datadev` date DEFAULT NULL,
  `nfdev` varchar(20) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `scnp_nfs`
--

INSERT INTO `scnp_nfs` (`id`, `cliente_id`, `cnpj`, `nnf`, `data_rec`, `chave`, `dev`, `datadev`, `nfdev`) VALUES
(12, 1, '59748988000114', '001741395', '2025-05-14', '35250559748988000114550010017413951816307063', 'NAO', '2025-07-17', ''),
(17, 1, '59748988000114', '001730032', '2024-12-28', '35241259748988000114550010017300321229338684', 'NAO', NULL, NULL),
(18, 1, '59748988000114', '001742616', '2025-05-26', '35250559748988000114550010017426161762252638', 'SIM', '2025-07-25', '189'),
(19, 1, '59748988000114', '001743046', '2025-05-29', '35250559748988000114550010017430461228064478', 'SIM', '2025-07-25', '190'),
(20, 1, '59748988000114', '001744140', '2025-06-11', '35250659748988000114550010017441401701245110', 'SIM', '2025-07-25', '191-P'),
(21, 4, '51780468000268', '000325872', '2025-07-14', '35250751780468000268550010003258721590538859', 'SIM', '2025-08-13', '192'),
(23, 1, '59748988000114', '001754139', '2025-09-25', '35250959748988000114550010017541391203943129', 'SIM', '2025-10-02', '195'),
(24, 1, '59748988000114', '001754140', '2025-09-25', '35250959748988000114550010017541401576081395', 'NAO', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `scnp_nfs_itens`
--

CREATE TABLE `scnp_nfs_itens` (
  `id` int(11) NOT NULL,
  `nf_id` int(11) NOT NULL,
  `descricao` text COLLATE latin1_general_ci,
  `qtd` int(11) DEFAULT NULL,
  `valor_unit` decimal(10,2) DEFAULT NULL,
  `cfop` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `ncm` varchar(20) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `scnp_nfs_itens`
--

INSERT INTO `scnp_nfs_itens` (`id`, `nf_id`, `descricao`, `qtd`, `valor_unit`, `cfop`, `ncm`) VALUES
(4, 12, '000000000610028682 CONTROLADORES PROGRAMAVEIS', 1, 8100.00, '5915', '85371020'),
(8, 17, '000000000610028663 OU MAQ E AP PARA SOLDAR METAIS POR ARCO', 2, 1200.00, '5915', '85153190'),
(9, 18, '000000000610028677 OUTROS APARELHOS', 2, 800.00, '5915', '85318000'),
(10, 18, '000000000610028526 OUTRA OBRA DE FERRO OU ACO', 2, 400.00, '5915', '73269090'),
(11, 19, '000000000610028746 MOVEIS DE MADEIRA UTILIZADOS ESCRITORIO', 2, 452.00, '5915', '94033000'),
(12, 20, '000000000610028526 OUTRA OBRA DE FERRO OU ACO', 1, 1000.00, '5915', '73269090'),
(13, 20, '000000000610028526 OUTRA OBRA DE FERRO OU ACO', 1, 150.00, '5915', '73269090'),
(14, 21, '610008748 0% OUTRAS OBRAS DE FERRO OU ACO', 1, 3000.00, '5915', '73269090'),
(15, 21, 'CILINDRO PNEUMATICO', 1, 1.00, '', ''),
(16, 20, 'Fechadura da Barreira ', 1, 1.00, '', ''),
(17, 20, 'CREMALHEIRA', 1, 1.00, '', ''),
(18, 23, '000000000610028471 OUTRAS OBRAS DE PLASTICO', 3, 220.00, '5915', '39269090'),
(19, 24, '000000000610028746 MOVEIS DE MADEIRA UTILIZADOS ESCRITORIO', 1, 1220.00, '5915', '94033000');

-- --------------------------------------------------------

--
-- Estrutura da tabela `scnp_ped`
--

CREATE TABLE `scnp_ped` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cnpj` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `numero` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `data` date DEFAULT NULL,
  `tipo` enum('SERVICO','VENDA') COLLATE latin1_general_ci NOT NULL,
  `descricao` text COLLATE latin1_general_ci,
  `valor` decimal(10,2) DEFAULT NULL,
  `faturado` enum('SIM','NAO') COLLATE latin1_general_ci DEFAULT 'NAO',
  `data_faturamento` date DEFAULT NULL,
  `prevpagto` date NOT NULL,
  `nnf` varchar(10) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `scnp_ped`
--

INSERT INTO `scnp_ped` (`id`, `cliente_id`, `cnpj`, `numero`, `data`, `tipo`, `descricao`, `valor`, `faturado`, `data_faturamento`, `prevpagto`, `nnf`) VALUES
(7, 4, '51780468000268', '553307190', '2025-07-14', 'SERVICO', 'INSTALAÃ‡AO DE CABIDES CF', 768.06, 'SIM', '2025-07-17', '2025-09-15', '278'),
(8, 4, '51780468000268', '553304998', '2025-07-01', 'SERVICO', 'ProteÃ§Ã£o para manutenÃ§Ã£o de plataformas', 4085.44, 'SIM', '2025-09-12', '2025-11-11', '287'),
(9, 2, '54516661008005', '553309982', '2025-07-29', 'SERVICO', 'MANUTENÃ‡ÃƒO CORRETIVA NA ESTEIRA BDM-6', 22434.51, 'SIM', '2025-08-05', '2025-10-04', '279'),
(11, 1, '59748988000114', '553306475', '2025-08-07', 'SERVICO', 'Laudo estrutural da rampa niveladora - OEA', 13236.84, 'SIM', '2025-08-18', '2025-10-17', '283'),
(12, 1, '59748988000114', '553306477', '2025-08-07', 'VENDA', 'ProteÃ§Ã£o do trilho da porta do Pharma', 3746.00, 'SIM', '2025-09-19', '2025-11-18', '194'),
(13, 1, '59748988000114', '553306481', '2025-08-07', 'SERVICO', 'ConfecÃ§Ã£o de 3 CalÃ§os em AÃ§o', 8285.28, 'NAO', NULL, '0000-00-00', ''),
(14, 1, '59748988000114', '553306661', '2025-08-08', 'SERVICO', 'ServiÃ§os FotogrÃ¡ficos', 2276.18, 'SIM', '2025-08-11', '2025-10-10', '280'),
(15, 2, '54516661008005', '553289495', '2025-04-08', 'SERVICO', 'MANUTENÃ‡ÃƒO ELÃ‰TRICA PREVENTIVA DE ESTEIRAS BDM', 2786.41, 'SIM', '2025-08-12', '2025-10-11', '277'),
(16, 1, '59748988000114', '553299401', '2025-06-06', 'SERVICO', 'ManutenÃ§Ã£o Well Lock Doca 18', 1690.92, 'SIM', '2025-08-12', '2025-08-13', '272'),
(17, 2, '54516661008005', '553309322', '2025-06-24', 'SERVICO', 'MANUTENÃ‡ÃƒO PREVENTIVA PARA 6 SISTEMAS DOCK SAFETY DA JJMT/GRU', 12665.34, 'SIM', '2025-08-19', '2025-10-18', '284'),
(18, 4, '51780468000268', '553311869', '2025-08-07', 'SERVICO', 'MANUTENÃ‡ÃƒO CORRETIVA NA DOCA 02', 4662.31, 'NAO', NULL, '0000-00-00', ''),
(20, 1, '59748988000114', '553307023', '2025-08-12', 'SERVICO', 'InspeÃ§Ã£o Eletrica Docas DPA Pharma', 1400.00, 'SIM', '2025-08-14', '2025-10-13', '282'),
(21, 1, '59748988000114', '553307212', '2025-08-13', 'SERVICO', 'InspeÃ§Ã£o Eletrica Docas DPA Consume', 11900.00, 'SIM', '2025-08-14', '2025-10-13', '281'),
(22, 2, '54516661008005', '553316012', '2025-08-28', 'SERVICO', 'INSTALAÃ‡ÃƒO DE PROTEÃ‡Ã•ES NR-12 NAS ESTEIRAS BDM', 28639.90, 'NAO', NULL, '0000-00-00', ''),
(23, 1, '59748988000114', '553309044', '2025-08-29', 'SERVICO', 'Laudo de estrutura robodock', 4814.99, 'SIM', '2025-09-05', '2025-11-04', '286'),
(24, 1, '59748988000114', '553309043', '2025-08-29', 'SERVICO', ' CAIXA PARA LACRE COM CONTROLE DE ACESSO', 4208.94, 'SIM', '2025-09-19', '2025-11-18', '288'),
(25, 1, '59748988000114', '553291072', '2025-03-26', 'SERVICO', 'SERVIÃ‡O DE TROCA DE CONECTORES ELÃ‰TRICOS EM PALETEIRAS', 3241.20, 'SIM', '2025-09-05', '2025-11-04', '285'),
(26, 1, '59748988000114', '553310175', '2025-09-09', 'SERVICO', 'Fornecimento e InstalaÃ§Ã£o de guard rail tubo aÃ§o carbono Ã˜ 4â€', 14987.74, 'NAO', NULL, '0000-00-00', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `helpdesk_usuarios`
--
ALTER TABLE `helpdesk_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indexes for table `scnp_nfs`
--
ALTER TABLE `scnp_nfs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indexes for table `scnp_nfs_itens`
--
ALTER TABLE `scnp_nfs_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nf_id` (`nf_id`);

--
-- Indexes for table `scnp_ped`
--
ALTER TABLE `scnp_ped`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `helpdesk_usuarios`
--
ALTER TABLE `helpdesk_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `scnp_nfs`
--
ALTER TABLE `scnp_nfs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `scnp_nfs_itens`
--
ALTER TABLE `scnp_nfs_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `scnp_ped`
--
ALTER TABLE `scnp_ped`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `scnp_nfs`
--
ALTER TABLE `scnp_nfs`
  ADD CONSTRAINT `scnp_nfs_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `helpdesk_clientes` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `scnp_nfs_itens`
--
ALTER TABLE `scnp_nfs_itens`
  ADD CONSTRAINT `scnp_nfs_itens_ibfk_1` FOREIGN KEY (`nf_id`) REFERENCES `scnp_nfs` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `scnp_ped`
--
ALTER TABLE `scnp_ped`
  ADD CONSTRAINT `scnp_ped_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `helpdesk_clientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
