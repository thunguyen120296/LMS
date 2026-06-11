<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/iam')]
final class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login')]
    public function login(Request $request, HttpClientInterface $client): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // client 
        $url = $this->getParameter('keycloak_url') . '/realms/master/protocol/openid-connect/token';
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body'    => http_build_query([ // <--- CÁI NÀY LÀM NÊN ĐỊNH DẠNG URL-ENCODED
                'grant_type'    => $this->getParameter('grant_type'),
                'client_id'     => $this->getParameter('client_id'),
                'client_secret' => $this->getParameter('client_secret'),
                'username'      => $data['username'],
                'password'      => $data['password'],
                // 'scope'         => $this->getParameter('scope')
            ]),
        ]);
        if($response->getStatusCode() !== 200){
            return new JsonResponse([
                'message' => 'Invalid credentials'
            ], 401);
        }
        $tokenData = $response->toArray();
        return new JsonResponse(['token' => $tokenData['access_token']]);
    }
}
