# Bazar Solidário

Cadastro público de participantes e painel administrativo. HTML5, Bootstrap 5.3, CSS3 e JavaScript puro na interface. PHP e PDO para requisições, sessão e acesso ao banco local.

## Configuração local

1. Com PHP 8.2+ e a extensão `pdo_sqlite` habilitada, execute da raiz do projeto:

   ```bash
   php -S 127.0.0.1:8080 router.php
   ```

2. Abra `http://127.0.0.1:8080/` e crie o administrador no primeiro acesso. Use entre 12 e 72 bytes para a senha.

O PHP cria o arquivo `.runtime/bazar.sqlite` e as quatro tabelas no primeiro acesso. Cadastros e credenciais persistem após reiniciar o servidor. Para usar outro local, defina `BAZAR_DB_PATH` com o caminho absoluto de um arquivo SQLite fora da pasta pública e gravável pelo PHP.

Dados de instalações MySQL ou MariaDB existentes precisam de migração antes de aparecer neste banco.

Se usar o Apache do XAMPP, habilite `mod_rewrite` e permita `AllowOverride All` no diretório do projeto para aplicar as regras de acesso do `.htaccess`. O CSS do Bootstrap é distribuído com o projeto.

## Arquivos

- `banco.sql` contém o schema SQLite aplicado automaticamente.
- `views/` contém os templates HTML com Bootstrap.
- `assets/style.css` contém a identidade visual e `assets/app.js` controla os campos e gráficos.
- Os arquivos PHP da raiz processam cadastro, configuração inicial, login, painel e logout. A conta única é protegida por sessão, CSRF e limite de tentativas.

O primeiro acesso reserva a configuração para o IP observado pelo servidor. Inicie o site em rede restrita até criar o administrador. Para produção, use HTTPS e limite o acesso HTTP às rotas públicas e aos arquivos de `assets/`.

## Verificação

```bash
for arquivo in *.php; do php -l "$arquivo" || exit 1; done
node --check assets/app.js
python3 tests/smoke.py
```

Para o teste integrado, inicie o PHP com `BAZAR_DB_PATH` apontando para um arquivo novo em um diretório temporário. Ele cria um administrador e cadastra participantes.
