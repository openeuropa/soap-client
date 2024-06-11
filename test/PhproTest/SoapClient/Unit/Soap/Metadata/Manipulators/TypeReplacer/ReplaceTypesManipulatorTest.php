<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\ReplaceTypesManipulator;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacer;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Property;
use Soap\Engine\Metadata\Model\Type;
use Soap\Engine\Metadata\Model\XsdType;

class ReplaceTypesManipulatorTest extends TestCase
{
    /** @test */
    public function it_can_replace_types(): void
    {
        $object = XsdType::create('object');
        $int = XsdType::create('int');
        $string = XsdType::create('string');

        $types = new TypeCollection(
            new Type($object, new PropertyCollection(new Property('property1', $int))),
            new Type($object, new PropertyCollection(new Property('property1', $string))),
        );

        $replacements = new class() implements TypeReplacer {
            public function __invoke(XsdType $type): XsdType
            {
                return $type->copy($type->getName() . '_replaced');
            }
        };

        $replace = new ReplaceTypesManipulator($replacements);
        $actual = $replace($types);

        self::assertEquals(
            new TypeCollection(
                new Type(
                    $object->copy('object_replaced'),
                    new PropertyCollection(
                        new Property('property1', $int->copy('int_replaced'))
                    )
                ),
                new Type(
                    $object->copy('object_replaced'),
                    new PropertyCollection(
                        new Property('property1', $string->copy('string_replaced'))
                    )
                ),
            ),
            $actual
        );
    }
}
