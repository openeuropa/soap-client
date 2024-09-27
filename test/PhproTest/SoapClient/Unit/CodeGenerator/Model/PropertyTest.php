<?php

namespace PhproTest\SoapClient\Unit\CodeGenerator\Model;

use Phpro\SoapClient\CodeGenerator\Model\Property;
use PHPUnit\Framework\TestCase;
use Psl\Option\Option;
use Soap\Engine\Metadata\Model\Property as EngineProperty;
use Soap\Engine\Metadata\Model\XsdType;

/**
 * Class PropertyTest
 *
 * @package PhproTest\SoapClient\Unit\CodeGenerator\Model
 */
class PropertyTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_mixed_type_post_php8(): void
    {
        $property = new Property('test', 'mixed', 'App', XsdType::create('mixed'));
        self::assertEquals('mixed', $property->getPhpType());
        self::assertEquals('mixed', $property->getType());
    }

    /** @test */
    public function it_can_use_fqcn_to_3rd_party_classes_as_type_name(): void
    {
        $property = Property::fromMetaData(
            'MyApp',
            new EngineProperty(
                'property',
                $xsdType = XsdType::create(Option::class)
            )
        );

        self::assertEquals('\\' . Option::class, $property->getType());
        self::assertNotSame($xsdType, $property->getXsdType());
        self::assertSame('Option', $property->getXsdType()->getName());
        self::assertSame('Option', $property->getXsdType()->getBaseType());
    }

    /** @test */
    public function it_can_use_a_php_built_in_class_as_type_name(): void
    {
        $property = Property::fromMetaData(
            'MyApp',
            new EngineProperty(
                'property',
                $xsdType = XsdType::create('\\'. \DateInterval::class)
            )
        );

        self::assertEquals('\\' . \DateInterval::class, $property->getType());
        self::assertNotSame($xsdType, $property->getXsdType());
        self::assertSame('DateInterval', $property->getXsdType()->getName());
        self::assertSame('DateInterval', $property->getXsdType()->getBaseType());
    }
}
