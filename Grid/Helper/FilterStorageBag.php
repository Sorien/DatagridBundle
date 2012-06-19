<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Helper;

use Symfony\Component\HttpFoundation\ParameterBag;

class FilterStorageBag extends ParameterBag
{
    /**
     * @param FilterStorageBag $storageBag
     * @return bool
     */
    public function equals(FilterStorageBag $storageBag)
    {
        return $storageBag != null ? $this->all() === $storageBag->all() : false;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->parameters) == 0;
    }

    public function assign(FilterStorageBag $storageBag)
    {
        $this->parameters = $storageBag->all();
    }
}
