<?php
// ============================================================
// MODEL — conexão e consultas à tabela de noticias
// ============================================================

function conectar_noticias() {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../banco.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function buscar_noticias() {
    $pdo = conectar_noticias();
    return $pdo->query("SELECT * FROM noticias ORDER BY data_publicacao DESC");
}

function buscar_noticias_recentes($limite = 5) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT * FROM noticias
        ORDER BY data_publicacao DESC
        LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function buscar_destaques_semana($limite = 3) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT * FROM noticias
        WHERE data_publicacao >= datetime('now', '-7 days')
        ORDER BY visualizacoes DESC
        LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function buscar_noticia_por_id($id) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tempo_decorrido($data_publicacao) {
    // Força UTC nos dois lados para evitar problema de fuso
    $agora     = new DateTime('now', new DateTimeZone('UTC'));
    $publicado = new DateTime($data_publicacao, new DateTimeZone('UTC'));
    $diff      = $agora->diff($publicado);
    $minutos   = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;

    if ($diff->days >= 365) return 'Há ' . floor($diff->days / 365) . 'a';
    if ($diff->days >= 30)  return 'Há ' . $diff->m . ($diff->m > 1 ? ' meses' : ' mês');
    if ($diff->days >= 1)   return 'Há ' . $diff->days . 'd';
    if ($minutos >= 60)     return 'Há ' . $diff->h . 'h';
    if ($minutos >= 1)      return 'Há ' . $minutos . 'min';
    return 'Agora mesmo';
}
?>