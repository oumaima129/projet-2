<?php
require 'db.php';
$id_client = $_GET['id_client'] ?? '';
$date_creation = $_GET['date_creation'] ?? '';
$date_echeance = $_GET['date_echeance'] ?? '';
$etat = $_GET['etat'] ?? '';

var_dump($_GET); 

$sql = "SELECT * FROM facture WHERE 1=1";
$params = [];

if (!empty($id_client)) {
    $sql .= " AND id_client = :client"; 
    $params['client'] = $id_client;
}
if (!empty($date_creation)) {
    $sql .= " AND DATE(date_creation) = :date_creation";
    $params['date_creation'] = $date_creation;
}
if (!empty($date_echeance)) {
    $sql .= " AND DATE(date_echeance) = :date_echeance";
    $params['date_echeance'] = $date_echeance;
}
if (!empty($etat)) {
    $sql .= " AND etat = :etat";
    $params['etat'] = $etat;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($factures);
?>
