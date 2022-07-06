<?php
namespace App\Util\Page;

use Symfony\Component\Serializer\Exception\UnsupportedException;

class Unpaged extends Pageable
{

    public function __construct()
    {}
    
    /**
     * {@inheritDoc}
     * @see \App\Util\Page\Pageable::isPaged()
     */
    public function isPaged(): bool
    {
        return false;
    }

    public function getPageNumber(): int
    {
        throw new UnsupportedException();
    }

    public function getPageSize(): int
    {
        throw new UnsupportedException();
    }
    
    public function getOffset(): int
    {
        throw new UnsupportedException();
    }
    public function next(): Pageable
    {
        return $this;
    }

    public function hasPrevious(): bool
    {
        return false;
    }

    public function first(): Pageable
    {
        return $this;
    }
    
    public function withPage(int $pageNumber): Pageable
    {
        if($pageNumber === 0) {
            return $this;
        }
        throw new UnsupportedException();
    }

    public function previousOrFirst(): Pageable
    {
        return $this;
    }

    
}

