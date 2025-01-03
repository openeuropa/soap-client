<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\LocalToGlobalEnumReplacer;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Model\Definitions\EncodingStyle;

final class LocalToGlobalEnumReplacerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideTestCases
     */
    public function it_can_replace_local_enums(XsdType $in, XsdType $expected): void
    {
        self::assertEquals($expected, (new LocalToGlobalEnumReplacer())($in));
    }

    public static function provideTestCases()
    {
        yield 'regular-type' => [
            $baseType = XsdType::create('object'),
            $baseType
        ];

        yield 'implied-global-enum' => [
            $baseType = XsdType::create('object')
                ->withMeta(
                    static fn (TypeMeta $meta): TypeMeta => $meta
                        ->withIsSimple(true)
                        ->withEnums(['a', 'b', 'c'])
                ),
            $baseType
        ];

        yield 'explicit-global-enum' => [
            $baseType = XsdType::create('object')
                ->withMeta(
                    static fn (TypeMeta $meta): TypeMeta => $meta
                        ->withIsSimple(true)
                        ->withEnums(['a', 'b', 'c'])
                        ->withIsLocal(false)
                ),
            $baseType
        ];

        yield 'local-enum' => [
            $baseType = XsdType::create('object')
                ->withMeta(
                    static fn (TypeMeta $meta): TypeMeta => $meta
                        ->withIsSimple(true)
                        ->withEnums(['a', 'b', 'c'])
                        ->withIsLocal(true)
                ),
            $baseType->withMeta(
                static fn (TypeMeta $meta): TypeMeta => $meta->withIsLocal(false)
            )
        ];
    }
}
