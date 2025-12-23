<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");

try {
    $databases = $client->listDatabases();
    echo "Connexion MongoDB réussie !\n";
    foreach ($databases as $db) {
        echo "- " . $db->getName() . "\n";
    }
} catch (Exception $e) {
    echo "Erreur de connexion : " . $e->getMessage() . "\n";
}
