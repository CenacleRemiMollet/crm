<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private $urlGenerator;
    
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if(str_starts_with($request->getPathInfo(), '/api')) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }
        $request->getSession()->getFlashBag()->add('note', 'You have to login in order to access this page.');
        return new RedirectResponse($this->urlGenerator->generate('security_login'));
    }

}
