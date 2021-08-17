<?php

namespace App\DataFixtures;

use App\Entity\ClubLesson;
use App\Entity\User;
use App\Entity\UserLessonSubscribe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\UserClubSubscribe;

class UserClubLinkFixtures extends Fixture implements OrderedFixtureInterface
{

	public function load(ObjectManager $manager)
	{
		$manager->persist($this->createUserLessonSubscribe(
			$this->getReference(AccountUserFixtures::CLUB_MANAGER_REFERENCE_PREFIX.'0'),
			$this->getReference('Koryo'),
			'CLUB_MANAGER'));

		$manager->persist($this->createUserLessonSubscribe(
			$this->getReference(AccountUserFixtures::TEACHER_USER_REFERENCE_PREFIX.'0'),
			$this->getReference('Koryo'),
			'TEACHER'));

		for ($i = 0; $i < 20; $i++)
		{
			$manager->persist($this->createUserLessonSubscribe(
					$this->getReference(AccountUserFixtures::STUDENT_USER_REFERENCE_PREFIX.$i),
					$this->getReference('Koryo'),
					'STUDENT'));
		}
		for ($i = 0; $i < 6; $i++)
		{
			$manager->persist($this->createUserLessonSubscribe(
					$this->getReference(AccountUserFixtures::STUDENT_USER_REFERENCE_PREFIX.$i),
					$this->getReference('A.S.C. Taekwondo-Hapkido'),
					'STUDENT'));
		}

		$manager->flush();
	}

	public function getOrder()
	{
		return 300;
	}

	private function createUserLessonSubscribe($user, $club, $role)
	{
		$subsc = new UserClubSubscribe();
		$subsc->setUser($user);
		$subsc->setClub($club);
		$subsc->setRoles([$role]);
		return $subsc;
	}

}
