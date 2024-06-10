<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap;

use Soap\CachedEngine\CachedEngine;
use Soap\Encoding\Driver;
use Soap\Engine\Engine;
use Soap\Engine\LazyEngine;
use Soap\Engine\SimpleEngine;
use Soap\WsdlReader\Wsdl1Reader;

final class DefaultEngineFactory
{
    public static function create(
        EngineOptions $options
    ): Engine {

        $cache = $options->getCache();
        $factory = static fn(): Engine => self::configureEngine($options);

        return match (true) {
            $cache->isSome() => new CachedEngine($cache->unwrap(), $options->getCacheConfig(), $factory),
            default => new LazyEngine($factory),
        };
    }

    private static function configureEngine(EngineOptions $options): Engine
    {
        $wsdl = (new Wsdl1Reader($options->getWsdlLoader()))(
            $options->getWsdl(),
            $options->getWsdlParserContext()
        );

        $driver = Driver::createFromWsdl1(
            $wsdl,
            $options->getWsdlServiceSelectionCriteria(),
            $options->getEncoderRegistry()
        );

        return new SimpleEngine(
            $driver,
            $options->getTransport()
        );
    }
}
