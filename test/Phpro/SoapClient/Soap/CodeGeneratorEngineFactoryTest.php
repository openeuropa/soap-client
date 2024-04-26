<?php

namespace Phpro\SoapClient\Soap;

use PHPUnit\Framework\TestCase;
use Soap\Engine\Engine;
use Soap\Engine\Metadata\Metadata;

class CodeGeneratorEngineFactoryTest extends TestCase
{
    public function test_it_loads_from_filesystem(): void
    {
        $engine = CodeGeneratorEngineFactory::create(__DIR__ . '/../../../fixtures/wsdl/functional/calculator.wsdl');
        self::assertInstanceOf(Metadata::class, $engine->getMetadata());
    }
}
