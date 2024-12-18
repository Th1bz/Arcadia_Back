<?php
// ---------------------------------Test de connexion à MongoDB----------------------------------
require 'vendor/autoload.php';

$uri = "mongodb+srv://zooarcadiadev:mYUNbfxVda6A36xj@arcadiacluster.9pxvu.mongodb.net/?retryWrites=true&majorityAppName=ArcadiaCluster";

try {
    $client = new MongoDB\Client($uri);
    $db = $client->arcadia;
    
    // Test de connexion
    $result = $db->command(['ping' => 1]);
    echo "Connecté avec succès à MongoDB Atlas!\n";
    
    // Liste des collections
    $collections = $db->listCollections();
    echo "Collections disponibles:\n";
    foreach ($collections as $collection) {
        echo " - " . $collection->getName() . "\n";
    }
} catch (Exception $e) {
    echo "Erreur de connexion : " . $e->getMessage() . "\n";
}