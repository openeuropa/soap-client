<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\SoapObjectReplacer;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Metadata\Detector\ApacheMapDetector;
use Soap\WsdlReader\Model\Definitions\EncodingStyle;

final class SoapObjectReplacerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideTestCases
     */
    public function it_can_replace_apache_map(XsdType $in, XsdType $expected): void
    {
        self::assertEquals($expected, (new SoapObjectReplacer())($in));
    }

    public static function provideTestCases()
    {
        yield 'struct' => [
            $baseType = (new XsdType(''))
                ->withXmlNamespace(EncodingStyle::SOAP_11->value)
                ->withXmlTypeName('Struct'),
            $baseType->copy('struct')
                ->withBaseType('object')
                ->withMeta(
                    static fn (TypeMeta $meta): TypeMeta => $meta->withIsSimple(true)
                )
        ];
        yield 'no-struct' => [
            $baseType = (new XsdType(''))
                ->withXmlNamespace('http://none')
                ->withXmlTypeName('Struct'),
            $baseType
        ];
    }
}
