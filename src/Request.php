<?php

namespace Rig\HTTP;

class Request
{
    protected $parameters;
    protected $server;
    protected $files;
    protected $cookies;
    protected $uri;
    protected $input;

    public function __construct(array $get = null, array $post = null, array $cookies = null, array $files = null, array $server = null, $phpInput = null)
    {
        $this->input = $phpInput;
        $this->parameters = array_merge($get, $post);
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;

        $this->uri = new URL($this->urlOrigin() . $this->server['REQUEST_URI']);
        if ($this->getMethod() == 'PUT') {
            $postVars = null;
            parse_str(file_get_contents("php://input"), $postVars);
            $this->parameters = array_merge($this->parameters, $postVars);
        }
        $this->prepareInput();
        $this->prepareMultipleFiles();
    }

    public static function createFromGlobals()
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents("php://input"));
    }

    public function getParameter($key, $defaultValue = null)
    {
        if ($this->hasParameter($key)) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    public function getAllParameters()
    {
        return $this->parameters;
    }

    public function setParameter($key, $value)
    {
        return $this->parameters[$key] = $value;
    }

    public function hasParameter($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    public function isEmpty($key)
    {
        if ($this->has($key)) {
            $keyContents = $this->get($key);
            return empty($keyContents);
        }

        return true;
    }

    public function has($key)
    {
        return $this->hasParameter($key);
    }

    public function hasOneOf(array $keys)
    {
        foreach ($keys as $key) {
            if ($this->hasParameter($key)) {
                return $key;
            }
        }
        return false;
    }

    public function get($key, $defaultValue = null)
    {
        return $this->getParameter($key, $defaultValue);
    }

    public function set($key, $value)
    {
        return $this->setParameter($key, $value);
    }

    // http://stackoverflow.com/a/8891890
    protected function urlOrigin($useForwardedHost = false)
    {
        $ssl = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] == 'on');
        $serverProtocol = strtolower($this->server['SERVER_PROTOCOL']);
        $protocol = substr($serverProtocol, 0, strpos($serverProtocol, '/')) . (($ssl) ? 's' : '');
        $port = $this->server['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = ($useForwardedHost && isset($this->server['HTTP_X_FORWARDED_HOST'])) ? $this->server['HTTP_X_FORWARDED_HOST'] : (isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $this->server['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    /* ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  */

    public function getFile($key, $defaultValue = null)
    {
        if (!array_key_exists($key, $this->files)) {
            return $defaultValue;
        }


        if (array_key_exists('size', $this->files[$key]) && $this->files[$key]['size'] == 0) {
            return $defaultValue;
        }

        return $this->files[$key];
    }

    public function getCookie($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->cookies)) {
            return $this->cookies[$key];
        }

        return $defaultValue;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getMethod()
    {
        return $this->getServerVariable('REQUEST_METHOD');
    }

    public function getHttpAccept()
    {
        return $this->getServerVariable('HTTP_ACCEPT');
    }

    public function getReferer()
    {
        return $this->getServerVariable('HTTP_REFERER');
    }

    public function getUserAgent()
    {
        return $this->getServerVariable('HTTP_USER_AGENT');
    }

    public function getIpAddress()
    {
        return $this->getServerVariable('REMOTE_ADDR');
    }

    public function isSecure()
    {
        return (array_key_exists('HTTPS', $this->server) && $this->server['HTTPS'] !== 'off');
    }

    public function getBody()
    {
        return file_get_contents('php://input');
    }

    public function getServerVariable($key)
    {
        if (!array_key_exists($key, $this->server)) {
            return null;
        }
        return $this->server[$key];
    }

    public function isXhr()
    {


        $hxrw = $this->getServerVariable('HTTP_X_REQUESTED_WITH');

        return ($hxrw and strtolower($hxrw) == 'xmlhttprequest');
    }

    private function prepareMultipleFiles()
    {
        if (is_array($this->files) && !empty($this->files)) {
            foreach ($this->files as &$file) {
                if (!is_array($file['tmp_name'])) {
                    continue;
                }

                $files = array();

                foreach ($file as $param => $values) {
                    foreach ($values as $key => $value) {
                        $files[$key][$param] = $value;
                    }
                }

                $file = $files;
            }
        }
    }

    private function prepareInput()
    {
        if (!empty($this->input)) {
            $data = json_decode($this->input);

            if (!is_null($data)) {
                $this->parameters = array_merge($this->parameters, (array)$data);
            } else {
                $params = array();
                parse_str($this->input, $params);
                $this->parameters = array_merge($this->parameters, $params);
            }
        }
    }

}
