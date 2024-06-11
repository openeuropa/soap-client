<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\ReplaceMethodTypesManipulator;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacer;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Collection\MethodCollection;
use Soap\Engine\Metadata\Collection\ParameterCollection;
use Soap\Engine\Metadata\Model\Method;
use Soap\Engine\Metadata\Model\Parameter;
use Soap\Engine\Metadata\Model\XsdType;

class ReplaceMethodTypesManipulatorTest extends TestCase
{

    /**
     * @test
     */
    public function test_it_can_replace_methods(): void
    {
        $object = XsdType::create('object');
        $int = XsdType::create('int');
        $string = XsdType::create('string');

        $methods = new MethodCollection(
            new Method(
                'hello',
                new ParameterCollection(
                    new Parameter('param1', $int),
                    new Parameter('param2', $string),
                ),
                $object
            )
        );

        $replacements = new class() implements TypeReplacer {
            public function __invoke(XsdType $type): XsdType
            {
                return $type->copy($type->getName() . '_replaced');
            }
        };

        $replace = new ReplaceMethodTypesManipulator($replacements);
        $actual = $replace($methods);

        self::assertEquals(
            new MethodCollection(
                new Method(
                    'hello',
                    new ParameterCollection(
                        new Parameter('param1', $int->copy('int_replaced')),
                        new Parameter('param2', $string->copy('string_replaced')),
                    ),
                    $object->copy('object_replaced')
                )
            ),
            $actual
        );
    }
}
