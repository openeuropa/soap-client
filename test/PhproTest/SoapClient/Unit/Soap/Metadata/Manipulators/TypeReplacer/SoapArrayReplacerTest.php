<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\SoapArrayReplacer;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Model\Definitions\EncodingStyle;

final class SoapArrayReplacerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideTestCases
     */
    public function it_can_replace_apache_map(XsdType $in, XsdType $expected): void
    {
        self::assertEquals($expected, (new SoapArrayReplacer())($in));
    }

    public static function provideTestCases()
    {
        yield 'soap11-array' => [
            $baseType = (new XsdType(''))
                ->withXmlNamespace(EncodingStyle::SOAP_11->value)
                ->withXmlTypeName('Array'),
            $baseType->copy('array')
                ->withBaseType('array')
                ->withMeta(
                    static fn (TypeMeta $meta): TypeMeta => $meta->withIsSimple(true)
                )
        ];

        foreach (EncodingStyle::listKnownSoap12Version() as $namespace) {
            yield 'soap12-array'.$namespace => [
                $baseType = (new XsdType(''))
                    ->withXmlNamespace($namespace)
                    ->withXmlTypeName('Array'),
                $baseType->copy('array')
                    ->withBaseType('array')
                    ->withMeta(
                        static fn (TypeMeta $meta): TypeMeta => $meta->withIsSimple(true)
                    )
            ];
        }

        yield 'no-array' => [
            $baseType = (new XsdType(''))
                ->withXmlNamespace('http://none')
                ->withXmlTypeName('Array'),
            $baseType
        ];
    }
}
