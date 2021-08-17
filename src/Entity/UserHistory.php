<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserHistoryRepository")
 */
class UserHistory
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $modifier_user;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $modified_user;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $modification_date;

	/**
	 * @ORM\Column(type="string", length=64)
	 */
	private $element_name;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $previous_value;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $new_value;

	/**
	 * @ORM\Column(type="string", length=1)
	 */
	private $action;

	public function __construct()
	{
		$this->modification_date = new \DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getModifierUser(): ?User
	{
		return $this->modifier_user;
	}

	public function setModifierUser(?User $modifier_user): self
	{
		$this->modifier_user = $modifier_user;
		return $this;
	}

	public function getModifiedUser(): ?User
	{
		return $this->modified_user;
	}

	public function setModifiedUser(?User $modified_user): self
	{
		$this->modified_user = $modified_user;
		return $this;
	}

	public function getModificationDate(): ?\DateTimeInterface
	{
		return $this->modification_date;
	}

	public function setModificationDate(\DateTimeInterface $modification_date): self
	{
		$this->modification_date = $modification_date;
		return $this;
	}

	public function getElementName(): ?string
	{
		return $this->element_name;
	}

	public function setElementName(string $element_name): self
	{
		$this->element_name = $element_name;
		return $this;
	}

	public function getPreviousValue(): ?string
	{
		return $this->previous_value;
	}

	public function setPreviousValue(?string $previous_value): self
	{
		$this->previous_value = $previous_value;
		return $this;
	}

	public function getNewValue(): ?string
	{
		return $this->new_value;
	}

	public function setNewValue(?string $new_value): self
	{
		$this->new_value = $new_value;
		return $this;
	}

	public function getAction(): ?string
	{
		return $this->action;
	}

	public function setAction(string $action): self
	{
		$this->action = $action;
		return $this;
	}

	public static function diffToHistories($diffArray, Account $modifierAccount, User $modifiedUser) {
		$excludes = ['id', 'uuid', 'created'];

		$histories = [];

		// new / created
		foreach ($diffArray['new'] as $key => $value) {
			if(! in_array($key, $excludes) && $value !== null && $value !== '{}') {
				$userHistory = new UserHistory();
				$userHistory->setModifierUser($modifierAccount->getUser());
				$userHistory->setModifiedUser($modifiedUser);
				$userHistory->setElementName($key);
				$userHistory->setNewValue($value);
				$userHistory->setAction('C');
				array_push($histories, $userHistory);
			}
		}

		// removed / deleted
		foreach ($diffArray['removed'] as $key => $value) {
			if(! in_array($key, $excludes) && $value !== null && $value !== '{}') {
				$userHistory = new UserHistory();
				$userHistory->setModifierUser($modifierAccount->getUser());
				$userHistory->setModifiedUser($modifiedUser);
				$userHistory->setElementName($key);
				$userHistory->setPreviousValue($value);
				$userHistory->setAction('D');
				array_push($histories, $userHistory);
			}
		}

		// edited / updated
		foreach ($diffArray['edited'] as $key => $values) {
			if(! in_array($key, $excludes)) {
				$userHistory = new UserHistory();
				$userHistory->setModifierUser($modifierAccount->getUser());
				$userHistory->setModifiedUser($modifiedUser);
				$userHistory->setElementName($key);
				$userHistory->setPreviousValue($values['oldvalue']);
				$userHistory->setNewValue($values['newvalue']);
				$userHistory->setAction('U');
				array_push($histories, $userHistory);
			}
		}

		return $histories;
	}
}
