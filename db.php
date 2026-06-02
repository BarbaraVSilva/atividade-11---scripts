<?php
// Detecta se a aplicação está rodando em ambiente local (localhost ou 127.0.0.1)
$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']);

if ($is_localhost) {
    // Configuração para ambiente de desenvolvimento local (ex: XAMPP, WAMP, Laragon)
    $host = '127.0.0.1'; // Usar 127.0.0.1 força conexão TCP e evita erro de socket
    $dbname = 'fatec_contatos';
    $username = 'root';
    $password = ''; // Padrão local sem senha
} else {
    // Configuração para o ambiente de produção do InfinityFree
    $host = 'sql201.infinityfree.com';        // Endereço do Servidor MySQL do seu painel
    $dbname = 'if0_41931839_atv_11';          // Nome do banco de dados real
    $username = 'if0_41931839';               // Usuário do banco de dados real
    $password = 'YVyulvX9myI7';               // Senha do MySQL exibida no painel (corrigido: letra l minúscula)
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
