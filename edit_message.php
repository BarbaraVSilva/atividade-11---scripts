<?php
session_start();
require_once 'db.php';

// Access control: enforce active administrator session
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check contact ID parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: admin.php');
    exit;
}

// Fetch current contact details from database
try {
    $stmt = $pdo->prepare("SELECT * FROM contatos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        header('Location: admin.php?status=not_found');
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao buscar o contato: " . $e->getMessage());
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = trim($_POST['mensagem'] ?? '');

    if (empty($mensagem)) {
        $error_message = 'A mensagem do contato não pode estar vazia.';
    } else {
        try {
            // Update the contact's message in the database
            $stmt = $pdo->prepare("UPDATE contatos SET mensagem = :mensagem WHERE id = :id");
            $stmt->execute([
                'mensagem' => $mensagem,
                'id' => $id
            ]);
            
            // Redirect back to admin.php with updated feedback
            header('Location: admin.php?status=updated');
            exit;
        } catch (PDOException $e) {
            $error_message = 'Erro de banco de dados ao atualizar a mensagem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mensagem - FATEC-SP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <a href="admin.php" class="logo-link">
                <img src="https://www.cps.sp.gov.br/wp-content/uploads/sites/1/2017/06/fatec.png" 
                     alt="Fatec Logo" 
                     class="logo-img" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="logo-fallback" style="display:none;">
                    FATEC <span>SÃO PAULO</span>
                </div>
            </a>
            <nav class="header-nav">
                <a href="admin.php" class="nav-btn btn-outline">Voltar ao Painel</a>
                <a href="logout.php" class="nav-btn btn-admin" style="background-color:#495057; border-color:#495057;">Sair</a>
            </nav>
        </div>
    </header>

    <!-- Main Content Container -->
    <main>
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h1 class="card-title">Editar Mensagem</h1>
            <p class="card-subtitle">Modifique a mensagem enviada pelo contato abaixo. Nome, e-mail e telefone são somente leitura.</p>

            <!-- Error Alerts -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div><?php echo $error_message; ?></div>
                </div>
            <?php endif; ?>

            <form action="edit_message.php?id=<?php echo $id; ?>" method="POST" autocomplete="off">
                
                <!-- Readonly Name -->
                <div class="form-group">
                    <label class="form-label">Nome do Contato</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($contact['nome']); ?>" readonly style="background-color: var(--grey-100); color: var(--grey-600); cursor: not-allowed;">
                </div>

                <div class="form-row">
                    <!-- Readonly Email -->
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contact['email']); ?>" readonly style="background-color: var(--grey-100); color: var(--grey-600); cursor: not-allowed;">
                    </div>

                    <!-- Readonly Phone -->
                    <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contact['telefone']); ?>" readonly style="background-color: var(--grey-100); color: var(--grey-600); cursor: not-allowed;">
                    </div>
                </div>

                <!-- Editable Message -->
                <div class="form-group">
                    <label for="mensagem" class="form-label">Mensagem (Editável)</label>
                    <textarea id="mensagem" name="mensagem" class="form-control" required placeholder="Digite a nova mensagem..."><?php echo htmlspecialchars($contact['mensagem']); ?></textarea>
                </div>

                <!-- Action Button Controls -->
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Salvar Alterações</button>
                    <a href="admin.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancelar</a>
                </div>

            </form>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>FATEC-SP – Programação de Scripts &copy; <?php echo date('Y'); ?></p>
    </footer>

</body>
</html>
