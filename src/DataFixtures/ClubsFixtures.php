<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\ClubLesson;
use App\Entity\ClubLocation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClubsFixtures extends Fixture implements OrderedFixtureInterface
{

	public function load(ObjectManager $manager)
	{
		// ======================== Bry
		$club           = $this->createClub('Koryo', 'bry_sur_marne.gif', 'http://www.taekwondobry.fr');
		$location       = $this->createLocation('Ecole Henri Cahn', '26 boulevard Général Gallieni', 'Bry-sur-Marne', '94360', '94');
		// Tuesday
		$this->persistLesson($manager, $club, $location, 'bry', 'Taekwonkido', 'Tous niveaux', 'tuesday', new \DateTime('19:00'), new \DateTime('19:45'));
		$this->persistLesson($manager, $club, $location, 'bry', 'Taekwondo', 'Tous niveaux', 'tuesday', new \DateTime('19:45'), new \DateTime('20:30'));
		$this->persistLesson($manager, $club, $location, 'bry', 'Hapkido', 'Tous niveaux', 'tuesday', new \DateTime('19:45'), new \DateTime('20:30'));
		// Wednesday
		$this->persistLesson($manager, $club, $location, 'bry', 'Taekwonkido', 'Baby / enfants', 'wednesday', new \DateTime('19:00'), new \DateTime('20:00'));
		// Thursday
		$this->persistLesson($manager, $club, $location, 'bry', 'Taekwonkido', 'Tous niveaux', 'thursday', new \DateTime('19:00'), new \DateTime('19:45'));
		$this->persistLesson($manager, $club, $location, 'bry', 'Taekwondo', 'Tous niveaux', 'thursday', new \DateTime('19:45'), new \DateTime('20:30'));
		$this->persistLesson($manager, $club, $location, 'bry', 'Hapkido', 'Tous niveaux', 'thursday', new \DateTime('19:45'), new \DateTime('20:30'));
		$this->persistLesson($manager, $club, $location, 'bry', 'Hapkido', 'Ceintures noires', 'thursday', new \DateTime('20:30'), new \DateTime('21:00'));
		$this->persistLesson($manager, $club, $location, 'bry', 'Gumdo', 'Tous niveaux', 'thursday', new \DateTime('21:00'), new \DateTime('22:00'));
		$manager->flush();


		// ======================== Chelles
		$club           = $this->createClub('A.S.C. Taekwondo-Hapkido', 'todo.gif', 'http://www.taekwondochelles.fr');
		$locationGDel   = $this->createLocation('Gymnase Delambre', 'avenue Delambre', 'Chelles', '77500', '77');
		$locationPomp   = $this->createLocation('Gymnase de Pomponne', 'rue cornouillers ', 'Pomponne', '77400', '77');
		$this->persistLesson($manager, $club, $locationGDel, 'chelles', 'Taekwondo', 'Enfants (6-9 ans), blanches à orange-pourpre', 'monday', new \DateTime('18:30'), new \DateTime('19:30'));
		$this->persistLesson($manager, $club, $locationGDel, 'chelles', 'Taekwondo', 'Enfants (10-12 ans), pourpre et +', 'monday', new \DateTime('19:30'), new \DateTime('20:30'));
		$this->persistLesson($manager, $club, $locationGDel, 'chelles', 'Taekwondo, Taekwonkido', 'Adultes tous niveaux', 'monday', new \DateTime('20:15'), new \DateTime('22:00'));
		$this->persistLesson($manager, $club, $locationGDel, 'chelles', 'Taekwondo', 'Enfants (6-9 ans), blanches à orange-pourpre', 'wednesday', new \DateTime('18:30'), new \DateTime('19:30'));
		$this->persistLesson($manager, $club, $locationGDel, 'chelles', 'Taekwondo', 'Enfants (10-12 ans), pourpre et +', 'wednesday', new \DateTime('19:30'), new \DateTime('20:30'));
		$this->persistLesson($manager, $club, $locationGDel, 'chelles', 'Taekwondo, Taekwonkido', 'Adultes tous niveaux', 'wednesday', new \DateTime('20:15'), new \DateTime('22:00'));
		$this->persistLesson($manager, $club, $locationPomp, 'chelles-enf', 'Taekwondo', 'Enfants (6-12 ans), blanches à orange-pourpre', 'thursday', new \DateTime('19:00'), new \DateTime('20:00'));
		$this->persistLesson($manager, $club, $locationPomp, 'chelles-adu', 'Taekwondo', 'Adultes (13 ans et +)', 'thursday', new \DateTime('19:00'), new \DateTime('20:30'));
		$this->persistLesson($manager, $club, $locationPomp, 'chelles', 'Taekwonkido', 'Adultes (13 ans et +)', 'thursday', new \DateTime('20:30'), new \DateTime('21:00'));
		$manager->flush();
	}


	public function getOrder()
	{
		return 200;
	}


	private function createClub($name, $logo, $website_url, $facebook_url = null, $mailing_list = null, $active = true)
	{
		$club = new Club();
		$club->setName($name);
		$club->setLogo($logo);
		$club->setWebsiteUrl($website_url);
		$club->setFacebookUrl($facebook_url);
		$club->setMailingList($mailing_list);
		$club->setActive($active);
		$this->addReference($name, $club);
		return $club;
	}

	private function persistLesson(ObjectManager $manager, $club, $location, $refloc, $discipline, $age_level, $day_of_week, $start_time, $end_time, $point = 1)
	{
		$lesson = new ClubLesson();
		$lesson->setClub($club);
		$lesson->setClubLocation($location);
		$lesson->setDiscipline($discipline);
		$lesson->setPoint($point);
		$lesson->setAgeLevel($age_level);
		$lesson->setDayOfWeek($day_of_week);
		$lesson->setStartTime($start_time);
		$lesson->setEndTime($end_time);
		$manager->persist($lesson);
		return $lesson;
	}

	private function createLocation($name, $address, $city, $zipcode, $county, $country = "France")
	{
		$clubloc = new ClubLocation();
		$clubloc->setName($name);
		$clubloc->setAddress($address);
		$clubloc->setCity($city);
		$clubloc->setZipcode($zipcode);
		$clubloc->setCounty($county);
		$clubloc->setCountry($country);
		return $clubloc;
	}

}
