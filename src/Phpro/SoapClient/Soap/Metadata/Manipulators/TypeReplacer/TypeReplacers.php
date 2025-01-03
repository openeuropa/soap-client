<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Soap\Engine\Metadata\Model\XsdType;
use function Psl\Iter\reduce;

final class TypeReplacers implements TypeReplacer
{
    /**
     * @var list<TypeReplacer>
     */
    private array $replacers;

    /**
     * @no-named-arguments
     */
    public function __construct(TypeReplacer ...$replacers)
    {
        $this->replacers = $replacers;
    }

    public static function defaults(): self
    {
        return new self(
            new ApacheMapReplacer(),
            new SoapObjectReplacer(),
            new SoapArrayReplacer(),
        );
    }

    public static function empty(): self
    {
        return new self();
    }

    public function add(TypeReplacer $replacer): self
    {
        $new = clone $this;
        $new->replacers[] = $replacer;

        return $new;
    }

    public function __invoke(XsdType $type): XsdType
    {
        return reduce(
            $this->replacers,
            static fn(XsdType $type, TypeReplacer $replacer): XsdType => $replacer($type),
            $type,
        );
    }
}
