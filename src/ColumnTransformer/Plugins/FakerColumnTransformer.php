<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Base;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class FakerColumnTransformer extends ColumnTransformer
{
    private static $generator;

    // These are kept for backward compatibility
    private static $formatterTansformerMap = [];

    protected function setSupportedFormats()
    {
        self::$formatterTansformerMap = [
            'longText'           => 'paragraph',
            'number'             => 'randomNumber',
            'randomText'         => 'sentence',
            'text'               => 'sentence',
            'uri'                => 'url',

            'member_number'      => function(Generator $faker) {
                return $faker->numerify('#########');
            },
            'social_number'      => function(Generator $faker) {
                return $faker->numerify('###-##-####');
            },
            'phone_format_union'      => function(Generator $faker) {
                return $faker->numerify('(###) ###-#### x###');
            },
            'license'      => function(Generator $faker) {
                return $faker->numerify('############');
            },
            'password_bcrypt' => function(Generator $faker) {
                return password_hash($faker->password(), PASSWORD_BCRYPT);
            },
            'money_float' => function (Generator $faker) {
                return $faker->randomFloat(2);
            },
        ];
    }


    protected function getSupportedFormatters()
    {
        return array_keys(self::$formatterTansformerMap);
    }


    public function __construct()
    {
        $this->setSupportedFormats();

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

        if (is_string($formatter)) {
            return self::$generator->format($formatter);
        }

        return $formatter(self::$generator);
    }
}
