<?php
session_start();

// Redirect to dashboard if session is already active
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Credentials matching the specification
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'admin';
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Usuário ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FATEC-SP - Login Administrativo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <a href="index.php" class="logo-link">
                <img src="https://www.cps.sp.gov.br/wp-content/uploads/sites/1/2017/06/fatec.png" 
                     alt="Fatec Logo" 
                     class="logo-img" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="logo-fallback" style="display:none;">
                    FATEC <span>SÃO PAULO</span>
                </div>
            </a>
            <nav class="header-nav">
                <a href="index.php" class="nav-btn btn-outline">Voltar ao Formulário</a>
            </nav>
        </div>
    </header>

    <!-- Main Content Wrapper for login -->
    <main class="login-wrapper">
        <div class="card">
            <h1 class="card-title">Painel Administrativo</h1>
            <p class="card-subtitle">Por favor, faça login com suas credenciais de administrador.</p>

            <!-- Error Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" autocomplete="off">
                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Digite o usuário" required autofocus>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Digite a senha" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Acessar Painel</button>
            </form>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>FATEC-SP – Programação de Scripts &copy; <?php echo date('Y'); ?></p>
    </footer>

</body>
</html>
