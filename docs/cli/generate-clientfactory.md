# Generate a base client factory

To make things a little easier to get started a client factory generator method is available.
The generated factory can be seen as a good starting point to initialize the client.
It can be customized to your needs.

```bash
vendor/bin/soap-client generate:clientfactory

Usage:
  generate:clientfactory [options]

Options:
      --config=CONFIG   The location of the soap code-generator config file
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```

Options:

- **config**: A [configuration file](../code-generation/configuration.md) is required to build the classmap. 

The factory will be put in the same namespace and directory as the client, and use the same name as the client, appended by Factory.

More advanced client factory:

```php
<?php

use Http\Client\Common\PluginClient;
use Http\Discovery\Psr18ClientDiscovery;
use Phpro\SoapClient\Caller\EngineCaller;
use Phpro\SoapClient\Caller\EventDispatchingCaller;
use Phpro\SoapClient\Soap\EngineOptions;
use Phpro\SoapClient\Soap\DefaultEngineFactory;
use Phpro\SoapClient\Soap\Metadata\Manipulators\DuplicateTypes\IntersectDuplicateTypesStrategy;
use Phpro\SoapClient\Soap\Metadata\MetadataOptions;
use Soap\CachedEngine\CacheConfig;
use Soap\Encoding\EncoderRegistry;
use Soap\Psr18Transport\Psr18Transport;
use Soap\Psr18Transport\Wsdl\Psr18Loader;
use Soap\Wsdl\Loader\FlatteningLoader;
use Soap\WsdlReader\Locator\ServiceSelectionCriteria
use Soap\WsdlReader\Model\Definitions\SoapVersion;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CalculatorClientFactory
{
    public static function factory(string $wsdl) : CalculatorClient
    {
        $engine = DefaultEngineFactory::create(
            EngineOptions::defaults($wsdl)
                ->withEncoderRegistry(
                    EncoderRegistry::default()
                        ->addClassMapCollection(CalculatorClassmap::getCollection())
                )
                ->withTransport(
                    Psr18Transport::createForClient(
                        new PluginClient(
                            Psr18ClientDiscovery::find(),
                            [$plugin1, $plugin2]
                        )
                    )    
                )
                ->withWsdlLoader(
                    new FlatteningLoader(
                        new Psr18Loader(Psr18ClientDiscovery::find())
                    )
                )
                ->withCache(
                    new RedisAdapter(RedisAdapter::createConnection('redis://localhost')),
                    new CacheConfig('my-wsdl-cache-key', ttlInSeconds: 3600)
                )
                ->withWsdlServiceSelectionCriteria(
                    ServiceSelectionCriteria::defaults()
                        ->withPreferredSoapVersion(SoapVersion::SOAP_12)
                        ->withServiceName('SpecificServiceName')
                        ->withPortName('SpecificPortName')
                )
        );

        $eventDispatcher = new EventDispatcher();
        $caller = new EventDispatchingCaller(new EngineCaller($engine), $eventDispatcher);

        return new CalculatorClient($caller);
    }
}


```

You can then tweak this class to fit your needs.

Here you can find some bookmarks for changing the factory:

- [Listening to events](../events.md)
- [Configuring the engine](https://github.com/php-soap/engine)
- [Using HTTP middleware](https://github.com/php-soap/psr18-transport/#middleware) 

Next: [Use your SOAP client.](/docs/usage.md)
