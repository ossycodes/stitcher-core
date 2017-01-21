<?php

namespace brendt\stitcher\parser;

use brendt\image\ResponsiveFactory;
use brendt\stitcher\Config;

/**
 * The ImageParser uses the ResponsiveFactory to create images from an entry (array of parsed data),
 * or a path to an image.
 *
 * @see \brendt\image\ResponsiveFactory::create()
 */
class ImageParser implements Parser {

    /**
     * @var ResponsiveFactory
     */
    protected $factory;

    /**
     * AbstractParser constructor.
     */
    public function __construct() {
        $this->factory = Config::getDependency('factory.image');
    }

    /**
     * @param $entry
     *
     * @return array
     */
    public function parse($entry) {
        $data = [];
        $defaults = [];
        $path = null;

        if (is_array($entry)) {
            if (array_key_exists('src', $entry)) {
                $path = $entry['src'];
                unset($entry['src']);
            }

            foreach ($entry as $field => $value) {
                $defaults[$field] = $value;
            }
        } else {
            $path = $entry;
        }

        if (!$path) {
            return $data;
        }

        $image = $this->factory->create($path);

        $data = [
                'src'    => $image->src(),
                'srcset' => $image->srcset(),
                'sizes'  => $image->sizes(),
            ] + $defaults;

        return $data;
    }

}