<?php 
require 'authorisation.php';
verificationRole('administrateur');
if($_SERVER["REQUEST_METHOD"]=="POST"){
    require 'db.php';
    
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($_POST['telephone']);
    $adresse = htmlspecialchars($_POST['adresse']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "L'email fourni est invalide.";
    } else {
        try {
  
            $sql = $pdo->prepare("INSERT INTO client (nom, prenom, email, telephone, adresse) VALUES (?, ?, ?, ?, ?)");
            $sql->execute([$nom, $prenom, $email, $telephone, $adresse]);
            echo "Client ajouté avec succès";
        } catch (PDOException $e) {
            echo "Erreur lors de l'insertion : " . $e->getMessage();
        }
    }
} else {
    echo "Erreur lors de la soumission du formulaire.";
}
?>

