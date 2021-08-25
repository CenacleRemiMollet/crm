<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\UserHistory;
use App\Model\UserCreate;
use App\Model\UserUpdate;
use App\Util\DateUtils;
use App\Util\DiffTool;
use App\Util\TreeWalker;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use App\Model\ConfigurationPropertyUpdate;
use App\Entity\ConfigurationProperty;


/**
 * @author f.agu
 */
class ConfigurationPropertyService
{

	private $em;

	public function __construct(ObjectManager $em)
	{
		$this->em = $em;
	}

	public function update(Account $modifierAccount, ConfigurationPropertyUpdate $propUpdate) :ConfigurationProperty
	{
		$properties = $this->em
			->getRepository(ConfigurationProperty::class)
			->findBy(['property_key' => $propUpdate->getKey()]);

		if(count($properties) == 0) {
			return $this->create($modifierAccount, $propUpdate);
		}
		$property = $properties[0];
		$property->setPreviousValue($property->getPropertyValue());
		$property->setPropertyValue($propUpdate->getValue());
		$property->setUpdatedDate(new \DateTime());
		$property->setUpdaterUserId($modifierAccount->getId());

		$this->em->flush();

		return $property;
	}

	public function __destruct()
	{
		$this->em = null;
	}

	private function create(Account $modifierAccount, ConfigurationPropertyUpdate $propCreate) :ConfigurationProperty
	{
		$prop = new ConfigurationProperty();
		$prop->setPropertyKey($propCreate->getKey());
		$prop->setPropertyValue($propCreate->getValue());
		$prop->setUpdatedDate(new \DateTime());
		$prop->setUpdaterUserId($modifierAccount->getId());

		$this->em->persist($prop);
		$this->em->flush();

		return $prop;
	}


}