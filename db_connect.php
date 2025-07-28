<?php
$hote = 'localhost';
$utilisateur = 'root';
$mot_de_passe = '';
$base_de_donnees = 'ocp_project';

$connexion = new mysqli($hote, $utilisateur, $mot_de_passe, $base_de_donnees);

if ($connexion->connect_error) {
    die("Échec de la connexion : " . $connexion->connect_error);
}
?>
