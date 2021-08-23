<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
use App\Entity\AccountSessionHistory;
use App\Entity\Account;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

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

    public function authenticate(Request $request): PassportInterface
    {
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
    	return 'app_login' === $request->attributes->get('_route')
    	&& $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
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

    public function getUser($credentials, UserProviderInterface $userProvider): ?Account
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
    }

    public function getPassword($credentials): ?string
    {
    	$pwd = $credentials['password'];
    	$user = $credentials['user'];
    	if(substr($pwd, 0, 5) === 'sha1:') {
    		return $this->checkCredentialsLegacy(substr($pwd, 5), $credentials, $user);
    	}
    	return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

//     public function checkCredentials($credentials, UserInterface $user): bool
//     {
//     	$pwd = $user->getPassword();
//     	if(substr($pwd, 0, 5) === 'sha1:') {
//     		return $this->checkCredentialsLegacy(substr($pwd, 5), $credentials, $user);
//     	}
//     	return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
//     }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $sessionHst = new AccountSessionHistory();
        $sessionHst->setAccount($token->getUser());
        $sessionHst->setIp($request->getClientIp());
        $sessionHst->setUserAgent($request->headers->get('User-Agent'));
        $this->entityManager->persist($sessionHst);
        $this->entityManager->flush();

        return new RedirectResponse($this->urlGenerator->generate('home'));
        // For example:
        //return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    //********************************************

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    //********************************************

    private function checkCredentialsLegacy($sha1, $credentials, Account $user)
    {
    	$salt = 'gh(-#fgbVD56ù@iutyxc +tyu_75^rrtyè6';
    	$input = sha1($credentials['password'].$salt);
    	if($input === $sha1) {
    		$this->logger->info('Upgrade legacy password for user '.$user->getId());
    		$newpwd = $this->passwordEncoder->encodePassword($user, $credentials['password']);
    		//$this->logger->info('newpwd '.$newpwd);
    		$user->setPassword($newpwd);
    		$user = $this->entityManager->flush();
    		return true;
    	}
    	$newpwd = $this->passwordEncoder->encodePassword($user, $credentials['password']);
    	$this->logger->info('Bad legacy password for user '.$user->getId());
    	//$this->logger->info('newpwd2: '.$newpwd);
    	return false;
    }

}
