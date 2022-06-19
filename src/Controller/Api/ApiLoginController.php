<?php
namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ApiLoginController extends AbstractController
{

    /*#[Route('/api/login', name: 'api_login')]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $token = ""; // somehow create an API token for $user
        
        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $token
        ]);
    }*/
}

