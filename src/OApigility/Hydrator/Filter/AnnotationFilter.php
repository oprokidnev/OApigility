<?php

namespace OApigility\Hydrator\Filter;

/**
 *
 * @author oprokidnev
 */
class AnnotationFilter
{

    protected $annotationReader;
    /**
     * 
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    protected function getAnnotationReader()
    {
        if ((null === $this->annotationReader)) {
            $this->annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
        }
        return $this->annotationReader;
    }

    public function filter($property, $value, $targetObject, $hydrator)
    {
        if (count($hydrator->getGroups())) {
            $annotationReader = $this->getAnnotationReader();

            $reflClass    = new \ReflectionClass(get_class($targetObject));
            $reflProperty = $reflClass->getProperty($property);

            $excludeAnnotation = $annotationReader->getPropertyAnnotation($reflProperty, \OApigility\Hydrator\Annotation\Exclude::class);

            /* @var $excludeAnnotation \OApigility\Hydrator\Annotation\Exclude */
            if ((null !== $excludeAnnotation)) {
                $groups = $excludeAnnotation->getGroups();
            } else {
                $groups = [];
            }
            if(count(array_intersect($hydrator->getGroups(), $groups))){
                return false;
            }
        }
        return true;
    }

}
