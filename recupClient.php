<<?php
require 'authorisation.php';
verificationRole('administrateur');
require 'db.php'; 

header('Content-Type: application/json');

try {
    if (!isset($_GET['id_client'])) {
        echo json_encode(["error" => "ID du client manquant"]);
        exit;
    }

    $id_client = $_GET['id_client'];

    $sql = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
    $sql->execute([$id_client]);
    $client = $sql->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($client);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des clients: " . $e->getMessage()]);
}
?>

