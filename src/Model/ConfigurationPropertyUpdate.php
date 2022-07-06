<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="ConfigurationPropertyUpdate",
 *   required={"key", "value"}
 * )
 */
class ConfigurationPropertyUpdate
{
	/**
	 * @Assert\NotBlank
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="my.key")
	 */
	private $key;

	/**
	 * @Assert\NotBlank
	 * @Assert\Type("string")
	 * @Assert\Length(max = 512)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="my-value")
	 */
	private $value;

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

}
