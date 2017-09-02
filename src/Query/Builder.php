<?php

namespace TelcoLAB\Cacher\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;

class Builder extends QueryBuilder
{
    /**
     * @var mixed
     */
    protected $cacheInstance;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var string
     */
    protected $cacheTag;

    /**
     * @var string
     */
    protected $cachePrefix = 'cacher';

    /**
     * @var int
     */
    protected $cacheExpireAfer;

    /**
     * @return array
     */
    public function runSelect()
    {
        return $this->runSelectOrGetFromCache();
    }

    /**
     * @return mixed
     */
    public function runSelectOrGetFromCache()
    {
        return $this->getCacheInstance()->remember($this->getCacheKey(), $this->getCacheExpiry(), function () {
            return parent::runSelect();
        });
    }

    /**
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        $parent = parent::update($values);

        $this->flush();

        return $parent;
    }

    /**
     * @param $id
     * @return int
     */
    public function delete($id = null)
    {
        $parent = parent::delete($id);

        $this->flush();

        return $parent;
    }

    /**
     * @return void
     */
    public function truncate()
    {
        parent::truncate();

        $this->flush();
    }

    /**
     * @return mixed
     */
    public function getCacheInstance()
    {
        return $this->cacheInstance ?? $this->cacheInstance = cache()->tags($this->getCacheTag());
    }

    /**
     * @return string
     */
    public function getCacheTag()
    {
        return $this->cacheTag ?? $this->cacheTag = implode(':', [
            $this->cachePrefix,
            $this->from,
        ]);
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey ?? $this->generateCacheKey();
    }

    /**
     * @return string
     */
    public function generateCacheKey()
    {
        $connectionName = $this->connection->getName();
        $sqlQuery       = $this->toSql();
        $sqlBindings    = serialize($this->getBindings());

        return hash('sha256', json_encode([
            $connectionName,
            $sqlQuery,
            $sqlBindings,
        ]));
    }

    /**
     * @return int
     */
    public function getCacheExpiry()
    {
        return $this->cacheExpireAfer;
    }

    /**
     * @param int $minutes
     * @return mixed
     */
    public function setCacheExpireAfter(int $minutes)
    {
        $this->cacheExpireAfer = $minutes;

        return $this;
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->getCacheInstance()->flush();
    }
}
