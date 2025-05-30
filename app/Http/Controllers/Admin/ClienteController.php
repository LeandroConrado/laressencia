<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Cliente;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ClienteController
{
    private Cliente $cliente;
    private Environment $twig;

    public function __construct()
    {
        $this->cliente = new Cliente();
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
    }

    public function listar()
    {
        $clientes = $this->cliente->buscarTodos();
        echo $this->twig->render(
            'admin/clientes/listar.twig', [
            'clientes' => $clientes,
            'session' => $_SESSION
            ]
        );
        unset($_SESSION['alerta']);
    }

    public function novo()
    {
        echo $this->twig->render('admin/clientes/novo.twig');
    }

    public function salvar()
    {
        $this->cliente->salvar(
            [
            'nome'     => $_POST['nome'] ?? '',
            'email'    => $_POST['email'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'cidade'   => $_POST['cidade'] ?? '',
            'estado'   => $_POST['estado'] ?? '',
            'cep'      => $_POST['cep'] ?? '',
            'numero'   => $_POST['numero'] ?? '',
            'bairro'   => $_POST['bairro'] ?? '',
            'rua'      => $_POST['rua'] ?? ''
            ]
        );

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Cliente cadastrado com sucesso!'
        ];

        header('Location: index.php?url=clientes/listar');
        exit;
    }

    public function editar()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID não informado";
            return;
        }

        $cliente = $this->cliente->buscarPorId($id);

        if (!$cliente) {
            echo "Cliente não encontrado";
            return;
        }

        echo $this->twig->render(
            'admin/clientes/editar.twig', [
            'cliente' => $cliente,
            'session' => $_SESSION
            ]
        );
        unset($_SESSION['alerta']);
    }

    public function atualizar()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID não informado";
            return;
        }

        $this->cliente->atualizar(
            $id, [
            'nome'     => $_POST['nome'] ?? '',
            'email'    => $_POST['email'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'cidade'   => $_POST['cidade'] ?? '',
            'estado'   => $_POST['estado'] ?? '',
            'cep'      => $_POST['cep'] ?? '',
            'numero'   => $_POST['numero'] ?? '',
            'bairro'   => $_POST['bairro'] ?? '',
            'rua'      => $_POST['rua'] ?? ''
            ]
        );

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Cliente atualizado com sucesso!'
        ];

        header('Location: index.php?url=clientes/listar');
        exit;
    }

    public function excluir()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID não informado";
            return;
        }

        $this->cliente->excluir($id);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Cliente excluído com sucesso!'
        ];

        header('Location: index.php?url=clientes/listar');
        exit;
    }
}
