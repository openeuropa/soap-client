<?php

namespace Phpro\SoapClient\CodeGenerator;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Phpro\SoapClient\Caller\EngineCaller;
use Phpro\SoapClient\Caller\EventDispatchingCaller;
use Phpro\SoapClient\CodeGenerator\Context\ClientFactoryContext;
use Phpro\SoapClient\CodeGenerator\Model\Type;
use Phpro\SoapClient\Soap\DefaultEngineFactory;
use Phpro\SoapClient\Soap\EngineOptions;
use Soap\Encoding\EncoderRegistry;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;

/**
 * @template-implements GeneratorInterface<ClientFactoryContext>
 */
class ClientFactoryGenerator implements GeneratorInterface
{
    const BODY = <<<BODY
\$engine = DefaultEngineFactory::create(
    EngineOptions::defaults(\$wsdl)
        ->withEncoderRegistry(
            EncoderRegistry::default()
                ->addClassMapCollection(%2\$s::types())
                ->addBackedEnumClassMapCollection(%2\$s::enums())
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

return new %1\$s(\$caller);

BODY;


    /**
     * @param FileGenerator $file
     * @param ClientFactoryContext $context
     * @return string
     */
    public function generate(FileGenerator $file, $context): string
    {
        $class = new ClassGenerator($context->getClientName().'Factory');
        $class->setNamespaceName($context->getClientNamespace());
        $class->addUse($context->getClientFqcn());
        $class->addUse($context->getClassmapFqcn());
        $class->addUse(EventDispatcher::class);
        $class->addUse(DefaultEngineFactory::class);
        $class->addUse(EngineOptions::class);
        $class->addUse(EventDispatchingCaller::class);
        $class->addUse(EngineCaller::class);
        $class->addUse(EncoderRegistry::class);
        $class->addMethodFromGenerator(
            (new MethodGenerator('factory'))
                ->setStatic(true)
                ->setBody(sprintf(self::BODY, $context->getClientName(), $context->getClassmapName()))
                ->setReturnType($context->getClientFqcn())
                ->setParameter(new ParameterGenerator('wsdl', 'string'))
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription(
                            'This factory can be used as a starting point '.
                            'to create your own specialized factory. Feel free to modify.'
                        )
                        ->setTags([
                            [
                                'name' => 'param',
                                'description' => 'non-empty-string $wsdl',
                            ],
                        ])
                )
        );

        $file->setClass($class);

        return $file->generate();
    }
}
