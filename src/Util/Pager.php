<?php
namespace App\Util;

use Symfony\Component\HttpFoundation\Request;

class Pager
{
	const DEFAULT_COUNT_ELEMENTS = 20;
	const MAX_COUNT_ELEMENTS = 100;

	private $page;

	private $elementByPage;

	private $offset;

	public function __construct(Request $request)
	{
		$this->page = max(intval($request->query->get('page', 1)), 1);
		$this->elementByPage = max(min(intval($request->query->get('n', self::DEFAULT_COUNT_ELEMENTS)), self::MAX_COUNT_ELEMENTS), 1);
		$this->offset = ($this->page - 1) * $this->elementByPage;
	}

	public function isValid(): ?bool
	{
		return $this->page >= 1 && $this->elementByPage >= 1;
	}

	public function getPage(): ?int
	{
		return $this->page;
	}

	public function getElementByPage(): ?int
	{
		return $this->elementByPage;
	}

	public function getOffset(): ?int
	{
		return $this->offset;
	}

}

