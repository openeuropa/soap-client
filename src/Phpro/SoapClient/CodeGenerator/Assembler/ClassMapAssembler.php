<?php

namespace Phpro\SoapClient\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Context\ClassMapContext;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Model\TypeMap;
use Phpro\SoapClient\Exception\AssemblerException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Soap\Encoding\ClassMap\ClassMap;
use Soap\Encoding\ClassMap\ClassMapCollection;
use Soap\WsdlReader\Metadata\Predicate\IsConsideredScalarType;

/**
 * Class ClassMapAssembler
 *
 * @package Phpro\SoapClient\CodeGenerator\Assembler
 */
class ClassMapAssembler implements AssemblerInterface
{
    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof ClassMapContext;
    }

    /**
     * @param ClassMapContext|ContextInterface $context
     *
     * @throws \Phpro\SoapClient\Exception\AssemblerException
     */
    public function assemble(ContextInterface $context)
    {
        $class = new ClassGenerator($context->getName());
        $file = $context->getFile();
        $file->setClass($class);
        $file->setNamespace($context->getNamespace());
        $typeMap = $context->getTypeMap();
        $typeNamespace = $typeMap->getNamespace();
        $file->setUse($typeNamespace, preg_match('/\\\\Type$/', $typeNamespace) ? null : 'Type');

        try {
            $file->setUse(ClassMapCollection::class);
            $file->setUse(ClassMap::class);
            $linefeed = $file::LINE_FEED;
            $classMap = $this->assembleClassMap($typeMap, $linefeed, $file->getIndentation());
            $code = $this->assembleClassMapCollection($classMap, $linefeed).$linefeed;
            $class->addMethodFromGenerator(
                (new MethodGenerator('getCollection'))
                    ->setStatic(true)
                    ->setBody('return '.$code)
                    ->setReturnType(ClassMapCollection::class)
            );
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    /***
     * @param TypeMap $typeMap
     * @param string  $linefeed
     * @param string  $indentation
     *
     * @return string
     */
    private function assembleClassMap(TypeMap $typeMap, string $linefeed, string $indentation): string
    {
        $classMap = [];
        foreach ($typeMap->getTypes() as $type) {
            if ((new IsConsideredScalarType())($type->getMeta())) {
                continue;
            }

            $classMap[] = sprintf(
                '%snew ClassMap(\'%s\', \'%s\', %s::class),',
                $indentation,
                $type->getXsdType()->getXmlNamespace(),
                $type->getXsdType()->getName(),
                'Type\\'.$type->getName()
            );
        }

        return implode($linefeed, $classMap);
    }

    /**
     * @param string $classMap
     * @param string $linefeed
     *
     * @return string
     */
    private function assembleClassMapCollection(string $classMap, string $linefeed): string
    {
        $code = [
            'new ClassMapCollection(',
            '%s',
            ');',
        ];

        return sprintf(implode($linefeed, $code), $classMap);
    }
}
