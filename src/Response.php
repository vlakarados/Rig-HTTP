<?php

namespace Rig\HTTP;

class Response
{

    protected $headers = array();
    protected $body = '';

    protected $version = '1.1';
    protected $statusCode = 200;
    protected $statusText = 'OK';

    protected static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );


    public function setStatusCode($statusCode, $statusText = null)
    {
        if ($statusText === null and array_key_exists((int)$statusCode, self::$statusTexts)) {
            $statusText = self::$statusTexts[$statusCode];
        }

        $this->statusCode = (int)$statusCode;
        $this->statusText = (string)$statusText;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }


    public function setBody($content)
    {
        $this->body = $content;
    }

    public function appendToBody($content)
    {
        $this->body .= $content;
    }

    public function getBody()
    {
        return $this->body;
    }


    public function setHeader($key, $value)
    {
        $this->headers[$key] = array($value);
    }

    public function addHeader($key, $value)
    {
        if (array_key_exists($key, $this->headers)) {
            $this->headers[$key][] = $value;
            return;
        }
        $this->setHeader($key, $value);
    }

    public function removeHeader($key)
    {
        unset($this->headers[$key]);
    }

    public function getHeader($key)
    {
        return $this->headers[$key];
    }

    public function getHeaders()
    {
        $headers = array_merge($this->getRequestLineHeaders(), $this->getStandardHeaders()); // , $this->getCookieHeaders());
        return $headers;
    }


    protected function getRequestLineHeaders()
    {
        $headers = array();
        $requestLine = sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText);
        $headers[] = trim($requestLine);

        return $headers;
    }

    private function getStandardHeaders()
    {
        $headers = array();

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                $headers[] = $name . ': ' . $value;
            }
        }

        return $headers;
    }

    // Should be migrated somewhere else
    public function send()
    {
        foreach ($this->getHeaders() as $header) {
            header($header, false);
        }
        echo $this->getBody();
    }

    public function redirect($url, $code = 302)
    {
        $this->addHeader('Location', $url);
        $this->setStatusCode($code);
    }
}
