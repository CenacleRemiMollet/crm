<?php
namespace App\Util\Page;


abstract class AbstractPageRequest extends Pageable
{
    /** @var int $page */
    private int $page;
    /** @var int $size */
    private int $size;
    
    public function __construct(int $page, int $size)
    {
        if($page < 0) {
            throw new \InvalidArgumentException("Page index must be not less than zero!");
        }
        if($size < 0) {
            throw new \InvalidArgumentException("Page size must be not less than one!");
        }
        $this->page = $page;
        $this->size = $size;
    }

    public function getPageNumber(): int
    {
        return $this->page;
    }
    
    public function getPageSize(): int
    {
        return $this->size;
    }

    public function getOffset(): int
    {
        return $this->page * $this->size;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 0;
    }

    public function previousOrFirst(): Pageable
    {
        return $this->hasPrevious() ? $this->previous() : $this->first();
    }
    
    abstract public function previous(): Pageable;
}

