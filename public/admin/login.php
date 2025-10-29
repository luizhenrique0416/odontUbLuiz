<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use App\Auth;

Auth::startSession();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u !== '' && $p !== '' && Auth::login($u, $p)) {
        header('Location: /admin/patients.php');
        exit;
    }
    $error = 'Usuário ou senha inválidos.';
}
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../assets/css/admin/login.css">
<title>Login do Estagiário</title>

<div class="card">
  <h1>Login</h1>
  <form class="form" method="post" action="">
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>
    <div><input name="username" placeholder="Usuário" required></div>
    <div><input type="password" name="password" placeholder="Senha" required></div>
    <button>Entrar</button>
    <div class="muted">usuário: <code>estagiario</code> • senha: <code>123456</code></div>
  </form>
</div>
