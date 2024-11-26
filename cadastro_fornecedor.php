<?php
// Inclui o arquivo que valida a sessão do usuário
include('valida_sessao.php');
// Inclui o arquivo de conexão com o banco de dados
include('conexao.php');

// Função para redimensionar e salvar a imagem
function redimensionarESalvarImagem($arquivo, $largura = 80, $altura = 80) {
    $diretorio_destino = "img/";
    $nome_arquivo = uniqid() . '_' . basename($arquivo["name"]);
    $caminho_completo = $diretorio_destino . $nome_arquivo;
    $tipo_arquivo = strtolower(pathinfo($caminho_completo, PATHINFO_EXTENSION));

    // Verifica se é uma imagem válida
    $check = getimagesize($arquivo["tmp_name"]);
    if($check === false) {
        return "O arquivo não é uma imagem válida.";
    }

    // Verifica o tamanho do arquivo (limite de 5MB)
    if ($arquivo["size"] > 5000000) {
        return "O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
    }

    // Permite alguns formatos de arquivo
    if($tipo_arquivo != "jpg" && $tipo_arquivo !="png" && $tipo_arquivo != "jpeg" && $tipo_arquivo != "gif"){
        return "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
    }

    // Permite apenas alguns formatos de arquivo enviado
    if ($tipo_arquivo == "jpg" || $tipo_arquivo == "jpeg") {
        $imagem_original = imagecreatefromjpeg($arquivo["tmp_name"]);
    } elseif ($tipo_arquivo == "png") {
        $imagem_original = imagecreatefrompng($arquivo["tmp_name"]);
    } elseif ($tipo_arquivo == "gif") {
        $imagem_original = imagecreatefromgif($arquivo["tmp_name"]);
    }

    // Obtém as dimensões originais da imagem
    $largura_original = imagesx($imagem_original);
    $altura_original = imagesy($imagem_original);

    // Calcula as novas dimensões mantendo a proporção
    $ratio = min($largura / $largura_original, $altura / $altura_original);
    $nova_largura = $largura_original * $ratio;
    $nova_altura = $altura_original * $ratio;
    
    // Cria uma nova imagem com as dimensões calculadas
    $nova_imagem = imagecreatetruecolor($nova_largura, $nova_altura);

    // Redimensiona a imagem original para a nova imagem
    imagecopyresampled($nova_imagem, $imagem_original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);

    // Salva a nova imagem
    if($tipo_arquivo == "jpg" || $tipo_arquivo == "jpeg") {
        imagejpeg($nova_imagem, $caminho_completo, 90);
    } elseif ($tipo_arquivo == "png") {
        imagepng($nova_imagem, $caminho_completo);
    } elseif ($tipo_arquivo == "gif") {
        imagegif($nova_imagem, $caminho_completo);
    }

    // Libera memória
    imagedestroy($imagem_original);
    imagedestroy($nova_imagem);

    return $caminho_completo;
}

    // Verifica se o formulário foi enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];

        // Processa o upload da imagem
        $imagem = "";
        if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $resultado_upload = redimensionarESalvarImagem($_FILES['imagem']);
            if(strpos($resultado_upload, 'img/') === 0){
                $imagem = $resultado_upload;
            } else {
                $mensagem_erro = $resultado_upload;
            }
        }

        // Prepara a query SQL para inserção ou atualização
        if ($id) {
            // Se o ID existe, é uma atualização
            $sql = "UPDATE fornecedor SET nome='$nome', email='$email', telefone='$telefone'";
            if($imagem) {
                $sql .= ",imagem='$imagem'";
            }
            $sql .= "WHERE id='$id'";
            $mensagem = "Fornecedor atualizado com sucesso!";
        } else {
            // Se não há ID, é uma nova inserção
            $sql = "INSERT INTO fornecedorv (nome, email, telefone, imagem) VALUES ('$nome', '$email', '$telefone', '$imagem')";
            $mensagem = "Fornecedor cadastrado com sucesso!";
        }

        // Executa a query e verifica se houve erro
        if ($conn->query($sql) !== TRUE){
            $mensagem = "Erro: " . $conn->error;
        }
  
} 
// Verifica se foi solicitada a exclusão de um fornecedor
if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Verifica se o fornecedor tem produtos cadastrados
    $check_produtos = $conn->query("SELECT COUNT(*) as count FROM produtos WHERE fornecedor_id = '$delete_id'")->fetch_assoc();

    if($check_produtos['count'] > 0){
        $mensagem = "Não é possível excluir este fornecedor pois existem produtos cadastrados para ele.";
    } else {
        $sql = "DELETE FROM fornecedor WHERE id='$delete_id'";
        if ($conn->query($sql) === TRUE) {
            $mensagem = "Fornecedor excluído com sucesso!";
        } else {
            $mensagem = "Erro ao excluir fornecedor: " . $conn->error;
        }
    }
}

// Busca todos os fornecedores para listar na tabela
$fornecedor = $conn->query("SELECT * FROM fornecedor");

// Se foi solicitada a edição de um fornecedor, busca os dados dele
$fornecedor = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $fornecedor = $conn->query("SELECT * FROM fornecedor WHERE id='$edit_id'")->fetch_assoc();
}
?>