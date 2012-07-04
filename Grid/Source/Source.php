<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Source;

use Sorien\DataGridBundle\Grid\Column;
use Sorien\DataGridBundle\Grid\Columns;
use Sorien\DataGridBundle\Grid\Mapping\Driver\DriverInterface;
use Sorien\DataGridBundle\Grid\Row;
use Sorien\DataGridBundle\Grid\Rows;

abstract class Source implements DriverInterface
{
    const EVENT_PREPARE = 0;
    const EVENT_PREPARE_QUERY = 1;
    const EVENT_PREPARE_ROW = 2;

    private $callbacks;

    /**
     * @todo move to entity source - query builder is specific part of doctrine
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    public function prepareQuery($queryBuilder)
    {
        if (isset($this->callbacks[$this::EVENT_PREPARE_QUERY]) && is_callable($this->callbacks[$this::EVENT_PREPARE_QUERY]))
        {
            call_user_func($this->callbacks[$this::EVENT_PREPARE_QUERY], $queryBuilder);
        }
    }

    /**
     * @param Row $row
     *
     * @return Row
     */
    public function prepareRow($row)
    {
        if (isset($this->callbacks[$this::EVENT_PREPARE_ROW]) && is_callable($this->callbacks[$this::EVENT_PREPARE_ROW]))
        {
            return call_user_func($this->callbacks[$this::EVENT_PREPARE_ROW], $row);
        }

        return $row;
    }

    /**
     * @param int $type Source::EVENT_PREPARE*
     * @param \Closure $callback
     *
     * @return Source
     */
    public function setCallback($type, $callback)
    {
        $this->callbacks[$type] = $callback;

        return $this;
    }

    /**
     * Find data for current page
     *
     * @abstract
     * @param Column[]|Columns $columns
     * @param int $page
     * @param int $limit
     *
     * @return Rows
     */
    abstract public function execute($columns, $page = 0, $limit = 0);

    /**
     * Get Total count of data items
     *
     * @param Column[]|Columns $columns
     *
     * @return int
     */
    abstract function getTotalCount($columns);

    /**
     * @param Column[]|Columns $columns
     * @return array
     */
    abstract function getPrimaryKeys($columns);

    /**
     * Set container
     *
     * @abstract
     * @param  $container
     * @return void
     */
    abstract public function initialise($container);

    /**
     * @abstract
     * @param $columns
     * @return mixed
     */
    abstract public function getColumns($columns);

    /**
     * @param $class
     * @return array
     */
    public function getClassColumns($class)
    {
        return array();
    }

    public function getFieldsMetadata($class)
    {
        return array();
    }

    /**
    * Return source hash string
    * @abstract
    */
    abstract function getHash();

    /**
     * Delete one or more objects
     *
     * @abstract
     * @param array $ids
     * @return void
     */
    abstract public function delete(array $ids);
}
