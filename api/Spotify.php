<?php
/**
 * Created by PhpStorm.
 * User: tborn
 * Date: 1/19/2020
 * Time: 1:18 PM
 */

namespace api;

class Spotify
{
    private static $client_id = '07ed1ef560154ef3963bbfff1178b4b5';
    private static $client_secret = 'eb996d44a16649a8b762ad2a7b724de9';
    private static $instance = false;

    private $code = false;
    private $access_token = false;
    private $refresh_token = false;

    /**
     * Get the active Spotify class instance.
     * If there is none, create one.
     * @return Spotify|bool
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            $spotify = new self();
            $spotify->authorize();

            self::$instance = $spotify;
        }

        return self::$instance;
    }

    /**
     * Set the fetch code Spotify needs to get the access code.
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get the url used for giving the api access to the users Spotify.
     * @return string
     */
    public static function getConnectUrl()
    {
        return 'https://accounts.spotify.com/authorize?client_id=' .
            self::$client_id . '&response_type=code&redirect_uri=' .
            urlencode(SITE_URL) .
            '&scope=user-top-read';
    }

    /**
     * Get and refresh the access token needed to communicate with the Spotify api.
     * @return bool
     */
    public function authorize()
    {
        if (isset($_SESSION['access_token']) && isset($_SESSION['refresh_token'])) {
            $this->refresh_token = $_SESSION['refresh_token'];

            if (!$this->refreshTokens()) {
                $_SESSION['access_token'] = false;
                $_SESSION['refresh_token'] = false;

                return false;
            }

            return true;
        } elseif ($this->code) {
            if ($result = SpotifyCurl::post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'authorization_code',
                'code' => $this->code,
                'redirect_uri' => SITE_URL
            ], [
                'Authorization: Basic ' . base64_encode(self::$client_id . ':' . self::$client_secret),
                'Content-type: application/x-www-form-urlencoded'
            ])) {
                $_SESSION['access_token'] = $result->access_token;
                $_SESSION['refresh_token'] = $result->refresh_token;

                $this->access_token = $result->access_token;
                $this->refresh_token = $result->refresh_token;

                return true;
            }
        }

        return false;
    }

    /**
     * Get the user data from Spotify.
     * DOCS: https://developer.spotify.com/documentation/web-api/reference/users-profile/get-current-users-profile/
     * @return bool|\stdClass
     */
    public function getUserInfo()
    {
        return SpotifyCurl::get('https://api.spotify.com/v1/me');
    }

    /**
     * Get the users top tracks from Spotify.
     * DOCS: https://developer.spotify.com/documentation/web-api/reference/personalization/get-users-top-artists-and-tracks/
     * @return bool|\stdClass
     */
    public function getUserTopTracks()
    {
        return SpotifyCurl::get('https://api.spotify.com/v1/me/top/tracks');
    }

    /**
     * Get the users top artists from Spotify.
     * DOCS: https://developer.spotify.com/documentation/web-api/reference/personalization/get-users-top-artists-and-tracks/
     * @return bool|\stdClass
     */
    public function getUserTopArtists()
    {
        return SpotifyCurl::get('https://api.spotify.com/v1/me/top/artists');
    }

    /**
     * Get the NL Top 40 playlist from Spotify.
     * @return false|\stdClass
     */
    public function getTop40()
    {
        return $this->getPlaylist('5lH9NjOeJvctAO92ZrKQNB');
    }

    /**
     * Get a specific playlist from Spotify by it's ID.
     * @param $playlist
     * @return bool|\stdClass
     */
    public function getPlaylist($playlist)
    {
        return SpotifyCurl::get('https://api.spotify.com/v1/playlists/' . $playlist);
    }

    /**
     * Get the access_token required for calls to the Spotify api.
     * @return bool
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Refresh the access_token to keep it from expiring.
     * @return bool
     */
    public function refreshTokens()
    {
        if ($this->refresh_token) {
            if ($result = SpotifyCurl::post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token
            ], [
                'Authorization: Basic ' . base64_encode(self::$client_id . ':' . self::$client_secret),
                'Content-type: application/x-www-form-urlencoded'
            ])) {
                $_SESSION['access_token'] = $result->access_token;

                $this->access_token = $result->access_token;

                return true;
            }
        }

        return false;
    }
}