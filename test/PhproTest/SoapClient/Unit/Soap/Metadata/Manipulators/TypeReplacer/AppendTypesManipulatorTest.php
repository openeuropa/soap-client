<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\AppendTypesManipulator;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Property;
use Soap\Engine\Metadata\Model\Type;
use Soap\Engine\Metadata\Model\XsdType;

class AppendTypesManipulatorTest extends TestCase
{
    /** @test */
    public function it_can_append_types(): void
    {
        $int = XsdType::create('int');
        $string = XsdType::create('string');

        $types = new TypeCollection(
            $type1 = new Type(XsdType::create('object1'), new PropertyCollection(new Property('property1', $int))),
            $type2 = new Type(XsdType::create('object2'), new PropertyCollection(new Property('property1', $string))),
        );

        $append = new AppendTypesManipulator(static fn (TypeCollection $original) => new TypeCollection(
            new Type(
                $original->fetchFirstByName('object1')->getXsdType()->copy('object3'),
                new PropertyCollection()
            ),
        ));
        $actual = $append($types);

        self::assertEquals(
            new TypeCollection(
                $type1,
                $type2,
                new Type(XsdType::create('object3'), new PropertyCollection()),
            ),
            $actual
        );
    }
}
