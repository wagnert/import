<?php

/**
 * TechDivision\Import\Cache\GenericCacheAdapter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Cache;

use Psr\Cache\CacheItemPoolInterface;
use TechDivision\Import\ConfigurationInterface;

/**
 * Generic cache adapter implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2019 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
class GenericCacheAdapter implements CacheAdapterInterface
{

    /**
     * The configuration instance.
     *
     * @var \TechDivision\Import\ConfigurationInterface
     */
    protected $configuration;

    /**
     * The cache for the query results.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * References that links to another cache entry.
     *
     * @var array
     */
    protected $references = array();

    /**
     * Initialize the cache handler with the passed cache and configuration instances.
     * .
     * @param \Psr\Cache\CacheItemPoolInterface           $cache The cache instance
     * @param \TechDivision\Import\ConfigurationInterface $configuration The configuration instance
     */
    public function __construct(CacheItemPoolInterface $cache, ConfigurationInterface $configuration)
    {

        // set the cache and configuration instance
        $this->cache = $cache;
        $this->configuration = $configuration;
    }

    /**
     * Resolve's the cache key.
     *
     * @param string $from The cache key to resolve
     *
     * @return string The resolved reference
     */
    protected function resolveReference($from)
    {

        // query whether or not a reference exists
        if (isset($this->references[$from])) {
            return $this->references[$from];
        }

        // return the passed reference
        return $from;
    }

    /**
     * Prepares a unique cache key for the passed query name and params.
     *
     * @param string $uniqueName A unique name used to prepare the cache key with
     * @param array  $params     The query params
     *
     * @return string The prepared cache key
     */
    public function cacheKey($uniqueName, array $params)
    {
        return str_replace('\\', '-', sprintf('%s-%s', $uniqueName, implode('-', $params)));
    }

    /**
     * Query whether or not a cache value for the passed cache key is available.
     *
     * @param string $key The cache key to query for
     *
     * @return boolean TRUE if the a value is available, else FALSE
     */
    public function isCached($key)
    {

        // query whether or not the item has been cached, and if yes if the cache is valid
        if ($this->cache->hasItem($resolvedKey = $this->resolveReference($key))) {
            return $this->cache->getItem($resolvedKey)->isHit();
        }

        // return FALSE in all other cases
        return false;
    }

    /**
     * Inversion of the isCached() method.
     *
     * @param string $key The cache key to query for
     *
     * @return boolean TRUE if the value is not available, else FALSE
     */
    public function notCached($key)
    {
        return !$this->isCached($key);
    }

    /**
     * Add's a cache reference from one key to another.
     *
     * @param string $from The key to reference from
     * @param string $to   The key to reference to
     *
     * @return void
     */
    public function addReference($from, $to)
    {
        $this->references[$from] = $to;
    }

    /**
     * Add the passed item to the cache.
     *
     * @param string  $key        The cache key to use
     * @param mixed   $value      The value that has to be cached
     * @param array   $references An array with references to add
     * @param boolean $override   Flag that allows to override an exising cache entry
     *
     * @return void
     */
    public function toCache($key, $value, array $references = array(), $override = false)
    {

        // query whether or not the key has already been used
        if ($this->isCached($key) && $override === false) {
            throw new \Exception(sprintf('Try to override data with key %s', $key));
        }

        // initialize the cache item
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($value);

        // set the attribute in the registry
        $this->cache->save($cacheItem);

        // also register the references if given
        foreach ($references as $from => $to) {
            $this->references[$from] = $to;
        }
    }

    /**
     * Returns a new cache item for the passed key
     *
     * @param string $key The cache key to return the item for
     *
     * @return mixed The value for the passed key
     */
    public function fromCache($key)
    {
        return $this->cache->getItem($this->resolveReference($key))->get();
    }

    /**
     * Flush the cache and remove the references.
     *
     * @return void
     */
    public function flushCache()
    {
        $this->cache->clear();
        $this->references = array();
    }

    /**
     * Remove the item with the passed key and all its references from the cache.
     *
     * @param string $key The key of the cache item to Remove
     *
     * @return void
     */
    public function removeCache($key)
    {
        $this->cache->deleteItem($this->resolveReference($key));
        unset($this->references[$key]);
    }

    /**
     * Raises the value for the attribute with the passed key by one.
     *
     * @param mixed $key         The key of the attribute to raise the value for
     * @param mixed $counterName The name of the counter to raise
     *
     * @return integer The counter's new value
     */
    public function raiseCounter($key, $counterName)
    {

        // initialize the counter
        $counter = 0;

        // raise/initialize the value
        if ($this->isCached($key)) {
            $value = $this->fromCache($key);
            $counter = $value[$counterName];
        }

        // set the counter value back to the cache item/cache
        $this->toCache($key, array($counterName => ++$counter), array(), true);

        // return the new value
        return $counter;
    }

    /**
     * This method merges the passed attributes with an array that
     * has already been added under the passed key.
     *
     * If no value will be found under the passed key, the attributes
     * will simply be registered.
     *
     * @param mixed $key        The key of the attributes that has to be merged with the passed ones
     * @param array $attributes The attributes that has to be merged with the exising ones
     *
     * @return void
     * @throws \Exception Is thrown, if the already registered value is no array
     * @link http://php.net/array_replace_recursive
     */
    public function mergeAttributesRecursive($key, array $attributes)
    {

        // if the key not exists, simply add the new attributes
        if ($this->notCached($key)) {
            $this->toCache($key, $attributes);
            return;
        }

        // if the key exists and the value is an array, merge it with the passed array
        if (is_array($value = $this->fromCache($key))) {
            $this->toCache($key, array_replace_recursive($value, $attributes), array(), true);
            return;
        }

        // throw an exception if the key exists, but the found value is not of type array
        throw new \Exception(sprintf('Can\'t merge attributes, because value for key %s already exists, but is not of type array', $key));
    }
}
