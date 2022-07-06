<?php
namespace App\Util\Page;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Spring
 */
abstract class Pageable
{
    const DEFAULT_COUNT_ELEMENTS = 20;
    const MAX_COUNT_ELEMENTS = 100;
    
    public static function unpaged(): Pageable
    {
        return new Unpaged();
    }
    
    public static function of(Request $request): Pageable
    {
        $page = max(intval($request->query->get('page', 1)), 1);
        $size = max(min(intval($request->query->get('n', self::DEFAULT_COUNT_ELEMENTS)), self::MAX_COUNT_ELEMENTS), 1);
        return new PageRequest($page, $size);
    }
    
    public function isPaged(): bool
    {
        return true;
    }
    
    public function isUnpaged(): bool
    {
        return ! $this->isPaged();
    }
    
    abstract public function getPageNumber(): int;
    
    abstract public function getPageSize(): int;
    
    abstract public function getOffset(): int;
    // TODO getSort()
    
    abstract public function next(): Pageable;
    
    abstract public function previousOrFirst(): Pageable;
    
    abstract public function first(): Pageable;
    
    abstract public function withPage(int $pageNumber): Pageable;
    
    abstract public function hasPrevious(): bool;
}

