<?php
namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class LegacyPasswordHasher implements PasswordHasherInterface
{

    use CheckPasswordLengthTrait;
    
    private LoggerInterface $logger;
    
    private RequestStack $requestStack;
    
    public function __construct(LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }
    
    public function hash(string $plainPassword): string
    {
        $salt = 'gh(-#fgbVD56ù@iutyxc +tyu_75^rrtyè6';
        $sha1 = sha1($plainPassword.$salt);
        return 'sha1:'.$sha1;
    }
    
    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if ('' === $plainPassword) {
            return false;
        }
        if ($this->isPasswordTooLong($plainPassword)) {
            return false;
        }
        if($this->hash($plainPassword) === $hashedPassword) {
            $attributes = $this->requestStack->getCurrentRequest()->attributes;
            $attributes->set('plainpwdforrehash', $plainPassword);
            return true;
        }
        return false;
    }
    
    public function needsRehash(string $hashedPassword): bool
    {
        $this->logger->debug('LegacyPasswordHasher.needsRehash()');
        return true;
    }
}

