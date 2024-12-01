CREATE DATABASE restaurante;

USE restaurante;

-- Tabela Usuario
CREATE TABLE Usuario (
    ID SERIAL PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Login VARCHAR(50) UNIQUE NOT NULL,
    Senha VARCHAR(255) NOT NULL,
    Ativo BOOLEAN NOT NULL DEFAULT TRUE-- TRUE = Ativo, FALSE = Inativo
);

-- Tabela Item
CREATE TABLE Item (
    ID SERIAL PRIMARY KEY,
    Descricao VARCHAR(255) NOT NULL,
    Valor NUMERIC(10, 2) NOT NULL,
    Tipo VARCHAR(20), --CHECK (Tipo IN ('Bebida', 'Prato')) NOT NULL,
    Ativo BOOLEAN NOT NULL DEFAULT TRUE-- TRUE = Ativo, FALSE = Inativo
);

-- Tabela Comanda
CREATE TABLE Comanda (
    ID SERIAL PRIMARY KEY,
    Valor NUMERIC(10, 2) NOT NULL,
    DataHoraAbertura TIMESTAMP NOT NULL DEFAULT NOW(),
    DataHoraFechamento TIMESTAMP,
    Descontos BOOLEAN NOT NULL, -- TRUE = Sim, FALSE = Não
    Status VARCHAR(20), -- CHECK (Status IN ('Aberta', 'Fechada')) NOT NULL,
    UsuarioID INT REFERENCES Usuario(ID)
);

-- Tabela Pedido
CREATE TABLE Pedido (
    ID SERIAL PRIMARY KEY,
    Quantidade INT NOT NULL CHECK (Quantidade > 0),
    DataHoraCadastro TIMESTAMP NOT NULL DEFAULT NOW(),
    DataHoraConfirmacao TIMESTAMP,
    DataHoraPronto TIMESTAMP,
    DataHoraEntregue TIMESTAMP,
    Status VARCHAR(20), -- CHECK (Status IN ('Cadastrado', 'Confirmado', 'Pronto', 'Entregue', 'Cancelado')) NOT NULL,
    Destino VARCHAR(20), -- CHECK (Destino IN ('Copa', 'Cozinha')) NOT NULL,
    ComandaID INT REFERENCES Comanda(ID) ON DELETE CASCADE
);

-- Tabela Pedido_Item (Relacionamento entre Pedido e Item)
CREATE TABLE Pedido_Item (
    PedidoID INT REFERENCES Pedido(ID) ON DELETE CASCADE,
    ItemID INT REFERENCES Item(ID),
    Quantidade INT NOT NULL CHECK (Quantidade > 0),
    PRIMARY KEY (PedidoID, ItemID)
);

-- Tabela Pagamento
CREATE TABLE Pagamento (
    ID SERIAL PRIMARY KEY,
    DataHora TIMESTAMP NOT NULL DEFAULT NOW(),
    FormaPagamento VARCHAR(20), -- CHECK (FormaPagamento IN ('Pix', 'Debito', 'Credito', 'Transferencia', 'Dinheiro')) NOT NULL,
    Valor NUMERIC(10, 2) NOT NULL,
    UsuarioRecebimentoID INT REFERENCES Usuario(ID),
    ComandaID INT REFERENCES Comanda(ID) ON DELETE CASCADE
);
