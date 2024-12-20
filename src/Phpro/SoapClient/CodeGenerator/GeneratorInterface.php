<?php

namespace Phpro\SoapClient\CodeGenerator;

use Laminas\Code\Generator\FileGenerator;

/**
 * Interface GeneratorInterface
 *
 * @package Phpro\SoapClient\CodeGenerator
 *
 * @template Context
 */
interface GeneratorInterface
{
    // to ease X-OS compat, always use linux newlines
    const EOL = "\n";
    
    /**
     * @param FileGenerator $file
     * @param Context       $context
     *
     * @return string
     */
    public function generate(FileGenerator $file, $context): string;
}
