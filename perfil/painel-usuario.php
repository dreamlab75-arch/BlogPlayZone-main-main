<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Só usuários logados
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=Faça login para acessar seu painel');
    exit;
}

require_once __DIR__ . '/../posts-index/posts-model.php';
require_once __DIR__ . '/../util/upload.php';

$pdo        = conectar();
$usuario_id = (int)$_SESSION['usuario_id'];

// Busca dados atualizados do usuário no banco
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: ../auth/ctrl-logout.php');
    exit;
}

// Aba ativa (posts | conta)
$aba = $_GET['aba'] ?? 'posts';

// Busca posts do usuário
$stmtPosts = $pdo->prepare("
    SELECT
        p.id, p.titulo, p.conteudo, p.imagem, p.data_publicacao,
        GROUP_CONCAT(DISTINCT t.nome) AS tags,
        COUNT(DISTINCT cp.usuario_id) AS curtidas,
        COUNT(DISTINCT co.id)         AS comentarios,
        COUNT(DISTINCT vp.usuario_id) AS visualizacoes
    FROM posts p
    LEFT JOIN post_tag pt        ON pt.post_id = p.id
    LEFT JOIN tags t             ON t.id = pt.tag_id
    LEFT JOIN Curte_post cp      ON cp.post_id = p.id AND cp.ativo = 1
    LEFT JOIN Comentarios_posts co ON co.post_id = p.id
    LEFT JOIN Visualiza_post vp  ON vp.post_id = p.id
    WHERE p.usuario_id = :uid
    GROUP BY p.id
    ORDER BY p.data_publicacao DESC
");
$stmtPosts->execute([':uid' => $usuario_id]);
$posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

// Busca todas as tags para o modal de edição
$todasTags = buscar_tags();

// Verifica se o usuário pode gerenciar notícias (adm=1 ou jornalista=3)
$perfil_id_sessao = (int)($_SESSION['usuario_perfil_id'] ?? $usuario['perfil_id'] ?? 2);
$pode_noticias = in_array($perfil_id_sessao, [1, 3]);

// Busca notícias do usuário (apenas se tiver permissão)
$noticias = [];
if ($pode_noticias) {
    require_once __DIR__ . '/../noticias-index/noticias-model.php';
    $stmtNoticias = conectar_noticias()->prepare("
        SELECT n.*, COUNT(DISTINCT vn.usuario_id) AS visualizacoes
        FROM noticias n
        LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
        WHERE n.usuario_id = :uid
        GROUP BY n.id
        ORDER BY n.data_publicacao DESC
    ");
    $stmtNoticias->execute([':uid' => $usuario_id]);
    $noticias = $stmtNoticias->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Painel — PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="painel-body">
<div class="painel-layout">

  <!-- ========== SIDEBAR ========== -->
  <aside class="painel-sidebar">

    <!-- Avatar + nome -->
    <div class="painel-sidebar-perfil">
      <div class="painel-sidebar-avatar-wrap">
        <img src="<?= htmlspecialchars(img_url($usuario['avatar'] ?? '', '../img/avatar-default.png')) ?>"
             alt="Avatar"
             class="painel-sidebar-avatar"
             <?= img_onerror('../img/avatar-default.png') ?>>
      </div>
      <div class="painel-sidebar-nome"><?= htmlspecialchars($usuario['nome']) ?></div>
      <div class="painel-sidebar-perfil-tipo">
        <?php
        $icone = match($usuario['perfil_id'] ?? 2) {
            1 => '<i class="bi bi-shield-fill-check"></i> Administrador',
            3 => '<i class="bi bi-newspaper"></i> Jornalista',
            default => '<i class="bi bi-person-fill"></i> Leitor',
        };
        echo $icone;
        ?>
      </div>
      <?php if (!empty($usuario['bio'])): ?>
        <p class="painel-sidebar-bio"><?= htmlspecialchars($usuario['bio']) ?></p>
      <?php endif; ?>
    </div>

    <!-- Stats rápidas -->
    <div class="painel-sidebar-stats">
      <div class="painel-stat">
        <span class="painel-stat-num"><?= count($posts) ?></span>
        <span class="painel-stat-label">Posts</span>
      </div>
      <div class="painel-stat">
        <span class="painel-stat-num"><?= array_sum(array_column($posts, 'curtidas')) ?></span>
        <span class="painel-stat-label">Curtidas</span>
      </div>
      <div class="painel-stat">
        <span class="painel-stat-num"><?= array_sum(array_column($posts, 'visualizacoes')) ?></span>
        <span class="painel-stat-label">Views</span>
      </div>
      <?php if ($pode_noticias): ?>
      <div class="painel-stat">
        <span class="painel-stat-num"><?= count($noticias) ?></span>
        <span class="painel-stat-label">Notícias</span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Navegação -->
    <nav class="painel-nav">
      <p class="adm-nav-label">Menu</p>
      <a href="?aba=posts" class="adm-nav-item <?= $aba === 'posts' ? 'active' : '' ?>">
        <i class="bi bi-grid-fill"></i> Meus Posts
      </a>
      <?php if ($pode_noticias): ?>
      <a href="?aba=noticias" class="adm-nav-item <?= $aba === 'noticias' ? 'active' : '' ?>">
        <i class="bi bi-newspaper"></i> Minhas Notícias
      </a>
      <?php endif; ?>
      <a href="?aba=conta" class="adm-nav-item <?= $aba === 'conta' ? 'active' : '' ?>">
        <i class="bi bi-person-gear"></i> Editar Conta
      </a>
    </nav>

    <div class="adm-sidebar-footer">
      <a href="../index.php" class="adm-nav-item">
        <i class="bi bi-house-fill"></i> Voltar ao blog
      </a>
      <a href="../auth/ctrl-logout.php" class="adm-nav-item adm-nav-item--sair">
        <i class="bi bi-box-arrow-right"></i> Sair
      </a>
    </div>

  </aside>

  <!-- ========== CONTEÚDO PRINCIPAL ========== -->
  <main class="painel-main">

    <!-- Topbar -->
    <div class="adm-topbar">
      <h4 class="adm-page-titulo">
        <?php
        echo match($aba) {
            'conta'    => 'Editar Conta',
            'noticias' => 'Minhas Notícias',
            default    => 'Meus Posts',
        };
        ?>
      </h4>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <!-- ===== ABA: MEUS POSTS ===== -->
    <?php if ($aba === 'posts'): ?>

      <?php if (empty($posts)): ?>
        <div class="painel-empty">
          <i class="bi bi-pencil-square"></i>
          <p>Você ainda não publicou nenhum post.</p>
          <a href="../posts-index/posts-view.php" class="btn-modal-publicar mt-3">
            <i class="bi bi-plus-circle me-1"></i> Criar meu primeiro post
          </a>
        </div>
      <?php else: ?>

        <div class="painel-posts-header">
          <p class="resultado-info mb-0">
            <strong><?= count($posts) ?></strong> post<?= count($posts) != 1 ? 's' : '' ?> publicado<?= count($posts) != 1 ? 's' : '' ?>
          </p>
          <a href="../posts-index/posts-view.php" class="btn-criar-post" style="font-size:.85rem;padding:8px 18px;">
            <i class="bi bi-plus-circle-fill"></i> Novo Post
          </a>
        </div>

        <div class="painel-posts-grid">
          <?php foreach ($posts as $post):
            $tags_post = $post['tags'] ? explode(',', $post['tags']) : [];
            $trecho    = mb_substr($post['conteudo'], 0, 100);
          ?>
          <div class="painel-post-card">

            <!-- Imagem ou placeholder -->
            <div class="painel-post-thumb"
                 style="<?= $post['imagem'] ? 'background-image:url('.htmlspecialchars($post['imagem']).')' : '' ?>">
              <?php if (!$post['imagem']): ?>
                <i class="bi bi-controller"></i>
              <?php endif; ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($tags_post)): ?>
            <div class="painel-post-tags">
              <?php foreach (array_slice($tags_post, 0, 2) as $tag): ?>
                <span class="post-tag" style="font-size:.7rem;padding:3px 9px;">
                  <?= htmlspecialchars(trim($tag)) ?>
                </span>
              <?php endforeach; ?>
              <?php if (count($tags_post) > 2): ?>
                <span style="font-size:.7rem;color:#aaa;">+<?= count($tags_post)-2 ?></span>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Título -->
            <h6 class="painel-post-titulo"><?= htmlspecialchars($post['titulo']) ?></h6>

            <!-- Trecho -->
            <p class="painel-post-trecho"><?= htmlspecialchars($trecho) ?>...</p>

            <!-- Stats -->
            <div class="painel-post-stats">
              <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $post['curtidas'] ?></span>
              <span><i class="bi bi-chat-fill" style="color:#611DF2;"></i> <?= $post['comentarios'] ?></span>
              <span><i class="bi bi-eye-fill" style="color:#611DF2;"></i> <?= $post['visualizacoes'] ?></span>
            </div>

            <!-- Tempo -->
            <div class="painel-post-data">
              <i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>">
                <?= tempo_decorrido_posts($post['data_publicacao']) ?>
              </span>
            </div>

            <!-- Ações -->
            <div class="painel-post-acoes">
              <a href="../posts-index/post.php?id=<?= $post['id'] ?>"
                 class="painel-btn-ver" title="Ver post">
                <i class="bi bi-eye"></i> Ver
              </a>
              <button onclick="abrirEditarPost(<?= $post['id'] ?>, <?= htmlspecialchars(json_encode($post['titulo'])) ?>, <?= htmlspecialchars(json_encode($post['conteudo'])) ?>, <?= htmlspecialchars(json_encode($post['imagem'] ?? '')) ?>, <?= htmlspecialchars(json_encode($post['tags'] ?? '')) ?>)"
                      class="painel-btn-editar" title="Editar">
                <i class="bi bi-pencil-fill"></i> Editar
              </button>
              <button onclick="confirmarDeletar(<?= $post['id'] ?>, '<?= htmlspecialchars(addslashes($post['titulo'])) ?>')"
                      class="painel-btn-deletar" title="Deletar">
                <i class="bi bi-trash-fill"></i>
              </button>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    <!-- ===== ABA: MINHAS NOTÍCIAS ===== -->
    <?php elseif ($aba === 'noticias' && $pode_noticias): ?>

      <?php if (empty($noticias)): ?>
        <div class="painel-empty">
          <i class="bi bi-newspaper"></i>
          <p>Você ainda não publicou nenhuma notícia.</p>
        </div>
      <?php else: ?>

        <div class="painel-posts-header">
          <p class="resultado-info mb-0">
            <strong><?= count($noticias) ?></strong> notícia<?= count($noticias) != 1 ? 's' : '' ?> publicada<?= count($noticias) != 1 ? 's' : '' ?>
          </p>
        </div>

        <div class="painel-posts-grid">
          <?php foreach ($noticias as $noticia): ?>
          <div class="painel-post-card">

            <!-- Thumb -->
            <div class="painel-post-thumb"
                 style="<?= $noticia['imagem'] ? 'background-image:url('.htmlspecialchars(normalizar_imagem_noticia($noticia['imagem'])).')' : '' ?>">
              <?php if (!$noticia['imagem']): ?>
                <i class="bi bi-newspaper"></i>
              <?php endif; ?>
            </div>

            <!-- Categoria -->
            <div class="painel-post-tags">
              <span class="badge <?= categoria_para_badge($noticia['categoria']) ?>" style="font-size:.7rem;">
                <?= strtoupper($noticia['categoria']) ?>
              </span>
            </div>

            <!-- Título -->
            <h6 class="painel-post-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h6>

            <!-- Resumo -->
            <p class="painel-post-trecho"><?= htmlspecialchars(mb_substr($noticia['resumo'], 0, 100)) ?>...</p>

            <!-- Stats -->
            <div class="painel-post-stats">
              <span><i class="bi bi-eye-fill" style="color:#611DF2;"></i> <?= $noticia['visualizacoes'] ?></span>
            </div>

            <!-- Tempo -->
            <div class="painel-post-data">
              <i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $noticia['data_publicacao'] ?>">
                <?= tempo_decorrido($noticia['data_publicacao']) ?>
              </span>
            </div>

            <!-- Ações -->
            <div class="painel-post-acoes">
              <a href="../noticias-index/noticia.php?id=<?= $noticia['id'] ?>"
                 class="painel-btn-ver" title="Ver notícia">
                <i class="bi bi-eye"></i> Ver
              </a>
              <button onclick="abrirEditarNoticia(<?= $noticia['id'] ?>, <?= htmlspecialchars(json_encode($noticia['titulo'])) ?>, <?= htmlspecialchars(json_encode($noticia['resumo'])) ?>, <?= htmlspecialchars(json_encode($noticia['conteudo'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['imagem'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['categoria'])) ?>)"
                      class="painel-btn-editar" title="Editar">
                <i class="bi bi-pencil-fill"></i> Editar
              </button>
              <button onclick="confirmarDeletarNoticia(<?= $noticia['id'] ?>, '<?= htmlspecialchars(addslashes($noticia['titulo'])) ?>')"
                      class="painel-btn-deletar" title="Deletar">
                <i class="bi bi-trash-fill"></i>
              </button>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    <!-- ===== ABA: EDITAR CONTA ===== -->
    <?php elseif ($aba === 'conta'): ?>

      <div class="adm-card" style="max-width:600px;">
        <form action="ctrl-editar-conta.php" method="POST" enctype="multipart/form-data">

          <!-- Avatar preview -->
          <div class="painel-avatar-preview-wrap mb-4">
            <img src="<?= htmlspecialchars(img_url($usuario['avatar'] ?? '', '../img/avatar-default.png')) ?>"
                 alt="Avatar" id="avatarPreview" class="painel-avatar-preview"
                 <?= img_onerror('../img/avatar-default.png') ?>>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nome</label>
            <input type="text" name="nome" class="form-control adm-form-input"
                   value="<?= htmlspecialchars($usuario['nome']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control adm-form-input"
                   value="<?= htmlspecialchars($usuario['email']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">
              Avatar <span class="text-muted fw-normal">(opcional)</span>
            </label>
            <input type="file" name="avatar" id="avatarInput" class="form-control adm-form-input"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   onchange="previewAvatar(this)">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">
              Bio <span class="text-muted fw-normal">(aparece no seu perfil)</span>
            </label>
            <textarea name="bio" class="form-control adm-form-input" rows="3"
                      maxlength="300"
                      placeholder="Conte um pouco sobre você..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
            <div class="form-text">Máximo 300 caracteres</div>
          </div>

          <hr class="my-4">

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <button type="button" class="btn-modal-cancelar"
                    onclick="abrirModalSenha()"
                    style="display:inline-flex;align-items:center;gap:6px;">
              <i class="bi bi-shield-lock"></i> Trocar senha
            </button>
            <div class="d-flex gap-3">
              <a href="?aba=posts" class="btn-modal-cancelar" style="text-decoration:none;display:inline-flex;align-items:center;">
                Cancelar
              </a>
              <button type="submit" class="btn-modal-publicar">
                <i class="bi bi-check-lg me-1"></i> Salvar Alterações
              </button>
            </div>
          </div>

        </form>
      </div>

    <?php endif; ?>

  </main>
</div>

<!-- ========== MODAL CONFIRMAR DELETAR ========== -->
<div class="adm-modal-overlay" id="modalDeletar">
  <div class="adm-modal" style="max-width:420px;">
    <h5 class="fw-bold mb-2" style="color:#1a0a4a;">Deletar post?</h5>
    <p class="text-muted mb-4" id="modalDeletarNome" style="font-size:.9rem;"></p>
    <div class="d-flex gap-3 justify-content-end">
      <button class="btn-modal-cancelar" onclick="fecharDeletar()">Cancelar</button>
      <a href="#" id="btnConfirmarDeletar" class="btn-modal-publicar"
         style="background:linear-gradient(135deg,#ef4444,#dc2626);text-decoration:none;">
        <i class="bi bi-trash-fill me-1"></i> Deletar
      </a>
    </div>
  </div>
</div>

<!-- ========== MODAL EDITAR POST ========== -->
<div class="adm-modal-overlay" id="modalEditar">
  <div class="adm-modal" style="max-width:620px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;"><i class="bi bi-pencil-square me-2"></i>Editar Post</h5>
      <button class="btn-close" onclick="fecharEditar()"></button>
    </div>

    <form action="ctrl-editar-post.php" method="POST" id="formEditarPost" enctype="multipart/form-data">
      <input type="hidden" name="post_id" id="editPostId">

      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" id="editTitulo" class="form-control adm-form-input"
               required maxlength="200">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" id="editConteudo" class="form-control adm-form-input"
                  rows="6" required minlength="50"
                  style="resize:vertical;"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">
          Imagem <span class="text-muted fw-normal">(opcional — deixe vazio para manter a atual)</span>
        </label>
        <input type="file" name="imagem" id="editImagem" class="form-control adm-form-input"
               accept="image/jpeg,image/png,image/webp,image/gif">
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">
          Tags <span class="text-muted fw-normal">(até 5)</span>
        </label>
        <div class="tags-modal-grid" id="editTagsGrid">
          <?php foreach ($todasTags as $tag): ?>
            <div class="tag-check-pill">
              <input type="checkbox" name="tags_post[]"
                     id="edit_tag_<?= $tag['id'] ?>"
                     value="<?= $tag['id'] ?>"
                     data-nome="<?= htmlspecialchars($tag['nome']) ?>">
              <label for="edit_tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharEditar()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar">
          <i class="bi bi-check-lg me-1"></i> Salvar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// ===== PREVIEW AVATAR =====
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ===== AUTO-HIDE ALERTAS =====
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 3500);
});

// ===== MODAL DELETAR =====
function confirmarDeletar(id, titulo) {
  document.getElementById('modalDeletarNome').textContent = 'Tem certeza que deseja deletar "' + titulo + '"? Esta ação não pode ser desfeita.';
  document.getElementById('btnConfirmarDeletar').href = 'ctrl-deletar-post.php?id=' + id;
  document.getElementById('modalDeletar').style.display = 'flex';
}
function fecharDeletar() {
  document.getElementById('modalDeletar').style.display = 'none';
}
document.getElementById('modalDeletar').addEventListener('click', function(e) {
  if (e.target === this) fecharDeletar();
});

// ===== MODAL EDITAR POST =====
function abrirEditarPost(id, titulo, conteudo, imagem, tagsStr) {
  document.getElementById('editPostId').value   = id;
  document.getElementById('editTitulo').value   = titulo;
  document.getElementById('editConteudo').value = conteudo;
  // editImagem é file input — não pode ser preenchido via JS por segurança

  // Desmarca todas as tags e marca as do post
  const tags = tagsStr ? tagsStr.split(',').map(t => t.trim()) : [];
  document.querySelectorAll('#editTagsGrid input[type=checkbox]').forEach(cb => {
    cb.checked = tags.includes(cb.getAttribute('data-nome'));
  });

  document.getElementById('modalEditar').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharEditar() {
  document.getElementById('modalEditar').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('modalEditar').addEventListener('click', function(e) {
  if (e.target === this) fecharEditar();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') { fecharEditar(); fecharDeletar(); } });

// Limita tags a 5 no modal de edição
document.querySelectorAll('#editTagsGrid input[type=checkbox]').forEach(cb => {
  cb.addEventListener('change', function() {
    const sel = document.querySelectorAll('#editTagsGrid input:checked');
    if (sel.length > 5) this.checked = false;
  });
});
</script>

<!-- ========== MODAL DELETAR NOTÍCIA ========== -->
<?php if ($pode_noticias): ?>
<div class="adm-modal-overlay" id="modalDeletarNoticia">
  <div class="adm-modal" style="max-width:420px;">
    <h5 class="fw-bold mb-2" style="color:#1a0a4a;">Deletar notícia?</h5>
    <p class="text-muted mb-4" id="modalDeletarNoticiaNome" style="font-size:.9rem;"></p>
    <div class="d-flex gap-3 justify-content-end">
      <button class="btn-modal-cancelar" onclick="fecharDeletarNoticia()">Cancelar</button>
      <a href="#" id="btnConfirmarDeletarNoticia" class="btn-modal-publicar"
         style="background:linear-gradient(135deg,#ef4444,#dc2626);text-decoration:none;">
        <i class="bi bi-trash-fill me-1"></i> Deletar
      </a>
    </div>
  </div>
</div>

<!-- ========== MODAL EDITAR NOTÍCIA ========== -->
<div class="adm-modal-overlay" id="modalEditarNoticia">
  <div class="adm-modal" style="max-width:660px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;">
        <i class="bi bi-pencil-square me-2"></i>Editar Notícia
      </h5>
      <button class="btn-close" onclick="fecharEditarNoticia()"></button>
    </div>

    <form action="ctrl-editar-noticia.php" method="POST" id="formEditarNoticia" enctype="multipart/form-data">
      <input type="hidden" name="noticia_id" id="editNoticiaId">

      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" id="editNoticiaTitulo"
               class="form-control adm-form-input" required maxlength="200">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Resumo *</label>
        <textarea name="resumo" id="editNoticiaResumo"
                  class="form-control adm-form-input" rows="2"
                  required maxlength="300"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" id="editNoticiaConteudo"
                  class="form-control adm-form-input" rows="6"
                  required minlength="50" style="resize:vertical;"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">
          Imagem <span class="text-muted fw-normal">(opcional — deixe vazio para manter a atual)</span>
        </label>
        <input type="file" name="imagem" id="editNoticiaImagem"
               class="form-control adm-form-input"
               accept="image/jpeg,image/png,image/webp,image/gif">
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Categoria *</label>
        <select name="categoria" id="editNoticiaCategoria" class="form-select adm-form-input" required>
          <?php
          $cats_disponiveis = ['lançamento','rumor','análise','urgente','review',
                               'prévia','atualização','evento','hardware','negócios',
                               'curiosidade','lista'];
          foreach ($cats_disponiveis as $c):
          ?>
            <option value="<?= $c ?>"><?= ucfirst($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharEditarNoticia()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar">
          <i class="bi bi-check-lg me-1"></i> Salvar
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
// ===== MODAL DELETAR NOTÍCIA =====
function confirmarDeletarNoticia(id, titulo) {
  document.getElementById('modalDeletarNoticiaNome').textContent =
    'Tem certeza que deseja deletar "' + titulo + '"? Esta ação não pode ser desfeita.';
  document.getElementById('btnConfirmarDeletarNoticia').href = 'ctrl-deletar-noticia.php?id=' + id;
  document.getElementById('modalDeletarNoticia').style.display = 'flex';
}
function fecharDeletarNoticia() {
  document.getElementById('modalDeletarNoticia').style.display = 'none';
}
document.getElementById('modalDeletarNoticia')?.addEventListener('click', function(e) {
  if (e.target === this) fecharDeletarNoticia();
});

// ===== MODAL EDITAR NOTÍCIA =====
function abrirEditarNoticia(id, titulo, resumo, conteudo, imagem, categoria) {
  document.getElementById('editNoticiaId').value        = id;
  document.getElementById('editNoticiaTitulo').value    = titulo;
  document.getElementById('editNoticiaResumo').value    = resumo;
  document.getElementById('editNoticiaConteudo').value  = conteudo;
  // editNoticiaImagem é file input — não pode ser preenchido via JS por segurança
  const sel = document.getElementById('editNoticiaCategoria');
  if (sel) sel.value = categoria;
  document.getElementById('modalEditarNoticia').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharEditarNoticia() {
  document.getElementById('modalEditarNoticia').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('modalEditarNoticia')?.addEventListener('click', function(e) {
  if (e.target === this) fecharEditarNoticia();
});

// Fecha todos com ESC
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    fecharEditarNoticia();
    fecharDeletarNoticia();
    fecharModalSenha();
  }
});
</script>

<!-- ========== MODAL TROCAR SENHA ========== -->
<div class="adm-modal-overlay" id="modalSenha">
  <div class="adm-modal" style="max-width:440px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;">
        <i class="bi bi-shield-lock me-2"></i>Trocar Senha
      </h5>
      <button class="btn-close" onclick="fecharModalSenha()"></button>
    </div>

    <form action="ctrl-trocar-senha.php" method="POST" id="formSenha">

      <div class="mb-3">
        <label class="form-label fw-semibold">Senha atual <span style="color:#ef4444;">*</span></label>
        <div class="position-relative">
          <input type="password" name="senha_atual" id="senhaAtual"
                 class="form-control adm-form-input" required
                 placeholder="Digite sua senha atual">
          <button type="button" class="btn-toggle-senha" onclick="toggleSenha('senhaAtual', this)" tabindex="-1">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Nova senha <span style="color:#ef4444;">*</span></label>
        <div class="position-relative">
          <input type="password" name="senha_nova" id="senhaNova"
                 class="form-control adm-form-input" required minlength="6"
                 placeholder="Mínimo 6 caracteres">
          <button type="button" class="btn-toggle-senha" onclick="toggleSenha('senhaNova', this)" tabindex="-1">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Confirmar nova senha <span style="color:#ef4444;">*</span></label>
        <div class="position-relative">
          <input type="password" name="senha_confirma" id="senhaConfirma"
                 class="form-control adm-form-input" required
                 placeholder="Repita a nova senha">
          <button type="button" class="btn-toggle-senha" onclick="toggleSenha('senhaConfirma', this)" tabindex="-1">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharModalSenha()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar">
          <i class="bi bi-check-lg me-1"></i> Salvar nova senha
        </button>
      </div>

    </form>
  </div>
</div>

<style>
.btn-toggle-senha {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: #aaa; cursor: pointer; padding: 4px;
  line-height: 1;
}
.btn-toggle-senha:hover { color: #611DF2; }
</style>

<script>
function abrirModalSenha() {
  document.getElementById('formSenha').reset();
  document.getElementById('modalSenha').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.getElementById('senhaAtual').focus(), 100);
}
function fecharModalSenha() {
  document.getElementById('modalSenha').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('modalSenha').addEventListener('click', function(e) {
  if (e.target === this) fecharModalSenha();
});

function toggleSenha(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>

<script src="../tempo-relativo.js"></script>
</body>
</html>