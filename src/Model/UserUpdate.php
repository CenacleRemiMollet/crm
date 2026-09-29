<?php
namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use App\Util\RequestUtil;
use App\Util\NestedValidation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationList;

#[OA\Schema(
    schema: 'UserUpdate',
    title: 'UserUpdate',
    description: 'Update an user',
    xml: new OA\Xml(name: 'UserUpdate')
)]
class UserUpdate implements NestedValidation
{

    #[OA\Property(type: 'string', example: 'Doe')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 255)]
    #[AcmeAssert\NoHTML]
    private $lastname;
    
    #[OA\Property(type: 'string', example: 'John')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 255)]
    #[AcmeAssert\NoHTML]
    private $firstname;
    
    #[OA\Property(type: 'string', example: '31/12/2000')]
    #[AcmeAssert\Birthday]
    private $birthday;
    
    #[OA\Property(type: 'string', pattern: '^[F|M]$', example: 'F')]
    #[Assert\Regex(pattern: '[F|M]')]
    private $sex;
    
    #[OA\Property(type: 'string', example: '5 Avenue Anatole France')]
    #[Assert\Length(max: 512)]
    #[AcmeAssert\NoHTML]
    private $address;
    
    #[OA\Property(type: 'string', example: '75007')]
    #[Assert\Length(max: 32)]
    #[AcmeAssert\NoHTML]
    private $zipcode;
    
    #[OA\Property(type: 'string', example: 'Paris')]
    #[Assert\Length(max: 255)]
    #[AcmeAssert\NoHTML]
    private $city;
    
    #[OA\Property(type: 'string', example: '0 892 70 12 39')]
    #[Assert\Length(max: 32)]
    #[AcmeAssert\NoHTML]
    private $phone;
    
    #[OA\Property(type: 'string', example: '0 892 70 12 39')]
    #[Assert\Length(max: 32)]
    #[AcmeAssert\NoHTML]
    private $phone_emergency;
    
    #[OA\Property(type: 'string', example: 'Française')]
    #[Assert\Length(max: 64)]
    #[AcmeAssert\NoHTML]
    private $nationality;
    
    #[OA\Property(type: 'string', example: 'mail_1@adresse.fr, mail_2@adresse.fr')]
    #[Assert\Length(max: 512)]
    #[AcmeAssert\NoHTML]
    private $mails;
    
    #[OA\Property(type: 'string', pattern: '^[A-Za-z0-9_@\\\\.]{3,64}$', example: 'j.doe')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(pattern: '/[A-Za-z0-9_@\\\\.]{3,64}/')]
    private $login;
    
    /**
     * @var UserClubSubscribeUpdate[]
     */
    #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/UserClubSubscribeUpdate'))]
    private $subscribes;
    
    /**
     * @var string[]
     */
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'ROLE_ADMIN')]
    #[AcmeAssert\Roles]
    private $roles;
    
    public function getLastname()
    {
        return $this->lastname;
    }
    
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }
    
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }
 
    public function getBirthday()
    {
        return $this->birthday;
    }
        
    public function getBirthdayDateTime():? \DateTimeInterface
    {
        if($this->birthday === null) {
            return null;
        }
        $date = new \DateTime();
        if (preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $this->birthday, $matches)) {
            $date->setDate(intval($matches[3]), intval($matches[2]), intval($matches[1]));
            return $date;
        }
        throw new \Exception("Never happen !");
    }
    
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }
    
    public function getSex(): ?string
    {
        return $this->sex;
    }
    
    public function setSex(string $sex): self
    {
        $this->sex = $sex;
        return $this;
    }
    
    
    public function getAddress(): ?string
    {
        return $this->address;
    }
    
    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }
    
    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }
    
    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;
        return $this;
    }
    
    public function getCity(): ?string
    {
        return $this->city;
    }
    
    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }
    
    public function getPhoneEmergency(): ?string
    {
        return $this->phone_emergency;
    }
    
    public function setPhoneEmergency(?string $phone_emergency): self
    {
        $this->phone_emergency = $phone_emergency;
        return $this;
    }
    
    public function getNationality(): ?string
    {
        return $this->nationality;
    }
    
    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;
        return $this;
    }
    
    public function getMails(): ?string
    {
        return $this->mails;
    }
    
    public function getMailsToArray(): array
    {
        if($this->mails == null || '' === $this->mails) {
            return [];
        }
        return explode(',', str_replace(' ', '', $this->mails));
    }
    
    public function setMails(?string $mails): self
    {
        $this->mails = $mails;
        return $this;
    }

    public function getLogin()
    {
        return $this->login;
    }
    
    public function setLogin($login)
    {
        $this->login = $login;
    }
    
    public function getSubscribes()
    {
        return $this->subscribes;
    }
    
    public function setSubscribes($subscribes)
    {
        $this->subscribes = $subscribes;
    }
 
    /**
     * {@inheritDoc}
     * @see \App\Util\NestedValidation::validateNested()
     */
    public function validateNested(RequestUtil $requestUtil): ConstraintViolationListInterface
    {
        // subscribes
        if(empty($this->subscribes)) {
            return new ConstraintViolationList();
        }
        return $requestUtil->findErrors($this->subscribes); // 400
    }
 
    public function getRoles(): ?array
    {
        return $this->roles === null ? null : array_unique(array_map('strtoupper', $this->roles));
    }
    
    public function setRoles($roles)
    {
        $this->roles = $roles !== null ? array_unique(array_map('strtoupper', $roles)) : [];
    }
    
    
}

