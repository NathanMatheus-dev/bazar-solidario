-- Schema do SQLite, aplicado automaticamente no primeiro acesso.
BEGIN IMMEDIATE;

CREATE TABLE IF NOT EXISTS configuracao (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    primeiro_ip TEXT NOT NULL,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS administradores (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    usuario TEXT NOT NULL COLLATE NOCASE UNIQUE,
    senha_hash TEXT NOT NULL,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doadores (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL,
    idade INTEGER NOT NULL CHECK (idade BETWEEN 1 AND 120),
    itens_doados TEXT NOT NULL,
    serie TEXT NOT NULL COLLATE NOCASE,
    doou INTEGER NOT NULL CHECK (doou IN (0, 1)),
    data_cadastro TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tentativas_login (
    ip TEXT PRIMARY KEY NOT NULL,
    tentativas INTEGER NOT NULL DEFAULT 0 CHECK (tentativas >= 0),
    janela_inicio INTEGER NOT NULL
);

PRAGMA user_version = 1;
COMMIT;
