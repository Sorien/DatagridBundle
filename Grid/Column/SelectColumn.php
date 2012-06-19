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

class SelectColumn extends Column
{
    const BLANK = '_default';

    private $values;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->values = $this->getParam('values', array());
    }

    public function setData(FilterStorageBag $data)
    {
        if ($data->get('value', $this::BLANK) !== $this::BLANK)
        {
            $this->data->assign($data);
        }

        return $this;
    }

    public function getFilters()
    {
        return array(new Filter(self::OPERATOR_EQ, $this->data->get('value')));
    }

    public function isFiltered()
    {
        return $this->data->has('value');
    }

    public function getValues()
    {
        return $this->values;
    }

    public function renderCell($value, $row, $router)
    {
        $value = is_bool($value) ? (int)$value : $value;

        if (key_exists((string)$value, $this->values))
        {
            $value = $this->values[$value];
        }

        return parent::renderCell($value, $row, $router);
    }

    public function getType()
    {
        return 'select';
    }
}
