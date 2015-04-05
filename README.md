Yii2 Taxonomy
=============
Yii2 Taxonomy management. A component which adds generic taxonomy functionalities to your application. The component
comes with a couple of term definitions(tags, properties). These additional info is added via addition tables created
by the extension. The extension also offers a search behavior which can be attached to AR instances for easier searching.
 

* THIS COMPONENT IS NOT READY FOR PRODUCTION YET

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist nkostadinov/yii2-taxonomy "*"
```

or add

```
"nkostadinov/yii2-taxonomy": "*"
```

to the require section of your `composer.json` file.

Then you need to configure the taxonomy component to your configuration file.

```
    'components' => [
        .......
        'taxonomy' => [
            'class' => 'nkostadinov\taxonomy\Taxonomy',
        ],
        .......
```        

Usage
-----

If you need to use the management interface for taxonomies you must add the Taxonomy module to you configuration

```    
    'modules' => [
        ......
        'taxonomy' => [
            'class' => 'nkostadinov\taxonomy\Module'
        ],
```        

## Taxonomies
The bundled taxonomies with these package are :

### TagTerm
Basically tag