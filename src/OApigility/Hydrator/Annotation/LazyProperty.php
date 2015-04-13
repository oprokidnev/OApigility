<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OApigility\Hydrator\Annotation;

/**
 * e.g:
 *  LazyProperty(propertyName="someField"[,arguments={"argument":"agrumentValue"}])
 * 
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *  @Attribute("propertyName", type = "string"),
 * })
 */
class LazyProperty
{

    /**
     *
     * @var array
     */
    protected $propertyName = 'additional';
    protected $arguments    = [];

    public function __construct($values)
    {
        if (isset($values['propertyName'])) {
            $this->setPropertyName($values['propertyName']);
        }
        if (isset($values['arguments'])) {
            $this->setArguments($values['arguments']);
        }
    }

    /**
     * 
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * 
     * @param string $propertyName
     * @return \OApigility\Hydrator\Annotation\LazyProperty
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * 
     * @param array $arguments
     * @return \OApigility\Hydrator\Annotation\LazyProperty
     */
    public function setArguments(array $arguments = [])
    {
        $this->arguments = $arguments;
        return $this;
    }

}
