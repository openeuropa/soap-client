<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap;

use Psl\Option\Option;
use Psr\Cache\CacheItemPoolInterface;
use Soap\CachedEngine\CacheConfig;
use Soap\Encoding\EncoderRegistry;
use Soap\Engine\Transport;
use Soap\Psr18Transport\Psr18Transport;
use Soap\Wsdl\Loader\FlatteningLoader;
use Soap\Wsdl\Loader\StreamWrapperLoader;
use Soap\Wsdl\Loader\WsdlLoader;
use Soap\WsdlReader\Locator\ServiceSelectionCriteria;
use Soap\WsdlReader\Model\Definitions\SoapVersion;
use Soap\WsdlReader\Parser\Context\ParserContext;
use function Psl\Option\from_nullable;

final class EngineOptions
{
    /**
     * @var non-empty-string
     */
    private string $wsdl;
    private ?WsdlLoader $wsdlLoader = null;
    private ?Transport $transport = null;
    private ?CacheItemPoolInterface $cache = null;
    private ?CacheConfig $cacheConfig = null;
    private ?SoapVersion $preferredSoapVersion = null;
    private ?ParserContext $wsdlParserContext = null;
    private ?EncoderRegistry $encoderRegistry = null;

    /**
     * @param non-empty-string $wsdl
     */
    private function __construct(string $wsdl)
    {
        $this->wsdl = $wsdl;
    }

    /**
     * @param non-empty-string $wsdl
     * @return self
     */
    public static function defaults(string $wsdl): self
    {
        return new self($wsdl);
    }

    public function withCache(CacheItemPoolInterface $cache, ?CacheConfig $config = null): self
    {
        $clone = clone $this;
        $clone->cache = $cache;
        $clone->cacheConfig = $config;

        return $clone;
    }

    public function withWsdlLoader(WsdlLoader $loader): self
    {
        $clone = clone $this;
        $clone->wsdlLoader = $loader;

        return $clone;
    }

    public function withWsdlParserContext(ParserContext $parserContext): self
    {
        $clone = clone $this;
        $clone->wsdlParserContext = $parserContext;

        return $clone;
    }

    public function withTransport(Transport $transport): self
    {
        $clone = clone $this;
        $clone->transport = $transport;

        return $clone;
    }

    public function withPreferredSoapVersion(SoapVersion $preferredSoapVersion): self
    {
        $clone = clone $this;
        $clone->preferredSoapVersion = $preferredSoapVersion;

        return $clone;
    }

    public function withEncoderRegistry(EncoderRegistry $registry): self
    {
        $clone = clone $this;
        $clone->encoderRegistry = $registry;

        return $clone;
    }

    /**
     * @return non-empty-string
     */
    public function getWsdl(): string
    {
        return $this->wsdl;
    }

    public function getWsdlLoader(): WsdlLoader
    {
        return $this->wsdlLoader ?? new FlatteningLoader(new StreamWrapperLoader());
    }

    public function getWsdlParserContext(): ParserContext
    {
        return $this->wsdlParserContext ?? ParserContext::defaults();
    }

    public function getTransport(): Transport
    {
        return $this->transport ?? Psr18Transport::createWithDefaultClient();
    }

    /**
     * @return Option<CacheItemPoolInterface>
     */
    public function getCache(): Option
    {
        return from_nullable($this->cache);
    }

    public function getCacheConfig(): CacheConfig
    {
        return $this->cacheConfig ?? new CacheConfig('soap-engine-'.md5($this->wsdl));
    }

    /**
     * @return Option<SoapVersion>
     */
    public function getPreferredSoapVersion(): Option
    {
        return from_nullable($this->preferredSoapVersion);
    }

    public function getWsdlServiceSelectionCriteria(): ServiceSelectionCriteria
    {
        return ServiceSelectionCriteria::defaults()
            ->withAllowHttpPorts(false)
            ->withPreferredSoapVersion($this->preferredSoapVersion);
    }

    public function getEncoderRegistry(): EncoderRegistry
    {
        return $this->encoderRegistry ?? EncoderRegistry::default();
    }
}
