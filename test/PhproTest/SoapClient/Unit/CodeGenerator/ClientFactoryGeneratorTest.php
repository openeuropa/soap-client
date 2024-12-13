<?php

namespace PhproTest\SoapClient\Unit\CodeGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Phpro\SoapClient\CodeGenerator\ClientFactoryGenerator;
use Phpro\SoapClient\CodeGenerator\Context\ClassMapContext;
use Phpro\SoapClient\CodeGenerator\Context\ClientContext;
use Phpro\SoapClient\CodeGenerator\Context\ClientFactoryContext;
use PHPUnit\Framework\TestCase;
use Laminas\Code\Generator\FileGenerator;

class ClientFactoryGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $expected = <<<BODY
<?php

namespace App\Client;

use App\Client\Myclient;
use App\Classmap\SomeClassmap;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Phpro\SoapClient\Soap\DefaultEngineFactory;
use Phpro\SoapClient\Soap\EngineOptions;
use Phpro\SoapClient\Caller\EventDispatchingCaller;
use Phpro\SoapClient\Caller\EngineCaller;
use Soap\Encoding\EncoderRegistry;

class MyclientFactory
{
    /**
     * This factory can be used as a starting point to create your own specialized
     * factory. Feel free to modify.
     *
     * @param non-empty-string \$wsdl
     */
    public static function factory(string \$wsdl) : \App\Client\Myclient
    {
        \$engine = DefaultEngineFactory::create(
            EngineOptions::defaults(\$wsdl)
                ->withEncoderRegistry(
                    EncoderRegistry::default()->addClassMapCollection(
                        SomeClassmap::getCollection()
                    )
                )
                // If you want to enable WSDL caching:
                // ->withCache() 
                // If you want to use Alternate HTTP settings:
                // ->withWsdlLoader()
                // ->withTransport()
                // If you want specific SOAP setting:
                // ->withWsdlParserContext()
                // ->withWsdlServiceSelectionCriteria()
        );

        \$eventDispatcher = new EventDispatcher();
        \$caller = new EventDispatchingCaller(new EngineCaller(\$engine), \$eventDispatcher);

        return new Myclient(\$caller);
    }
}


BODY;
        $clientContext = new ClientContext(new ClassGenerator(), 'Myclient', 'App\\Client');
        $classMapContext = new ClassMapContext(
            new FileGenerator(),
            new \Phpro\SoapClient\CodeGenerator\Model\TypeMap('App\\Types', []),
            'SomeClassmap',
            'App\\Classmap'
        );
        $context = new ClientFactoryContext($clientContext, $classMapContext);
        $generator = new ClientFactoryGenerator();
        self::assertEquals($expected, $generator->generate(new FileGenerator(), $context));
    }
}
