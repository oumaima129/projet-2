<?php
$host='localhost';
$db_name='gestion_factures';
$username='root';
$password='';
try{
    $pdo=new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4",$username,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e){
    die(json_encode(["error" => "Erreur de connexion : " . $e->getMessage()]));
}
?>