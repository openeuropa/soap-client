<?php

namespace Phpro\SoapClient\CodeGenerator\Config;

enum EnumerationGenerationStrategy
{

    /**
     * Only generates and uses globally accessible XSD enumerations.
     */
    case GlobalOnly;

    /**
     * Tries to find properties that have local XSD enumerations and copies them as global enumerations.
     */
    case LocalAndGlobal;

    public static function default(): self
    {
        return self::GlobalOnly;
    }
}
