<?php
$nome   = $_POST["nome"];
$email  = $_POST["email"];
$senha  = $_POST["senha"];
$avatar = $_POST["avatar"] ?: "img/avatar-default.png";


if (strlen($senha) < 6) {
    header("Location: cadastro.php?erro=A senha deve ter no mínimo 6 caracteres");
    exit;
}

$senha_hash = hash("sha256", $senha);

$string_de_conexao = "sqlite:../banco.db";
$pdo = new \PDO($string_de_conexao);

$sql_verifica = "SELECT id FROM usuarios WHERE email = :email";
$stmt_verifica = $pdo->prepare($sql_verifica);
$stmt_verifica->bindValue(":email", $email);
$stmt_verifica->execute();

if ($stmt_verifica->fetch()) {
    header("Location: cadastro.php?erro=Este email já está cadastrado");
    exit;
}

$sql = "
    INSERT INTO usuarios (nome, email, senha, avatar, perfil_id)
    VALUES (:nome, :email, :senha, :avatar, 2)
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":nome",   $nome);
$stmt->bindValue(":email",  $email);
$stmt->bindValue(":senha",  $senha_hash);
$stmt->bindValue(":avatar", $avatar);
$stmt->execute();

header("Location: login.php?sucesso=Cadastro realizado! Faça seu login");
exit;
?>