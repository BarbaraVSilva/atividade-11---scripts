<?php
// Detecta se a aplicação está rodando em ambiente local (localhost ou 127.0.0.1)
$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) 
             || (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1');

if ($is_localhost) {
    // Configuração para ambiente de desenvolvimento local (ex: XAMPP, WAMP, Laragon)
    $host = 'localhost';
    $dbname = 'fatec_contatos';
    $username = 'root';
    $password = ''; // Padrão local sem senha
} else {
    // Configuração para o ambiente de produção do InfinityFree
    // IMPORTANTE: Altere os valores abaixo com os dados reais exibidos no seu painel do InfinityFree (MySQL Databases).
    $host = 'sqlXXX.infinityfree.com';        // Endereço do Servidor MySQL (ex: sql301.infinityfree.com)
    $dbname = 'if0_XXXXXX_fatec_contatos';    // Nome do banco de dados (ex: if0_38210391_fatec_contatos)
    $username = 'if0_XXXXXX';                 // Usuário do banco de dados (ex: if0_38210391)
    $password = 'SUA_SENHA_MYSQL_INFINITY';   // Senha gerada pelo painel (pode ser encontrada na área do cliente)
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
