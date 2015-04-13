<?php

namespace OApigility\Hydrator\Filter;

/**
 *
 * @author oprokidnev
 */
class MetadataFilter
    implements \Zend\ServiceManager\ServiceLocatorAwareInterface
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    
    
    /**
     *
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $annotationReader = null;

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

    /**
     * @param EntityManager $entityManager
     * @param string|object $object
     *
     * @return boolean
     */
    protected function isEntity($object)
    {
        $entityManager = $this->getEntityManager();
        if (is_object($object)) {
            $object = ($object instanceof \Doctrine\Common\Proxy\Proxy) ? get_parent_class($object)
                    : get_class($object);
        }else{
            return false;
        }

        return !$entityManager->getMetadataFactory()->isTransient($object);
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        $serviceLocator = $this->getServiceLocator();
        return $serviceLocator->get(\Doctrine\ORM\EntityManager::class);
    }

    public function filter($property, $value, $targetObject, $hydrator)
    {
        if ($this->isEntity($value)) {
            $annotationReader = $this->getAnnotationReader();

            $reflClass    = new \ReflectionClass(get_class($targetObject));
            $reflProperty = $reflClass->getProperty($property);

            $embeddedAnnotation = $annotationReader->getPropertyAnnotation($reflProperty,
                \OApigility\Hydrator\Annotation\Embedded::class);

            /* @var $excludeAnnotation \OApigility\Hydrator\Annotation\Exclude */
            if ((null !== $embeddedAnnotation)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

}
