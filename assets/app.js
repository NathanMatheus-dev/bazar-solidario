'use strict';

const cadastro = document.querySelector('[data-cadastro]');
if (cadastro) {
    const doou = cadastro.querySelector('#doou-sim');
    const grupo = cadastro.querySelector('[data-itens]');
    const itens = grupo.querySelector('textarea');

    function atualizarItens() {
        grupo.hidden = !doou.checked;
        itens.disabled = !doou.checked;
        itens.required = doou.checked;
    }

    cadastro.addEventListener('change', atualizarItens);
    window.addEventListener('pageshow', atualizarItens);
    atualizarItens();
}

const primeiroAcesso = document.querySelector('[data-primeiro-acesso]');
if (primeiroAcesso) {
    const senha = primeiroAcesso.querySelector('#senha');
    const confirmacao = primeiroAcesso.querySelector('#confirmacao');
    const codificador = new TextEncoder();

    function validarSenhas() {
        const bytes = codificador.encode(senha.value).length;
        senha.setCustomValidity(senha.value && (bytes < 12 || bytes > 72)
            ? 'Use uma senha de 12 a 72 bytes.' : '');
        confirmacao.setCustomValidity(confirmacao.value && confirmacao.value !== senha.value
            ? 'As senhas devem coincidir.' : '');
    }

    primeiroAcesso.addEventListener('input', validarSenhas);
    primeiroAcesso.addEventListener('submit', (evento) => {
        validarSenhas();
        if (!primeiroAcesso.reportValidity()) evento.preventDefault();
    });
}

const dadosGraficos = document.querySelector('#dados-graficos');
if (dadosGraficos) {
    const { medias, doacoes } = JSON.parse(dadosGraficos.textContent);

    function adicionarBarra(item, valor, maximo) {
        const barra = document.createElement('progress');
        barra.className = 'd-block w-100';
        barra.max = maximo;
        barra.value = valor;
        barra.setAttribute('aria-hidden', 'true');
        item.append(barra);
    }

    document.querySelectorAll('#idades li').forEach((item, i) => {
        adicionarBarra(item, medias[i], 120);
    });

    const lista = document.querySelector('#doacoes');
    const rotulos = ['Doaram', 'Ainda não doaram'];
    const total = doacoes[0] + doacoes[1];
    if (total > 0) {
        doacoes.forEach((valor, i) => {
            const item = document.createElement('li');
            item.className = 'mb-2';
            item.textContent = rotulos[i] + ' · ' + valor;
            adicionarBarra(item, valor, total);
            lista.append(item);
        });
        lista.hidden = false;
    }
}
