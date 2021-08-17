<?php
namespace App\Util;

use Symfony\Component\HttpFoundation\Request;

class Pager
{
	const DEFAULT_COUNT_ELEMENTS = 20;
	const MAX_COUNT_ELEMENTS = 100;

	private $page;
	private $elementbypage;
	private $offset;

	public function __construct(Request $request)
	{
		$this->page = max(intval($request->query->get('page', 0)), 0);
		$this->elementbypage = min(intval($request->query->get('n', self::DEFAULT_COUNT_ELEMENTS)), self::MAX_COUNT_ELEMENTS);
		$this->offset = $this->page * $this->elementbypage;
	}

	public function isValid(): ?bool
	{
		return $this->page >= 0 && $this->elementbypage >= 0;
	}

	public function getPage(): ?int
	{
		return $this->page;
	}

	public function getElementByPage(): ?int
	{
		return $this->elementbypage;
	}

	public function getOffset(): ?int
	{
		return $this->offset;
	}

}

