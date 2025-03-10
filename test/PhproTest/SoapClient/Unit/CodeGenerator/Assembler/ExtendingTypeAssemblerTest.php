<?php

namespace PhproTest\SoapClient\Unit\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Assembler\ExtendingTypeAssembler;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\CodeGenerator\Model\Type;
use PHPUnit\Framework\TestCase;
use Laminas\Code\Generator\ClassGenerator;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;

/**
 * Class ExtendingTypeAssemblerTest
 *
 * @package PhproTest\SoapClient\Unit\CodeGenerator\Assembler
 */
class ExtendingTypeAssemblerTest extends TestCase
{

    /**
     * @test
     */
    function it_is_an_assembler()
    {
        $assembler = new ExtendingTypeAssembler();
        $this->assertInstanceOf(AssemblerInterface::class, $assembler);
    }

    /**
     * @test
     */
    function it_can_assemble_type_context()
    {
        $assembler = new ExtendingTypeAssembler();
        $context = $this->createContext();
        $this->assertTrue($assembler->canAssemble($context));
    }

    /**
     * @test
     */
    function it_assembles_a_type_in_same_namespace()
    {
        $assembler = new ExtendingTypeAssembler();
        $context = $this->createContext();
        $assembler->assemble($context);

        $code = $context->getClass()->generate();
        $expected = <<<CODE
namespace MyNamespace;

class MyType extends MyBaseType
{
}

CODE;

        $this->assertEquals($expected, $code);
    }

    /**
     * @test
     */
    function it_assembles_a_type_in_other_namespace()
    {
        $assembler = new ExtendingTypeAssembler();
        $context = $this->createContext('OtherNamespace');
        $assembler->assemble($context);

        $code = $context->getClass()->generate();
        $expected = <<<CODE
namespace MyNamespace;

use OtherNamespace\MyBaseType;

class MyType extends MyBaseType
{
}

CODE;

        $this->assertEquals($expected, $code);
    }

    /**
     * @test
     */
    function it_skips_assambling_on_non_extending_type()
    {
        $assembler = new ExtendingTypeAssembler();
        $class = new ClassGenerator('MyType', 'MyNamespace');
        $type = new Type('MyNamespace', 'MyType', 'MyType', [], XsdType::create('MyType'));

        $context = new TypeContext($class, $type);
        $assembler->assemble($context);

        $code = $context->getClass()->generate();
        $expected = <<<CODE
namespace MyNamespace;

class MyType
{
}

CODE;

        $this->assertEquals($expected, $code);
    }

    /**
     * @test
     */
    function it_skips_assambling_on_extending_simple_type()
    {
        $assembler = new ExtendingTypeAssembler();
        $class = new ClassGenerator('MyType', 'MyNamespace');
        $type = new Type('MyNamespace', 'MyType', 'MyType', [], XsdType::create('MyType')->withMeta(static fn (TypeMeta $meta) => $meta->withExtends([
            'type' => 'string',
            'namespace' => 'xsd',
            'isSimple' => true,
        ])));

        $context = new TypeContext($class, $type);
        $assembler->assemble($context);

        $code = $context->getClass()->generate();
        $expected = <<<CODE
namespace MyNamespace;

class MyType
{
}

CODE;

        $this->assertEquals($expected, $code);
    }

    /**
     * @return TypeContext
     */
    private function createContext(?string $importedNamespace = null)
    {
        $class = new ClassGenerator('MyType', 'MyNamespace');
        $type = new Type($importedNamespace ?? 'MyNamespace', 'MyType', 'MyType', [], XsdType::create('MyType')->withMeta(static fn (TypeMeta $meta) => $meta->withExtends([
            'type' => 'MyBaseType',
            'namespace' => 'xxxx'
        ])));

        return new TypeContext($class, $type);
    }
}
