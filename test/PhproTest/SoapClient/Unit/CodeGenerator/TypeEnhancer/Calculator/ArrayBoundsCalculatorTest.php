<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\CodeGenerator\TypeEnhancer\Calculator;

use Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator\ArrayBoundsCalculator;
use Phpro\SoapClient\CodeGenerator\TypeEnhancer\MetaTypeEnhancer;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Model\TypeMeta;

class ArrayBoundsCalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideExpectations
     */
    public function it_can_enhance_types(
        TypeMeta $meta,
        string $expected,
    ): void{
        $calculator = new ArrayBoundsCalculator();

        self::assertSame($expected, $calculator($meta));
    }

    public function provideExpectations()
    {
        yield 'simpleType' => [
            new TypeMeta(),
            'int<0,max>',
        ];
        yield 'array' => [
            (new TypeMeta())->withIsList(true),
            'int<0,max>',
        ];
        yield 'min' => [
            (new TypeMeta())->withIsList(true)->withMinOccurs(1),
            'int<0,max>',
        ];
        yield 'max' => [
            (new TypeMeta())->withIsList(true)->withMaxOccurs(3),
            'int<0,2>',
        ];
        yield 'min-max' => [
            (new TypeMeta())->withIsList(true)->withMinOccurs(1)->withMaxOccurs(3),
            'int<0,2>',
        ];
        yield 'max-1' => [
            (new TypeMeta())->withIsList(true)->withMaxOccurs(1),
            'int<0,0>',
        ];
        yield 'max-0' => [
            (new TypeMeta())->withIsList(true)->withMaxOccurs(0),
            'never',
        ];
    }
}
