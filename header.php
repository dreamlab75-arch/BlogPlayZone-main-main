<?php
// Inicia sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('img_url')) require_once __DIR__ . '/util/upload.php';

// $base é definido pela página que inclui este header.
// Na raiz: $base = '' (ou não definido)
// Em subpastas: $base = '../'
if (!isset($base)) $base = '';
?>

<nav class="navbar navbar-expand-lg navbar-playzone fixed-top">
  <div class="container">

    <!-- LOGO -->
    <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>index.php">
      <img src="<?= $base ?>img/BlogLogo-01-01.svg" alt="PlayZone">
    </a>

    <!-- BOTÃO MOBILE -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border-color: rgba(255,255,255,0.5);">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

      <!-- LINKS centralizados -->
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link nav-link-playzone" href="<?= $base ?>index.php">Início</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-playzone" href="<?= $base ?>posts-index/posts-view.php">Posts</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-playzone" href="<?= $base ?>noticias-index/noticias-view.php">Notícias</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-playzone" href="<?= $base ?>quem-somos.php">Sobre</a>
        </li>
      </ul>

      <!-- DIREITA: busca + conta -->
      <div class="d-flex align-items-center gap-3">

        <div class="search-wrapper">
          <input type="text" class="form-control search-box" placeholder="Buscar...">
          <i class="bi bi-search search-icon"></i>
        </div>

        <div class="account-dropdown">
          <button class="btn btn-account" id="accountToggle" onclick="toggleAccountMenu()">
            <?php if (isset($_SESSION["usuario_avatar"]) && $_SESSION["usuario_avatar"]): ?>
              <img src="<?= htmlspecialchars(img_url($_SESSION['usuario_avatar'], $base . 'img/avatar-default.png')) ?>"
                   class="account-avatar" alt="Avatar"
                   <?= img_onerror($base . 'img/avatar-default.png') ?>>
            <?php else: ?>
              <i class="bi bi-person-circle"></i>
            <?php endif; ?>
            <i class="bi bi-chevron-down chevron-icon" id="accountChevron"></i>
          </button>
          <div class="account-menu" id="accountMenu">
            <?php if (isset($_SESSION["usuario_id"])): ?>
              <?php if ($_SESSION["usuario_perfil"] === "adm"): ?>
                <a href="<?= $base ?>adm/painel-adm.php" class="btn btn-signup w-100 mb-2 d-block text-center">Painel ADM</a>
              <?php endif; ?>
              <a href="<?= $base ?>perfil/painel-usuario.php" class="btn btn-signup w-100 mb-2 d-block text-center">Minha Conta</a>
              <a href="<?= $base ?>auth/ctrl-logout.php" class="btn btn-login w-100 d-block text-center">Sair</a>
            <?php else: ?>
              <a href="<?= $base ?>auth/login.php" class="btn btn-login w-100 d-block text-center">Login</a>
              <a href="<?= $base ?>auth/cadastro.php" class="btn btn-signup w-100 mt-2 d-block">Cadastrar-se</a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</nav>

<script src="<?= $base ?>header.js"></script>