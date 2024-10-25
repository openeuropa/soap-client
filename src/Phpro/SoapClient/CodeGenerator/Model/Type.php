<?php

namespace Phpro\SoapClient\CodeGenerator\Model;

use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Soap\Engine\Metadata\Model\Property as MetadataProperty;
use Soap\Engine\Metadata\Model\Type as MetadataType;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use SplFileInfo;
use function Psl\Type\non_empty_string;

/**
 * Class Type
 *
 * @package Phpro\SoapClient\CodeGenerator\Model
 */
class Type
{
    /**
     * @var non-empty-string
     */
    private $namespace;

    /**
     * @var non-empty-string
     */
    private $xsdName;

    /**
     * @var non-empty-string
     */
    private $name;

    /**
     * @var array
     */
    private $properties = [];

    private XsdType $xsdType;

    private TypeMeta $meta;

    /**
     * @internal - Use Type::fromMetadata instead
     *
     * TypeModel constructor.
     *
     * @param non-empty-string     $namespace
     * @param non-empty-string     $xsdName
     * @param non-empty-string     $name
     * @param Property[] $properties
     */
    public function __construct(string $namespace, string $xsdName, string $name, array $properties, XsdType $xsdType)
    {
        $this->namespace = $namespace;
        $this->xsdName = $xsdName;
        $this->name = $name;
        $this->properties = $properties;
        $this->xsdType = $xsdType;
        $this->meta = $xsdType->getMeta();
    }

    /**
     * @param non-empty-string $namespace
     */
    public static function fromMetadata(string $namespace, MetadataType $type): self
    {
        $xsdName = non_empty_string()->assert($type->getName());

        return new self(
            Normalizer::normalizeNamespace($namespace),
            $xsdName,
            Normalizer::normalizeClassname($xsdName),
            array_map(
                function (MetadataProperty $property) use ($namespace) {
                    return Property::fromMetaData(
                        $namespace,
                        $property
                    );
                },
                iterator_to_array($type->getProperties())
            ),
            $type->getXsdType(),
        );
    }

    /**
     * @return non-empty-string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function getXsdName(): string
    {
        return $this->xsdName;
    }

    /**
     * @param non-empty-string $destination
     *
     * @return SplFileInfo
     */
    public function getFileInfo(string $destination): SplFileInfo
    {
        $name = Normalizer::normalizeClassname($this->getName());
        $path = rtrim($destination, '/\\').'/'.$name.'.php';

        return new SplFileInfo($path);
    }

    /**
     * @return non-empty-string
     */
    public function getFullName(): string
    {
        $fqnName = sprintf('%s\\%s', $this->getNamespace(), $this->getName());

        return Normalizer::normalizeNamespace($fqnName);
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getXsdType(): XsdType
    {
        return $this->xsdType;
    }

    public function getMeta(): TypeMeta
    {
        return $this->meta;
    }
}
