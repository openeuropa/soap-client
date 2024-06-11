<?php
declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacer;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacers;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Model\XsdType;

final class TypeReplacersTest extends TestCase
{

    /** @test */
    public function it_can_replace_multiple_types(): void
    {
        $replace = TypeReplacers::empty()
            ->add(
                new class() implements TypeReplacer {
                    public function __invoke(XsdType $type): XsdType
                    {
                        return $type->copy($type->getName() . '_replaced');
                    }
                }
            )
            ->add(
                new class() implements TypeReplacer {
                    public function __invoke(XsdType $type): XsdType
                    {
                        return $type->copy($type->getName() . '_again');
                    }
                }
            );

        $type = XsdType::create('hello');
        $actual = $replace($type);

        self::assertSame('hello_replaced_again', $actual->getName());
    }
}
