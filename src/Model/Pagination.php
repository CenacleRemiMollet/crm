<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @Serializer\XmlRoot("pagination")
 * @OA\Schema(schema="Pagination")
 */
class Pagination
{
	/**
	 * @OA\Property(type="integer", format="int32", example="0")
	 */
	private $page;

	/**
	 * @OA\Property(type="integer", format="int32", example="20")
	 */
	private $n;

	/**
	 * @OA\Property(type="boolean")
	 */
	private $hasmore;

	public function __construct($page, $n, $hasmore)
	{
		$this->page = $page;
		$this->n = $n;
		$this->hasmore = $hasmore;
	}

	public function getPage(): ?int
	{
		return $this->page;
	}

	public function getN(): ?int
	{
		return $this->n;
	}

	public function getHasmore(): ?bool
	{
		return $this->hasmore;
	}
}

