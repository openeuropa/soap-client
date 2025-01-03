<?php

namespace Phpro\SoapClient\CodeGenerator\Config;

use Phpro\SoapClient\CodeGenerator\Assembler;
use Phpro\SoapClient\CodeGenerator\Rules;
use Phpro\SoapClient\CodeGenerator\Rules\RuleInterface;
use Phpro\SoapClient\CodeGenerator\Rules\RuleSet;
use Phpro\SoapClient\CodeGenerator\Rules\RuleSetInterface;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\InvalidArgumentException;
use Phpro\SoapClient\Soap\Metadata\Detector\LocalEnumDetector;
use Phpro\SoapClient\Soap\Metadata\Manipulators\DuplicateTypes\IntersectDuplicateTypesStrategy;
use Phpro\SoapClient\Soap\Metadata\Manipulators\MethodsManipulatorChain;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\LocalToGlobalEnumReplacer;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\AppendTypesManipulator;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\ReplaceMethodTypesManipulator;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\ReplaceTypesManipulator;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacer;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacers;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypesManipulatorChain;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypesManipulatorInterface;
use Phpro\SoapClient\Soap\Metadata\MetadataFactory;
use Phpro\SoapClient\Soap\Metadata\MetadataOptions;
use Soap\Engine\Engine;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Metadata;

final class Config
{
    /**
     * @var string
     */
    protected $clientName = 'Client';

    /**
     * @var string
     */
    protected $typeNamespace = '';

    /**
     * @var string
     */
    protected $clientNamespace = '';

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @var string
     */
    protected $clientDestination = '';

    /**
     * @var string
     */
    protected $typeDestination = '';

    protected TypesManipulatorInterface $duplicateTypeIntersectStrategy;

    protected TypeReplacer $typeReplacementStrategy;

    protected ?MetadataOptions $metadataOptions = null;

    /**
     * @var RuleSetInterface
     */
    protected $ruleSet;

    /**
     * @var string
     */
    protected $classMapName;

    /**
     * @var string
     */
    protected $classMapNamespace;

    /**
     * @var string
     */
    protected $classMapDestination;

    protected EnumerationGenerationStrategy $enumerationGenerationStrategy;

    public function __construct()
    {
        $this->typeReplacementStrategy = TypeReplacers::defaults();

        // Working with duplicate types is hard (see FAQ).
        // Therefore, we decided to combine all duplicate types into 1 big intersected type by default instead.
        // The resulting type will always be usable, but might contain some additional empty properties.
        $this->duplicateTypeIntersectStrategy = new IntersectDuplicateTypesStrategy();

        // By default, we only generate global enumerations to avoid naming conflicts.
        $this->enumerationGenerationStrategy = EnumerationGenerationStrategy::default();

        $this->ruleSet = new RuleSet([
            new Rules\AssembleRule(new Assembler\PropertyAssembler()),
            new Rules\AssembleRule(new Assembler\ClassMapAssembler()),
            new Rules\AssembleRule(new Assembler\ClientConstructorAssembler()),
            new Rules\AssembleRule(new Assembler\ClientMethodAssembler())
        ]);
    }

    /**
     * @return Config
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * @return string
     */
    public function getTypeNamespace(): string
    {
        return $this->typeNamespace;
    }

    /**
     * @param non-empty-string $namespace
     *
     * @return Config
     */
    public function setTypeNamespace($namespace): self
    {
        $this->typeNamespace = Normalizer::normalizeNamespace($namespace);

        return $this;
    }

    /**
     * @return Engine
     */
    public function getEngine(): Engine
    {
        if (!$this->engine instanceof Engine) {
            throw InvalidArgumentException::engineNotConfigured();
        }
        return $this->engine;
    }

    /**
     * @param Engine $engine
     *
     * @return Config
     */
    public function setEngine(Engine $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @return RuleSetInterface
     */
    public function getRuleSet(): RuleSetInterface
    {
        return $this->ruleSet;
    }

    /**
     * @param RuleSetInterface $ruleSet
     *
     * @return Config
     */
    public function setRuleSet(RuleSetInterface $ruleSet): self
    {
        $this->ruleSet = $ruleSet;

        return $this;
    }

    /**
     * @param RuleInterface $rule
     *
     * @return Config
     */
    public function addRule(RuleInterface $rule): self
    {
        $this->ruleSet->addRule($rule);

        return $this;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     * @return $this
     */
    public function setClientName($clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientNamespace(): string
    {
        if (!$this->clientNamespace) {
            throw InvalidArgumentException::clientNamespaceIsMissing();
        }

        return $this->clientNamespace;
    }

    /**
     * @param string $clientNamespace
     * @return Config
     */
    public function setClientNamespace($clientNamespace): self
    {
        $this->clientNamespace = $clientNamespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientDestination(): string
    {
        if (!$this->clientDestination) {
            throw InvalidArgumentException::clientDestinationIsMissing();
        }

        return $this->clientDestination;
    }

    /**
     * @param string $clientDestination
     * @return Config
     */
    public function setClientDestination($clientDestination): self
    {
        $this->clientDestination = $clientDestination;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeDestination(): string
    {
        if (!$this->typeDestination) {
            throw InvalidArgumentException::typeDestinationIsMissing();
        }

        return $this->typeDestination;
    }

    /**
     * @param string $typeDestination
     * @return Config
     */
    public function setTypeDestination($typeDestination): self
    {
        $this->typeDestination = $typeDestination;

        return $this;
    }

    public function getMetadataOptions(): MetadataOptions
    {
        if ($this->metadataOptions) {
            return $this->metadataOptions;
        }

        $typeReplacementStrategy = $this->typeReplacementStrategy;
        $appendTypes = static fn () => new TypeCollection();

        if ($this->enumerationGenerationStrategy === EnumerationGenerationStrategy::LocalAndGlobal) {
            $typeReplacementStrategy = new TypeReplacers($typeReplacementStrategy, new LocalToGlobalEnumReplacer());
            $appendTypes = new LocalEnumDetector();
        }

        return MetadataOptions::empty()
            ->withTypesManipulator(
                new TypesManipulatorChain(
                    new AppendTypesManipulator($appendTypes),
                    $this->duplicateTypeIntersectStrategy,
                    new ReplaceTypesManipulator($typeReplacementStrategy),
                )
            )->withMethodsManipulator(
                new MethodsManipulatorChain(
                    new ReplaceMethodTypesManipulator($typeReplacementStrategy)
                )
            );
    }

    public function getManipulatedMetadata(): Metadata
    {
        return MetadataFactory::manipulated(
            $this->getEngine()->getMetadata(),
            $this->getMetadataOptions()
        );
    }

    public function setTypeReplacementStrategy(TypeReplacer $typeReplacementStrategy): self
    {
        $this->typeReplacementStrategy = $typeReplacementStrategy;

        return $this;
    }

    public function setDuplicateTypeIntersectStrategy(TypesManipulatorInterface $duplicateTypeIntersectStrategy): self
    {
        $this->duplicateTypeIntersectStrategy = $duplicateTypeIntersectStrategy;

        return $this;
    }

    public function setMetadataOptions(MetadataOptions $metadataOptions): self
    {
        $this->metadataOptions = $metadataOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassMapName(): string
    {
        if (!$this->classMapName) {
            throw InvalidArgumentException::classmapNameMissing();
        }

        return $this->classMapName;
    }

    /**
     * @return string
     */
    public function getClassMapNamespace(): string
    {
        if (!$this->classMapNamespace) {
            throw InvalidArgumentException::classmapNamespaceMissing();
        }

        return $this->classMapNamespace;
    }

    /**
     * @return string
     */
    public function getClassMapDestination(): string
    {
        if (!$this->classMapDestination) {
            throw InvalidArgumentException::classmapDestinationMissing();
        }

        return $this->classMapDestination;
    }

    /**
     * @param string $classMapName
     * @return Config
     */
    public function setClassMapName(string $classMapName): self
    {
        $this->classMapName = $classMapName;

        return $this;
    }

    /**
     * @param string $classMapNamespace
     * @return Config
     */
    public function setClassMapNamespace(string $classMapNamespace): self
    {
        $this->classMapNamespace = $classMapNamespace;

        return $this;
    }

    /**
     * @param string $classMapDestination
     * @return Config
     */
    public function setClassMapDestination(string $classMapDestination): self
    {
        $this->classMapDestination = $classMapDestination;

        return $this;
    }

    public function setEnumerationGenerationStrategy(EnumerationGenerationStrategy $enumerationGenerationStrategy): self
    {
        $this->enumerationGenerationStrategy = $enumerationGenerationStrategy;

        return $this;
    }

    public function getEnumerationGenerationStrategy(): EnumerationGenerationStrategy
    {
        return $this->enumerationGenerationStrategy;
    }
}
