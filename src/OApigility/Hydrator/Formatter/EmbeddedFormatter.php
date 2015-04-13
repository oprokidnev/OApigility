<?php

namespace OApigility\Hydrator\Formatter;

/**
 * Description of DateTimeFormatter
 *
 * @author oprokidnev
 */
class EmbeddedFormatter implements FormatterInterface, \Zend\ServiceManager\ServiceLocatorAwareInterface
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
     * 
     * @param \DateTime $value
     * @param Entity $targetObject
     * @throws \Exception
     */
    public function format($value, &$property, $targetObject, &$commonData)
    {
        $hydrator         = $this->getServiceLocator()->get('HydratorManager')->get(\OApigility\Hydrator\DoctrineObject::class);
        $annotationReader = $this->getAnnotationReader();

        $reflClass    = new \ReflectionClass(get_class($targetObject));
        $reflProperty = $reflClass->getProperty($property);

        $embeddedAnnotation = $annotationReader->getPropertyAnnotation($reflProperty,
            \OApigility\Hydrator\Annotation\Embedded::class);


        if ((null !== $embeddedAnnotation)) {
            unset($commonData[$property]);
            $result   = [
                $property => $value
            ];
            $property = '_embedded';
            
        }else{
            $result = $value;
        }
        return $result;
    }

    protected $renderedEntities = [];
    protected static $c         = 0;

    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        $serviceLocator = $this->getServiceLocator();
        return $serviceLocator->get(\Doctrine\ORM\EntityManager::class);
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
        }

        return !$entityManager->getMetadataFactory()->isTransient($object);
    }

    protected function getClass($object)
    {
        if (is_object($object)) {
            $object = ($object instanceof \Doctrine\Common\Proxy\Proxy) ? get_parent_class($object)
                    : get_class($object);
        }
        return $object;
    }

    public function isFormattable($targetEntity, $property, $value)
    {
        $em       = $this->getEntityManager();
        $metadata = $em->getMetadataFactory()->getMetadataFor($this->getClass($targetEntity));
        
        return (is_object($value) && $this->isEntity($value))||isset($metadata->associationMappings[$property]);
    }

    public function decode($value)
    {
        return (boolean) \DateTime::createFromFormat(\DateTime::ISO8601, $value);
    }

    /**
     * 
     * @param boolean $value
     */
    public function isDecodeable($value)
    {
        return (boolean) \DateTime::createFromFormat(\DateTime::ISO8601, $value);
    }

}
