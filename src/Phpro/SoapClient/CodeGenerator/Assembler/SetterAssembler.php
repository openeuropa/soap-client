<?php

namespace Phpro\SoapClient\CodeGenerator\Assembler;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\PropertyContext;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;
use Laminas\Code\Generator\MethodGenerator;

/**
 * Class SetterAssembler
 *
 * @package Phpro\SoapClient\CodeGenerator\Assembler
 */
class SetterAssembler implements AssemblerInterface
{
    /**
     * @var SetterAssemblerOptions
     */
    private $options;

    /**
     * SetterAssembler constructor.
     *
     * @param SetterAssemblerOptions|null $options
     */
    public function __construct(SetterAssemblerOptions $options = null)
    {
        $this->options = $options ?? new SetterAssemblerOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof PropertyContext;
    }

    /**
     * @param ContextInterface|PropertyContext $context
     *
     * @throws AssemblerException
     */
    public function assemble(ContextInterface $context)
    {
        $class = $context->getClass();
        $property = $context->getProperty();
        try {
            $methodName = Normalizer::generatePropertyMethod('set', $property->getName());
            $class->removeMethod($methodName);

            $param = (new ParameterGenerator($property->getName()));
            if ($this->options->useTypeHints()) {
                $param->setType($property->getPhpType());
            }

            $methodGenerator = new MethodGenerator($methodName);
            $methodGenerator->setReturnType('void');
            $methodGenerator->setParameter($param);
            $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
            $methodGenerator->setBody(sprintf('$this->%1$s = $%1$s;', $property->getName()));
            if ($this->options->useDocBlocks()) {
                $methodGenerator->setDocBlock(
                    (new DocBlockGenerator())
                        ->setWordWrap(false)
                        ->setTags([
                            [
                                'name' => 'param',
                                'description' => sprintf('%s $%s', $property->getDocBlockType(), $property->getName()),
                            ]
                        ])
                );
            }

            $class->addMethodFromGenerator($methodGenerator);
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }
}
