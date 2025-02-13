<?php
require 'db.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "secret_key";

header("Content-Type: application/json");

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = trim(str_replace('Bearer', '', $authHeader));

if (!$token) {
    die(json_encode(["error" => "Token manquant ou mal formé"]));
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_utilisateur = $decoded->id_utilisateur; 

    if (!$id_utilisateur) {
        die(json_encode(["error" => "Utilisateur non valide dans le token"]));
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id_client = $data['id_client'];
    $produits = $data['produits'];
    $etat = $data['etat'];
    $methode_paiement=$data['methode_paiement'];

    $date_echeance = $data['date_echeance'];
    
    $montant_total = 0;
    foreach ($produits as $produit) {
        $stmt = $pdo->prepare("SELECT prix_unitaire FROM produits WHERE id_produit = ?");
        $stmt->execute([$produit['id_produit']]);
        $produit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produit_data) {
            $montant_total += $produit_data['prix_unitaire'] * $produit['quantité'];
        }
    }

    $stmt = $pdo->prepare("INSERT INTO facture (id_client, id_utilisateur, montant, etat, methode_paiement, date_creation, date_echeance) 
                            VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->execute([$id_client, $id_utilisateur, $montant_total, $etat, $methode_paiement, $date_echeance]);
      
        echo json_encode(["message" => "Facture créée"]);
    
} catch (Exception $e) {
    die(json_encode(["error" => "Token invalide", "details" => $e->getMessage()]));
}
?>
