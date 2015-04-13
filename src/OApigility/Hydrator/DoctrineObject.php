<?php

namespace OApigility\Hydrator;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Di\ServiceLocator;

/**
 * Hydration groups
 * {"id", "list", "detailed"}
 */
class DoctrineObject extends \DoctrineModule\Stdlib\Hydrator\DoctrineObject
    implements ServiceLocatorAwareInterface, HydratorInterface
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     *
     * @var Formatter\FormatterInterface[]
     */
    protected $formatters = [];

    /**
     *
     * @var Strategy\NamingStrategyInterface
     */
    protected $namingStrategy = null;

    /**
     *
     * @var Filter\FilterInterface[]
     */
    protected $filters = [];

    /**
     * 
     * @param Strategy\NamingStrategyInterface $namingStrategy
     * @param Formatter\FormatterInterface[] $formatters
     * @param Filter\FilterInterface[] $filters
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param boolean $byValue
     * @return DoctrineObject
     */
    public function __construct($namingStrategy, $formatters, $filters, \Doctrine\Common\Persistence\ObjectManager $objectManager, $byValue = true)
    {
        $this->namingStrategy = $namingStrategy;
        $this->filters        = $filters;
        $this->formatters     = $formatters;

        return parent::__construct($objectManager, $byValue);
    }

    protected $groups = [];

    /**
     * Extract values from an object using a by-value logic (this means that it uses the entity
     * API, in this case, getters)
     *
     * @param  object $object
     * @throws RuntimeException
     * @return array
     */
    protected function extractByValue($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(),
            $this->metadata->getAssociationNames());

        $methods = get_class_methods($object);
        $filter  = $object instanceof FilterProviderInterface ? $object->getFilter()
                : $this->filterComposite;

        $data = array();
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName)) {
                continue;
            }
            $getter = 'get' . ucfirst($fieldName);
            $isser  = 'is' . ucfirst($fieldName);

            $dataFieldName = $this->computeExtractFieldName($fieldName);
            if (in_array($getter, $methods)) {
                $data[$fieldName] = $this->extractValue($fieldName,
                    $object->$getter(), $object);
            } elseif (in_array($isser, $methods)) {
                $data[$fieldName] = $this->extractValue($fieldName,
                    $object->$isser(), $object);
            } elseif (substr($fieldName, 0, 2) === 'is' && ctype_upper(substr($fieldName,
                        2, 1)) && in_array($fieldName, $methods)) {
                $data[$fieldName] = $this->extractValue($fieldName,
                    $object->$fieldName(), $object);
            }

            // Unknown fields are ignored
        }

        if ($object !== null) {
            $reader = $this->getAnnotationReader();

            $class = new \ReflectionClass($object);
            if ($class) {
                $reflMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

                foreach ($reflMethods as $reflMethod) {
                    $lazyProprtyAnnotation = $reader->getMethodAnnotation($reflMethod,
                        Annotation\LazyProperty::class);
                    if ($lazyProprtyAnnotation !== null && $lazyProprtyAnnotation instanceof Annotation\LazyProperty) {
                        $fieldName = $lazyProprtyAnnotation->getPropertyName();

                        $fieldData = $reflMethod->invokeArgs($object,
                            $lazyProprtyAnnotation->getArguments());
                         $data[$fieldName] = $fieldData;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Extract values from an object using a by-reference logic (this means that values are
     * directly fetched without using the public API of the entity, in this case, getters)
     *
     * @param  object $object
     * @return array
     */
    protected function extractByReference($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(),
            $this->metadata->getAssociationNames());
        $refl       = $this->metadata->getReflectionClass();
        $filter     = $object instanceof FilterProviderInterface ? $object->getFilter()
                : $this->filterComposite;

        $data = array();
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName)) {
                continue;
            }
            $reflProperty     = $refl->getProperty($fieldName);
            $reflProperty->setAccessible(true);
            $data[$fieldName] = $this->extractValue($fieldName,
                $reflProperty->getValue($object), $object);
        }

        if ($object !== null) {
            $reader = $this->getAnnotationReader();

            $class = new \ReflectionClass($object);
            if ($class) {
                $reflMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

                foreach ($reflMethods as $reflMethod) {
                    $lazyProprtyAnnotation = $reader->getMethodAnnotation($reflMethod,
                        Annotation\LazyProperty::class);
                    if ($lazyProprtyAnnotation !== null && $lazyProprtyAnnotation instanceof Annotation\LazyProperty) {
                        $fieldName = $lazyProprtyAnnotation->getPropertyName();

                        $fieldData = $reflMethod->invokeArgs($object,
                            $lazyProprtyAnnotation->getArguments());
                         $data[$fieldName] = $fieldData;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public static function objectHash($object)
    {
        if (is_object($object)) {
            $id     = $object->getId();
            $object = ($object instanceof \Doctrine\Common\Proxy\Proxy) ? get_parent_class($object)
                    : get_class($object);
            return $object . ':' . $id;
        }
        return null;
    }

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

    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        $this->prepare($object);
        $this->addContext(self::objectHash($object));

        if ($this->byValue) {
            $doctrineData = $this->extractByValue($object);
        } else {
            $doctrineData = $this->extractByReference($object);
        }

        /**
         * Filtering data
         */
        $filteredProperties = [];
        foreach ($this->getFilters() as $filter) {
            foreach ($doctrineData as $property => $value) {
                if (!isset($filteredProperties[$property])) {
                    $filteredProperties[$property] = true;
                }
                $filteredProperties[$property] = $filteredProperties[$property] && $filter->filter($property,
                        $value, $object, $this);
            }
        }
        foreach ($doctrineData as $property => $value) {
            if (!@$filteredProperties[$property]) {
                unset($doctrineData[$property]);
            }
        }

        $formattedData = [];
        /**
         * FormattingData
         */
        foreach ($this->getFormatters() as $formatter) {
            foreach ($doctrineData as $property => &$value) {
                $transformable = $property;
                if ($formatter->isFormattable($object, $property, $value)) {
                    try {
                        $value = $formatter->format($value, $transformable,
                            $object, $formattedData);
                        if (is_array($value) && array_key_exists($property,
                                $value)) {
                            if (!isset($formattedData[$transformable])) {
                                $formattedData[$transformable] = [];
                            }
                            $formattedData[$transformable] = array_merge_recursive($formattedData[$transformable],
                                $value);
                        } else {
                            $formattedData[$transformable] = $value;
                        }
                    } catch (\Exception $ex) {
                        unset($doctrineData[$property]);
                    }
                } else {
                    $formattedData[$transformable] = $value;
                }
            }
        }

        /**
         * NamingData
         */
        $data = [];
        foreach ($formattedData as $fieldName => $value) {
            $dataFieldName        = $this->computeExtractFieldName($fieldName);
            $data[$dataFieldName] = $value;
        }

        $this->removeContext(self::objectHash($object));
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(array $data, $object)
    {
        $object           = parent::hydrate($data, $object);
        $associationNames = $this->metadata->getAssociationNames();
        /**
         * @todo inject embedded
         */
        foreach ($associationNames as $assocName) {
            if (isset($data['_embedded'][$assocName])) {
                if (!$this->metadata->isCollectionValuedAssociation($assocName)) {
                    if (is_scalar($entity = $data['_embedded'][$assocName])) {
                        $targetEntityClass = $this->metadata->getAssociationTargetClass($assocName);
                        $targetEntity      = $this->toOne($targetEntityClass,
                            $entity);

                        if ($targetEntity !== null) {
                            $setter = 'set' . ucfirst($assocName);
                            if (method_exists($object, $setter)) {
                                $object->$setter($targetEntity);
                            }
                        }
                    }
                } else {
                    $targetEntityClass = $this->metadata->getAssociationTargetClass($assocName);
                    if (is_array($entities          = $data['_embedded'][$assocName])) {
                        $this->toMany($object, $assocName, $targetEntityClass,
                            $entities);
                    }
                }
            }
        }
        return $object;
    }

    /**
     * 
     * @return Formatter\FormatterInterface[]
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * 
     * @return Strategy\NamingStrategyInterface
     */
    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    /**
     * 
     * @return Filter\FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * 
     * @param \OApigility\Hydrator\Formatter\FormatterInterface[] $formatters
     * @return \OApigility\Hydrator\DoctrineObject
     */
    public function setFormatters(Formatter\FormatterInterfaceхъ $formatters)
    {
        $this->formatters = $formatters;
        return $this;
    }

    /**
     * 
     * @param \OApigility\Hydrator\Strategy\NamingStrategyInterface $namingStrategy
     * @return \OApigility\Hydrator\DoctrineObject
     */
    public function setNamingStrategy(\Zend\Stdlib\Hydrator\NamingStrategy\NamingStrategyInterface $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
        return $this;
    }

    /**
     * 
     * @param array $filters
     * @return \OApigility\Hydrator\DoctrineObject
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    protected $contexts = [];

    public function addContext($context)
    {
        $this->contexts[] = $context;
    }

    public function removeContext($context)
    {
        foreach ($this->contexts as $key => $contextItem) {
            if ($contextItem == $context) {
                unset($this->contexts[$key]);
            }
        }
    }

    public function getContexts()
    {
        return $this->contexts;
    }

}
