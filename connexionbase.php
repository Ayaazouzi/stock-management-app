<?php
// Paramètres de connexion
$host = "localhost"; 
$dbname = "gestion de stock cofat";
$username = "root";
$password = ""; 

try {
    // Création de la connetxion PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Activation des exceptions PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données.";
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
