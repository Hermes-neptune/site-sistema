CREATE DATABASE tcc;
USE tcc;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nome_completo` VARCHAR(120) NOT NULL,
  `email` varchar(100) NOT NULL,
  `hash_rm_password` varchar(64) NOT NULL,
  `codigo_unico` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_unico` (`codigo_unico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `creditos` (
  `id_cred` int NOT NULL AUTO_INCREMENT,
  `quantidade` int NOT NULL,
  `username` int NOT NULL,
  PRIMARY KEY (`id_cred`),
  UNIQUE KEY `username` (`username`),
  CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `mensagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `mensagem` text NOT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `noticias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `detalhes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `noticias` (`id`, `data`, `assunto`, `detalhes`) VALUES
(1, '2024-12-07', 'Nova funcionalidade no sistema', 'O sistema recebeu uma nova funcionalidade para gerenciamento de usuários.'),
(2, '2024-12-06', 'Manutenção programada', 'A manutenção ocorrerá no dia 06/12 das 22h às 23h. Durante esse período, o sistema ficará indisponível.'),
(3, '2024-12-05', 'Atualização de segurança aplicada', 'Atualização realizada para corrigir vulnerabilidades críticas. Recomenda-se alterar as senhas.'),
(4, '2024-12-04', 'Evento especial para usuários VIP', 'Evento exclusivo para usuários premium com benefícios adicionais e suporte prioritário.');

CREATE TABLE `pendencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `status` enum('pendente','concluida') DEFAULT 'pendente',
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pendencias_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `presencas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `data` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`data`),
  CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

