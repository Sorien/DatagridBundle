<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Column;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Sorien\DataGridBundle\Grid\Helper\FilterStorageBag;
use Sorien\DataGridBundle\Grid\Row;
use Sorien\DataGridBundle\Grid\Filter;

abstract class Column
{
    const DATA_CONJUNCTION = 0;
    const DATA_DISJUNCTION = 1;

    const OPERATOR_EQ   = 'eq';
    const OPERATOR_NEQ  = 'neq';
    const OPERATOR_LT   = 'lt';
    const OPERATOR_LTE  = 'lte';
    const OPERATOR_GT   = 'gt';
    const OPERATOR_GTE  = 'gte';
    const OPERATOR_REGEXP = 'req';

    const ALIGN_LEFT = 'left';
    const ALIGN_RIGHT = 'right';
    const ALIGN_CENTER = 'center';

    /**
     * Internal parameters
     */
    private $id;
    private $title;
    private $sortable;
    private $filterable;
    private $visible;
    private $callback;
    private $order = '';
    private $size;
    private $visibleForSource;
    private $primary;
    private $align;
    private $field;
    private $role;

    private $params;
    private $isSorted = false;
    private $orderUrl;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var FilterStorageBag
     */
    protected $data;

    /**
     * Default Column constructor
     *
     * @param array $params
     * @return Column
     */
    public function __construct($params = null)
    {
        $this->__initialize((array) $params);
    }

    public function __initialize(array $params)
    {
        $this->params = $params;

        $this->setId($this->getParam('id'));
        $this->setTitle($this->getParam('title', ''));
        $this->setSortable($this->getParam('sortable', true));
        $this->setVisible($this->getParam('visible', true));
        $this->setSize($this->getParam('size', -1));
        $this->setFilterable($this->getParam('filterable', true));
        $this->setVisibleForSource($this->getParam('source', false));
        $this->setPrimary($this->getParam('primary', false));
        $this->setAlign($this->getParam('align', self::ALIGN_LEFT));
        $this->setField($this->getParam('field'));
        $this->setRole($this->getParam('role'));
        $this->setOrder($this->getParam('order'));

        $this->data = new FilterStorageBag();
    }

    protected function getParam($id, $default = null)
    {
        return isset($this->params[$id]) ? $this->params[$id] : $default;
    }

    /**
     * Draw filter
     *
     * @todo probably make function as abstract
     * @param string $gridHash
     * @return string
     */
    public function renderFilter($gridHash)
    {
        return '';
    }

    /**
     * Draw cell
     *
     * @param string $value
     * @param Row $row
     * @param $router
     * @return string
     */
    public function renderCell($value, $row, $router)
    {
        if (is_callable($this->callback))
        {
            return call_user_func($this->callback, $value, $row, $router);
        }
        else
        {
            return $value;
        }
    }

    /**
     * Set column callback
     *
     * @param  $callback
     * @return Column
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set column identifier
     *
     * @param $id
     * @return Column
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * get column identifier
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set column title
     *
     * @param string $title
     * @return Column
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get column title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return column visibility
     *
     * @return bool return true when column is visible
     */
    public function isVisible()
    {
        if ($this->visible && $this->securityContext !== null && $this->getRole() != null)
        {
            return $this->securityContext->isGranted($this->getRole());
        }

        return $this->visible;
    }

    /**
     * Set column visibility
     *
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * Return true is column is sorted
     *
     * @return bool return true when column is sorted
     */
    public function isSorted()
    {
        return $this->isSorted;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
    }

    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Return true is column is sorted filtered
     *
     * @return boolean
     */
    public function isFiltered()
    {
        return !$this->data->isEmpty();
    }

    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;
    }

    public function getFilterable()
    {
        return $this->filterable;
    }

    /**
     * Column ability to filter
     *
     * @return bool return true when column can be filtred
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * Column ability to sort
     *
     * @return bool return true when column can be sorted
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * Set column order
     *
     * @param string $order asc|desc
     * @return Column
     */
    public function setOrder($order)
    {
        if (!$order) {
            return $this;
        }

        $this->order = $order;
        $this->isSorted = true;

        return $this;
    }

    /**
     * Get column order
     *
     * @return string asc|desc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get data filter connection (conndition between filters)
     *
     * @return bool column::DATA_CONJUNCTION | column::DATA_DISJUNCTION
     */
    public function getFiltersConnection()
    {
        return self::DATA_CONJUNCTION;
    }

    /**
     * Get column data filters
     * todo: maybe change to own class not array
     *
     * @return Filter[]
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * Set column width
     *
     * @param int $size in pixels
     * @return Column
     */
    public function setSize($size)
    {
        if ($size >= -1)
        {
            $this->size = $size;
        }
        else throw new \InvalidArgumentException(sprintf('Unsupported column size %s, use positive value or -1 for auto resize', $size));

        return $this;
    }

    /**
     * Get column width
     *
     * @return int column width in pixels
     */
    public function getSize()
    {
        return $this->size;
    }

    public function setOrderUrl($url)
    {
        $this->orderUrl = $url;

        return $this;
    }

    public function getOrderUrl()
    {
        return $this->orderUrl;
    }

    /**
     * Set filter data from Storage or Request
     *
     * @param  FilterStorageBag $data
     * @return Column
     */
    public function setData(FilterStorageBag $data)
    {
        $this->data->assign($data);
        return $this;
    }

    /**
     * Get filter data for Storage
     *
     * @return FilterStorageBag data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set column visibility for source class
     *
     * @param $value
     * @return Column
     */
    public function setIsVisibleForSource($value)
    {
        $this->visibleForSource = $value;

        return $this;
    }

    /**
     * Return true is column in visible for source class
     *
     * @return boolean
     */
    public function isVisibleForSource()
    {
        return $this->visibleForSource;
    }

    public function setVisibleForSource($visibleForSource)
    {
        $this->visibleForSource = $visibleForSource;
    }

    /**
     * Return true is column in primary
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->primary;
    }

    /**
     * Set column as primary
     *
     * @param boolean $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }

    /**
     * Set column align
     *
     * @param string $align left/right/center
     * @throws \InvalidArgumentException
     */
    public function setAlign($align)
    {
        if ($align == $this::ALIGN_LEFT || $align == $this::ALIGN_CENTER || $align == $this::ALIGN_RIGHT)
        {
            $this->align = $align;
        }
        else throw new \InvalidArgumentException(sprintf('Unsupported align %s, just left, right and center are supported', $align));
    }

    /**
     * Get column align
     *
     * @return bool
     */
    public function getAlign()
    {
        return $this->align;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getType()
    {
        return '';
    }

    public function getParentType()
    {
        return '';
    }

    /**
     * Internal function
     *
     * @param $securityContext
     */
    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }
}
