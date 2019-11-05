Yii2 CountrySelect widget
============================
This widget extends [Kartik's Select2 widget](https://github.com/kartik-v/yii2-widget-select2). It uses country codes and names as data and renders list in dropdown.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist xtomdex/yii2-widget-country "*"
```

or add

```
"xtomdex/yii2-widget-country": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \xtomdex\widgets\CountrySelect::widget([
    'preferredCountries' => ['UA', 'RU', 'BY'], //these countries will be at the top
    'enableIpCountryFirst' => true, //sets user IP country to the top (preferred countries go next), by default is true
    'displayFlags' => true, //displays country flag images next to their names, by default is true
    /*
     * Select2 config
     */
]); ?>

```