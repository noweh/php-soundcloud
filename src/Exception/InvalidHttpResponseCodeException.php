<?php

namespace Noweh\SoundcloudApi\Exception;

use \Exception;

/**
 * Soundcloud invalid HTTP response code exception.
 *
 * @author Julien SCHMITT <jschmitt95@protonmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link http://github.com/noweh/php-soundcloud
 */
class InvalidHttpResponseCodeException extends Exception
{
    /**
     * HTTP response body.
     *
     * @access protected
     *
     * @var string
     */
    protected $httpBody;

    /**
     * HTTP response code.
     *
     * @access protected
     *
     * @var integer
     */
    protected $httpCode;

    /**
     * Default message.
     *
     * @access protected
     *
     * @var string
     */
    protected $message = 'The requested URL responded with HTTP code %d.';

    /**
     * Constructor.
     *
     * @param string $message
     * @param int $code
     * @param string $httpBody
     * @param integer $httpCode
     *
     * @return void
     */
    public function __construct(string $message = '', int $code = 0, string $httpBody = '', int $httpCode = 0)
    {
        $this->httpBody = $httpBody;
        $this->httpCode = $httpCode;
        if (!$message) {
            $message = sprintf($this->message, $httpCode);
        }

        parent::__construct($message, $code);
    }

    /**
     * Get HTTP response body.
     *
     * @return mixed
     */
    public function getHttpBody()
    {
        return $this->httpBody;
    }

    /**
     * Get HTTP response code.
     *
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

}