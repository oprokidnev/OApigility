<?php

namespace OApigility\Hydrator\Formatter;

/**
 *
 * @author oprokidnev
 */
interface FormatterInterface
{

    public function format($value, &$property, $targetObject, &$commonData);
    public function decode($value);

    public function isFormattable($targetEntity, $property, $value);
    public function isDecodeable($value);
}
