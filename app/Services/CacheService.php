<?php

namespace App\Services;

class CacheService
{
    protected $cache;

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    public function getCache(string $key)
    {
        return $this->cache->get($key);
    }

    public function saveCache(string $key, $data, int $ttl)
    {
        $this->cache->save($key, $data, $ttl);
    }

    public function invalidateCache(string $key)
    {
        $this->cache->delete($key);
    }
}

