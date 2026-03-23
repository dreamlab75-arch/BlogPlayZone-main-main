<?php
session_start();

// Bloqueia acesso se não for adm
if (!isset($_SESSION["usuario_perfil"]) || $_SESSION["usuario_perfil"] !== "adm") {
    header("Location: ../auth/login.php?erro=Acesso restrito");
    exit;
}

$id_usuario = $_GET["id"];

$string_de_conexao = "sqlite:../banco.db";
$pdo = new \PDO($string_de_conexao);

$sql = "DELETE FROM usuarios WHERE id = :id_usuario";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":id_usuario", $id_usuario);
$stmt->execute();

header("Location: painel-adm.php?sucesso=Usuário deletado com sucesso");
exit;
?>