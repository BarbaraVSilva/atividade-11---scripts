<?php
session_start();
require_once 'db.php';

// Access control: enforce active administrator session
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Feedback alerts mapping
$status = $_GET['status'] ?? '';
$alert_class = '';
$alert_message = '';

if ($status === 'deleted') {
    $alert_class = 'alert-success';
    $alert_message = 'O contato foi removido com sucesso.';
} elseif ($status === 'updated') {
    $alert_class = 'alert-success';
    $alert_message = 'A mensagem do contato foi atualizada com sucesso.';
} elseif ($status === 'not_found') {
    $alert_class = 'alert-danger';
    $alert_message = 'O contato solicitado não foi encontrado no sistema.';
} elseif ($status === 'error') {
    $alert_class = 'alert-danger';
    $alert_message = 'Ocorreu um erro ao processar a ação.';
}

// Read search and filter criteria from GET params
$search = trim($_GET['search'] ?? '');
$days = isset($_GET['days']) ? intval($_GET['days']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Build search SQL parts
$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(nome LIKE :search_nome OR email LIKE :search_email)";
    $params['search_nome'] = "%$search%";
    $params['search_email'] = "%$search%";
}

if (in_array($days, [7, 30, 90])) {
    $where_clauses[] = "criado_em >= DATE_SUB(NOW(), INTERVAL " . intval($days) . " DAY)";
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// 1. Get total count for pagination calculations
try {
    $count_query = "SELECT COUNT(*) FROM contatos $where_sql";
    $stmt = $pdo->prepare($count_query);
    foreach ($params as $key => $val) {
        $type = ($key === 'days') ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(":$key", $val, $type);
    }
    $stmt->execute();
    $total_contacts = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Erro na consulta de contagem: " . $e->getMessage());
}

// Pagination constants
$limit = 10;
$total_pages = ceil($total_contacts / $limit);

// Clamp page value within valid boundaries
if ($page < 1) {
    $page = 1;
} elseif ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;

// 2. Fetch the paginated contact records
try {
    $select_query = "SELECT * FROM contatos $where_sql ORDER BY criado_em DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($select_query);
    
    // Bind dynamic filters
    foreach ($params as $key => $val) {
        $type = ($key === 'days') ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(":$key", $val, $type);
    }
    // Bind pagination limits
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro na consulta de listagem: " . $e->getMessage());
}

// Helper to construct query URLs while maintaining search/filter state
function build_url($page_number) {
    $query_params = $_GET;
    $query_params['page'] = $page_number;
    return 'admin.php?' . http_build_query($query_params);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - FATEC-SP</title>
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
                <a href="index.php" class="nav-btn btn-outline">Ir para o Formulário</a>
                <a href="logout.php" class="nav-btn btn-admin" style="background-color:#495057; border-color:#495057;">Sair</a>
            </nav>
        </div>
    </header>

    <!-- Main Dashboard Container -->
    <main>
        <div class="admin-header">
            <div>
                <h1 class="admin-title">Painel de <span>Contatos</span></h1>
                <p style="color: var(--grey-600); margin-top: 0.25rem;">Gerencie os contatos recebidos através do site institucional.</p>
            </div>
            <div style="background-color: var(--grey-200); padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-weight:600; font-size: 0.9rem;">
                Total: <?php echo $total_contacts; ?> contato(s)
            </div>
        </div>

        <!-- Alert messages -->
        <?php if (!empty($alert_message)): ?>
            <div class="alert <?php echo $alert_class; ?>" role="alert">
                <?php if ($status === 'deleted' || $status === 'updated'): ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?php else: ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?php endif; ?>
                <div><?php echo htmlspecialchars($alert_message); ?></div>
            </div>
        <?php endif; ?>

        <!-- Search and Date Filter Form -->
        <form method="GET" action="admin.php" class="filter-bar">
            <!-- Text Search -->
            <div class="filter-group">
                <label for="search" class="form-label" style="font-size:0.75rem; margin-bottom:0.25rem;">Buscar por Nome ou E-mail</label>
                <input type="text" name="search" id="search" class="form-control" 
                       placeholder="Digite o nome ou e-mail..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <!-- Date range dropdown filter -->
            <div class="filter-group" style="max-width: 250px;">
                <label for="days" class="form-label" style="font-size:0.75rem; margin-bottom:0.25rem;">Filtro por Data</label>
                <select name="days" id="days" class="form-control">
                    <option value="" <?php echo $days === 0 ? 'selected' : ''; ?>>Todo o período</option>
                    <option value="7" <?php echo $days === 7 ? 'selected' : ''; ?>>Últimos 7 dias</option>
                    <option value="30" <?php echo $days === 30 ? 'selected' : ''; ?>>Últimos 30 dias</option>
                    <option value="90" <?php echo $days === 90 ? 'selected' : ''; ?>>Últimos 90 dias</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">Filtrar</button>
                <?php if ($search !== '' || $days > 0): ?>
                    <a href="admin.php" class="btn btn-secondary" style="padding: 0.75rem 1.25rem;">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Contacts Table View -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Contato</th>
                        <th style="width: 15%;">Telefone</th>
                        <th style="width: 35%;">Mensagem</th>
                        <th style="width: 15%;">Data de Envio</th>
                        <th style="width: 10%; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--grey-600); padding: 3rem;">
                                Nenum contato cadastrado ou encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td>
                                    <div class="contact-name"><?php echo htmlspecialchars($contact['nome']); ?></div>
                                    <div class="contact-email"><?php echo htmlspecialchars($contact['email']); ?></div>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size:0.9rem; font-weight:500;">
                                        <?php echo htmlspecialchars($contact['telefone']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="contact-message"><?php echo htmlspecialchars($contact['mensagem']); ?></div>
                                </td>
                                <td>
                                    <div class="contact-date">
                                        <?php echo date('d/m/Y H:i', strtotime($contact['criado_em'])); ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="actions-cell">
                                        <a href="edit_message.php?id=<?php echo $contact['id']; ?>" class="btn btn-secondary btn-icon" title="Editar Mensagem">
                                            Editar
                                        </a>
                                        <a href="delete_contact.php?id=<?php echo $contact['id']; ?>" 
                                           class="btn btn-danger btn-icon" 
                                           onclick="return confirm('Tem certeza que deseja excluir o contato de <?php echo htmlspecialchars($contact['nome'], ENT_QUOTES, 'UTF-8'); ?>?');"
                                           title="Excluir Contato">
                                            Excluir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Exibindo página <?php echo $page; ?> de <?php echo $total_pages; ?> (contatos <?php echo $offset + 1; ?> a <?php echo min($offset + $limit, $total_contacts); ?> de <?php echo $total_contacts; ?>)
                </div>
                <nav>
                    <ul class="pagination">
                        <!-- First & Previous -->
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a href="<?php echo build_url(1); ?>">&laquo;</a></li>
                            <li class="page-item"><a href="<?php echo build_url($page - 1); ?>">&lsaquo;</a></li>
                        <?php else: ?>
                            <li class="page-item disabled"><span>&laquo;</span></li>
                            <li class="page-item disabled"><span>&lsaquo;</span></li>
                        <?php endif; ?>

                        <!-- Numbered page items -->
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php if ($i === $page): ?>
                                    <span><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="<?php echo build_url($i); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <!-- Next & Last -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a href="<?php echo build_url($page + 1); ?>">&rsaquo;</a></li>
                            <li class="page-item"><a href="<?php echo build_url($total_pages); ?>">&raquo;</a></li>
                        <?php else: ?>
                            <li class="page-item disabled"><span>&rsaquo;</span></li>
                            <li class="page-item disabled"><span>&raquo;</span></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer Section -->
    <footer>
        <p>FATEC-SP – Programação de Scripts &copy; <?php echo date('Y'); ?></p>
    </footer>

</body>
</html>
