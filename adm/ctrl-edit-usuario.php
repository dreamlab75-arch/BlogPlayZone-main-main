<?php
session_start();

if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'adm') {
    header('Location: ../auth/login.php?erro=Acesso restrito');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel-adm.php');
    exit;
}

$usuario_id = (int)($_POST['usuario_id'] ?? 0);
$perfil_id  = (int)($_POST['perfil_id']  ?? 0);

if (!$usuario_id || !$perfil_id) {
    header('Location: painel-adm.php?erro=' . urlencode('Dados inválidos.'));
    exit;
}

// Impede que o adm logado altere o próprio perfil
if ($usuario_id === (int)$_SESSION['usuario_id']) {
    header('Location: painel-adm.php?erro=' . urlencode('Você não pode alterar seu próprio perfil.'));
    exit;
}

$pdo = new \PDO('sqlite:../banco.db');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// Verifica se o perfil_id existe na tabela perfil
$stmtPerfil = $pdo->prepare('SELECT id FROM perfil WHERE id = :id');
$stmtPerfil->execute([':id' => $perfil_id]);
if (!$stmtPerfil->fetch()) {
    header('Location: painel-adm.php?erro=' . urlencode('Perfil inválido.'));
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE usuarios SET perfil_id = :perfil_id WHERE id = :id');
    $stmt->execute([':perfil_id' => $perfil_id, ':id' => $usuario_id]);

    header('Location: painel-adm.php?sucesso=' . urlencode('Perfil atualizado com sucesso.'), true, 303);
} catch (\Exception $e) {
    header('Location: painel-adm.php?erro=' . urlencode('Erro ao atualizar o perfil.'));
}
exit;