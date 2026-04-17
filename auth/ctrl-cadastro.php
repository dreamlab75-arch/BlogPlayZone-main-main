<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../util/upload.php';

$nome  = trim($_POST['nome']  ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (strlen($nome) < 2) {
    header('Location: cadastro.php?erro=' . urlencode('Nome muito curto.'));
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cadastro.php?erro=' . urlencode('E-mail inválido.'));
    exit;
}
if (strlen($senha) < 6) {
    header('Location: cadastro.php?erro=' . urlencode('A senha deve ter no mínimo 6 caracteres.'));
    exit;
}

$senha_hash        = hash('sha256', $senha);
$pdo               = new PDO('sqlite:' . __DIR__ . '/../banco.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    header('Location: cadastro.php?erro=' . urlencode('Este e-mail já está cadastrado.'));
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, avatar, perfil_id) VALUES (:nome, :email, :senha, :avatar, 2)');
    $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senha_hash, ':avatar' => null]);
    $usuario_id = (int)$pdo->lastInsertId();

    $pasta  = __DIR__ . '/../perfil/avatares';
    $avatar = salvar_imagem('avatar', 'avatar', $usuario_id, $pasta);

    if ($avatar) {
        $pdo->prepare('UPDATE usuarios SET avatar = :avatar WHERE id = :id')
            ->execute([':avatar' => $avatar, ':id' => $usuario_id]);
    }

    header('Location: login.php?sucesso=' . urlencode('Cadastro realizado! Faça seu login.'), true, 303);
    exit;

} catch (RuntimeException $e) {
    header('Location: cadastro.php?erro=' . urlencode($e->getMessage()));
    exit;
} catch (Exception $e) {
    header('Location: cadastro.php?erro=' . urlencode('Erro ao criar a conta. Tente novamente.'));
    exit;
}