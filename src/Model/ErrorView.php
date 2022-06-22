<?php

namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *     schema="Error",
 *     description="Error",
 *     title="Error",
 *     @OA\Xml(
 *         name="Error"
 *     )
 * )
 * @Serializer\XmlRoot("error")
 */
class ErrorView
{
	/**
	 * @OA\Property(type="int", example=500, minimum=100, maximum=599)
	 */
	private $status;

	/**
	 * @OA\Property(type="string", example="Internal Server Error")
	 */
	private $error;
	
	/**
	 * @OA\Property(type="string", example="Club not found: anywhere")
	 */
	private $message;

	/**
	 * @OA\Property(type="string", example="2022-06-22T07:27:38+00:00")
	 */
	private $timestamp;
	
	private $details;
	
	
	public function __construct()
	{
	    $datetime = new \DateTime();
	    $this->timestamp = $datetime->format(\DateTime::ATOM);
	}

	public function getStatus(): ?int
	{
	    return $this->status;
	}
	
	public function setStatus($status)
	{
	    $this->status = $status;
	    $this->error = Response::$statusTexts[$status];
	}

	public function getError(): ?string
	{
	    return $this->error;
	}
	
	public function setError($error)
	{
	    $this->error = $error;
	}
	
	public function getMessage(): ?string
	{
	    return $this->message;
	}
	
	public function setMessage($message)
	{
	    $this->message = $message;
	}
	
	public function getTimestamp(): ?string
	{
	    return $this->timestamp;
	}
	
	public function setTimestamp($timestamp)
	{
	    $this->timestamp = $timestamp;
	}
	
	public function getDetails()
	{
	    return $this->details;
	}
	
	public function setDetails($details)
	{
	    $this->details = $details;
	}
	
}
