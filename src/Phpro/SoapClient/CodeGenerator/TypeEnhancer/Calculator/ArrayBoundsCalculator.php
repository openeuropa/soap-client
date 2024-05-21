<?php
declare(strict_types=1);

namespace Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator;

use Soap\Engine\Metadata\Model\TypeMeta;

final class ArrayBoundsCalculator
{
    /**
     * All lists will start from index 0.
     *
     * The maximum amount of items in the list will be maxOccurs - 1, since arrays are zero-index based.
     * Edge cases like -1 (unbounded) and 0 (empty) are handled as well.
     *
     * These bounds don't take into account minOccurs, since minOccurs still starts from 0.
     */
    public function __invoke(TypeMeta $meta): string
    {
        $max = $meta->maxOccurs()->unwrapOr(-1);

        return match (true) {
            $max < 0 => 'int<0,max>',
            $max === 0 => 'never',
            default => 'int<0,'.($max - 1).'>'
        };
    }
}
