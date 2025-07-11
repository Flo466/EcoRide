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
            $pdo = $pdoService->getPdo();

            $stmt = $pdo->query("SELECT 1");
            $result = $stmt->fetch();

            return new Response('Connexion réussie à la base de données !');
        } catch (\Exception $e) {
            return new Response('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }
}
