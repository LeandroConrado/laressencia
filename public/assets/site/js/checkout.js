document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const dadosCadastro = document.getElementById('dados-cadastro');
    
    // Verificar cliente ao sair do campo de email
    if (emailInput) {
        emailInput.addEventListener('blur', verificarCliente);
    }
    
    // Máscara para o campo de CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3}).*/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{3}).*/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }
    
    // Máscara para o campo de telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{4}).*/, '($1) $2');
            }
            
            e.target.value = value;
        });
    }
    
    // Máscara para o campo de CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            
            if (value.length > 5) {
                value = value.replace(/^(\d{5})(\d{3}).*/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }

    // Máscara para o campo de CEP do frete
    const cepFreteInput = document.getElementById('cep-frete');
    if (cepFreteInput) {
        cepFreteInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            
            if (value.length > 5) {
                value = value.replace(/^(\d{5})(\d{3}).*/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }

    // Verificar validação do formulário antes de enviar
    const checkoutForm = document.querySelector('form[action*="finalizar-pedido"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            // Verificar se email foi preenchido
            if (!emailInput.value.trim()) {
                event.preventDefault();
                alert('Por favor, informe seu email');
                emailInput.focus();
                return false;
            }
            
            // Se os campos de cadastro estiverem visíveis, validar campos obrigatórios
            if (dadosCadastro.style.display !== 'none') {
                const camposObrigatorios = [
                    { id: 'nome', mensagem: 'nome completo' },
                    { id: 'cpf', mensagem: 'CPF' },
                    { id: 'telefone', mensagem: 'telefone' },
                    { id: 'cep', mensagem: 'CEP' },
                    { id: 'rua', mensagem: 'rua' },
                    { id: 'numero', mensagem: 'número' },
                    { id: 'bairro', mensagem: 'bairro' },
                    { id: 'cidade', mensagem: 'cidade' },
                    { id: 'estado', mensagem: 'estado' }
                ];
                
                for (let campo of camposObrigatorios) {
                    const input = document.getElementById(campo.id);
                    if (!input.value.trim()) {
                        event.preventDefault();
                        alert(`Por favor, informe seu ${campo.mensagem}`);
                        input.focus();
                        return false;
                    }
                }
            }
            
            // Verificar se o frete foi selecionado
            const freteSelecionado = document.getElementById('frete_selecionado');
            if (!freteSelecionado.value.trim()) {
                event.preventDefault();
                alert('Por favor, calcule e selecione uma opção de frete');
                document.getElementById('cep-frete').focus();
                return false;
            }
            
            console.log('Formulário validado com sucesso, enviando...');
            return true;
        });
    }
});

// Função para verificar cliente por email
function verificarCliente() {
    const email = document.getElementById('email').value;
    const dadosCadastro = document.getElementById('dados-cadastro');
    
    if (!email || !validateEmail(email)) {
        console.log('Email inválido');
        return;
    }
    
    console.log('Verificando cliente para o email:', email);
    
    fetch(`${BASE_URL}/validar-cliente?email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da validação:', data);
            if (data.existe) {
                // Cliente existe, ocultar campos de cadastro
                dadosCadastro.style.display = 'none';
            } else {
                // Cliente não existe, mostrar campos de cadastro
                dadosCadastro.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro ao verificar cliente:', error);
            // Em caso de erro, mostrar campos de cadastro por segurança
            dadosCadastro.style.display = 'block';
        });
}

// Função para buscar endereço pelo CEP
function buscarEndereco() {
    const cepInput = document.getElementById('cep');
    const cep = cepInput.value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        alert('Por favor, digite um CEP válido com 8 dígitos');
        return;
    }
    
    console.log('Buscando endereço para o CEP:', cep);
    
    // Limpar formulário
    document.getElementById('rua').value = 'Carregando...';
    document.getElementById('bairro').value = 'Carregando...';
    document.getElementById('cidade').value = 'Carregando...';
    document.getElementById('estado').value = 'Carregando...';
    
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            console.log('Dados do CEP:', data);
            if (data.erro) {
                alert('CEP não encontrado');
                document.getElementById('rua').value = '';
                document.getElementById('bairro').value = '';
                document.getElementById('cidade').value = '';
                document.getElementById('estado').value = '';
                return;
            }
            
            document.getElementById('rua').value = data.logradouro;
            document.getElementById('bairro').value = data.bairro;
            document.getElementById('cidade').value = data.localidade;
            document.getElementById('estado').value = data.uf;
            
            // Foco no campo número
            document.getElementById('numero').focus();
        })
        .catch(error => {
            console.error('Erro ao buscar CEP:', error);
            alert('Erro ao buscar CEP. Por favor, tente novamente.');
            
            document.getElementById('rua').value = '';
            document.getElementById('bairro').value = '';
            document.getElementById('cidade').value = '';
            document.getElementById('estado').value = '';
        });
}

// Validação de email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

// Atualizar resumo do pedido
function atualizarResumo(frete, freteDescricao) {
    const subtotal = parseFloat(document.getElementById('subtotalValor').getAttribute('data-valor'));
    const freteValor = parseFloat(frete);
    const total = subtotal + freteValor;
    
    document.getElementById('freteResumo').textContent = `R$ ${freteValor.toFixed(2).replace('.', ',')} (${freteDescricao})`;
    document.getElementById('totalResumo').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// Definir a URL base para uso global
const BASE_URL = window.location.origin;
console.log('URL base:', BASE_URL);