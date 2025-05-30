<?php

namespace App\Http\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Configuracao;
use Twig\Environment;

class ConfiguracaoController extends Controller
{
    private Configuracao $model;

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
        $this->model = new Configuracao();
    }

    public function editar(): void
    {
        $dados = [
            'configs' => $this->model->listarTodas()
        ];

        $this->view('admin/configuracoes/editar.twig', $dados);
    }

    public function salvar(): void
    {
        // Verifica se foi feito upload de nova logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $arquivoTmp = $_FILES['logo']['tmp_name'];
            $nomeOriginal = $_FILES['logo']['name'];
            $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
            $nomeUnico = uniqid('logo_') . '.' . $extensao;
            $caminhoFinal = __DIR__ . '/../../../public/uploads/logo/' . $nomeUnico;

            // Cria pasta se não existir
            if (!is_dir(dirname($caminhoFinal))) {
                mkdir(dirname($caminhoFinal), 0755, true);
            }

            // Deleta logo anterior, se houver
            $logoAtual = $this->model->obterValor('logo');
            if ($logoAtual && file_exists(__DIR__ . '/../../../public/uploads/logo/' . $logoAtual)) {
                unlink(__DIR__ . '/../../../public/uploads/logo/' . $logoAtual);
            }

            // Move nova logo
            move_uploaded_file($arquivoTmp, $caminhoFinal);

            // Atualiza no banco de dados
            $this->model->atualizar('logo', $nomeUnico);
        }

        // Atualiza demais configurações
        foreach ($_POST as $chave => $valor) {
            if ($chave !== 'logo') {
                $this->model->atualizar($chave, $valor);
            }
        }

        $this->redirecionar('/laressencia/public/admin/index.php?url=configuracoes/editar');
    }

}
