<?php
namespace Druidvav\MemcacheBundle\Doctrine\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Clickalicious\Memcached\Client as MemcachedClient;

/**
 * Memcache cache provider (with prefix support).
 *
 * Based on: Doctrine/Common/Cache/MemcacheCache.php
 */
class MemcacheCache extends CacheProvider
{
    /**
     * @var MemcachedClient
     */
    private $memcache;

    /**
     * @var string prefix
     */
    private $prefix;

    /**
     * Sets the memcache instance to use.
     *
     * @param MemcachedClient $memcache
     */
    public function setMemcache(MemcachedClient $memcache)
    {
        $this->memcache = $memcache;
    }

    /**
     * Gets the memcache instance used by the cache.
     *
     * @return MemcachedClient
     */
    public function getMemcache()
    {
        return $this->memcache;
    }

    /**
     * Sets the prefix to use.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Gets the prefix used by the cache.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return $this->memcache->get($this->prefix.$id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return (bool) $this->memcache->get($this->prefix.$id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->memcache->set($this->prefix.$id, $data, (int) $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->memcache->delete($this->prefix.$id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->memcache->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $stats = $this->memcache->stats();
        return array(
            Cache::STATS_HITS   => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE       => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILIABLE  => $stats['limit_maxbytes'],
        );
    }
}
