<?php
require_once 'db.php';
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$key = "my_key";
$issuedAt = time();
$expiredAt = $issuedAt + 3600; 

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$email = isset($data['email']) ? $data['email'] : null;
$mot_de_passe = isset($data['mot_de_passe']) ? $data['mot_de_passe'] : null;

$sql = "SELECT id_utilisateur, role, mot_de_passe FROM utilisateur WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
    $payload = [
        "iat" => $issuedAt,
        "exp" => $expiredAt,
        "id_utilisateur" => $utilisateur['id_utilisateur'],
        "role" => $utilisateur['role']
    ];
    $jwt = JWT::encode($payload, $key, 'HS256');
    echo json_encode(["token" => $jwt]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Identifiants incorrects"]);
}
?>
