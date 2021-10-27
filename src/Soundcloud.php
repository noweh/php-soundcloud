<?php

namespace Noweh\SoundcloudApi;

use Noweh\SoundcloudApi\Exception\InvalidArgumentException;
use Noweh\SoundcloudApi\Exception\InvalidHttpResponseCodeException;

class Soundcloud
{
    /**
     * OAuth client id
     *
     * @var string
     *
     * @access private
     */
    private $_clientId;

    /**
     * OAuth client secret
     *
     * @var string
     *
     * @access private
     */
    private $_clientSecret;

    /**
     * OAuth redirect URI
     *
     * @var string
     *
     * @access private
     */
    private $_redirectUri;

    /**
     * Development mode
     *
     * @var boolean
     *
     * @access private
     */
    private $_development;

    /**
     * Access code
     *
     * @var boolean
     *
     * @access private
     */
    private $_code;

    /**
     * Access token returned by the service provider after a successful authentication
     *
     * @var string
     *
     * @access private
     */
    private $_accessToken;

    /**
     * Available API domains
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_domains = array(
        'development' => 'api.sandbox-soundcloud.com',
        'production' => 'api.soundcloud.com'
    );

    /**
     * Class constructor.
     *
     * @param string  $clientId     OAuth client id
     * @param string  $clientSecret OAuth client secret
     * @param string  $redirectUri  OAuth redirect URI
     * @param boolean $development  Sandbox mode
     *
     * @return void
     * @throws InvalidArgumentException
     *
     * @access public
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri = '', bool $development = false)
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new InvalidArgumentException('clientId and clientSecret must be set');
        }

        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
        $this->_development = $development;
    }

    /**
     * Get authorization URL.
     *
     * @param string $state Any value included here will be appended to te redirect URI
     *
     * @return string
     */
    public function getAuthorizeUrl(string $state): string
    {
        $params = [
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUri,
            'response_type' => 'code',
            'state' => $state
        ];

        return $this->buildUrl('connect', $params);
    }

    /**
     * Send a GET HTTP request.
     *
     * @param string $path Request path
     * @param array $params Optional query string parameters
     * @param array $curlOptions Optional cURL options
     * @param bool $needAccessToken Optional add/remove access token in header
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    public function get(string $path, array $params = [], array $curlOptions = [], bool $needAccessToken = true)
    {
        $url = $this->buildUrl($path, $params);

        return $this->performRequest($url, $curlOptions, $needAccessToken);
    }

    /**
     * Send a POST HTTP request.
     *
     * @param string $path Request path
     * @param array $postData Optional post data
     * @param array $curlOptions Optional cURL options
     * @param bool $needAccessToken Optional add/remove access token in header
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    public function post(string $path, array $postData = [], array $curlOptions = [], bool $needAccessToken = true)
    {
        $url = $this->buildUrl($path);
        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
        ];
        $options = array_replace($options, $curlOptions);

        return $this->performRequest($url, $options, $needAccessToken);
    }

    /**
     * Send a PUT HTTP request.
     *
     * @param string $path Request path
     * @param array $postData Optional post data
     * @param array $curlOptions Optional cURL options
     * @param bool $needAccessToken Optional add/remove access token in header
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    public function put(string $path, array $postData, array $curlOptions = [], bool $needAccessToken = true)
    {
        $url = $this->buildUrl($path);
        $options = [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $postData
        ];
        $options = array_replace($options, $curlOptions);

        return $this->performRequest($url, $options, $needAccessToken);
    }

    /**
     * Send a DELETE HTTP request.
     *
     * @param string $path Request path
     * @param array $params Optional query string parameters
     * @param array $curlOptions Optional cURL options
     * @param bool $needAccessToken Optional add/remove access token in header
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    public function delete(string $path, array $params = [], array $curlOptions = [], bool $needAccessToken = true)
    {
        $url = $this->buildUrl($path, $params);
        $options = [CURLOPT_CUSTOMREQUEST => 'DELETE'];
        $options = array_replace($options, $curlOptions);

        return $this->performRequest($url, $options, $needAccessToken);
    }

    /**
     * Serve the widget embed code for any SoundCloud URL pointing to a user, set, or a playlist.
     *
     * @param string $url
     * @param int $maxheight
     * @param bool $sharing
     * @param bool $liking
     * @param false $download
     * @param bool $show_comments
     * @param false $show_playcount
     * @param false $show_user
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     */
    public function getPlayerEmbed(
        string $url,
        int $maxheight = 180,
        bool $sharing = true,
        bool $liking = true,
        bool $download = false,
        bool $show_comments = true,
        bool $show_playcount = false,
        bool $show_user = false
    ) {
        $soundcloudResponse = $this->get('https://soundcloud.com/oembed',
            [
                'url' => $url,
                'maxheight' => $maxheight,
                'sharing' => $sharing,
                'liking' => $liking,
                'download' => $download,
                'show_comments' => $show_comments,
                'show_playcount' => $show_playcount,
                'show_user' => $show_user
            ],
            [
                CURLOPT_FOLLOWLOCATION => true
            ],
            false
        );

        return $soundcloudResponse->html ?? null;
    }

    public function setCode(string $code): void
    {
        $this->_code = $code;
    }

    /**
     * Set Access Token
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->_accessToken = $accessToken;
    }

    /**
     * Get Access Token
     * @return string
     * @throws InvalidArgumentException
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     */
    private function getAccessToken(): string
    {
        if (!$this->_accessToken) {
            // Try to auto retrieve the access token with code in instance or in the URL
            if (!$this->_code) {
                $this->_code = $_GET['code'];
            } else {
                throw new InvalidArgumentException('accessToken must be set or accessible in URL parameters');
            }

            $soundCloudResponse = $this->post(
                $this->buildUrl('oauth2/token'),
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->_clientId,
                    'client_secret' => $this->_clientSecret,
                    'code' => $this->_code,
                    'redirect_uri' => $this->_redirectUri,
                ],
                [],
                false
            );

            if ($soundCloudResponse && $soundCloudResponse->access_token) {
                $this->_accessToken = $soundCloudResponse->access_token;
            }
        }

        return $this->_accessToken;
    }

    /**
     * Construct a URL
     *
     * @param string  $path           Relative or absolute URI
     * @param array   $params         Optional query string parameters
     *
     * @return string $url
     *
     * @access protected
     */
    protected function buildUrl(string $path, array $params = []): string
    {
        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
            if ($path[0] === '/') {
                $path = substr($path, 1);
            }

            $url = 'https://';
            $url .= ($this->_development) ? self::$_domains['development'] : self::$_domains['production'];
            $url .= '/';
            $url .= $path;
        }

        $url .= (count($params)) ? '?' . http_build_query($params) : '';

        return $url;
    }

    /**
     * Performs the actual HTTP request using cURL
     *
     * @param string $url Absolute URL to request
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     * @throws InvalidArgumentException
     *
     * @access protected
     */
    protected function performRequest(string $url, array $curlOptions = [], $needAccessToken = true)
    {
        $ch = curl_init($url);
        $options = array_replace([CURLOPT_RETURNTRANSFER => true], $curlOptions);

        $options[CURLOPT_HTTPHEADER] = [];
        $options[CURLOPT_HTTPHEADER][] = array_key_exists(CURLOPT_POSTFIELDS, $options)
            ? 'Content-Type: multipart/form-data' : 'Content-Type: application/json';
        $options[CURLOPT_HTTPHEADER][] = 'Accept: application/json';

        if ($needAccessToken) {
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: OAuth ' . $this->getAccessToken();
        }

        curl_setopt_array($ch, $options);

        // to remove
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($info['http_code'] >= 400) {
            throw new InvalidHttpResponseCodeException(
                '', 0, $data, $info['http_code']
            );
        }

        return json_decode($data, false, 512, JSON_THROW_ON_ERROR);
    }
}