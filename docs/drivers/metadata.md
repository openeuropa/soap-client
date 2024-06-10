# Driver metadata

### Duplicate types

XSDs can have both internal and external types making that the names of the type can be non-unique in a given XML namespace.
It might not be possible to generate unique types for all types in the WSDL.
Therefore, we added some strategies to deal with duplicate types by default.

This can be configured in the [client configuration](/docs/code-generation/configuration.md):

**IntersectDuplicateTypesStrategy**

Enabled by default when using `Config::create()`.

This duplicate types strategy will merge all duplicate types into one big type which contains all properties.


```php
use Phpro\SoapClient\CodeGenerator\Config\Config;
use Phpro\SoapClient\Soap\Metadata\Manipulators\DuplicateTypes\IntersectDuplicateTypesStrategy;
use Phpro\SoapClient\Soap\Metadata\MetadataOptions;

return Config::create()
    //...
    ->setTypeMetadataOptions(
        MetadataOptions::empty()->withTypesManipulator(new IntersectDuplicateTypesStrategy())
    )
    // ...
```

**RemoveDuplicateTypesStrategy**

This duplicate types strategy will remove all duplicate types it finds.

You can overwrite the strategy on the `DefaultEngineFactory` object inside the client factory:

```php
use Phpro\SoapClient\CodeGenerator\Config\Config;
use Phpro\SoapClient\Soap\Metadata\Manipulators\DuplicateTypes\RemoveDuplicateTypesStrategy;
use Phpro\SoapClient\Soap\Metadata\MetadataOptions;

return Config::create()
    //...
    ->setTypeMetadataOptions(
        MetadataOptions::empty()->withTypesManipulator(new RemoveDuplicateTypesStrategy())
    )
    // ...
```
