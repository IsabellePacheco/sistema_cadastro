<?php include('valida_sessao.php');?>
<?php include('conexao.php');?>

<?php
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET ['delete_id'];
    $sql = "DELETE FROM produtos WHERE id='$delete_id'";
    if ($conn->query($sql) ===true){
        $mensagem = "Produto excluído com secesso!";
    } else {
        $mensagem ="Erro ao excluir produto:" . $conn->error;
    }
}

$produtos = $conn->query("SELECT p.id, p.nome, p.descricao, p.preco, p.imagem, f.nome AS fronecerdor_nome FROM produtos p JOIN fornecedor f ON p.fornecedor_id = f.id");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <h2>Listagem de Produtos</h2>
        <?php if (isset($mensagem)) echo "<p class='message". ($conn->error ? "error" :"success") . "'>$mensagem</p>"; ?>
        <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th>Fornecedor</th>
            <th>Imagem</th>
            <th>Ações</th>
        </tr>
        <?php while ($row)






        </table>




    </div>
</body>
</html>