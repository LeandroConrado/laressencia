// Objeto para gerenciamento de frete
const Frete = {
    // Calcular frete a partir da página de checkout
    calcularCheckout: function() {
        const cepInput = document.getElementById('cep-frete');
        const opcoesContainer = document.getElementById('frete-opcoes');
        
        if (!cepInput || !opcoesContainer) {
            console.error('Elementos de frete não encontrados');
            return;
        }
        
        const cep = cepInput.value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            alert('Por favor, digite um CEP válido com 8 dígitos');
            return;
        }
        
        // Mostrar estado de carregamento
        opcoesContainer.innerHTML = '<p>Calculando frete...</p>';
        console.log('Calculando frete para o CEP:', cep);
        
        // Fazer requisição para o backend
        fetch(`${window.location.origin}/calcular-frete/${cep}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao calcular frete');
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta do cálculo de frete:', data);
                if (data.status === 'ok') {
                    this.mostrarOpcoesFrete(data.opcoes, opcoesContainer);
                } else {
                    opcoesContainer.innerHTML = `<p class="error-message">${data.mensagem || 'Erro ao calcular o frete.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Erro na requisição de frete:', error);
                opcoesContainer.innerHTML = '<p class="error-message">Ocorreu um erro ao calcular o frete. Por favor, tente novamente.</p>';
            });
    },
    
    // Mostrar opções de frete na interface
    mostrarOpcoesFrete: function(opcoes, container) {
        if (!opcoes || opcoes.length === 0) {
            container.innerHTML = '<p class="error-message">Não há opções de frete disponíveis para este CEP.</p>';
            return;
        }
        
        let html = '';
        
        opcoes.forEach(opcao => {
            const preco = parseFloat(opcao.preco).toFixed(2).replace('.', ',');
            const prazo = opcao.prazo > 1 ? `${opcao.prazo} dias úteis` : '1 dia útil';
            
            html += `
                <div class="frete-item" data-codigo="${opcao.codigo}" data-preco="${opcao.preco}" data-descricao="${opcao.nome}">
                    <div style="flex:1">
                        <strong>${opcao.nome}</strong>
                        <div>Prazo de entrega: ${prazo}</div>
                    </div>
                    <div>
                        <strong>R$ ${preco}</strong>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Adicionar eventos de clique
        const itens = container.querySelectorAll('.frete-item');
        itens.forEach(item => {
            item.addEventListener('click', () => {
                this.selecionarOpcaoFrete(item);
            });
        });
    },
    
    // Selecionar opção de frete
    selecionarOpcaoFrete: function(elemento) {
        // Remover seleção anterior
        const itens = document.querySelectorAll('.frete-item');
        itens.forEach(item => item.classList.remove('ativo'));
        
        // Adicionar seleção atual
        elemento.classList.add('ativo');
        
        // Obter dados do frete
        const codigo = elemento.getAttribute('data-codigo');
        const preco = elemento.getAttribute('data-preco');
        const descricao = elemento.getAttribute('data-descricao');
        
        console.log('Frete selecionado:', { codigo, preco, descricao });
        
        // Atualizar campo oculto
        document.getElementById('frete_selecionado').value = codigo;
        
        // Salvar frete na sessão
        this.salvarFrete(codigo, preco);
        
        // Atualizar resumo
        atualizarResumo(preco, descricao);
    },
    
    // Salvar frete selecionado na sessão
    salvarFrete: function(codigo, preco) {
        fetch(`${window.location.origin}/selecionar-frete?codigo=${encodeURIComponent(codigo)}&preco=${encodeURIComponent(preco)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Frete salvo na sessão:', data);
            })
            .catch(error => {
                console.error('Erro ao salvar frete:', error);
                alert('Erro ao salvar opção de frete. Por favor, tente novamente.');
            });
    }
};