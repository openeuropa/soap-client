<?php

declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Detector;

use Soap\Engine\Metadata\Collection\MethodCollection;
use Soap\Engine\Metadata\Model\Method;
use function Psl\Dict\unique;
use function Psl\Vec\filter;
use function Psl\Vec\flat_map;
use function Psl\Vec\values;

final class ResponseTypesDetector
{
    public function __invoke(MethodCollection $methods): array
    {
        return values(
            unique(
                filter(
                    flat_map(
                        $methods,
                        static fn (Method $method): array => [
                            $method->getReturnType()->getName(),
                            $method->getReturnType()->getXmlTypeName()
                        ]
                    )
                )
            )
        );
    }
}
