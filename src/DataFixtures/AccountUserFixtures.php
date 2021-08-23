<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountUserFixtures extends Fixture // implements OrderedFixtureInterface
{
	public const ADMIN_USER_REFERENCE = 'admin-user';
	public const CLUB_MANAGER_REFERENCE_PREFIX = 'club-manager-';
	public const TEACHER_USER_REFERENCE_PREFIX = 'prof-user-';
	public const STUDENT_USER_REFERENCE_PREFIX = 'student-user-';
	public const TEACHER_STUDENT_USER_REFERENCE_PREFIX = 'prof-eleve-user-';

	private $passwordEncoder;


	public function __construct(UserPasswordHasherInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

	public function load(ObjectManager $manager)
	{
		$this->persistUserAccount($manager, 'admin nom', 'admin prenom', '07/11/2019', 'admin', 'admin', ['ROLE_ADMIN'], self::ADMIN_USER_REFERENCE);


		for ($i = 0; $i < 1; $i++)
		{
			$this->persistUserAccount($manager, 'cman nom '.$i, 'cman prenom '.$i, '07/11/2019', 'man'.$i, 'man'.$i, ['ROLE_CLUB_MANAGER'], self::CLUB_MANAGER_REFERENCE_PREFIX.$i);
		}

		for ($i = 0; $i < 10; $i++)
		{
			$this->persistUserAccount($manager, 'prof nom '.$i, 'prof prenom '.$i, '07/11/2019', 'prof'.$i, 'prof'.$i, ['ROLE_TEACHER'], self::TEACHER_USER_REFERENCE_PREFIX.$i);
			$this->persistUserAccount($manager, 'prof elev nom '.$i, 'prof elev prenom '.$i, '07/11/2019', 'profelev'.$i, 'profelev'.$i, ['ROLE_TEACHER', 'ROLE_STUDENT'], self::TEACHER_STUDENT_USER_REFERENCE_PREFIX.$i);
		}

		for ($i = 0; $i < 50; $i++)
		{
			$this->persistUserAccount($manager, 'eleve nom '.$i, 'eleve prenom '.$i, '07/11/2019', 'eleve'.$i, 'eleve'.$i, ['ROLE_STUDENT'], self::STUDENT_USER_REFERENCE_PREFIX.$i);
		}
		$manager->flush();
	}

	public function getOrder()
	{
		return 100;
	}

	private function persistUserAccount(ObjectManager $manager, $lastname, $firstname, $birthday, $login, $password, $roles, $reference)
	{
		$user = $this->createUser($lastname, $firstname, $birthday);
		$manager->persist($user);
		$manager->persist($this->createAccount($user, $login, $password, $roles));
		//$manager->flush();
		$this->addReference($reference, $user);
	}


	private function createUser($lastname, $firstname, $birthday)
	{
		$user = new User();
		$user->setLastname($lastname);
		$user->setFirstname($firstname);
		$user->setBirthday(new \DateTime($birthday));
		return $user;
	}

	private function createAccount($user, $login, $password, $roles)
	{
		$account = new Account();
		$account->setUser($user);
		$account->setLogin($login);
		$account->setPassword($this->passwordEncoder->hashPassword($account, $password));
		$account->setRoles($roles);
		return $account;
	}

}
