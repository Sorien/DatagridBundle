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

class RangeColumn extends TextColumn
{
    private $inputType;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->setInputType($this->getParam('inputType', 'text'));
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
    }

    public function getFilters()
    {
        $result = array();

        if ($this->data->has('from'))
        {
           $result[] =  new Filter(self::OPERATOR_GTE, $this->data->getInt('from'));
        }

        if ($this->data->has('to'))
        {
           $result[] =  new Filter(self::OPERATOR_LTE,  $this->data->getInt('to'));
        }

        return $result;
    }

    public function setData(FilterStorageBag $data)
    {
        if ($data->get('from', 0) != 0)
        {
            $this->data->set('from', $data->get('from'));
        }

        if ($data->get('to', 0) != 0)
        {
            $this->data->set('to', $data->get('to'));
        }

        return $this;
    }

    public function isFiltered()
    {
        return ($this->data->has('from') || $this->data->has('to'));
    }

    public function getType()
    {
        return 'range';
    }
}
