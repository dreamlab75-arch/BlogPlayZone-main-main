<?php
session_start();

if (!isset($_SESSION["usuario_perfil"]) || $_SESSION["usuario_perfil"] !== "adm") {
    header("Location: ../auth/login.php?erro=Acesso restrito");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: painel-adm.php?erro=Método inválido");
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$perfil_id = (int)($_POST['perfil_id'] ?? 0);

if (empty($nome) || empty($email) || empty($senha) || $perfil_id <= 0) {
    header("Location: painel-adm.php?erro=Dados incompletos");
    exit;
}

if (strlen($senha) < 6) {
    header("Location: painel-adm.php?erro=Senha deve ter pelo menos 6 caracteres");
    exit;
}

$string_de_conexao = "sqlite:../banco.db";
$pdo = new \PDO($string_de_conexao);

$sql_check = "SELECT id FROM usuarios WHERE email = :email";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->bindValue(':email', $email);
$stmt_check->execute();

if ($stmt_check->fetch()) {
    header("Location: painel-adm.php?erro=Email já cadastrado");
    exit;
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nome, email, senha, perfil_id) VALUES (:nome, :email, :senha, :perfil_id)";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome', $nome);
$stmt->bindValue(':email', $email);
$stmt->bindValue(':senha', $senha_hash);
$stmt->bindValue(':perfil_id', $perfil_id);

if ($stmt->execute()) {
    // VOLTE PRA ESTE header() por enquanto:
    header("Location: painel-adm.php?sucesso=Usuário criado com sucesso!");
} else {
    header("Location: painel-adm.php?erro=Erro ao criar usuário");
}
exit;
?>