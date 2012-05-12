<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Mapping\Metadata;

class Metadata
{
    private $name;
    private $fields;
    private $fieldsMappings;

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFieldsMappings($fieldsMappings)
    {
        $this->fieldsMappings = $fieldsMappings;
    }

    public function hasFieldMapping($field)
    {
        return isset($this->fieldsMappings[$field]);
    }

    public function getFieldMapping($field)
    {
        return $this->fieldsMappings[$field];
    }

    public function getFieldMappingType($field)
    {
        return (isset($this->fieldsMappings[$field]['type'])) ? $this->fieldsMappings[$field]['type'] : 'text';
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @todo move column extensions from columns to another place
     * @param \Sorien\DataGridBundle\Grid\Columns $columnExtensions
     * @return \Sorien\DataGridBundle\Grid\Column[]
     * @throws \Exception
     */
    public function getColumnsFromMapping($columnExtensions)
    {
        $columns = new \SplObjectStorage();

        foreach ($this->getFields() as $value)
        {
            $params = $this->getFieldMapping($value);
            $type = $this->getFieldMappingType($value);

            /** todo move available extensions from columns */
            if ($columnExtensions->hasExtensionForColumnType($type))
            {
                $column = clone $columnExtensions->getExtensionForColumnType($type);
                $column->__initialize($params);

                $columns->attach($column);
            }
            else
            {
                throw new \Exception(sprintf("No suitable Column Extension found for column type: %s", $type));
            }
        }

        return $columns;
    }
}
