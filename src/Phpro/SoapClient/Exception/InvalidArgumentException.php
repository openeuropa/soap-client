<?php

namespace Phpro\SoapClient\Exception;

use Throwable;

/**
 * Class InvalidArgumentException
 *
 * @package Phpro\SoapClient\Exception
 */
final class InvalidArgumentException extends \InvalidArgumentException
{
    public static function engineNotConfigured(): self
    {
        return new static('You did not configure a soap engine');
    }

    public static function destinationConfigurationIsMissing(): self
    {
        return new static('You did not configure a destination.');
    }

    public static function invalidConfigFile(): self
    {
        return new static('You have to provide a code-generator config file which returns a ConfigInterface.');
    }

    public static function clientNamespaceIsMissing(): self
    {
        return new static('You did not configure a client namespace.');
    }

    public static function typeNamespaceIsMissing(): self
    {
        return new static('You did not configure a type namespace.');
    }

    public static function clientDestinationIsMissing(): self
    {
        return new static('You did not configure a client destination.');
    }

    public static function typeDestinationIsMissing(): self
    {
        return new static('You did not configure a type destination.');
    }

    public static function classmapNameMissing(): self
    {
        return new static('You did not configure a classmap name.');
    }

    public static function classmapNamespaceMissing(): self
    {
        return new static('You did not configure a classmap namespace.');
    }

    public static function classmapDestinationMissing(): self
    {
        return new static('You did not configure a classmap destination.');
    }
}
