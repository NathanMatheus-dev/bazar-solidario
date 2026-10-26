"""Teste destrutivo: requer instalação descartável sem administrador."""
import http.cookiejar
import re
import secrets
import urllib.request
import urllib.parse
import urllib.error
import os

BASE = os.environ.get('TEST_BASE_URL', 'http://127.0.0.1:8080/')
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
def req(path, data=None):
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    try:
        with client.open(BASE+path, body) as r:
            return r.status, r.url, r.read().decode()
    except urllib.error.HTTPError as r:
        return r.code, r.url, r.read().decode()
def token(html):
    return re.search(r'name="csrf" value="([a-f0-9]+)"', html).group(1)
def check(ok, message):
    assert ok, message
    print('PASS:', message)

status, url, html = req('index.php')
check(status == 200 and url.endswith('primeiro_acesso.php'), 'primeiro IP encaminhado à configuração')
check(req('primeiro_acesso.php', {'csrf':'invalido'})[0] == 403, 'CSRF bloqueia criação administrativa')
password = secrets.token_urlsafe(24)
status, url, html = req('primeiro_acesso.php', {'csrf':token(html),'usuario':'admin_teste','senha':password,'confirmacao':password})
check(url.endswith('login.php') and 'Administrador criado' in html, 'criação de administrador único')
check(req('primeiro_acesso.php')[1].endswith('index.php'), 'configuração fechada após criação')
check(req('dashboard.php')[1].endswith('login.php'), 'dashboard exige sessão')
_, _, html = req('index.php')
csrf = token(html)
check(req('processa_cadastro.php', {'nome':'Pessoa'})[0] == 403, 'cadastro exige CSRF')
_, _, html = req('processa_cadastro.php', {'csrf':csrf,'nome':'<script>alert(1)</script>','idade':'-1','serie':'3°D','doou':'1','itens_doados':'Livros'})
check('Confira os campos' in html and '<script>alert(1)</script>' not in html, 'validação rejeita entrada inválida e escapa formulário')
for nome, idade, doou, itens in [('Ana Silva','16','1','Livros <script>alert(1)</script>'), ('João Souza','18','0','')]:
    _, _, html = req('processa_cadastro.php', {'csrf':csrf,'nome':nome,'idade':idade,'serie':'3°D','doou':doou,'itens_doados':itens})
    check('Participação registrada!' in html, 'cadastro válido: '+nome)
_, _, html = req('login.php')
_, url, html = req('login.php', {'csrf':token(html),'usuario':"' OR 1=1 --",'senha':'incorreta'})
check(url.endswith('login.php') and 'Usuário ou senha incorretos' in html, 'tentativa SQL injection não autentica')
old_session = next(c.value for c in jar if c.name == 'PHPSESSID')
_, url, html = req('login.php', {'csrf':token(html),'usuario':'admin_teste','senha':password})
check(url.endswith('dashboard.php') and 'Ana Silva' in html and 'João Souza' in html, 'login válido e listagem completa')
check(next(c.value for c in jar if c.name == 'PHPSESSID') != old_session, 'sessão regenerada no login')
check('&lt;script&gt;alert(1)&lt;/script&gt;' in html and '<script>alert(1)</script>' not in html, 'dashboard escapa descrição para prevenir XSS')
check('"medias":[17]' in html and '"doacoes":[1,1]' in html, 'gráficos com média e contagens corretas')
check(req('logout.php')[0] == 405, 'logout exige POST')
_, url, html = req('logout.php', {'csrf':token(html)})
check(url.endswith('login.php') and req('dashboard.php')[1].endswith('login.php'), 'logout invalida autenticação')
for n in range(5):
    _, _, html = req('login.php')
    _, _, html = req('login.php', {'csrf':token(html),'usuario':'admin_teste','senha':'incorreta'})
_, _, html = req('login.php', {'csrf':token(html),'usuario':'admin_teste','senha':password})
check('Muitas tentativas' in html, 'limite persistente de tentativas de login')
print('Fluxo integrado concluído. Banco contém dados de teste; não usar em produção.')
