<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Action;

class DeleteMassAction extends MassAction
{
    /**
     * Default DeleteMassAction constructor
     *
     * @param boolean $confirm Show confirm message if true
     * @return \Sorien\DataGridBundle\Grid\Action\MassAction
     */
    public function __construct($confirm = false)
    {
        parent::__construct('Delete', 'static::deleteAction', $confirm);
    }
}
