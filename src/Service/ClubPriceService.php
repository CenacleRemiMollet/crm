<?php
namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Club;
use App\Entity\ClubPrice;
use App\Model\ClubPriceView;

/**
 * @author f.agu
 */
class ClubPriceService
{

    private ManagerRegistry $manager;
    
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }
    
    public function convertByClubUuidToView($clubUuid)
    {
        $clubs = $this->manager->getManager()
            ->getRepository(Club::class)
            ->findBy(['uuid' => $clubUuid]);
        if(empty($clubs)) {
            return null;
        }
        $prices = $this->manager->getManager()
            ->getRepository(ClubPrice::class)
            ->findBy(['club_id' => $clubs[0]->getId()]);
        $output = array();
        foreach($prices as &$r) {
            array_push($output, new ClubPriceView($r));
        }
        return $output;
    }
 
    public function findByClubUuidAndPriceUuid($clubUuid, $priceUuid)
    {
        $clubs = $this->manager->getManager()
            ->getRepository(Club::class)
            ->findBy(['uuid' => $clubUuid]);
        if(empty($clubs)) {
            return null;
        }
        $prices = $this->manager->getManager()
            ->getRepository(ClubPrice::class)
            ->findBy(['club_id' => $clubs[0]->getId(), 'uuid' => $priceUuid]);
        if(empty($prices)) {
            return null;
        }
        return $prices[0];
    }
    
    public function convertByClubUuidAndPriceUuidToView($clubUuid, $priceUuid)
    {
        $price = $this->findByClubUuidAndPriceUuid($clubUuid, $priceUuid);
        if($price == null) {
            return null;
        }
        return new ClubPriceView($price);
    }
    
}

