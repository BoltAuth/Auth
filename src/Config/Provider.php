<?php

namespace Bolt\Extension\Bolt\Members\Config;

use Bolt\Helpers\Arr;

/**
 * Provider configuration definition.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Provider
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $label;
    /** @var boolean */
    protected $enabled;
    /** @var string */
    protected $clientId;
    /** @var string */
    protected $clientSecret;
    /** @var array */
    protected $scopes;

    /**
     * Constructor.
     *
     * @param       $name
     * @param array $providerConfig
     */
    public function __construct($name, array $providerConfig)
    {
        $providerConfig = $this->setDefaultConfig($providerConfig);
        $this->name = $name;
        $this->label = $providerConfig['label'];
        $this->clientId = $providerConfig['keys']['clientId'];
        $this->clientSecret = $providerConfig['keys']['clientSecret'];
        $this->scopes = $providerConfig['scopes'];

        if ($this->clientId === null || $this->clientSecret === null) {
            $this->enabled = false;
        } else {
            $this->enabled = (boolean) $providerConfig['enabled'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Provider
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Provider
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     *
     * @return Provider
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     *
     * @return Provider
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     *
     * @return Provider
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     *
     * @return Provider
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @param array $providerConfig
     *
     * @return array
     */
    private function setDefaultConfig(array $providerConfig)
    {
        $default = [
            'label'   => null,
            'keys'    => [
                'clientId'     => null,
                'clientSecret' => null,
            ],
            'scopes'  => null,
            'enabled' => false,
        ];

        return Arr::mergeRecursiveDistinct($default, $providerConfig);
    }
}
