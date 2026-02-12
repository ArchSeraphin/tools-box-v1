<?php
// Configuration base de données
// Remplissez ces variables avec vos identifiants Plesk/MySQL
$db_host = 'localhost';
$db_name = 'voila_voila_hub';
$db_user = 'toolsbox';
$db_pass = '4?NlDgB4u#d2kieh';
$charset = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// Options PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Connexion
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
}
catch (\PDOException $e) {
    // Affiche un message d'erreur explicite au lieu de l'erreur 500
    // En production, il est recommandé de logger l'erreur plutôt que de l'afficher.
    // Mais pour le debug actuel, on affiche le message.
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>