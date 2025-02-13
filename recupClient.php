<?php
require 'db.php'; 

try {
    
    $sql = $pdo->prepare("SELECT * FROM client");
    $sql->execute();
    $client = $sql->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($client);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur lors de la récupération des clients: " . $e->getMessage()]);
}
?>
