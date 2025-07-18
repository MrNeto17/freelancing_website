DROP TABLE IF EXISTS SERVICE;
DROP TABLE IF EXISTS CATEGORY;
DROP TABLE IF EXISTS TRANSACTIONS;
DROP TABLE IF EXISTS MESSAGE;
DROP TABLE IF EXISTS USER;

/*******************************************************************************
   Create Tables
********************************************************************************/
CREATE TABLE USER (
    id INTEGER PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name NVARCHAR(60),
    phone NVARCHAR(24),
    role VARCHAR(20) NOT NULL CHECK (role IN ('client', 'freelancer', 'admin')), -- Define o papel do usuário
    description TEXT, -- Apenas para freelancers
    rating DECIMAL(3, 2) DEFAULT 0.00 CHECK (rating >= 0 AND rating <= 5), -- Apenas para freelancers
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE SERVICE(
    id INTEGER PRIMARY KEY,
    title VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00 CHECK (rating >= 0 AND rating <= 5),
    delivery_time INTEGER NOT NULL, -- Tempo de entrega em dias
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    freelancer_id INTEGER NOT NULL,
    category_name VARCHAR(16),
    FOREIGN KEY (freelancer_id) REFERENCES USER(id)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    FOREIGN KEY (category_name) REFERENCES CATEGORY(name)
        ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE TRANSACTIONS (
    id INTEGER PRIMARY KEY,
    subtotal DECIMAL(5, 2)
        CONSTRAINT SubtotalNotNull NOT NULL
        CONSTRAINT SubtotalNotNegative CHECK (subtotal >= 0),
    firstName VARCHAR(32)
        CONSTRAINT FirstNameNotNull NOT NULL,
    lastName  VARCHAR(32)
        CONSTRAINT LastNameNotNull NOT NULL,
    email     VARCHAR(64)
        CONSTRAINT EmailNotNull NOT NULL,
    client_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES USER(id)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    FOREIGN KEY (service_id) REFERENCES SERVICE(id)
        ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE CATEGORY
(
    name VARCHAR(16)
        CONSTRAINT NameNotNull NOT NULL,
    CONSTRAINT NamePK PRIMARY KEY (name)
);

CREATE TABLE MESSAGE(
    id INTEGER PRIMARY KEY,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES USER(id)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    FOREIGN KEY (receiver_id) REFERENCES USER(id)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT SenderNotReceiver CHECK (sender_id <> receiver_id) -- O remetente não pode ser o mesmo que o destinatário
);

CREATE TABLE IF NOT EXISTS REVIEW (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    rating INTEGER CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(service_id) REFERENCES SERVICE(id),
    FOREIGN KEY(user_id) REFERENCES USER(id)
);

/*******************************************************************************
   Create Indexes
********************************************************************************/

CREATE INDEX IFK_serviceId ON SERVICE (id);

CREATE INDEX IFK_UserId ON USER (id);

CREATE INDEX IFK_ServicesPrice ON SERVICE (price);

CREATE INDEX IFK_UserRole ON USER (role);

CREATE INDEX MessageSenderIndex ON MESSAGE (sender_id);
CREATE INDEX MessageReceiverIndex ON MESSAGE (receiver_id);

/*******************************************************************************
   Create Triggers
********************************************************************************/


CREATE TRIGGER ServiceOwnerIsFreelancerOrAdmin
    BEFORE INSERT
    ON SERVICE
    FOR EACH ROW
    WHEN (SELECT role
          FROM User
          WHERE id = New.freelancer_id) NOT IN ('freelancer', 'admin')
BEGIN
    SELECT RAISE(FAIL, 'The service publisher must be a freelancer or admin');
END;

/*******************************************************************************
   Insert Data
********************************************************************************/

-- Inserindo usuários (USER)
INSERT INTO USER (id, username, email, password, name, phone, role, description, rating) VALUES
(1, 'joaosilva', 'joao@email.com', 'senha123', 'João Silva', '912345678', 'client', NULL, NULL),
(2, 'mariafern', 'maria@email.com', 'senha456', 'Maria Fernandes', '934567890', 'client', NULL, NULL),
(3, 'carlos85', 'carlos@email.com', 'senha789', 'Carlos Santos', '956789012', 'client', NULL, NULL),
(4, 'AnaDesign', 'ana@email.com', 'senha123', 'Ana Design', '912345678', 'freelancer', 'Especialista em design gráfico.', 4.8),
(5, 'PedroDev', 'pedro@email.com', 'senha456', 'Pedro Dev', '934567890', 'freelancer', 'Desenvolvedor full-stack.', 4.6);

-- Inserindo categorias (CATEGORY)
INSERT INTO CATEGORY (name) VALUES
('Design Gráfico'),
('Desenvolvimento Web'),
('Marketing Digital'),
('Fotografia'),
('Consultoria'),
('Gerenciamento de Redes Sociais'),
('Criação de Conteúdo'),
('Tradução'),
('Edição de Vídeo'),
('Desenvolvimento de Aplicativos');


-- Inserindo serviços (SERVICE)
INSERT INTO SERVICE (id, title, description, price, rating, delivery_time, freelancer_id, category_name) VALUES
(1, 'Criação de Logotipo', 'Criação de logotipo personalizado para sua empresa.', 150.00, 4.5, 7, 4, 'Design Gráfico'),
(2, 'Desenvolvimento de Site', 'Desenvolvimento de site responsivo e otimizado.', 800.00, 4.7, 14, 5, 'Desenvolvimento Web'),
(3, 'Gerenciamento de Redes Sociais', 'Gerenciamento completo das suas redes sociais.', 300.00, 4.6, 30, 5, 'Gerenciamento de Redes Sociais'),
(4, 'Fotografia Profissional', 'Serviço de fotografia profissional para eventos.', 500.00, 4.9, 10, 4, 'Fotografia'),
(5, 'Consultoria em Marketing Digital', 'Consultoria especializada em marketing digital.', 200.00, 4.8, 21, 5, 'Consultoria'),
(6, 'Criação de Conteúdo', 'Criação de conteúdo otimizado para SEO.', 100.00, 4.5, 14, 4, 'Criação de Conteúdo'),
(7, 'Edição de Vídeo', 'Edição profissional de vídeos para redes sociais.', 250.00, 4.7, 10, 5, 'Edição de Vídeo'),
(8, 'Desenvolvimento de Aplicativos', 'Desenvolvimento de aplicativos móveis.', 1200.00, 4.8, 30, 5, 'Desenvolvimento de Aplicativos'),
(9, 'Tradução de Textos', 'Tradução profissional de textos e documentos.', 80.00, 4.6, 14, 4, 'Tradução'),
(10, 'Criação de Campanhas Publicitárias', 'Criação e gerenciamento de campanhas publicitárias.', 400.00, 4.9, 21, 5, 'Marketing Digital');


-- Inserindo transações (TRANSACTIONS)
INSERT INTO TRANSACTIONS (id, subtotal, firstName, lastName, email, client_id, service_id) VALUES
(1, 150.00, 'João', 'Silva', 'joao@email.com', 1, 1),
(2, 800.00, 'Maria', 'Fernandes', 'maria@email.com', 2, 2),
(3, 300.00, 'Carlos', 'Santos', 'carlos@email.com', 3, 3),
(4, 500.00, 'João', 'Silva', 'joao@email.com', 1, 4),
(5, 200.00, 'Maria', 'Fernandes', 'maria@email.com', 2, 5);



-- Inserindo mensagens (MESSAGE)
INSERT INTO MESSAGE (id, sender_id, receiver_id, content) VALUES
(1, 1, 4, 'Oi Ana, gostaria de saber mais sobre o serviço de criação de logotipo.'),
(2, 4, 1, 'Olá João! Claro, posso te ajudar com isso. Vamos conversar?'),
(3, 2, 5, 'Oi Pedro, estou interessada no serviço de desenvolvimento de site.'),
(4, 5, 2, 'Oi Maria! Vamos agendar uma reunião para discutir os detalhes?'),
(5, 3, 4, 'Carlos aqui! Estou interessado no gerenciamento de redes sociais.'),
(6, 4, 3, 'Oi Carlos! Vamos conversar sobre suas necessidades?'),
(7, 1, 5, 'Oi Ana, você pode me enviar mais detalhes sobre o serviço de fotografia?'),
(8, 4, 1, 'Claro João! Vou te enviar as informações por aqui.'),
(9, 2, 6, 'Oi Pedro, gostaria de saber mais sobre a consultoria em marketing digital.');

