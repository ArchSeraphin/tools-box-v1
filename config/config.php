<?php
// Configuration de la base de données sécurisée (PDO)

// Paramètres de connexion
$host = 'localhost';
$dbname = 'voila_voila_hub';
$user = 'toolsbox';
$password = '4?NlDgB4u#d2kieh';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Options PDO pour la sécurité et la gestion des erreurs
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lance des exceptions en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retourne les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES => false, // Désactive l'émulation des requêtes préparées (protection SQL Injection)
];

try {
    // Création de l'instance PDO
    $pdo = new PDO($dsn, $user, $password, $options);
}
catch (\PDOException $e) {
    // En cas d'erreur, on loggue le message interne mais on ne l'affiche pas à l'utilisateur
    // pour éviter de divulguer des informations sensibles (chemins, users, etc.)
    error_log($e->getMessage());
    die("Erreur de connexion à la base de données.");
}
?>