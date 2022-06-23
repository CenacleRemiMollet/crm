<?php
namespace App\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityFinder
{

    private ManagerRegistry $manager;
    
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }
    
    public function findOneByOrThrow($className, array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        $entities = $this->manager->getManager()
            ->getRepository($className)
            ->findBy($criteria);
        if(empty($entities)) {
            $reflect = new \ReflectionClass($className);
            throw new NotFoundHttpException($reflect->getShortName());
        }
        return $entities[0];
    }
    
    public function findNoneByOrThrow($className, array $criteria, $callback)
    {
        $entities = $this->manager->getManager()
            ->getRepository($className)
            ->findBy($criteria);
        if(! empty($entities)) {
            $callback();
        }
    }
    
}

