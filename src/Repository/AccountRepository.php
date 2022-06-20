<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\SodiumPasswordEncoder;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\User;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private LoggerInterface $logger;
    private PasswordHasherInterface $passwordEncoder;
    private RequestStack $requestStack;
    
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger, RequestStack $requestStack)
    {
        parent::__construct($registry, Account::class);
        $this->passwordEncoder = new NativePasswordHasher();
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // attribute set in LegacyPasswordHasher
        $plainPassword = $this->requestStack->getCurrentRequest()->attributes->get('plainpwdforrehash');
        //$this->logger->debug('upgradePassword '.$newHashedPassword.' with '.$plainPassword);
        if($plainPassword == null) {
            return;
        }
        $this->requestStack->getCurrentRequest()->attributes->remove('plainpwdforrehash');
        
        if (!$user instanceof Account) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }
        $newHash = $this->passwordEncoder->hash($plainPassword);
        //$this->logger->debug('upgradePassword new hash: '.$newHash.'  user_id: '.$user->getId());
        $user->setPassword($newHash);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    // /**
    //  * @return Account[] Returns an array of Account objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
