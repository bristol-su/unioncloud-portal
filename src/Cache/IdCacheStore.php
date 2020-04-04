<?php

namespace BristolSU\UnionCloud\Cache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;

class IdCacheStore implements IdStore
{

    /**
     * @var string
     */
    private $key;
    /**
     * @var Repository
     */
    private $cache;

    public function __construct(string $key, Repository $cache)
    {
        $this->key = $key;
        $this->cache = $cache;
    }

    /**
     * Get all ids
     * 
     * @return \Illuminate\Support\Collection
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function ids()
    {
        if($this->cache->has($this->key)) {
            return $this->cache->get($this->key);
        }
        return collect();
    }

    public function setIds(Collection $ids)
    {
        $this->cache->forever($this->key, $ids);
    }

    public function count()
    {
        return $this->ids()->count();
    }

    public function pop()
    {
        $ids = $this->ids();
        $id = $ids->shift();
        $this->setIds($ids);
        return $id;
    }

    public function push($id)
    {
        $ids = $this->ids();
        $ids = $ids->push($id);
        $this->setIds($ids);
    }
}