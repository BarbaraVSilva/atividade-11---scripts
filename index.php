<?php
require_once 'db.php';

$success_message = '';
$error_message = '';

// Variables to keep inputs "sticky" on validation failure
$nome = '';
$email = '';
$telefone = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter e limpar dados de entrada (o PDO se encarrega de evitar SQL injection de forma segura)
    $nome = trim($_POST['nome'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    // Server-side validation
    if (empty($nome) || empty($email) || empty($telefone) || empty($mensagem)) {
        $error_message = 'Todos os campos do formulário são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Por favor, insira um endereço de e-mail válido.';
    } elseif (!preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone)) {
        $error_message = 'O formato do telefone deve ser (XX) 9XXXX-XXXX ou (XX) XXXX-XXXX.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM contatos WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error_message = 'Este e-mail já está cadastrado em nosso sistema!';
            } else {
                // Insert contact info into database
                $stmt = $pdo->prepare("INSERT INTO contatos (nome, email, telefone, mensagem) VALUES (:nome, :email, :telefone, :mensagem)");
                $stmt->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => $telefone,
                    'mensagem' => $mensagem
                ]);
                $success_message = 'Seu contato foi registrado com sucesso!';
                // Clear sticky values on success
                $nome = '';
                $email = '';
                $telefone = '';
                $mensagem = '';
            }
        } catch (PDOException $e) {
            $error_message = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Contatos da Faculdade de Tecnologia de São Paulo (Fatec-SP)">
    <title>FATEC-SP - Cadastro de Contato</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <a href="index.php" class="logo-link">
                <!-- Fatec Official Logo with SVG Text fallback -->
                <img src="https://www.cps.sp.gov.br/wp-content/uploads/sites/1/2017/06/fatec.png" 
                     alt="Fatec Logo" 
                     class="logo-img" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="logo-fallback" style="display:none;">
                    FATEC <span>SÃO PAULO</span>
                </div>
            </a>
            <nav class="header-nav">
                <a href="admin.php" class="nav-btn btn-admin">Painel Administrativo</a>
            </nav>
        </div>
    </header>

    <!-- Main Registration Section -->
    <main>
        <div class="card" style="max-width: 700px; margin: 0 auto;">
            <h1 class="card-title">Fale Conosco</h1>
            <p class="card-subtitle">Preencha o formulário abaixo para registrar o seu contato. A equipe da Fatec-SP responderá em breve.</p>

            <!-- Success and Error Alerts -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <div><?php echo $success_message; ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div><?php echo $error_message; ?></div>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" autocomplete="off" id="contactForm">
                <!-- Name -->
                <div class="form-group">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Digite seu nome completo" required value="<?php echo htmlspecialchars($nome); ?>">
                </div>

                <div class="form-row">
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="exemplo@email.com" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>

                    <!-- Phone with Mask and Regex Pattern validation -->
                    <div class="form-group">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" id="telefone" name="telefone" class="form-control" 
                               placeholder="(11) 91234-5678" 
                               required 
                               pattern="\(\d{2}\) \d{4,5}-\d{4}" 
                               title="Digite no formato (XX) XXXXX-XXXX ou (XX) XXXX-XXXX"
                               value="<?php echo htmlspecialchars($telefone); ?>">
                    </div>
                </div>

                <!-- Message -->
                <div class="form-group">
                    <label for="mensagem" class="form-label">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" class="form-control" placeholder="Escreva sua mensagem aqui..." required><?php echo htmlspecialchars($mensagem); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Enviar Cadastro</button>
            </form>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>FATEC-SP – Programação de Scripts &copy; <?php echo date('Y'); ?></p>
    </footer>

    <!-- Phone mask javascript helper -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('telefone');

            phoneInput.addEventListener('input', function(e) {
                let input = e.target.value.replace(/\D/g, '');
                
                // Limit to 11 digits (2 for DDD + 9 for number)
                if (input.length > 11) {
                    input = input.substring(0, 11);
                }
                
                let formatted = '';
                if (input.length > 0) {
                    formatted += '(' + input.substring(0, 2);
                }
                if (input.length > 2) {
                    formatted += ') ';
                    if (input.length <= 10) {
                        // Landline formatting: (XX) XXXX-XXXX
                        formatted += input.substring(2, 6);
                        if (input.length > 6) {
                            formatted += '-' + input.substring(6, 10);
                        }
                    } else {
                        // Mobile formatting: (XX) XXXXX-XXXX
                        formatted += input.substring(2, 7);
                        formatted += '-' + input.substring(7, 11);
                    }
                }
                e.target.value = formatted;
            });
        });
    </script>
</body>
</html>
