<?php

function conectar_noticias() {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../banco.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function buscar_noticias() {
    $pdo = conectar_noticias();
    return $pdo->query("
        SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
               COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
               COUNT(DISTINCT cn.usuario_id) AS curtidas,
               COUNT(DISTINCT co.id)         AS comentarios
        FROM noticias n
        JOIN usuarios u ON u.id = n.usuario_id
        LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
        LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
        LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
        GROUP BY n.id ORDER BY n.data_publicacao DESC
    ");
}

function buscar_noticias_recentes($limite = 5) {
    $pdo  = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
               COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
               COUNT(DISTINCT cn.usuario_id) AS curtidas,
               COUNT(DISTINCT co.id)         AS comentarios
        FROM noticias n
        JOIN usuarios u ON u.id = n.usuario_id
        LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
        LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
        LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
        GROUP BY n.id ORDER BY n.data_publicacao DESC LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function buscar_destaques_semana($limite = 3) {
    $pdo  = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
               COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
               COUNT(DISTINCT cn.usuario_id) AS curtidas,
               COUNT(DISTINCT co.id)         AS comentarios
        FROM noticias n
        JOIN usuarios u ON u.id = n.usuario_id
        LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
        LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
        LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
        WHERE n.data_publicacao >= datetime('now', '-7 days')
        GROUP BY n.id ORDER BY visualizacoes DESC LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function buscar_noticia_por_id($id) {
    $pdo  = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
               COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
               COUNT(DISTINCT cn.usuario_id) AS curtidas,
               COUNT(DISTINCT co.id)         AS comentarios
        FROM noticias n
        JOIN usuarios u ON u.id = n.usuario_id
        LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
        LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
        LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
        WHERE n.id = :id GROUP BY n.id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscar_noticias_paginadas($pagina = 1, $limite = 10, $categoria = '', $busca = '') {
    $pdo    = conectar_noticias();
    $offset = ($pagina - 1) * $limite;
    $where  = []; $params = [];

    if ($categoria) { $where[] = "n.categoria = :categoria"; $params[':categoria'] = $categoria; }
    if ($busca)     { $where[] = "(n.titulo LIKE :busca OR n.resumo LIKE :busca)"; $params[':busca'] = "%$busca%"; }

    $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

    $stmt = $pdo->prepare("
        SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
               COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
               COUNT(DISTINCT cn.usuario_id) AS curtidas,
               COUNT(DISTINCT co.id)         AS comentarios
        FROM noticias n
        JOIN usuarios u ON u.id = n.usuario_id
        LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
        LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
        LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
        $whereSQL
        GROUP BY n.id ORDER BY n.data_publicacao DESC
        LIMIT :limite OFFSET :offset
    ");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function contar_noticias($categoria = '', $busca = '') {
    $pdo = conectar_noticias(); $where = []; $params = [];
    if ($categoria) { $where[] = "categoria = :categoria"; $params[':categoria'] = $categoria; }
    if ($busca)     { $where[] = "(titulo LIKE :busca OR resumo LIKE :busca)"; $params[':busca'] = "%$busca%"; }
    $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM noticias $whereSQL");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function buscar_categorias() {
    return conectar_noticias()->query(
        "SELECT DISTINCT categoria FROM noticias ORDER BY categoria ASC"
    )->fetchAll(PDO::FETCH_COLUMN);
}

function registrar_visualizacao_noticia($noticia_id, $usuario_id) {
    try {
        conectar_noticias()->prepare("
            INSERT OR IGNORE INTO Visualiza_noticia (usuario_id, noticia_id) VALUES (:uid, :nid)
        ")->execute([':uid' => $usuario_id, ':nid' => $noticia_id]);
    } catch (Exception $e) {}
}

function tempo_decorrido($data_publicacao) {
    $agora     = new DateTime('now', new DateTimeZone('UTC'));
    $publicado = new DateTime($data_publicacao, new DateTimeZone('UTC'));
    $diff      = $agora->diff($publicado);
    $minutos   = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;

    if ($diff->days >= 365) { $a = floor($diff->days/365); return 'Há '.$a.' ano'.($a>1?'s':''); }
    if ($diff->days >= 30)  return 'Há '.$diff->m.($diff->m>1?' meses':' mês');
    if ($diff->days >= 7)   { $s = floor($diff->days/7); return 'Há '.$s.' semana'.($s>1?'s':''); }
    if ($diff->days >= 1)   return 'Há '.$diff->days.' dia'.($diff->days>1?'s':'');
    if ($minutos >= 60)     return 'Há '.$diff->h.' hora'.($diff->h>1?'s':'');
    if ($minutos >= 1)      return 'Há '.$minutos.' minuto'.($minutos>1?'s':'');
    return 'Agora mesmo';
}

function formatar_data_noticia($data_publicacao) {
    $dt = new DateTime($data_publicacao, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
    return $dt->format('d/m/Y') . ' ' . $dt->format('H:i') . 'min';
}

function categoria_para_badge($categoria) {
    $mapa = [
        'lançamento'  => 'bg-primary text-white',
        'rumor'       => 'bg-warning text-dark',
        'análise'     => 'bg-info text-white',
        'urgente'     => 'bg-danger text-white',
        'review'      => 'bg-success text-white',
        'prévia'      => 'bg-secondary text-white',
        'atualização' => 'bg-secondary text-white',
        'evento'      => 'bg-dark text-white',
        'hardware'    => 'bg-warning text-dark',
        'negócios'    => 'bg-danger text-white',
        'curiosidade' => 'bg-info text-white',
        'lista'       => 'bg-primary text-white',
    ];
    return $mapa[mb_strtolower(trim($categoria))] ?? 'bg-secondary text-white';
}

function categoria_para_cor($categoria) {
    $mapa = [
        'lançamento'  => '#0d6efd', 'rumor'       => '#f59e0b',
        'análise'     => '#0dcaf0', 'urgente'     => '#ef4444',
        'review'      => '#22c55e', 'prévia'      => '#8b5cf6',
        'atualização' => '#6c757d', 'evento'      => '#1a0a4a',
        'hardware'    => '#f59e0b', 'negócios'    => '#ef4444',
        'curiosidade' => '#0dcaf0', 'lista'       => '#0d6efd',
    ];
    return $mapa[mb_strtolower(trim($categoria))] ?? '#611DF2';
}

function normalizar_imagem_noticia($imagem, $prefixo = '../') {
    if (empty($imagem)) return '';

    if (str_starts_with($imagem, 'http')) return $imagem;

    if (str_starts_with($imagem, 'img/')) return $prefixo . $imagem;
    return $imagem;
}