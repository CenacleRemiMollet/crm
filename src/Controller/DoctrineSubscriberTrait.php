<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;

/**
 * Symfony 6+ no longer exposes 'doctrine' in the AbstractController service locator:
 * re-subscribe it for controllers (and ClubAccess) using $this->container->get('doctrine').
 */
trait DoctrineSubscriberTrait
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + ['doctrine' => ManagerRegistry::class];
    }
}
