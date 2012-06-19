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

class MassAction implements MassActionInterface
{
    private $title;
    private $callback;
    private $confirm;
    private $arguments;
    
    /**
     * Default MassAction constructor
     *
     * @param string $title Title of the mass action
     * @param string $callback Callback of the mass action
     * @param boolean $confirm Show confirm message if true
     * @return MassAction
     */
    public function __construct($title, $callback = null, $confirm = '', $arguments = array())
    {
        $this->setTitle($title);
        $this->setCallback($callback);
        $this->setConfirm($confirm);
        $this->setArguments($arguments);
    }

    /**
     * Set action title
     *
     * @param $title
     * @return MassAction
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * get action title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set action callback
     *
     * @param  $callback
     * @return MassAction
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * get action callback
     *
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set action confirm
     *
     * @param  $confirm
     * @return MassAction
     */
    public function setConfirm($confirm)
    {
        $this->confirm = str_replace('{title}', $this->getTitle(), $confirm);

        return $this;
    }

    /**
     * get action confirm
     *
     * @return string
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @param $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
