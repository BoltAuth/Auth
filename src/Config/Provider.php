<?php

namespace Bolt\Extension\Bolt\Members\Config;

use Bolt\Helpers\Arr;

/**
 * Provider configuration definition.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Provider
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $labelSignIn;
    /** @var string */
    protected $labelAssociate;
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

        $this->name = strtolower($name);
        $this->labelSignIn = $providerConfig['label']['sign_in'] ?: 'Sign-in with ' . $name;
        $this->labelAssociate = $providerConfig['label']['associate'] ?: 'Add ' . $name;
        $this->clientId = $providerConfig['keys']['client_id'];
        $this->clientSecret = $providerConfig['keys']['client_secret'];
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
    public function getLabelSignIn()
    {
        return $this->labelSignIn;
    }

    /**
     * @param string $labelSignIn
     *
     * @return Provider
     */
    public function setLabelSignIn($labelSignIn)
    {
        $this->labelSignIn = $labelSignIn;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelAssociate()
    {
        return $this->labelAssociate;
    }

    /**
     * @param string $labelAssociate
     *
     * @return Provider
     */
    public function setLabelAssociate($labelAssociate)
    {
        $this->labelAssociate = $labelAssociate;

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
            'label'   => [
                'sign_in'   => null,
                'associate' => null,
            ],
            'keys'    => [
                'client_id'     => null,
                'client_secret' => null,
            ],
            'scopes'  => null,
            'enabled' => false,
        ];

        return Arr::mergeRecursiveDistinct($default, $providerConfig);
    }
}
