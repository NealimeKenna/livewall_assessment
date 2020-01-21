<?php
/**
 * Created by PhpStorm.
 * User: tborn
 * Date: 1/19/2020
 * Time: 5:09 PM
 */

namespace api;

class SpotifyCurl
{
    private $ch = false;

    /**
     * Start a curl instance with some basic settings for a clal to the Spotify api.
     * SpotifyCurl constructor.
     * @param array $headers
     */
    public function __construct($headers = [])
    {
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false); // SSL Disabled for localhost testing, never put this on production!
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        return $this;
    }

    /**
     * Make a post call to the Spotify api.
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return bool|\stdClass
     */
    public static function post($url, $data = [], $headers = [])
    {
        $spotify_curl = new self($headers);
        $ch = $spotify_curl->ch;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (count($data) > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        return $spotify_curl->exec();
    }

    /**
     * Make a get call to the Spotify api.
     * @param string $url
     * @return bool|\stdClass
     */
    public static function get($url)
    {
        if ($spotify = Spotify::getInstance()) {
            if ($access_token = $spotify->getAccessToken()) {
                $spotify_curl = new self([
                    'Authorization: Bearer ' . $access_token,
                    'Content-type: application/json',
                    'Accept: application/json'
                ]);
                $ch = $spotify_curl->ch;

                curl_setopt($ch, CURLOPT_URL, $url);

                return $spotify_curl->exec();
            }
        }

        return false;
    }

    /**
     * Execute the call to the Spotify api.
     * @return bool|\stdClass
     */
    public function exec()
    {
        if ($server_output = curl_exec($this->ch)) {
            curl_close($this->ch);

            /** @var \stdClass $result */
            $result = json_decode($server_output);

            if (isset($result->error)) {
                //TODO write error handler for Spotify
                return false;
            }

            return $result;
        }

        //TODO write error handler for curl.

        curl_close($this->ch);

        return false;
    }

}