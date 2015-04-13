<?php

namespace OApigility\Hydrator\Strategy;

/**
 * Description of JsonNamingStrategy
 *
 * @author oprokidnev
 */
class JsonNamingStrategy implements NamingStrategyInterface
{

    /**
     *
     * @var \Zend\Filter\FilterInterface
     */
    protected $arrayFilter = null;

    /**
     *
     * @var \Zend\Filter\FilterInterface
     */
    protected $objectFilter = null;

    public function __construct()
    {
        $this->arrayFilter  = new \Zend\Filter\Word\CamelCaseToUnderscore();
        $this->objectFilter = new \Zend\Filter\Word\UnderscoreToCamelCase();
    }

    /**
     * {@inheritDoc}
     */
    public function extract($objectKey)
    {
        return strtolower( $this->getArrayFilter()->filter($objectKey) );
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($arrayKey)
    {
        return $this->getObjectFilter()->filter($arrayKey);
    }

    /**
     * 
     * @return \Zend\Filter\FilterInterface
     */
    public function getArrayFilter()
    {
        return $this->arrayFilter;
    }

    /**
     * 
     * @return \Zend\Filter\FilterInterface
     */
    public function getObjectFilter()
    {
        return $this->objectFilter;
    }

    /**
     * 
     * @param \Zend\Filter\FilterInterface $arrayFilter
     * @return \OApigility\Hydrator\Strategy\JsonNamingStrategy
     */
    public function setArrayFilter(\Zend\Filter\FilterInterface $arrayFilter)
    {
        $this->arrayFilter = $arrayFilter;
        return $this;
    }

    /**
     * 
     * @param \Zend\Filter\FilterInterface $objectFilter
     * @return \OApigility\Hydrator\Strategy\JsonNamingStrategy
     */
    public function setObjectFilter(\Zend\Filter\FilterInterface $objectFilter)
    {
        $this->objectFilter = $objectFilter;
        return $this;
    }

}
