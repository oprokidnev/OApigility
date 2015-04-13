<?php

namespace OApigility\Hydrator\Formatter;

/**
 * Description of DateTimeFormatter
 *
 * @author oprokidnev
 */
class GroupFormatter implements FormatterInterface, \Zend\ServiceManager\ServiceLocatorAwareInterface
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
            \OApigility\Hydrator\Annotation\Group::class);


        /* @var $embeddedAnnotation \OApigility\Hydrator\Annotation\Group */
        if ((null !== $embeddedAnnotation)) {
            $groups = $embeddedAnnotation->getGroups();
        } else {
            $groups = ['default'];
        }

        $currentGroups = $hydrator->getGroups();
        $hydrator->setGroups($groups);
        $result        = $hydrator->extract($value);
        $hydrator->setGroups($currentGroups);

        return $result;
    }

    protected $renderedEntities = [];
    protected static $c         = 0;

    /**
     * 
     * @param object $entity
     */
    protected function preventCircular($value, $targetObject, $property, $hydrator)
    {
        $contexts = $hydrator->getContexts();
        if (in_array($hydrator::objectHash($value), $contexts)) {
            throw new \Exception(sprintf('Circular serialization found for entity "%s::%s": trying to render entity "%s" in context [%s]',
                $hydrator::objectHash($targetObject), $property,
                $hydrator::objectHash($value), implode(',', $contexts)));
        }
    }

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
        return is_object($value) && $this->isEntity($value);
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
