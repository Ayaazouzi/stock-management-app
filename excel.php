<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; 
// Charger la librairie PhpSpreadsheet
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

    // Préparer les statistiques des tables
    $tables = ['admin', 'articles', 'categories', 'fournisseurs', 'user'];
    $stats = [];

    foreach ($tables as $table) {
        // Compter le nombre de lignes dans chaque table
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM $table");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats[] = ['table' => $table, 'total_rows' => $result['total']];
    }

    // Générer un fichier Excel avec les statistiques
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter des en-têtes
    $sheet->setCellValue('A1', 'Nom de la table');
    $sheet->setCellValue('B1', 'Nombre de lignes');

    // Ajouter les données des statistiques
    $row = 2; // Commence après les en-têtes
    foreach ($stats as $stat) {
        $sheet->setCellValue("A$row", $stat['table']);
        $sheet->setCellValue("B$row", $stat['total_rows']);
        $row++;
    }

    // Nom du fichier Excel
    $fileName = 'statistiques_stock_cofat.xlsx';

    // Télécharger le fichier Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    // Création d’un writer au format .xlsx
    $writer->save('php://output'); 
    // Envoyer directement au client
    exit;
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
