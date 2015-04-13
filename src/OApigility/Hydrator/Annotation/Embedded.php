<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OApigility\Hydrator\Annotation;

/**
 * e.g:
 *  Embedded("identity")
 * 
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Embedded
{

    /**
     *
     * @var array
     */
    protected $groups = ['default'];

    public function __construct($values)
    {
        if (isset($values['value'])) {
            $this->setGroups((array)$values['value']);
        }
    }

    /**
     * 
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * 
     * @param array $groups
     * @return \OApigility\Hydrator\Annotation\Embedded
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

}
