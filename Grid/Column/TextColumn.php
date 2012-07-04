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

use Sorien\DataGridBundle\Grid\Filter;
use Sorien\DataGridBundle\Grid\Helper\FilterStorageBag;

class TextColumn extends Column
{
    private $pattern;
    private $columns;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        /**
         * defined like ":foo, :faa - :foo"
         */
        $this->pattern = $this->getParam('pattern', ':'.$this->getId());
        $this->columns = $this->getParam('columns', array());
    }

    public function getFilters()
    {
        $result = array(new Filter(self::OPERATOR_REGEXP, '/.*'.$this->data->get('value', '').'.*/i'));

        if (!empty($this->columns))
        {
            foreach ($this->columns as $column)
            {
                $result[] = new Filter(self::OPERATOR_REGEXP, '/.*'.$this->data->get('value', '').'.*/i', $column);
            }
        }

        return $result;
    }

    public function renderCell($value, $row, $router)
    {
        $value = str_replace(":".$this->getId(), $value, $this->pattern);

        if (!empty($this->columns))
        {
            foreach ($this->columns as $column)
            {
                $value = str_replace(":".$column, $row->getField($column), $value);
            }
        }

        return $value;
    }

    public function getFiltersConnection()
    {
        return empty($this->columns) ? self::DATA_CONJUNCTION : self::DATA_DISJUNCTION;
    }

    public function setData(FilterStorageBag $data)
    {
        if ($data->get('value', '') != '')
        {
            $this->data->assign($data);
        }

        return $this;
    }

    public function isFiltered()
    {
        return $this->data->has('value');
    }

    public function getType()
    {
        return 'text';
    }
}
