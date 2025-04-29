<?php

namespace App\Controller;

use App\Service\PdoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function index(PdoService $pdoService): Response
    {
        try {
            // Obtient la connexion PDO
            $pdo = $pdoService->getPdo();
            
            // Optionnel : Effectue une simple requête pour tester la connexion
            $stmt = $pdo->query("SELECT 1");
            $result = $stmt->fetch();

            // Si le test est réussi, renvoie une réponse positive
            return new Response('Connexion réussie à la base de données !');
        } catch (\Exception $e) {
            // Si une erreur de connexion survient
            return new Response('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }
}
