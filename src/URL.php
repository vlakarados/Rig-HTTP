<?php

namespace Rig\HTTP;

/**
 * Class URL
 * Constructing and working with URL's using their components
 * @package OHTTP
 */
class URL
{
    private $components = array();

    /**
     * @param $url
     */
    public function __construct($url)
    {
        if (!is_array($url)) {
            $url = parse_url($url);
        }
        $this->components = $url;
    }

    /**
     * Returns the URL's component $component value
     * @param $component
     * @return string|null
     */
    public function get($component)
    {
        if (array_key_exists($component, $this->components)) {
            return $this->components[$component];
        }
        return null;
    }

    /**
     * @param $component
     * @param $value
     * @throws \InvalidArgumentException when $component value is an invalid component name
     * @return mixed
     */
    public function set($component, $value)
    {
        if (!array_key_exists($component, $this->components)) {
            throw new \InvalidArgumentException('Trying to set an invalid URL component "'.$component.'"');
        }
        return $this->components[$component] = $value;
    }

    /**
     * @param $key
     * @param $value
     * @throws \InvalidArgumentException when $component value is an invalid component name
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * @param $key
     * @return null|string
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Build a URL from the components using http_build_url() php function
     * @return string
     */
    public function build()
    {
        return http_build_url($this->components);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }

    /**
     * Gets the base url from the components
     * @return string
     */
    public function getBaseUri()
    {
        // Refer to the link below
        // http://php.net/manual/en/function.http-build-url.php#114753
        return $this->components['scheme'].'://'.$this->components['host'];

        // The code below appends 'index.php', so we won't use it.
        /*
        return http_build_url(array(
            'scheme' => $this->components['scheme'],
            'host' => $this->components['host'],
        ));
        */
    }
}
