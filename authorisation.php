<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "my_key"; 

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = trim(str_replace('Bearer', '', $authHeader));

if ($token) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $_SESSION['id_utilisateur'] = $decoded->id_utilisateur;
        $_SESSION['role'] = $decoded->role;
    } catch (Exception $e) {
        die(json_encode(["error" => "Token invalide", "details" => $e->getMessage()]));
    }
}
function connexion() {
    if (!isset($_SESSION['id_utilisateur'])) {
        http_response_code(403);
        die(json_encode(["error" => "Accès interdit : Vous devez être connecté"]));
    }
}
function verificationRole($role_requis) {
    connexion(); 
    if ($_SESSION['role'] !== $role_requis) {
        http_response_code(403);
        die(json_encode(["error" => "Accès refusé : Vous n'avez pas les permissions nécessaires"]));
    }
}
?>
