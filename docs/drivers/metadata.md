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
    ->setDuplicateTypeIntersectStrategy(
        new IntersectDuplicateTypesStrategy()
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
    ->setDuplicateTypeIntersectStrategy(
        new IntersectDuplicateTypesStrategy()
    )
    // ...
```

### Type replacements

Depending on what XML encoders you configure, you might want to replace some types with other types.
Take following example:

By default, a "date" type from the XSD namespace `http://www.w3.org/2001/XMLSchema` will be converted to a `DateTimeImmutable` object.
However, if you configure an encoder that does not support `DateTimeImmutable`,
you might want to replace it with a `int` type that represents the amount of seconds since the unix epoch.

This can be configured in the [client configuration](/docs/code-generation/configuration.md):

```php
use Phpro\SoapClient\CodeGenerator\Config\Config;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacers;

return Config::create()
    //...
    ->setTypeReplacements(
        TypeReplacers::defaults()
            ->add(new MyDateReplacer())
    )
    // ...
```

The `MyDateReplacer` class should implement the `TypeReplacerInterface` and should return the correct type for the given type.

```php
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer\TypeReplacer;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Metadata\Predicate\IsOfType;use Soap\Xml\Xmlns;

final class MyDateReplacer implements TypeReplacer
{
    public function __invoke(XsdType $xsdType) : XsdType
    {
        $check = new IsOfType(Xmlns::xsd()->value(), 'date');
        if (!$check($xsdType)) {
            return $xsdType;
        }

        return $xsdType->copy('int')->withBaseType('int');
    }
}
```

This way, the generated code will use the `int` type instead of the `DateTimeImmutable` type for the `date` type in the XSD.

The TypeReplacers contain a default set of type replacements that are being used to improve the generated code:

* `array` for SOAP 1.1 and 1.2 Arrays
* `object` for SOAP 1.1 Objects
* `array` for Apache Map types
