<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\InputArgument;
use App\Entity\Club;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;
use App\Media\MediaManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Exception;
use App\Entity\ClubLocation;
use App\Entity\ClubLesson;
use App\Entity\ClubPrice;

/**
 * php bin/console crm:migration --domainname=<domain> --dump=dump_src.sql
 * @author f.agu
 */
class MigrationCommand extends Command
{
	protected static $defaultName = 'crm:migration';

	private $doctrine;
	private $mediaManager;
	private $projectDir;

	public function __construct(ManagerRegistry $doctrine, KernelInterface $appKernel, LoggerInterface $logger)
	{
		parent::__construct();
		$this->doctrine = $doctrine;
		$this->mediaManager = new MediaManager($appKernel, $logger);
		$this->projectDir = $appKernel->getProjectDir();
	}

	protected function configure()
	{
		$this->addOption('domainname', 'd', InputOption::VALUE_REQUIRED, 'Domain name to download club logo', null);
		$this->addOption('dump', null, InputOption::VALUE_REQUIRED, 'Dump file from legacy database', null);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$domain = $input->getOption('domainname');
		$srcdump = $input->getOption('dump');
		if(! file_exists($srcdump)) {
			throw new \Exception('File not found: '.$srcdump);
		}

		$this->importDump($srcdump);
		$this->importDump($this->projectDir.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'migration.sql');

		echo PHP_EOL.'====== Logo ======'.PHP_EOL;
		$this->downloadClubLogo($domain);

		echo PHP_EOL.'====== CSV Locations ======'.PHP_EOL;
		$locations = $this->loadCSVLocations();

		echo PHP_EOL.'====== CSV Hours ======'.PHP_EOL;
		$this->loadCSVHours($locations);
		
		echo PHP_EOL.'====== CSV Prices ======'.PHP_EOL;
		$this->loadCSVPrices();

		return Command::SUCCESS;
	}


	private function importDump($dumpfile)
	{
		$conn = $this->doctrine->getConnection();
		$params = $conn->getParams();
		$cmd = sprintf('mysql -u %s --password=%s %s < %s',
		    $params['user'],
		    $params['password'],
			$conn->getDatabase(),
			$dumpfile
			);
		//$output->writeln($cmd);
		//echo $cmd.PHP_EOL;
		echo 'Import DB dump '.$dumpfile.PHP_EOL;

		exec($cmd);
	}


	private function downloadClubLogo($domain)
	{
		$imgClubsPath = $this->projectDir.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'html_test'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'clubs'.DIRECTORY_SEPARATOR;

		foreach($this->doctrine->getManager()->getRepository(Club::class)->findAll() as $club)
		{
			$imgLogo = $club->getLogo();
			if("villiers_sur_marne.gif" === $imgLogo) {
				$imgLogo = "villiers_sur_marne.jpg";
			}
			$imgFile = $imgClubsPath.$imgLogo;
			if (file_exists($imgFile)) {
				echo 'Copy logo for "'.$club->getUuid().'" from '.$imgFile.PHP_EOL;
				$this->mediaManager->save($imgFile, 'club', $club->getUuid());

				if("villiers_sur_marne.jpg" === $imgLogo) {
					$club->setLogo($imgLogo);
					$this->doctrine->getManager()->persist($club);
				}

			} else {
				$url = 'http://'.$domain.'/param_clubs/logo_club/'.$club->getLogo();
				echo 'Download logo for "'.$club->getUuid().'" from '.$url.PHP_EOL;
				$this->mediaManager->downloadAndSave($url, 'club', $club->getUuid());
			}
		}
	}


	private function loadCSVLocations()
	{
		$clubsPath = $this->projectDir.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'clubs'.DIRECTORY_SEPARATOR;
		$serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

		$locations = array();
		foreach($this->doctrine->getManager()->getRepository(Club::class)->findAll() as $club)
		{
			$csvFile = $clubsPath.$club->getUuid().'-locations.csv';
			if (! file_exists($csvFile)) {
				echo 'File not found: '.$csvFile.PHP_EOL;
				continue;
			}
			echo 'Loading: '.$csvFile.' (clubId: '.$club->getId().')'.PHP_EOL;
			$data = $serializer->decode(file_get_contents($csvFile), 'csv');
			foreach($this->saveOrUpdateLocation($club, $data) as $location)
			{
				$locations[$location->getUuid()] = $location;
			}
		}

		return $locations;
	}


	private function saveOrUpdateLocation(Club $club, $data)
	{
		$locations = array();
		foreach ($data as $line) {
			$uuid = $line["uuid"];
			if($uuid == NULL) {
				continue;
			}
			$location = $this->doctrine->getManager()->getRepository(ClubLocation::class)->findOneBy(["uuid" => $uuid]);
			if($location == NULL) {
				echo '  Creating '.$uuid.PHP_EOL;
				$location = new ClubLocation();
				$this->populateLocation($club, $location, $line);
				$this->doctrine->getManager()->persist($location);
			} else {
				echo '  Updating '.$uuid.PHP_EOL;
				$this->populateLocation($club, $location, $line);
			}
			array_push($locations, $location);
		}
		$this->doctrine->getManager()->flush();
		return $locations;
	}

	private function populateLocation(Club $club, ClubLocation $location, $line) {
		$location->setUuid($line["uuid"]);
		$location->setClub($club);
		$location->setName($line["name"]);
		$location->setAddress($line["address"]);
		$location->setCity($line["city"]);
		$location->setZipcode($line["zipcode"]);
		$location->setCounty($line["county"]);
		$location->setCountry($line["country"]);
	}


	private function loadCSVHours($locations)
	{
		$clubsPath = $this->projectDir.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'clubs'.DIRECTORY_SEPARATOR;
		$serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
		foreach($this->doctrine->getManager()->getRepository(Club::class)->findAll() as $club)
		{
			$csvFile = $clubsPath.$club->getUuid().'-hours.csv';
			if (! file_exists($csvFile)) {
				echo 'File not found: '.$csvFile.PHP_EOL;
				continue;
			}
			echo 'Loading: '.$csvFile.PHP_EOL;
			$data = $serializer->decode(file_get_contents($csvFile), 'csv');
			$this->saveOrUpdateHour($club, $data, $locations);
		}
	}


	private function saveOrUpdateHour(Club $club, $data, $locations)
	{
		$this->deleteHoursForAClub($club);
		foreach ($data as $line) {
			if(implode($line, "-") === "") {
				continue;
			}
			try {
				$lesson = new ClubLesson();
				$lesson->setClub($club);
				$lesson->setClubLocation($locations[$line["location"]]);
				$lesson->setDiscipline($line["discipline"]);
				$lesson->setDescription(isset($line["description"]) ? $line["description"] : '');
				$lesson->setPoint(1);
				$lesson->setAgeLevel($line["age_level"]);
				$lesson->setDayOfWeek($line["day_of_week"]);
				$lesson->setStartTime(new \DateTime($line["start_time"]));
				$lesson->setEndTime(new \DateTime($line["end_time"]));
				$this->doctrine->getManager()->persist($lesson);
			} catch(Exception $e) {
				throw new \Exception("Failed to save the club ".$club->getName()." with line (".implode($line, ",").")", 0, $e);
			}
		}
		$this->doctrine->getManager()->flush();
	}


	private function deleteHoursForAClub(Club $club)
	{
		$manager = $this->doctrine->getManager();
		foreach($manager->getRepository(ClubLesson::class)->findBy(["club" => $club]) as $lesson)
		{
			$manager->remove($lesson);
		}
		$manager->flush();
	}

	
	private function loadCSVPrices()
	{
	    $clubsPath = $this->projectDir.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'clubs'.DIRECTORY_SEPARATOR;
	    $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
	    foreach($this->doctrine->getManager()->getRepository(Club::class)->findAll() as $club)
	    {
	        $csvFile = $clubsPath.$club->getUuid().'-prices.csv';
	        if (! file_exists($csvFile)) {
	            echo 'File not found: '.$csvFile.PHP_EOL;
	            continue;
	        }
	        echo 'Loading: '.$csvFile.PHP_EOL;
	        $data = $serializer->decode(file_get_contents($csvFile), 'csv');
	        $this->saveOrUpdatePrice($club, $data);
	    }
	}

	private function saveOrUpdatePrice(Club $club, $data)
	{
	    $this->deletePricesForAClub($club);
	    foreach ($data as $line) {
	        if(implode($line, "-") === "") {
	            continue;
	        }
	        try {
	            $clubPrice = new ClubPrice();
	            $clubPrice->setClubId($club->getId());
	            $clubPrice->setDiscipline($line["discipline"]);
	            $clubPrice->setCategory(isset($line["category"]) ? $line["category"] : '');
	            $clubPrice->setComment(isset($line["comment"]) ? $line["comment"] : '');
	            $clubPrice->setPriceChild1(isset($line["child_1"]) ? floatval($line["child_1"]) : null);
	            $clubPrice->setPriceChild2(isset($line["child_2"]) ? floatval($line["child_2"]) : null);
	            $clubPrice->setPriceChild3(isset($line["child_3"]) ? floatval($line["child_3"]) : null);
	            $clubPrice->setPriceAdult(isset($line["adult"]) ? floatval($line["adult"]) : null);
	            $this->doctrine->getManager()->persist($clubPrice);
	        } catch(Exception $e) {
	            throw new \Exception("Failed to save the club price ".$club->getName()." with line (".implode($line, ",").")", 0, $e);
	        }
	    }
	    $this->doctrine->getManager()->flush();
	}

	private function deletePricesForAClub(Club $club)
	{
	    $manager = $this->doctrine->getManager();
	    foreach($manager->getRepository(ClubPrice::class)->findBy(["club_id" => $club->getId()]) as $price)
	    {
	        $manager->remove($price);
	    }
	    $manager->flush();
	}
}

