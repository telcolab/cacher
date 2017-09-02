<?php
namespace TelcoLAB\Cacher\Traits;

use TelcoLAB\Cacher\Query\Builder;

trait Cacheable
{
    /**
     * @var int
     */
    protected $cacheExpireAfter = 1440;

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \TelcoLAB\Cacher\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        $grammar = $connection->getQueryGrammar();

        $builder = new Builder($connection, $grammar, $connection->getPostProcessor());

        $builder->setCacheExpireAfter($this->cacheExpireAfter);

        return $builder;
    }
}
