<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\AccountSessionHistory;
use App\Entity\Account;

class ApiAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordHasherInterface $passwordEncoder, LoggerInterface $logger)
    {
    	$this->entityManager = $entityManager;
    	$this->urlGenerator = $urlGenerator;
    	$this->csrfTokenManager = $csrfTokenManager;
    	$this->passwordEncoder = $passwordEncoder;
    	$this->logger = $logger;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
    	$this->logger->debug('ApiAuthenticator.start(...) '.$request->getRequestUri());
    	$url = $this->getLoginUrl($request);

    	return new RedirectResponse($url);
    }


    public function authenticate(Request $request): PassportInterface
    {
        $this->logger->debug('ApiAuthenticator.authenticate() '.$request->getRequestUri());
        $login = $request->request->get('login', '');

        $request->getSession()->set(Security::LAST_USERNAME, $login);

        return new Passport(
            new UserBadge($login),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    public function supports(Request $request): bool
    {
        $supported = $request->getPathInfo() == '/api/login' && $request->isMethod('POST');
        $this->logger->debug('ApiAuthenticator.supports('.$request->getMethod().' '.$request->getPathInfo().'): '.($supported ? 'yes' : 'no'));
        return $supported;
    }

    public function getCredentials(Request $request)
    {
        $this->logger->debug('ApiAuthenticator.getCredentials() '.$request->getRequestUri());
        $credentials = [
    		'login' => $request->request->get('login'),
    		'password' => $request->request->get('password'),
    		'csrf_token' => $request->request->get('_csrf_token'),
    	];
    	$request->getSession()->set(
    		Security::LAST_USERNAME,
    		$credentials['login']
    		);

    	return $credentials;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->debug('ApiAuthenticator.onAuthenticationSuccess() '.$request->getRequestUri());
    	// on success, let the request continue
    	return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->debug('ApiAuthenticator.onAuthenticationFailure() '.$request->getRequestUri());
    	$data = [
    		// you may want to customize or obfuscate the message first
    		'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

    		// or to translate this message
    		// $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
    	];

    	return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /*public function getUser($credentials, UserProviderInterface $userProvider): ?Account
    {
    	$token = new CsrfToken('authenticate', $credentials['csrf_token']);
    	if (!$this->csrfTokenManager->isTokenValid($token)) {
    		throw new InvalidCsrfTokenException();
    	}

    	$user = $this->entityManager
    	->getRepository(Account::class)
    	->findOneBy([
    		'login' => $credentials['login'],
    		'has_access' => true
    	]);

    	if (!$user) {
    		// fail authentication with a custom error
    		throw new CustomUserMessageAuthenticationException('Login could not be found.');
    	}

    	return $user;
    }*/

//     public function getPassword($credentials): ?string
//     {
//     	$pwd = $credentials['password'];
//     	$user = $credentials['user'];
//     	if(substr($pwd, 0, 5) === 'sha1:') {
//     		return $this->checkCredentialsLegacy(substr($pwd, 5), $credentials, $user);
//     	}
//     	return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
//     }

//     public function checkCredentials($credentials, UserInterface $user): bool
//     {
//     	$pwd = $user->getPassword();
//     	if(substr($pwd, 0, 5) === 'sha1:') {
//     		return $this->checkCredentialsLegacy(substr($pwd, 5), $credentials, $user);
//     	}
//     	return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
//     }

//     public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
//     {
//         if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
//             return new RedirectResponse($targetPath);
//         }

//         $sessionHst = new AccountSessionHistory();
//         $sessionHst->setAccount($token->getUser());
//         $sessionHst->setIp($request->getClientIp());
//         $sessionHst->setUserAgent($request->headers->get('User-Agent'));
//         $this->entityManager->persist($sessionHst);
//         $this->entityManager->flush();

//         return new RedirectResponse($this->urlGenerator->generate('home'));
//         // For example:
//         //return new RedirectResponse($this->urlGenerator->generate('some_route'));
//         // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
//     }

    //********************************************

//     protected function getLoginUrl(Request $request): string
//     {
//     	throw new \Exception(); // never call
//     }

}
