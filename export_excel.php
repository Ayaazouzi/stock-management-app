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

    // CORRECTION: Requête adaptée aux colonnes existantes dans votre table stock
    $stmtStock = $conn->query("
        SELECT 
            stock.id_stock, 
            articles.nom AS article_name, 
            articles.prix AS prix_article,
            articles.quantite AS quantite_article,
            stock.quantite_stock, 
            stock.nom_emplacement
        FROM stock 
        JOIN articles ON stock.id_article = articles.id_article
        ORDER BY stock.id_stock
    ");
    
    $stock = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

// Création d'un nouvel objet Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// CORRECTION: En-têtes adaptés aux colonnes disponibles
$sheet->setCellValue('A1', 'ID Stock');
$sheet->setCellValue('B1', 'Article');
$sheet->setCellValue('C1', 'Prix Article (TND)');
$sheet->setCellValue('D1', 'Quantité Article');
$sheet->setCellValue('E1', 'Quantité en Stock');
$sheet->setCellValue('F1', 'Emplacement');

// Style pour les en-têtes
$headerStyle = [
    'font' => ['bold' => true, 'size' => 12],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '007bff']
    ],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
];
$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

// Remplir les données
$row = 2;
foreach ($stock as $stockItem) {
    $sheet->setCellValue('A' . $row, $stockItem['id_stock']);
    $sheet->setCellValue('B' . $row, $stockItem['article_name']);
    $sheet->setCellValue('C' . $row, $stockItem['prix_article']);
    $sheet->setCellValue('D' . $row, $stockItem['quantite_article']);
    $sheet->setCellValue('E' . $row, $stockItem['quantite_stock']);
    $sheet->setCellValue('F' . $row, $stockItem['nom_emplacement']);
    $row++;
}

// Auto-dimensionner les colonnes
foreach(range('A','F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Générer le fichier Excel
$writer = new Xlsx($spreadsheet);
$filename = 'stock_export_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
?>