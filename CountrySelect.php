<?php

namespace xtomdex\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\web\View;
use xtomdex\countryflags\CountryFlag;

/**
 * CountrySelect widget extends the [[Select2]] widget and uses countries list as data.
 *
 * @author Dmitrii Sharonov <sharonovde2@gmail.com>
 */
class CountrySelect extends Select2
{
    /**
     * Countries which should be at the top of the list.
     *
     * @var array
     */
    public $preferredCountries;
    /**
     * If it is true the widget will display user IP country at the top of the list.
     *
     * @var bool
     */
    public $enableIpCountryFirst = true;
    /**
     * If it is true the widget will display country flag images.
     *
     * @var bool
     */
    public $displayFlags = true;
    /**
     * User IP country ISO code.
     *
     * @var string
     */
    protected $ipCountry;

    /**
     * @inheritdoc
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->setupIpCountry();
        $this->setupFlagsRendering();
        $this->options['placeholder'] = $this->options['placeholder'] ?: 'Choose country';
        $this->data = $this->getDropdownData();

        parent::init();
    }

    /**
     * Returns country name by its code.
     *
     * @param $code
     * @return mixed
     */
    public static function getName($code)
    {
        $language = Yii::$app->language;
        $fileName = __DIR__ . '/data/' . strtolower($language) . '.json';
        if (!file_exists($fileName))
            $fileName = __DIR__ . '/data/en.json';

        $countries = json_decode(file_get_contents($fileName), 1);
        return $countries[$code];
    }

    /**
     * Returns normalized array for dropdown mapped from country code to its name.
     *
     * @return array
     */
    protected function getDropdownData()
    {
        $rawData = $this->getRawData();
        $priorityCountries = $this->getPreferredList();
        $finalList = [];

        if ($priorityCountries) {
            foreach ($priorityCountries as $priorityCountry){
                $finalList[$priorityCountry] = ArrayHelper::remove($rawData, $priorityCountry);
            }
        }

        foreach ($rawData as $code => $name){
            $finalList[$code] = $name;
            ArrayHelper::remove($rawData, $code);
        }

        return $finalList;
    }

    /**
     * Finds countries data by language from file.
     *
     * Returns countries array mapped from its code to name.
     *
     * @return array
     */
    protected function getRawData()
    {
        $language = $this->processLanguage();
        $fileName = __DIR__ . '/data/' . $language . '.json';

        if (!file_exists($fileName))
            $fileName = __DIR__ . '/data/en.json';

        $file = file_get_contents($fileName);

        return json_decode($file, 1);
    }

    /**
     * Returns preferred countries list.
     *
     * If IP country first param is enabled IP country will be at the top of the list.
     *
     * @return array
     */
    protected function getPreferredList()
    {
        if ($this->ipCountry) {
            $needle = array_search($this->ipCountry, $this->preferredCountries);
            $arr[] = ArrayHelper::remove($this->preferredCountries, $needle);
            return array_merge($arr, $this->preferredCountries);
        } else
            return $this->preferredCountries;
    }

    /**
     * Sets IP country code if enableIpCountryFirst param is true using GeoIP extension.
     *
     * Throws the exception if the extension class is not found.
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function setupIpCountry()
    {
        if ($this->enableIpCountryFirst) {
            $components = Yii::$app->getComponents();
            if (isset($components['geoip']) && $components['geoip']['class'] === 'lysenkobv\GeoIP\GeoIP')
                $this->ipCountry = Yii::$app->geoip->ip()->isoCode;
            else {
                $geoip = Yii::createObject([
                    'class' => 'lysenkobv\GeoIP\GeoIP'
                ]);
                $this->ipCountry = $geoip->ip()->isoCode;
            }
        }
    }

    /**
     * If displayFlags param is true formats countries list by adding to each element its flag image.
     */
    protected function setupFlagsRendering()
    {
        if ($this->displayFlags) {
            $this->registerFlagsDisplayJs();
            $this->pluginOptions['templateResult'] = new JsExpression('formatCountriesList');
            $this->pluginOptions['templateSelection'] = new JsExpression('formatCountriesList');
            $this->pluginOptions['escapeMarkup'] = new JsExpression('function(m) { return m; }');
        }
    }

    /**
     * Registers JS callback function which formats countries list by adding to each element its flag image.
     */
    protected function registerFlagsDisplayJs()
    {
        $url = CountryFlag::URL;
        $format = <<< SCRIPT
function formatCountriesList(state) {
    if (!state.id) return state.text;
    src = '$url/' +  state.id.toLowerCase() + '/shiny/16.png'
    return '<img class="flag" src="' + src + '" height="16" style="display: inline-block; margin-right: 5px;"/>' + state.text;
}
SCRIPT;

        $this->view->registerJs($format, View::POS_HEAD);
    }

    /**
     * Returns only language if the language consists of two parts ('language-COUNTRY').
     *
     * @return string
     */
    protected function processLanguage()
    {
        $language = $this->language ?: Yii::$app->language;
        $parts = explode('-', $language);
        return $parts[0];
    }
}
