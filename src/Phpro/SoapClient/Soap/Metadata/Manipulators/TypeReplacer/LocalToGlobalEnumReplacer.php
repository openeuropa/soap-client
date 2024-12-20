<?php

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Soap\Engine\Metadata\Model\XsdType;

/**
 * This replacer can be used to mark local enums as global.
 * It will result in the enum type name being used in the generated code instead of a list of all possible enum values.
 */
final class LocalToGlobalEnumReplacer implements TypeReplacer
{
    public function __invoke(XsdType $xsdType): XsdType
    {
        $meta = $xsdType->getMeta();
        if (!$meta->isSimple()->unwrapOr(false)
            || !$meta->isLocal()->unwrapOr(false)
            || !$meta->enums()->isSome()
        ) {
            return $xsdType;
        }

        return $xsdType->copy($xsdType->getName())
            ->withMeta(
                static fn ($meta) => $meta->withIsLocal(false)
            );
    }
}
