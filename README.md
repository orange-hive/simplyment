# Simplyment - simplified development
The aim of this extension is to spend less time on configuring, setting up and jumping around in your TYPO3 extension code for doing common tasks.\
Some things are simplified by having conventions where to store e.g. backend layouts or by using PHP attributes on your classes and properties, making development of extensions simpler.\
More time for concentrating on the interesting parts like writing logic and getting development speeded up!


## Requirements
- PHP 8.0 or newer
- TYPO3 11.5

## Installation
- Install the extension with Composer - dependency name: **orange-hive/simplyment**

## Configuration
Include static "Simplyment" in your Template before loading the static typoscript for defining your page configuration typoscript \
if you want to use BackendLayout and Frontend Template loading of Simplyment.
This already defines a page=PAGE and uses 10 = FLUIDTEMPLATE.\
If you want to define the page object on your own you can use the Template autoloading based on the backend layout name \
using the following code for *templateName*:
```typoscript
templateName < simplyment.page.resolveTemplateName
```

Add the following code in your extension - that's all!:
  - file Configuration/Services.yaml
```yaml
services:
  MyVendorName\MyExtensionKey:
    tags:
      - name: simplyment
```


*Alternatively you can skip the registration in Configuration/Services.yaml and manually register your extension for usage with Simplyment:*
  - *file: ext_localconf.php*
```php
\OrangeHive\Simplyment\Loader::extLocalconf(
  vendorName: 'MyVendorName',
  extensionName: 'my_extension_key'
);
```
  - *file: ext_tables.php*
```php
\OrangeHive\Simplyment\Loader::extTables(
  vendorName: 'MyVendorName',
  extensionName: 'my_extension_key'
);
```

*As a third argument you could add *loaders* as an array containing the loaders you want to use. 
If no loaders are defined all loaders will be used.*

## Usage

### BackendLayouts
Loader: BackendLayoutLoader\
\
You can create BackendLayout definitions by creating a TypoScript file with the extension: .ts, .tsconfig, .typoscript or .txt \
in your extension directory *EXT:<MY_EXTENSION>\Resources\Private\BackendLayouts*.\
The filename has to be written lower_snake_case.\
In the file use the following structure:
```typoscript
{
    title = My title
    description = My template description
    icon = EXT:my_extension/Resources/Public/Images/BackendLayouts/default.png
    config {
        backend_layout {
            colCount = 1
            rowCount = 1
            rows {
                1 {
                    columns {
                        1 {
                            name = LLL:EXT:my_extension/Resources/Private/Language/locallang_be.xlf:backend_layout.column.normal
                            colPos = 0
                        }
                    }
                }
            }
        }
    }
}
```

For the properties *title* and *description* you can use LLL-notation for using localized translations.

The frontend template is automatically determined by your TemplatesRootPath directory.\
The filename of your template file has to be equal to your BackendLayout file name but with using UpperCamelCase instead of lower_snake_case!

<br />

### Plugins
Loader: PluginLoader\
\
You can register a new Plugin directly on your ActionController with the PHP attribute **Plugin**.
Plugin actions can be added with adding the PHP attribute **PluginAction** at the action methods.

#### Adding FlexForm to your plugin
FlexForms can be easily added to your Plugin using the **flexFormPath** property in the **Plugin** PHP attribute.
The value of this property has to be a string starting with *EXT:* and defining the path to your FlexForm XML file.
If no FlexForm has been defined Simplyment tries to find a FlexForm file in the location *EXT:my_extension/Configuration/FlexForms/MyPluginName.xml* and adds this automatically.

For using this functionality add the following code to the file *TCA/Overrides/tt_content.php* in your extension:
```php
\OrangeHive\Simplyment\Loader::tcaTtContentOverrides('MyVendorName', 'my_extension_key');
```

<br />

### Database models
Loader: DatabaseModelLoader\
\
Register a new database model with the PHP attribute **DatabaseTable**.
Properties of the model which should be persisted to the database table have to receive the PHP attribute **DatabaseField** defining the field type.

For generating the TCA add the PHP attribute **TcaField** to your property.\
You can additionally add the PHP attribute **Tca** to your class for defining the following options:
- icon: string containing the path to your custom icon for the model, if none is provided the Simplyment extension icon will be used
- allowOnStandardPage: boolean - allow table entries to be added on standard pages and not only in folders, default: false 
- config: array - global TCA configuration, can override every TCA value of this model

<br />

For the TCA configuration of your new database model the TCA file is automatically generated by Simplyment in **Configuration/TCA/** 
on cache clear if not already existent containing the following content:
```php
<?php

$base = \OrangeHive\Simplyment\Utility\ModelTcaUtility::getTca(\MyVendor\MyExtension\Domain\Model\MyModel::class);

$custom = [];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($base, $custom);


return $base;
```
> **Note:**
> 
> *\MyVendor\MyExtension\Domain\Model\MyModel* will be the FQCN to the model.\
> In the array *$custom* you can override the auto generated TCA configuration.

<br />

#### Extending existent tables
Existent tables can be extended by adding the property **tableName** in the DatabaseTable attribute.

Already existent fields in table can be defined as property without any PHP attribute. Custom fields have to receive the PHP attributes **DatabaseField** and **TcaField**.
Example:
```php
<?php

namespace MyVendor\MyExtension\Domain\Model;

use OrangeHive\Simplyment\Attributes\DatabaseField;
use OrangeHive\Simplyment\Attributes\DatabaseTable;
use OrangeHive\Simplyment\Attributes\TcaField;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

#[DatabaseTable(
    tableName: 'pages' // extending table 'pages'
)]
class Page extends AbstractEntity
{

    protected string $title = ''; // already existent field title

    #[DatabaseField(type: 'text')]
    #[TcaField]
    protected string $txMyField; // custom field

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getTxMyField(): string
    {
        return $this->txMyField;
    }

}
```


Instead of the file in **Configuration/TCA/** a file will be created in **Configuration/TCA/Overrides/** with the tableName as filename.\
The content of the file is automatically generated by Simplyment on  cache clear if not already existent containing the following content:
```php
<?php

use OrangeHive\Simplyment\Utility\ModelTcaUtility;


$customColumnOverrides = [];
ModelTcaUtility::addColumnTcaOverrides(
    fqcn: (\MyVendor\MyExtension\Domain\Model\MyModel::class,
    tableName: 'givenTableName',
    columnOverrides: $customColumnOverrides
);

ModelTcaUtility::addColumnsToAllTcaTypes(
    fqcn: (\MyVendor\MyExtension\Domain\Model\MyModel::class,
    tableName: 'givenTableName'
);
```
> **Note:**
>
> *\MyVendor\MyExtension\Domain\Model\MyModel* will be the FQCN to the model.\
> givenTableName will be the tableName defined in the DatabaseTable attribute.
> 
> All your custom fields defined in the model will be added to all TCA types. In the method **ModelTcaUtility::addColumnsToAllTcaTypes** you can limit the fields to be added (or change order by providing the fields in a custom array) with the argument **fieldsOverride**.
> Additionally you can define types to which your fields should only be added with the argument **typeList** containing a string with comma separated values.
> Also the position of the insert can be defined with the attribute **position**.
> 
> The logic is based on **\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes** 
> (https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/ExtensionArchitecture/HowTo/ExtendingTca/Examples/Index.html).

<br />

#### Relations with ObjectStorage
For ObjectStorage relations wirte as usual the PHP annotation for defining the ObjectStorage in the doc block above your property:
```php
/**
 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\MyVendor\MyExtension\Domain\Model\MySubModel>
 */
```
You can define the TCA on your own or use the **TcaField** attribute with the arguments **type** and **targetClass**.\
This scans the defined targetClass by reflection and searches for a property with the type of your model from which you reference the sub model. 
This property is used as foreign_field in the TCA.
Per default the foreign_sortby is set to *sorting*. 
```php
#[DatabaseField(sql: 'int')]
#[TcaField(
    label: 'Sub models',
    type: TcaFieldTypeEnum::INLINE,
    targetClass: TestSubModel::class
)]
protected ObjectStorage $subModels;
```


<br />
There are predefined types for referencing FAL files (TcaFieldTypeEnum::FILE) , images (TcaFieldTypeEnum::FILE_IMAGE) or media (TcaFieldTypeEnum::FILE_MEDIA).

Example for referencing only images through FAL:

```php
/**
 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
 */
#[DatabaseField(sql: 'int')]
#[TcaField(
    label: 'My images',
    type: TcaFieldTypeEnum::FILE_IMAGE
)]
protected ObjectStorage $images;
```

<br />

### Custom content elements
Loader: ContentElementLoader\
\
Custom content elements have to created in your extension in the directory **Classes\Domain\Model\Content**.
For each custom content element create a new PHP class with the PHP Attribute **ContentElement** and set the property *name*.\
The class has to extend *TYPO3\CMS\Extbase\DomainObject\AbstractEntity*.

Content element fields are defined using properties in your class. You can reuse already existent fields.\
For custom fields add those as property and add the PHP attributes **DatabaseField** (used for field generation in SQL)
and **TcaField** for defining the configuration of the field.

For existent fields the TCA configuration can be overloaded by using the PHP attribute **TcaField**.

If no label has been defined in **TcaField** and the property has the PHP attribute **DatabaseField** the translation key 
*LLL:EXT:<MY_EXTENSION>/Resources/Private/Language/locallang.xlf:tt_content.<field_name>* is used.


> **IMPORTANT:**
> 
> Add getter methods for all properties in order to access those properties in the frontend template!


<br />

In order to load the TCA configuration of custom content elements add the following code to **<MY_EXTENSION>\Configuration\TCA\Overrides\tt_content.php**:
```php
\OrangeHive\Simplyment\Loader::tcaTtContentOverrides('MyVendorName', 'my_extension_key');
```
> **Note:**
> 
> This file is automatically generated by Simplyment on cache clear if not already existent.

<br />

Additionally add the following code to **<MY_EXTENSION>\Configuration\Extbase\Persistence\Classes.php** to map the custom content element to the table tt_content automatically:
```php
$mapping = \OrangeHive\Simplyment\Loader::classes('##VENDOR_NAME##', '##EXTENSION_KEY##');

$custom = [];

return array_merge($mapping, $custom);
```
> **Note:** 
> 
> This file is automatically generated by Simplyment on cache clear if not already existent.

<br />


Example of custom content element PHP class:
```php
<?php

namespace MyVendor\MyExtension\Domain\Model\Content;

use OrangeHive\Simplyment\Attributes\ContentElement;
use OrangeHive\Simplyment\Attributes\DatabaseField;
use OrangeHive\Simplyment\Attributes\TcaField;
use OrangeHive\Simplyment\Enumeration\TcaFieldTypeEnum;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

#[ContentElement(
    name: 'Teaser'
)]
class Teaser extends AbstractEntity
{

    protected string $header = ''; // use already existent header field

    #[TcaField(
        type: TcaFieldTypeEnum::TEXT,
        config: [
            'enableRichtext' => true,
        ]
    )]
    protected string $bodytext = ''; // use already existent bodytext field and define it as type='text' with richtext enabled

    #[TcaField(
        type: TcaFieldTypeEnum::TEXT,
        config: [
            'enableRichtext' => true,
        ]
    )]
    #[DatabaseField(type: 'mediumtext')]
    protected string $txAdditionalText = ''; // create custom field tx_additional_text and define it as type='text' with richtext enabled

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    /**
     * @return string
     */
    public function getTxAdditionalText(): string
    {
        return $this->txAdditionalText;
    }

}
```

The template files for the custom content element are located in **EXT:<MY_EXTENSION>/Resources/Privat/Templates/Content/**.
The frontend template has the name of the PHP class and the backend template the name of the PHP class with the suffix *Backend*.\
Both files are created with dummy content automatically by Simplyment on cache clear if not already existent.

<br />

#### Adding FlexForm to your custom content element
FlexForms can be easily added to your custom content element using the **flexFormPath** property in the **ContentElement** PHP attribute.
The value of this property has to be a string starting with *EXT:* and defining the path to your FlexForm XML file.
If no FlexForm has been defined Simplyment tries to find a FlexForm file in the location *EXT:my_extension/Configuration/FlexForms/Content/MyContentElementName.xml* and adds this automatically.

The FlexForm wil be added automatically at the end of your columns. You can specify the position in your Model by defining the property *pi_flexform* in the following way:
```php
#[TcaField(
    type: TcaFieldTypeEnum::FLEX
)]
protected string $piFlexform = '';
```

For retrieving the content of the FlexForm in your Fluid template add a getter in your content element model with the following code: 
```php
public function getPiFlexform(): array
{
    return \OrangeHive\Simplyment\Utility\FlexFormUtility::xml2array((string)$this->piFlexform);
}
```

For using this functionality add the following code to the file *TCA/Overrides/tt_content.php* in your extension:
```php
\OrangeHive\Simplyment\Loader::tcaTtContentOverrides('MyVendorName', 'my_extension_key');
```

<br />


### Hooks
Loader: HookLoader\
\
Hooks can be registered by using the PHP Attribute **Hook**.
Define the *identifier* and *key* on which your hook class or method should be binded.\
Example:
```php
namespace OrangeHive\Simplyment\Hook;

use OrangeHive\Simplyment\Attributes\Hook;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface;

#[Hook(identifier: 'TYPO3_CONF_VARS/SC_OPTIONS/BackendLayoutDataProvider', key: 'simplyment')]
class BackendLayoutDataProvider implements DataProviderInterface
{
 // not relevant
}
```
will result in the following native Hook in the *ext_localconf.php* of your extension:
```php
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['simplyment'] = 'OrangeHive\Simplyment\Hook\BackendLayoutDataProvider';
```

> **Note:**
> 
> If the *key* attribute has not been set the current timestamp will be used as key.