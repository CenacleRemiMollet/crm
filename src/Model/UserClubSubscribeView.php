<?php

namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use App\Entity\User;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Attributes as OA;
use Hateoas\Helper\LinkHelper;
use App\Entity\UserClubSubscribe;

#[OA\Schema(schema: 'UserClubSubscribe')]
#[Serializer\XmlRoot('UserClubSubscribe')]
class UserClubSubscribeView
{

    #[OA\Property(type: 'string', example: 'abcDEF654')]
    private $uuid;
    
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'ROLE_STUDENT')]
	private $roles;
	
	#[OA\Property(type: 'object')]
	private $subscribeDate;
	
	#[OA\Property(type: 'object')]
	private $unsubscribeDate;

	#[OA\Property(ref: '#/components/schemas/Club')]
	private $club;
	
	public function __construct(UserClubSubscribe $UserClubSubscribe)
	{
	    $this->uuid = $UserClubSubscribe->getUuid();
	    $this->roles = $UserClubSubscribe->getRoles();
	    $this->subscribeDate = $UserClubSubscribe->getSubscribeDate() !== null ? new DateModel($UserClubSubscribe->getSubscribeDate()) : null;
	    $this->unsubscribeDate = $UserClubSubscribe->getUnsubscribeDate() !== null ? new DateModel($UserClubSubscribe->getUnsubscribeDate()) : null;
	    $this->club = new ClubView($UserClubSubscribe->getClub());
	}

	public function getUuid(): ?string
	{
	    return $this->uuid;
	}
	
	public function getRoles()
	{
		return $this->roles;
	}

	public function getSubscribeDate()
	{
	    return $this->subscribeDate;
	}
	
	public function getUnsubscribeDate()
	{
	    return $this->unsubscribeDate;
	}
	
}