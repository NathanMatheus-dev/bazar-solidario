# Bazar Solidário

Cadastro público de participantes e painel administrativo. HTML5, Bootstrap 5.3, CSS3 e JavaScript puro na interface. PHP e PDO para requisições, sessão e acesso ao banco local.

## Configuração local

1. Inicie o MySQL ou MariaDB e abra o phpMyAdmin. Na aba **Importar** da página inicial, selecione `banco.sql`. O arquivo cria o banco `bazar_solidario` e suas quatro tabelas.
2. No phpMyAdmin, crie o usuário `bazar` para conexões locais com uma senha própria. Conceda `SELECT`, `INSERT`, `UPDATE` e `DELETE` apenas no banco `bazar_solidario`.
3. Copie `config.example.php` para `config.local.php` e informe os dados do usuário. Para configurar pelo ambiente, use `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`.
4. Com PHP 8.2+ e `pdo_mysql` habilitado, execute da raiz do projeto:

   ```bash
   php -S 127.0.0.1:8080 router.php
   ```

5. Abra `http://127.0.0.1:8080/` e crie o administrador no primeiro acesso. Use entre 12 e 72 bytes para a senha.

Se usar o Apache do XAMPP, habilite `mod_rewrite` e permita `AllowOverride All` no diretório do projeto para aplicar as regras de acesso do `.htaccess`. O CSS do Bootstrap é distribuído com o projeto.

## Arquivos

- `banco.sql` contém o schema importável.
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

Execute o teste integrado somente em uma instância descartável com banco vazio. Ele cria um administrador e cadastra participantes.
