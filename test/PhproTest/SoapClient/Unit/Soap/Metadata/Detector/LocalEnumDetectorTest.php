<?php

declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Detector;

use Phpro\SoapClient\Soap\Metadata\Detector\LocalEnumDetector;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Property;
use Soap\Engine\Metadata\Model\Type;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;

class LocalEnumDetectorTest extends TestCase
{
    /** @test */
    public function it_can_detect_local_enums(): void
    {
        $markAsEnum = static fn(XsdType $type, bool $local) => $type->withMeta(
            static fn (TypeMeta $meta) => $meta->withIsSimple(true)->withEnums(['a'])->withIsLocal($local)
        );

        $detector = new LocalEnumDetector();
        $types = new TypeCollection(
            new Type(XsdType::create('a'), new PropertyCollection()),
            new Type(XsdType::create('b'), new PropertyCollection()),
            new Type($markAsEnum(XsdType::create('global'), local: false), new PropertyCollection()),
            new Type(XsdType::create('local1_wrapper'), new PropertyCollection(
                new Property('global', $markAsEnum(XsdType::create('global'), local: false)),
                new Property('local1', $local1 = $markAsEnum(XsdType::create('local1'), local: true)),
                new Property('local2', $local2 = $markAsEnum(XsdType::create('local2'), local: true)),
            )),
            new Type($markAsEnum(XsdType::create('local2_wrapper'), local: true), new PropertyCollection(
                new Property('local1', $local1_other = $markAsEnum(XsdType::create('local1'), local: true)),
                new Property('local3', $local3 = $markAsEnum(XsdType::create('local3'), local: true)),
            )),

        );

        $detected = $detector($types);

        self::assertEquals(
            new TypeCollection(
                new Type($local1, new PropertyCollection()),
                new Type($local2, new PropertyCollection()),
                new Type($local1_other, new PropertyCollection()),
                new Type($local3, new PropertyCollection()),
            ),
            $detected
        );
    }
}
