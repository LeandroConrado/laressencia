<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Pedido;
use App\Models\Admin\Cliente;
use App\Models\Admin\Produto;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Dompdf\Dompdf;
use Dompdf\Options;

class PedidoController
{
    private Pedido $pedido;
    private Cliente $cliente;
    private Produto $produto;
    private Environment $twig;

    public function __construct()
    {
        $this->pedido = new Pedido();
        $this->cliente = new Cliente();
        $this->produto = new Produto();

        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
    }

    public function listar()
    {
        $pedidos = $this->pedido->buscarTodosComCliente();
        echo $this->twig->render('admin/pedidos/listar.twig', ['pedidos' => $pedidos, 'session' => $_SESSION]);
        unset($_SESSION['alerta']);
    }

    public function novo()
    {
        $clientes = $this->cliente->buscarTodos();
        $produtos = $this->produto->buscarTodos();
        echo $this->twig->render('admin/pedidos/novo.twig', ['clientes' => $clientes, 'produtos' => $produtos, 'session' => $_SESSION]);
        unset($_SESSION['alerta']);
    }

    public function salvar()
    {
        $dados = [
            'cliente_id' => $_POST['cliente_id'],
            'status'     => $_POST['status'],
            'total'      => $_POST['total'],
            'itens'      => $_POST['itens'] ?? [],
        ];

        $this->pedido->salvar($dados);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Pedido cadastrado com sucesso!'
        ];

        header("Location: index.php?url=pedidos/listar");
        exit;
    }

    public function visualizar()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID do pedido não informado.";
            return;
        }

        $pedido = $this->pedido->buscarPorIdComItens($id);
        echo $this->twig->render('admin/pedidos/visualizar.twig', ['pedido' => $pedido, 'session' => $_SESSION]);
        unset($_SESSION['alerta']);
    }

    public function atualizarStatus()
    {
        $id = $_GET['id'] ?? null;
        $novoStatus = $_POST['status'] ?? null;

        if (!$id || !$novoStatus) {
            echo "ID ou status não informado.";
            return;
        }

        $this->pedido->atualizarStatus($id, $novoStatus);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Status do pedido atualizado com sucesso!'
        ];

        header("Location: index.php?url=pedidos/listar");
        exit;
    }

    public function imprimir()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $pedido = $this->pedido->buscarPorIdComItens($id);

        $html = $this->twig->render('admin/pedidos/pdf.twig', ['pedido' => $pedido]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("pedido_{$pedido['numero']}.pdf", ["Attachment" => false]);
    }
}
