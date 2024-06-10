<?php
declare(strict_types=1);

namespace Phpro\SoapClient\CodeGenerator\Model;

use Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator\TypeNameCalculator;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Metadata\Predicate\IsConsideredScalarType;
use function Psl\Type\non_empty_string;

final class ReturnType
{
    /**
     * @var non-empty-string
     */
    private string $type;

    /**
     * @var non-empty-string
     */
    private string $namespace;

    private XsdType $xsdType;

    private TypeMeta $meta;

    /**
     * Property constructor.
     *
     * @param non-empty-string $type
     * @param non-empty-string $namespace
     */
    public function __construct(string $type, string $namespace, XsdType $xsdType)
    {
        $this->type = Normalizer::normalizeDataType($type);
        $this->namespace = Normalizer::normalizeNamespace($namespace);
        $this->xsdType = $xsdType;
        $this->meta = $xsdType->getMeta();
    }

    /**
     * @param non-empty-string $namespace
     */
    public static function fromMetaData(string $namespace, XsdType $returnType): self
    {
        $typeName = (new TypeNameCalculator())($returnType);

        return new self(
            non_empty_string()->assert($typeName),
            $namespace,
            $returnType
        );
    }
    /**
     * @return non-empty-string
     */
    public function getType(): string
    {
        if (Normalizer::isKnownType($this->type)) {
            return $this->type;
        }

        return '\\'.$this->namespace.'\\'.Normalizer::normalizeClassname($this->type);
    }

    public function getXsdType(): XsdType
    {
        return $this->xsdType;
    }

    public function getMeta(): TypeMeta
    {
        return $this->meta;
    }

    public function shouldGenerateAsMixedResult(): bool
    {
        return (new IsConsideredScalarType())($this->meta)
            || Normalizer::isKnownType($this->type);
    }
}
