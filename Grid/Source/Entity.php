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

use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Columns;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Row;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Entity extends Source
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $manager;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $query;

    /**
     * @var string e.g Vendor\Bundle\Entity\Page
     */
    private $class;

    /**
     * @var string e.g Cms:Page
     */
    private $entityName;

    /**
     * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Metadata
     */
    private $metadata;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $ormMetadata;

    /**
     * @var array
     */
    private $joins;

    /**
     * @var array
     */
    private $hasDqlFunction;

    const TABLE_ALIAS = '_a';
    const COUNT_ALIAS = '__count';

    /**
     * @param string $entityName e.g Cms:Page
     */
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
        $this->joins = array();
        $this->hasDqlFunction = false;
    }

    public function initialise($container)
    {
        $this->manager = $container->get('doctrine')->getEntityManager();
        $this->ormMetadata = $this->manager->getClassMetadata($this->entityName);

        $this->class = $this->ormMetadata->getReflectionClass()->name;

        $mapping = $container->get('grid.mapping.manager');

        /** todo autoregister mapping drivers with tag */
        $mapping->addDriver($this, -1);
        $this->metadata = $mapping->getMetadata($this->class);
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
     * @param boolean $withAlias
     * @return string
     */
    private function getFieldName($column, $inSelect = true)
    {
        $name = $column->getField();
        $function = '';

        if (strpos($name, ':') !== false)
        {
            list($name, $function) = explode(':', $name, 2);
        }

        if (strpos($name, '.') === false)
        {
             $name = self::TABLE_ALIAS.'.'.$name;
        }
        else
        {
            $parent = self::TABLE_ALIAS;
            $elements = explode('.', $name);

            while ($element = array_shift($elements))
            {
                if (count($elements) > 0)
                {
                    $this->joins['_'.$element] = $parent.'.'.$element;
                    $parent = '_'.$element;
                    $name = $element;
                }
                else
                {
                    $name = '_'.$name.'.'.$element;
                }
            }
        }

        if ($inSelect)
        {
            if ($function != '')
            {
                $this->hasDqlFunction = true;
                $name = $function.'('.$name.')';
            }

            $name .= ' as '.'column_'.$column->getId();
        }

        return $name;
    }

    /**
     * @param Columns $columns
     * @return null
     */
    public function getColumns($columns)
    {
        foreach ($this->metadata->getColumnsFromMapping($columns) as $column)
        {
            $columns->addColumn($column);
        }
    }

    private function normalizeOperator($operator)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? 'like' : $operator);
    }

    /**
     * Fix Filter values to prevent SQL Injection
     *
     * @param $operator
     * @param $value
     * @return string
     */
    private function normalizeValue($operator, $value)
    {
        switch($operator)
        {
            case COLUMN::OPERATOR_REGEXP:
                preg_match('/\/\.\*([^\/]+)\.\*\//s', $value, $matches);
                return '\'%'.$matches[1].'%\'';

            case COLUMN::OPERATOR_EQ:
                return '\''.$value.'\'';

            default:
                return $value;
        }
    }

    /**
     * @param Column[]|Columns $columns
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildQuery($columns)
    {
        if ($this->query == null)
        {
            $this->query = $this->manager->createQueryBuilder($this->class);
            $this->query->from($this->class, self::TABLE_ALIAS);

            $where = $this->query->expr()->andx();

            foreach ($columns->getSourceColumns() as $column)
            {
                $this->query->addSelect($this->getFieldName($column));

                if ($column->isSorted())
                {
                    $this->query->orderBy($this->getFieldName($column, false), $column->getOrder());
                }

                if ($column->isFiltered())
                {
                    if($column->getFiltersConnection() == column::DATA_CONJUNCTION)
                    {
                        foreach ($column->getFilters() as $filter)
                        {
                            $operator = $this->normalizeOperator($filter->getOperator());

                            $where->add($this->query->expr()->$operator(
                                $this->getFieldName($filter->hasId() ? $columns->getColumnById($filter->getId()) : $column, false),
                                $this->normalizeValue($filter->getOperator(), $filter->getValue())
                            ));
                        }
                    }
                    elseif($column->getFiltersConnection() == column::DATA_DISJUNCTION)
                    {
                        $sub = $this->query->expr()->orx();

                        foreach ($column->getFilters() as $filter)
                        {
                            $operator = $this->normalizeOperator($filter->getOperator());

                            $sub->add($this->query->expr()->$operator(
                                $this->getFieldName($filter->hasId() ? $columns->getColumnById($filter->getId()) : $column, false),
                                $this->normalizeValue($filter->getOperator(), $filter->getValue())
                            ));
                        }
                        $where->add($sub);
                    }
                    $this->query->where($where);
                }
            }

            foreach ($this->joins as $alias => $field)
            {
                $this->query->leftJoin($field, $alias);
            }

            if ($this->hasDqlFunction)
            {
                foreach ($columns as $column)
                {
                    if ($column->isPrimary())
                    {
                        $this->query->groupBy($this->getFieldName($column, false));
                        break;
                    }
                }
               //
            }

            //call overridden prepareQuery or associated closure
            $this->prepareQuery($this->query);
        }

        return clone $this->query;
    }

    /**
     * @param Column[]|Columns $columns
     * @param int $page  Page Number
     * @param int $limit Rows Per Page
     * @return Rows
     */
    public function execute($columns, $page = 0, $limit = 0)
    {
        $query = $this->buildQuery($columns);

        $res = $query->getDQL();

        if ($page > 0)
        {
            $query->setFirstResult($page * $limit);
        }

        if ($limit > 0)
        {
            $query->setMaxResults($limit);
        }

        $items = $query->getQuery()->getResult();

        // hydrate result
        $result = new Rows();

        foreach ($items as $item)
        {
            $row = new Row();

            foreach ($item as $key => $value)
            {
                list($id, $name) = explode('_', $key, 2);
                $row->setField($name, $value);
            }

            //call overridden prepareRow or associated closure
            if (($modifiedRow = $this->prepareRow($row)) != null)
            {
                $result->addRow($modifiedRow);
            }
        }

        return $result;
    }

    /**
     * @param Column[]|Columns $columns
     * @return array
     */
    public function getPrimaryKeys($columns)
    {
        $query = $this->buildQuery($columns);
        $query->select($this->getFieldName($columns->getPrimaryColumn()));

        return $query->getQuery()->getResult();
    }

    /**
     * @param Column[]|Columns $columns
     * @return int
     */
    public function getTotalCount($columns)
    {
//        $query->select($this->getFieldName($columns->getPrimaryColumn(), false));
//
//        $qb = $this->manager->createQueryBuilder();
//
//        $qb->select($qb->expr()->count(self::COUNT_ALIAS. '.' . $columns->getPrimaryColumn()->getField()));
//        $qb->from($this->entityName, self::COUNT_ALIAS);
//        $qb->where($qb->expr()->in(self::COUNT_ALIAS. '.' . $columns->getPrimaryColumn()->getField(), $query->getDQL()));
//
//        //copy existing parameters.
//        $qb->setParameters($this->query->getParameters());
//
//        echo $qb->getQuery()->getDQL(); die();
//          $result = $qb->getQuery()->getSingleResult();
        try
        {
            $query = $this->buildQuery($columns);
            $query->resetDQLPart('orderBy')->select($this->query->expr()->count(self::TABLE_ALIAS.'.'.$columns->getPrimaryColumn()->getField()));
            $result = $query->getQuery()->getSingleResult();

            return (int) $result[1];
        }
        catch (NonUniqueResultException $e)
        {
            $query = $this->buildQuery($columns);
            $result = $query->getQuery()->getResult();
            return count($result);
        }
        catch (NoResultException $e)
        {
            return 0;
        }
    }

    public function getFieldsMetadata($class)
    {
        $result = array();
        foreach ($this->ormMetadata->getFieldNames() as $name)
        {
            $mapping = $this->ormMetadata->getFieldMapping($name);
            $values = array('title' => $name, 'source' => true);

            if (isset($mapping['fieldName']))
            {
                $values['field'] = $mapping['fieldName'];
                $values['id'] = $mapping['fieldName'];
            }

            if (isset($mapping['id']) && $mapping['id'] == 'id')
            {
                $values['primary'] = true;
            }

            switch ($mapping['type'])
            {
                case 'integer':
                case 'smallint':
                case 'bigint':
                case 'string':
                case 'text':
                case 'float':
                case 'decimal':
                    $values['type'] = 'text';
                    break;
                case 'boolean':
                    $values['type'] = 'boolean';
                    break;
                case 'date':
                case 'datetime':
                case 'time':
                    $values['type'] = 'date';
                break;
            }

            $result[$name] = $values;
        }

        return $result;
    }

    public function getHash()
    {
        return $this->entityName;
    }

    public function delete(array $ids)
    {
        $repository = $this->manager->getRepository($this->entityName);

        foreach ($ids as $id) {
            $object = $repository->find($id);

            if (!$object) {
                throw new \Exception(sprintf('No %s found for id %s', $this->entityName, $id));
            }

            $this->manager->remove($object);
        }

        $this->manager->flush();
    }
}
