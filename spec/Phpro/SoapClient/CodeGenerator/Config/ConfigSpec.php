<?php

namespace spec\Phpro\SoapClient\CodeGenerator\Config;

use Phpro\SoapClient\CodeGenerator\Config\Config;
use Phpro\SoapClient\CodeGenerator\Config\ConfigInterface;
use Phpro\SoapClient\CodeGenerator\Rules\RuleSet;
use Phpro\SoapClient\Exception\InvalidArgumentException;
use Phpro\SoapClient\Util\Filesystem;
use PhpSpec\ObjectBehavior;
use Soap\Engine\Engine;

/**
 * Class ConfigSpec
 *
 * @package spec\Phpro\SoapClient\CodeGenerator\Config
 * @mixin Config
 */
class ConfigSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Config::class);
    }

    function it_has_an_engine(Engine $engine)
    {
        $this->setEngine($engine);
        $this->getEngine()->shouldReturn($engine);
    }

    function it_requires_an_engine()
    {
        $this->shouldThrow(InvalidArgumentException::class)->duringGetEngine();
    }

    function it_requires_a_typedestination()
    {
        $this->shouldThrow(InvalidArgumentException::class)->duringGetTypeDestination();
    }

    function it_has_a_ruleset()
    {
        $this->setRuleSet($value = new RuleSet([]));
        $this->getRuleSet()->shouldBe($value);
    }

    public function it_has_a_type_destination()
    {
        $this->setTypeDestination($value = 'src/type');
        $this->getTypeDestination()->shouldBe($value);
    }

    public function it_has_a_client_destination()
    {
        $this->setClientDestination($value = 'src/client');
        $this->getClientDestination()->shouldBe($value);
    }

    public function it_has_a_type_namespace()
    {
        $this->setTypeNamespace($value = 'TypeNamespace');
        $this->getTypeNamespace()->shouldBe($value);
    }

    public function it_has_a_client_namespace()
    {
        $this->setClientNamespace($value = 'ClientNamespace');
        $this->getClientNamespace()->shouldBe($value);
    }

    public function it_requires_a_client_namespace()
    {
        $this->shouldThrow(InvalidArgumentException::class)->duringGetClientNamespace();
    }

    public function it_has_a_client_name()
    {
        $this->setClientName($value = 'ClientName');
        $this->getClientName()->shouldBe($value);
    }
}
