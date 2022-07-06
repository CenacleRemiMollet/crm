<?php
namespace App\Util\Page;


class PageRequest extends AbstractPageRequest
{
    
    public function __construct(int $page, int $size)
    {
        parent::__construct($page, $size);
    }

    public function next(): Pageable
    {
        return new PageRequest($this->getPageNumber() + 1, $this->getPageSize());
    }
    
    public function previous(): Pageable
    {
        return $this->getPageNumber() == 0 ? $this : new PageRequest($this->getPageNumber() - 1, $this->getPageSize());
    }
    
    public function first(): Pageable
    {
        return $this->getPageNumber() == 0 ? $this : new PageRequest(0, $this->getPageSize());
    }
    
    public function withPage(int $pageNumber): Pageable
    {
        return new PageRequest($pageNumber, $this->getPageSize());
    }
   
}

