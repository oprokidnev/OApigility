<?php

namespace OApigility\Hydrator\Formatter;

/**
 * Description of DateTimeFormatter
 *
 * @author oprokidnev
 */
class DateTimeFormatter implements FormatterInterface
{
    /**
     * 
     * @param \DateTime $value
     * @param Entity $targetObject
     */
    public function format($value, &$property, $targetObject, &$commonData)
    {
        return $value->format(\DateTime::ISO8601);
    }

    public function isFormattable($targetEntity, $property, $value)
    {
        return $value instanceof \DateTime;
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
