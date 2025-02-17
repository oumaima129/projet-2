<?php
require 'authorisation.php';
verificationRole('administrateur');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'db.php';

    $id = intval($_POST['id']); 
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($_POST['telephone']);
    $adresse = htmlspecialchars($_POST['adresse']);

    try {
     
        $sql = $pdo->prepare("UPDATE client SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, adresse = :adresse WHERE id_client = :id_client");
        $sql->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':adresse' => $adresse,
            ':id_client' => $id
        ]);

        echo "Le client a été mis à jour avec succès.";
    } catch (PDOException $e) {
        echo "Une erreur est survenue : " . $e->getMessage();
    }
} else {
    echo "Erreur lors de la mise à jour.";
}
?>

