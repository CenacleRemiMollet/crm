<?php
namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;

class LegacyPasswordHasher implements PasswordHasherInterface
{

    use CheckPasswordLengthTrait;
    
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        return $this->hash($plainPassword) === $hashedPassword;
    }
    
    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}

