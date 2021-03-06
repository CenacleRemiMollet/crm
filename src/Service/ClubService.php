<?php
namespace App\Service;

use App\Model\ClubLocationView;
use App\Model\ClubView;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ClubLocation;
use App\Entity\ClubPrice;
use App\Model\ClubPriceView;
use App\Entity\Club;
use Psr\Log\LoggerInterface;
use App\Entity\ClubProperty;
use App\Model\ClubPropertyView;

/**
 * @author f.agu
 */
class ClubService
{

    private ManagerRegistry $manager;
    
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }
    
    public function getAll()
    {
        return $this->manager->getManager()
        ->getRepository(Club::class)
        ->findAll();
    }
    
    public function getAllActive()
    {
        return $this->manager->getManager()
            ->getRepository(Club::class)
            ->findBy(['active' => true]);
    }
    
    public function convertAllActiveToView()
    {
        $clubs = $this->manager->getManager()
            ->getRepository(Club::class)
            ->findBy(['active' => true]);
        return $this->convertToView($clubs);
    }
    
    public function convertAllToView()
    {
        $clubs = $this->manager->getManager()
            ->getRepository(Club::class)
            ->findAll();
        return $this->convertToView($clubs);
    }
    
    public function convertToView($clubs) {
        $inputSingle = false;
        if($clubs instanceof Club) {
            $inputSingle = true;
            $clubs = [$clubs];
        }
        $clubByIds = array();
        $clubIds = array();
        foreach ($clubs as &$club) {
            $clubByIds[$club->getId()] = $club;
            array_push($clubIds, $club->getId());
        }
        
        // locations
        $locations = $this->manager->getManager()
            ->getRepository(ClubLocation::class)
            ->findByClubIds($clubIds);
        $loclist = array();
        foreach($locations as &$r) {
            $cid = $r['c'];
            $loc = new ClubLocationView($clubByIds[$cid], $r[0]);
            if(! array_key_exists($cid, $loclist)) {
                $loclist[$cid] = [$loc];
            } else {
                array_push($loclist[$cid], $loc);
            }
        }
        
        // prices
        $prices = $this->manager
            ->getRepository(ClubPrice::class)
            ->findBy(array("club" => $clubs));
        $pricelist = array();
        foreach($prices as &$r) {
            $cid = $r->getClub()->getId();
            $price = new ClubPriceView($clubByIds[$cid], $r);
            if(! array_key_exists($cid, $pricelist)) {
                $pricelist[$cid] = [$price];
            } else {
                array_push($pricelist[$cid], $price);
            }
        }
        
        // properties
        $properties = $this->manager
            ->getRepository(ClubProperty::class)
            ->findBy(array("club" => $clubs));
        $propertylist = array();
        foreach($properties as &$r) {
            $cid = $r->getClub()->getId();
            $property = new ClubPropertyView($clubByIds[$cid], $r);
            if(! array_key_exists($cid, $propertylist)) {
                $propertylist[$cid] = [$property];
            } else {
                array_push($propertylist[$cid], $property);
            }
        }
            
        // ClubView array
        $output = array();
        foreach($clubByIds as $cid => $club) {
            $locs = array_key_exists($cid, $loclist) ? $loclist[$cid] : [];
            $prices = array_key_exists($cid, $pricelist) ? $pricelist[$cid] : [];
            $properties = array_key_exists($cid, $propertylist) ? $propertylist[$cid] : [];
            array_push($output, new ClubView($club, $locs, $prices, $properties));
        }
        
        return $inputSingle ? $output[0] : $output;
    }
    
}

