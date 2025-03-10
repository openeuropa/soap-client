<?php

namespace spec\Phpro\SoapClient\CodeGenerator\Model;

use Phpro\SoapClient\CodeGenerator\Model\Parameter;
use PhpSpec\ObjectBehavior;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;

/**
 * Class ParameterSpec
 *
 * @package spec\Phpro\SoapClient\CodeGenerator\Model
 * @mixin Parameter
 */
class ParameterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('MyParameter', 'MyParameterType', 'MyNamespace', XsdType::create('MyParameter'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Parameter::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('MyParameter');
    }

    function is_has_a_namespace()
    {
        $this->getNamespace()->shouldBe('MyParameterType');
    }

    public function it_has_type_meta(): void
    {
        $this->getMeta()->shouldBeLike(new TypeMeta());
    }
}
