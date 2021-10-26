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
        'development' => 'sandbox-soundcloud.com',
        'production' => 'soundcloud.com'
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
     * @return string
     */
    public function getAuthorizeUrl(): string
    {
        $params = [
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUri,
            'response_type' => 'code'
        ];

        return $this->buildUrl('connect', $params);
    }

    /**
     * Send a GET HTTP request.
     *
     * @param string $path Request path
     * @param array $params Optional query string parameters
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     */
    public function get(string $path, array $params = [], array $curlOptions = [])
    {
        $url = $this->buildUrl($path, $params);

        return $this->performRequest($url, $curlOptions);
    }

    /**
     * Send a POST HTTP request.
     *
     * @param string $path Request path
     * @param array $postData Optional post data
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     */
    public function post(string $path, array $postData = [], array $curlOptions = [])
    {
        $url = $this->buildUrl($path);
        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
        ];
        $options = array_replace($options, $curlOptions);

        return $this->performRequest($url, $options);
    }

    /**
     * Send a PUT HTTP request.
     *
     * @param string $path Request path
     * @param array $postData Optional post data
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     */
    public function put(string $path, array $postData, array $curlOptions = [])
    {
        $url = $this->buildUrl($path);
        $options = [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $postData
        ];
        $options = array_replace($options, $curlOptions);

        return $this->performRequest($url, $options);
    }

    /**
     * Send a DELETE HTTP request.
     *
     * @param string $path Request path
     * @param array $params Optional query string parameters
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws InvalidHttpResponseCodeException
     * @throws \JsonException
     */
    public function delete(string $path, array $params = [], array $curlOptions = [])
    {
        $url = $this->buildUrl($path, $params);
        $options = [CURLOPT_CUSTOMREQUEST => 'DELETE'];
        $options = array_replace($options, $curlOptions);

        return $this->performRequest($url, $options);
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
            ]
        );

        return $soundcloudResponse->html ?? null;
    }

    private function getAccessToken(): void
    {

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
        if (!$this->_accessToken) {
            $params['consumer_key'] = $this->_clientId;
        }

        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
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
     *
     * @access protected
     */
    protected function performRequest(string $url, array $curlOptions = [])
    {
        $ch = curl_init($url);
        $options = array_replace([CURLOPT_RETURNTRANSFER => true], $curlOptions);

        $options[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: OAuth ' . $this->getAccessToken()
        ];

        curl_setopt_array($ch, $options);

        $data = json_decode(curl_exec($ch), false, 512, JSON_THROW_ON_ERROR);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($info['http_code'] >= 400) {
            throw new InvalidHttpResponseCodeException(
                null, 0, $data, $info['http_code']
            );
        }

        return $data;
    }
}