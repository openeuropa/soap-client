<?php
declare(strict_types=1);

namespace Phpro\SoapClient\CodeGenerator\TypeEnhancer;

use Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator\ArrayBoundsCalculator;
use Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator\EnumValuesCalculator;
use Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator\UnionTypesCalculator;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\WsdlReader\Metadata\Predicate\IsConsideredNullableType;

final class MetaTypeEnhancer implements TypeEnhancer
{
    public function __construct(
        private TypeMeta $meta
    ) {
    }

    /**
     * @param non-empty-string $type
     * @return non-empty-string
     */
    public function asDocBlockType(string $type): string
    {
        $isLocal = $this->meta->isLocal()->unwrapOr(false);
        $isEnum = (bool) $this->meta->enums()->unwrapOr([]);
        $isUnion = (bool) $this->meta->unions()->unwrapOr([]);

        $type = match (true) {
            $isLocal && $isEnum => (new EnumValuesCalculator())($this->meta),
            $isUnion => (new UnionTypesCalculator())($this->meta),
            default => $type
        };

        $isArray = $this->meta->isList()->unwrapOr(false);
        if ($isArray) {
            $nonEmpty = $this->meta->minOccurs()->unwrapOr(0) > 0;
            $arrayType = $nonEmpty ? 'non-empty-array' : 'array';

            $type = $arrayType.'<'.(new ArrayBoundsCalculator())($this->meta).', '.$type.'>';
        }

        $isNullable = (new IsConsideredNullableType())($this->meta);
        if ($isNullable) {
            $type = 'null | '.$type;
        }

        return $type;
    }

    /**
     * @param non-empty-string $type
     * @return non-empty-string
     */
    public function asPhpType(string $type): string
    {
        $unions = (bool) $this->meta->unions()->unwrapOr([]);
        if ($unions) {
            $type = 'mixed';
        }

        $isArray = $this->meta->isList()->unwrapOr(false);
        if ($isArray) {
            $type = 'array';
        }

        $isNullable = (new IsConsideredNullableType())($this->meta);
        if ($isNullable && $type !== 'mixed') {
            $type = '?'.$type;
        }

        return $type;
    }
}
