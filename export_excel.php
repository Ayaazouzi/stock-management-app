<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$host = "localhost";
$dbname = "gestion de stock cofat";
$username = "root";
$password = "";

try {
    // Connexion à la base de données
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les données des stocks avec une jointure sur la table articles
    $stmtStock = $conn->query("
        SELECT 
            stock.id_stock, 
            articles.nom AS article_name, 
            stock.quantite_stock, 
            stock.date_ajout, 
            stock.nom_emplacement, 
            stock.quantite
        FROM stock 
        JOIN articles ON stock.id_article = articles.id_article
    ");
    // Récupération des résultats sous forme de tableau associatif
    $stock = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Affichage d'une erreur en cas d'échec de connexion à la base
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

// Création d'un nouvel objet Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Ajouter les en-têtes de colonnes
$sheet->setCellValue('A1', 'ID Stock');
$sheet->setCellValue('B1', 'Article');
$sheet->setCellValue('C1', 'Quantité Stock');
$sheet->setCellValue('D1', 'Date d\'Ajout');
$sheet->setCellValue('E1', 'Nom Emplacement');
$sheet->setCellValue('F1', 'Quantité');

// Remplir les données dans le fichier Excel
$row = 2; // Ligne de départ pour les données
foreach ($stock as $stockItem) {
    $sheet->setCellValue('A' . $row, $stockItem['id_stock']);
    $sheet->setCellValue('B' . $row, $stockItem['article_name']);
    $sheet->setCellValue('C' . $row, $stockItem['quantite_stock']);
    $sheet->setCellValue('D' . $row, $stockItem['date_ajout']);
    $sheet->setCellValue('E' . $row, $stockItem['nom_emplacement']);
    $sheet->setCellValue('F' . $row, $stockItem['quantite']);
    $row++;
}

// Générer le fichier Excel et l'envoyer comme téléchargement
$writer = new Xlsx($spreadsheet);
$filename = 'stock_data.xlsx'; // Nom du fichier à télécharger

// Définition des en-têtes HTTP pour forcer le téléchargement du fichier
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
// Envoie le fichier généré directement au navigateur
$writer->save('php://output');
?>