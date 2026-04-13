<?php
session_start();


if (!isset($_SESSION["usuario_perfil"]) || $_SESSION["usuario_perfil"] !== "adm") {
    header("Location: ../auth/login.php?erro=Acesso restrito");
    exit;
}

$string_de_conexao = "sqlite:../banco.db";
$pdo = new \PDO($string_de_conexao);

$sql_perfis = "SELECT id, tipo FROM perfil ORDER BY tipo";
$result_perfis = $pdo->query($sql_perfis);


$sql = "
    SELECT usuarios.id, usuarios.nome, usuarios.email, perfil.tipo as perfil_tipo
    FROM usuarios
    JOIN perfil ON usuarios.perfil_id = perfil.id
    ORDER BY usuarios.id ASC
";
$result_set_usuarios = $pdo->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel ADM - PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<script>

document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(function() {
        alert.remove();
      }, 500);
    }, 3000);
  });
});
</script>
<body class="adm-body">
  <div class="adm-layout">


    <aside class="adm-sidebar">
      <div class="adm-sidebar-logo">
        <img src="../img/BlogLogo-01-01.svg" alt="PlayZone">
      </div>
      <nav class="adm-nav">
        <p class="adm-nav-label">Gerenciar</p>
        <a href="painel-adm.php" class="adm-nav-item active">
          <i class="bi bi-people-fill"></i> Usuários
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


    <main class="adm-main">
      <div class="adm-topbar">
        <h4 class="adm-page-titulo">Usuários</h4>
        <span class="adm-usuario-logado">
          <i class="bi bi-person-circle"></i> <?= $_SESSION["usuario_nome"] ?>
        </span>
      </div>

      <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
      <?php endif; ?>


      <div class="adm-add-user-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="adm-section-titulo mb-0">Adicionar Novo Usuário</h5>
          <button class="btn btn-adm-add" onclick="openAddUserModal()">
          <i class="bi bi-person-plus me-2"></i> Novo Usuário
          </button>
        </div>
      </div>


      <div class="adm-card">
        <table class="table adm-table">
          <thead>
            <tr>
              <th class="coluna-id">#</th>
              <th>Nome</th>
              <th>Email</th>
              <th>Perfil</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php while ($uma_linha = $result_set_usuarios->fetch(\PDO::FETCH_ASSOC)): ?>
              <?php
                $id     = $uma_linha["id"];
                $nome   = $uma_linha["nome"];
                $email  = $uma_linha["email"];
                $perfil = $uma_linha["perfil_tipo"];
              ?>
              <tr>
                <td class="coluna-id"><?= $id ?></td>
                <td><?= $nome ?></td>
                <td><?= $email ?></td>
                <td><span class="adm-badge adm-badge--<?= $perfil ?>"><?= $perfil ?></span></td>
                <td>
                <a href="ctrl-edit-usuario.php?id=<?= $id ?>"

                       class="btn-adm-editar">
                       <i class="bi bi-pencil-square"></i>
                    </a>
                  <?php if ($id !== (int)$_SESSION["usuario_id"]): ?>
                    <a href="ctrl-apagar-usuario.php?id=<?= $id ?>"
                       onclick="return confirm('Deletar <?= $nome ?>?')"
                       class="btn-adm-deletar">
                      <i class="bi bi-trash-fill"></i>
                    </a>           
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>


  <div class="adm-modal-overlay" id="addUserModal">
    <div class="adm-modal">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold text-dark">Novo Usuário</h5>
        <button class="btn-close" onclick="closeAddUserModal()"></button>
      </div>
      
      <form id="formAddUser" action="ctrl-add-usuario.php" method="POST">
        <div class="adm-form-group">
          <label class="form-label fw-semibold text-dark">Nome</label>
          <input type="text" class="form-control adm-form-input" name="nome" required>
        </div>
        
        <div class="adm-form-group">
          <label class="form-label fw-semibold text-dark">Email</label>
          <input type="email" class="form-control adm-form-input" name="email" required>
        </div>
        
        <div class="adm-form-group">
          <label class="form-label fw-semibold text-dark">Senha</label>
          <input type="password" class="form-control adm-form-input" name="senha" required minlength="6">
        </div>
        
        <div class="adm-form-group">
          <label class="form-label fw-semibold text-dark">Perfil</label>
          <select class="form-select adm-form-input" name="perfil_id" required>
            <option value="">Selecione o perfil</option>
            <?php 
            $pdo->query("SELECT id, tipo FROM perfil ORDER BY tipo")->fetchAll(); // Reset
            while ($perfil = $result_perfis->fetch(\PDO::FETCH_ASSOC)): ?>
              <option value="<?= $perfil['id'] ?>"><?= htmlspecialchars($perfil['tipo']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
        <label class="form-label fw-semibold text-dark">Avatar</label>
        <input type="text" name="avatar" class="form-control auth-input" placeholder="https://...">
        </div>
        
        <div class="d-flex gap-3 justify-content-end mt-4">
          <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancelar</button>
          <button type="submit" class="btn btn-adm-add">
            <i></i> Criar Usuário
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openAddUserModal() {
      document.getElementById('addUserModal').style.display = 'flex';
      document.getElementById('formAddUser').reset();
    }
    
    function closeAddUserModal() {
      document.getElementById('addUserModal').style.display = 'none';
    }
    
    document.getElementById('addUserModal').addEventListener('click', function(e) {
      if (e.target === this) closeAddUserModal();
    });
    
    document.querySelector('input[name="email"]').addEventListener('blur', function() {
      const email = this.value;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && !emailRegex.test(email)) {
        this.setCustomValidity('Email inválido');
      } else {
        this.setCustomValidity('');
      }
    });
  </script>
</body>
</html>