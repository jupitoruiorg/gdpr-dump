<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use Faker\Factory;
use Faker\Provider\Base;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class FakerColumnTransformer extends ColumnTransformer
{

    private static $generator;

    // These are kept for backward compatibility
    private static $formatterTansformerMap = [
        'longText'           => 'paragraph',
        'number'             => 'randomNumber',
        'randomText'         => 'sentence',
        'text'               => 'sentence',
        'uri'                => 'url',
        'member_number'      => [
            'numerify' => '#########'
        ],
        'social_number'      => [
            'numerify' => '###-##-####'
        ],
        'phone_format_union' => [
            'numerify' => '(###) ###-#### x###'
        ],
        'license'            => [
            'numerify' => '############'
        ],
    ];


    protected function getSupportedFormatters()
    {
        return array_keys(self::$formatterTansformerMap);
    }


    public function __construct()
    {
        if ( ! isset(self::$generator)) {
            self::$generator = Factory::create();

            foreach (self::$generator->getProviders() as $provider) {
                $clazz   = new \ReflectionClass($provider);
                $methods = $clazz->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $m) {
                    if (strpos($m->name, '__') === 0) {
                        continue;
                    }
                    self::$formatterTansformerMap[$m->name] = $m->name;
                }
            }
        }
    }


    public function getValue($expression)
    {
        $formatter = self::$formatterTansformerMap[$expression['formatter']];

        if (is_array($formatter)) {
            $key   = key($formatter);
            $value = $formatter[$key];

            return self::$generator->{$key}($value);
        }

        return self::$generator->format($formatter);
    }
}
