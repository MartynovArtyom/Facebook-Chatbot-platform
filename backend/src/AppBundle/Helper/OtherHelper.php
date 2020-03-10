<?php
/**
 * Created by PhpStorm.
 * Date: 25.07.18
 * Time: 16:15
 */

namespace AppBundle\Helper;


use Symfony\Component\Filesystem\Filesystem;

class OtherHelper
{
    /**
     * @param $image
     * @param $path
     * @return bool
     */
    public static function saveImage($image, $path){
        try{
            if(!empty($image) && !empty($path)){
                $fileSystem = new Filesystem();
                $fileSystem->copy($image, $path);

                return true;
            }

            return false;
        }
        catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    public static function getFeed($url){
        $process = curl_init($url);
        $headers = [
            'Content-Type: text/xml',
        ];
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($process);
        $info = curl_getinfo($process);
        curl_close($process);
        if($info['http_code'] === 200) {
            $headers = explode('; ', $info['content_type']);
            if(in_array('text/xml', $headers) || in_array('application/rss+xml', $headers)){
                return $result;
            }
        }

        return false;
    }
}