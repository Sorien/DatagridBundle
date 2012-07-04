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

class DateColumn extends Column
{
    private $format;
    private $pattern;
    private $single;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->format = $this->getParam('format', 'dd.MM.yyyy');
        $this->pattern = $this->getParam('pattern', 'Y-m-d H:i:s');
        $this->single =  $this->getParam('single', true);
    }

    public function renderCell($value, $row, $router)
    {
        if ($value != null)
        {
            if (is_string($value))
            {
                $value = new \DateTime($value);
            }

            if ($value instanceof \DateTime)
            {
                return parent::renderCell($value->format($this->pattern), $row, $router);
            }

            throw \InvalidArgumentException('Date Column value have to be DataTime object');
        }
        else
        {
            return '';
        }
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
        return 'date';
    }
}
