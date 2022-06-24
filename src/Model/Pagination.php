<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;
use App\Util\Pager;

/**
 * @Serializer\XmlRoot("pagination")
 * @OA\Schema(schema="Pagination")
 * 
 * @Hateoas\Relation(
 *     "first",
 *     href = "expr(object.getRoute() ~ '?n=' ~ object.getSize() ~ '&page=1' ~ object.getQueryParameters())")
 * @Hateoas\Relation(
 *     "previous",
 *     href = "expr(object.getRoute() ~ '?n=' ~ object.getSize() ~ '&page=' ~ (object.getPage() - 1) ~ object.getQueryParameters())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(false === object.getHasPrevious())"))
 * @Hateoas\Relation(
 *     "next",
 *     href = "expr(object.getRoute() ~ '?n=' ~ object.getSize() ~ '&page=' ~ (object.getPage() + 1) ~ object.getQueryParameters())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(false === object.getHasNext())"))
 */
class Pagination
{

    /**
     * @Serializer\Exclude
     */
    private $route;
    
    /**
	 * @OA\Property(type="integer", format="int32", example="0")
	 */
	private $page;

	/**
	 * @OA\Property(type="integer", format="int32", example="20")
	 */
	private $size;

	/**
	 * @Serializer\Exclude
	 */
	private $hasNext;

	/**
	 * @Serializer\Exclude
	 */
	private $hasPrevious;
	
	private $_embedded;
	
	/**
	 * @Serializer\Exclude
	 */
	private ?array $queryParameters;
	
	public function __construct($route, Pager $pager, $data, $viewConverter, ?array $queryParameters = [])
	{
	    $dataSliced = array_slice($data, 0, $pager->getElementByPage());
	    $this->_embedded = array();
	    foreach ($dataSliced as &$d) {
	       array_push($this->_embedded, $viewConverter($d));
 	    }
	    
	    $this->route = $route;
	    $this->page = $pager->getPage();
	    $this->size = $pager->getElementByPage();
	    $this->hasNext = count($data) > $pager->getElementByPage();
	    $this->hasPrevious = $this->page > 1;
		$this->queryParameters = $queryParameters;
	}
	
	public function getPage(): ?int
	{
		return $this->page;
	}

	public function getSize(): ?int
	{
		return $this->size;
	}
	
	public function getRoute(): string
	{
	    return $this->route;
	}
	
	public function getHasNext(): ?bool
	{
	    return $this->hasNext;
	}
	
	public function getHasPrevious(): ?bool
	{
	    return $this->hasPrevious;
	}

	public function get_embedded()
	{
	    return $this->_embedded;
	}
	
	public function getQueryParameters(): string
	{
	    if(empty($this->queryParameters)) {
	        return '';
	    }
	    return '&'.http_build_query($this->queryParameters);
	}
}

