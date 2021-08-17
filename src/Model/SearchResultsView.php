<?php

namespace App\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("searchResults")
 */
class SearchResultsView
{
	private $q;
	private $pagination;
	private $results;

	public function __construct($q, $results, $pagination)
	{
		$this->q = $q;
		$this->pagination = $pagination;
		$this->results = $results;
	}

	public function getQ()
	{
		return $this->q;
	}

	public function getPagination()
	{
		return $this->pagination;
	}

	public function getResults()
	{
		return $this->results;
	}

}
