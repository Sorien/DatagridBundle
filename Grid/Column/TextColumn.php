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
    public function getFilters()
    {
        return array(new Filter(self::OPERATOR_REGEXP, '/.*'.$this->data->get('value', '').'.*/i'));
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
