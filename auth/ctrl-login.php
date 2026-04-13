<?php
session_start();

$login = $_POST["login"];
$senha = $_POST["senha"];

$senha_hash = hash("sha256", $senha);

$string_de_conexao = "sqlite:../banco.db";
$pdo = new \PDO($string_de_conexao);

$sql = "
    SELECT usuarios.*, perfil.tipo as perfil_tipo
    FROM usuarios
    JOIN perfil ON usuarios.perfil_id = perfil.id
    WHERE (email = :login OR nome = :login) AND senha = :senha
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":login", $login);
$stmt->bindValue(":senha", $senha_hash);
$stmt->execute();

$usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

if ($usuario) {
    $_SESSION["usuario_id"]        = $usuario["id"];
    $_SESSION["usuario_nome"]      = $usuario["nome"];
    $_SESSION["usuario_perfil"]    = $usuario["perfil_tipo"];
    $_SESSION["usuario_perfil_id"] = $usuario["perfil_id"];
    $_SESSION["usuario_avatar"]    = $usuario["avatar"];

    

    header("Location: ../index.php");
    exit;
} else {
    header("Location: login.php?erro=Email ou senha incorretos");
    exit;
}
?>