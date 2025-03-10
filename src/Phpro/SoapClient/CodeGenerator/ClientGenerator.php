<?php

namespace Phpro\SoapClient\CodeGenerator;

use Laminas\Code\Generator\Exception\ClassNotFoundException;
use Phpro\SoapClient\CodeGenerator\Context\ClientContext;
use Phpro\SoapClient\CodeGenerator\Context\ClientMethodContext;
use Phpro\SoapClient\CodeGenerator\Context\FileContext;
use Phpro\SoapClient\CodeGenerator\Context\PropertyContext;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\CodeGenerator\Model\Client;
use Phpro\SoapClient\CodeGenerator\Model\Type;
use Phpro\SoapClient\CodeGenerator\Rules\RuleSetInterface;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;

/**
 * @template-implements GeneratorInterface<Client>
 */
class ClientGenerator implements GeneratorInterface
{
    /**
     * @var RuleSetInterface
     */
    private $ruleSet;

    /**
     * TypeGenerator constructor.
     *
     * @param RuleSetInterface $ruleSet
     */
    public function __construct(RuleSetInterface $ruleSet)
    {
        $this->ruleSet = $ruleSet;
    }

    /**
     * @param FileGenerator $file
     * @param Client        $client
     *
     * @return string
     */
    public function generate(FileGenerator $file, $client): string
    {
        try {
            // @phpstan-ignore-next-line
            $class = $file->getClass() ?: new ClassGenerator();
        } catch (ClassNotFoundException $exception) {
            $class = new ClassGenerator();
        }
        $class->setNamespaceName($client->getNamespace());
        $class->setName($client->getName());

        $this->ruleSet->applyRules(new ClientContext($class, $client->getName(), $client->getNamespace()));

        $methods = $client->getMethodMap();
        foreach ($methods->getMethods() as $method) {
            $this->ruleSet->applyRules(new ClientMethodContext($class, $method));
        }

        $this->ruleSet->applyRules(new FileContext($file));
        $file->setClass($class);

        return $file->generate();
    }
}
