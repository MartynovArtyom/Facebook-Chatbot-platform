<?php

namespace AppBundle\Helper;

/**
 * Class OpenGraph
 * @package AppBundle\Helper
 */
class OpenGraph {

    /**
     * @param $url
     * @return array
     */
    public static function fetch($url){
        $content = file_get_contents($url);

        $parseResult = self::parse($content);
        $result = [];
        if(isset($parseResult['og']) && !empty($parseResult['og'])){
            $result = [
                'title' => (isset($parseResult['og']['og:title'])) ? $parseResult['og']['og:title'] : '',
                'url' => (isset($parseResult['og']['og:url'])) ? $parseResult['og']['og:url'] : '',
                'image' => (isset($parseResult['og']['og:image'])) ? $parseResult['og']['og:image'] : '',
                'description' => (isset($parseResult['og']['og:description'])) ? $parseResult['og']['og:description'] : ''
            ];
        }
        return $result;
    }

    /**
     * Parse content into an array.
     * @param $content html The HTML
     * @return array
     */
    public static function parse($content) {
        $doc = new \DOMDocument();

        // Fudge to handle a situation when an encoding isn't present
        if (strpos($content, 'xml encoding=')===false)
            $content = '<?xml encoding="utf-8" ?>' . $content;

        @$doc->loadHTML($content);
        $interested_in = ['og', 'fb', 'twitter']; // Open graph namespaces we're interested in (open graph + extensions)
        $ogp = [];
        // Open graph
        $metas = $doc->getElementsByTagName('meta');
        if (!empty($metas)) {
            for ($n = 0; $n < $metas->length; $n++) {
                $meta = $metas->item($n);
                foreach (array('name', 'property') as $name) {
                    $meta_bits = explode(':', $meta->getAttribute($name));
                    if (in_array($meta_bits[0], $interested_in)) {
                        // If we're adding to an existing element, convert it to an array
                        if (isset($ogp[$meta->getAttribute($name)]) && (!is_array($ogp[$meta->getAttribute($name)])))
                            $ogp[$meta_bits[0]][$meta->getAttribute($name)] = array($ogp[$meta->getAttribute($name)], $meta->getAttribute('content'));
                        else if (isset($ogp[$meta->getAttribute($name)]) && (is_array($ogp[$meta->getAttribute($name)])))
                            $ogp[$meta_bits[0]][$meta->getAttribute($name)][] = $meta->getAttribute('content');
                        else
                            $ogp[$meta_bits[0]][$meta->getAttribute($name)] = $meta->getAttribute('content');
                    }
                }
            }
        }
        // OEmbed
        $metas = $doc->getElementsByTagName('link');
        if (!empty($metas)) {
            for ($n = 0; $n < $metas->length; $n++) {
                $meta = $metas->item($n);

                if (strtolower($meta->getAttribute('rel')) == 'alternate') {

                    if (in_array(strtolower($meta->getAttribute('type')), ['application/json+oembed'])) {
                        $ogp['oembed']['jsonp'][] = $meta->getAttribute('href');
                    }
                    if (in_array(strtolower($meta->getAttribute('type')), ['text/json+oembed'])) {
                        $ogp['oembed']['json'][] = $meta->getAttribute('href');
                    }
                    if (in_array(strtolower($meta->getAttribute('type')), ['text/xml+oembed'])) {
                        $ogp['oembed']['xml'][] = $meta->getAttribute('href');
                    }
                }
            }
        }
        // Basics
        foreach (['title'] as $basic) {
            if (preg_match("#<$basic>(.*?)</$basic>#siu", $content, $matches))
                $ogp[$basic] = trim($matches[1], " \n");
        }
        $metas = $doc->getElementsByTagName('meta');
        if (!empty($metas)) {
            for ($n = 0; $n < $metas->length; $n++) {
                $meta = $metas->item($n);

                if (strtolower($meta->getAttribute('name')) == 'description') {
                    $ogp['description'] = $meta->getAttribute('content');
                }
                if (strtolower($meta->getAttribute('name')) == 'keywords') {
                    $ogp['keywords'] = $meta->getAttribute('content');
                }
            }
        }
        return $ogp;
    }
}