# Bazar Solidário

Site responsivo em PHP 8.3 e MySQL 8.4, com cadastro público, administrador único e gráficos Chart.js 4.4.8. Sem framework ou dependências PHP externas.

## Executar com Docker (desenvolvimento)

1. Copie `.env.example` para `.env` e preencha duas senhas fortes distintas.
2. Execute `docker compose up -d --build` na raiz do projeto.
3. Abra a porta 8080 localmente. **O primeiro acesso deve ser feito pelo responsável**, antes de divulgar o site. Crie seu usuário e senha (mínimo 12 bytes) e faça login.
4. Use `docker compose logs web db` para diagnóstico e `docker compose down` para parar. Os cadastros permanecem no volume `dados_mysql`. Não use `down -v` se precisar preservá-los.

O SQL é importado automaticamente somente na criação de um volume vazio. Para instalação manual: importe `banco.sql` com uma conta autorizada, crie um usuário de aplicação com SELECT/INSERT/UPDATE/DELETE apenas neste banco e forneça `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` ao processo PHP. Valores padrão: 127.0.0.1, 3306, bazar_solidario, bazar e senha vazia. Não há leitura automática de `.env` fora do Docker Compose. Requer extensão `pdo_mysql`. Para desenvolvimento manual, execute `php -S 127.0.0.1:8080 router.php` na raiz.

## Funcionamento e segurança

- O primeiro acesso a `index.php` ou `login.php` grava `REMOTE_ADDR` de maneira atômica. Até concluir o administrador, esse IP é encaminhado à configuração; os demais usam o cadastro público. A URL de configuração não pode reservar um IP por conta própria. Após criar o administrador, a configuração fica inacessível para todos.
- IP identifica uma conexão de rede, não uma pessoa ou máquina: usuários em NAT podem compartilhar IP. Atrás de proxy, configure o servidor web para restaurar `REMOTE_ADDR` **apenas de proxies confiáveis**. O PHP ignora `X-Forwarded-For`. No Docker local todos os acessos podem aparecer com o IP do gateway. O primeiro IP não substitui autenticação. Acesse a instalação em rede restrita até criar o administrador.
- Hash `password_hash(PASSWORD_DEFAULT)` e verificação `password_verify`; nenhuma senha administrativa é salva em texto plano. Apenas um administrador é permitido pela chave primária fixa. Não existe página pública de troca de senha ou criação de outros administradores.
- Sessão com cookie HttpOnly/SameSite, regeneração após login, expiração administrativa por 30 minutos de inatividade e logout POST com CSRF. Em HTTPS, o cookie é Secure. Termine TLS corretamente no servidor web; não confie em cabeçalhos arbitrários para HTTPS.
- Consultas preparadas, validação no servidor, escape HTML, CSRF em todos os POSTs e bloqueio de login após cinco tentativas por IP em 15 minutos. Itens não são armazenados quando o participante declara que não doou. Gráficos consideram cadastros, não quantidade de objetos.
- Chart.js é carregado de `cdn.jsdelivr.net`; dados e resumos continuam legíveis sem o CDN. A tabela inclui todos os cadastros; grandes volumes exigirão paginação em evolução futura.

## Produção

O servidor embutido PHP é somente para desenvolvimento. Em produção, use Apache/Nginx + PHP-FPM com HTTPS, banco privado e backups. Bloqueie acesso HTTP a `.env`, `.git`, arquivos SQL, testes e arquivos de configuração; permita somente os arquivos PHP públicos e `assets/`. Use horário consistente no servidor PHP e MySQL. Restrinja quem pode ler o volume do banco. Defina política de retenção e acesso para os dados pessoais, incluindo de menores.

Se o primeiro IP foi reservado incorretamente, um operador com acesso direto ao banco pode executar `DELETE FROM configuracao WHERE id=1` **somente enquanto não existir administrador**, em manutenção e antes de reabrir o site. Isso reabre a disputa pelo primeiro IP; não é uma função pública.

## Arquivos

- `banco.sql`: estrutura e restrições do banco.
- `conexao.php`, `bootstrap.php`: conexão, sessão e proteções compartilhadas.
- `index.php`, `processa_cadastro.php`: formulário e gravação.
- `primeiro_acesso.php`, `login.php`, `logout.php`: administração e autenticação.
- `dashboard.php`, `assets/dashboard.js`: tabela, estatísticas e gráficos.
- `assets/style.css`: identidade visual branca e azul royal, responsiva.
- `tests/smoke.py`: teste integrado; use apenas em banco descartável, pois cria administrador e cadastros.

## Verificar

`docker compose exec -T web sh -c 'for f in /app/*.php; do php -l "$f" || exit 1; done'`

Com uma instalação descartável e banco vazio em execução: `python3 tests/smoke.py`. Não execute esse teste em produção.
