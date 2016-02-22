<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Exception;
use Bolt\Extension\Bolt\Members\Oauth2\Handler\HandlerInterface;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provider object management class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProviderManager
{
    /** @var Config */
    protected $config;
    /** @var \GuzzleHttp\Client */
    protected $guzzleClient;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var string */
    protected $rootUrl;
    /** @var AbstractProvider */
    protected $provider;
    /** @var string */
    protected $providerName;
    /** @var HandlerInterface */
    protected $providerHandler;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param Client          $guzzleClient
     * @param LoggerInterface $logger
     * @param string          $rootUrl
     */
    public function __construct(Config $config, Client $guzzleClient, LoggerInterface $logger, $rootUrl)
    {
        $this->config = $config;
        $this->guzzleClient = $guzzleClient;
        $this->logger = $logger;
        $this->rootUrl = $rootUrl;
    }

    /**
     * Set the provider for this request.
     *
     * @internal
     *
     * @param Application $app
     * @param string      $providerName
     *
     * @throws Exception\InvalidProviderException
     */
    public function setProvider(Application $app, $providerName)
    {
        // Set the provider name that we're using for this request
        $providerName = $providerName === 'submit' ? 'local' : $providerName;
        $this->providerName = $providerName;

        $providerKey = 'members.oauth.provider.' . strtolower($providerName);

        $app['members.oauth.provider'] = $app->share(
            function ($app) use ($providerKey) {
                return $app[$providerKey]([]);
            }
        );

        $app['members.oauth.provider.name'] = $app->share(
            function () use ($providerName) {
                return $providerName;
            }
        );

        // Get the provider class name
        if (!isset($app['members.oauth.provider.map'][$providerName])) {
            throw new Exception\InvalidProviderException(Exception\InvalidProviderException::UNMAPPED_PROVIDER);
        }
        $providerClass = $app['members.oauth.provider.map'][$providerName];
        if (!class_exists($providerClass)) {
            throw new Exception\InvalidProviderException(Exception\InvalidProviderException::INVALID_PROVIDER);
        }
        $options = $this->getProviderOptions($providerName);
        $collaborators = ['httpClient' => $this->guzzleClient];

        $this->provider = new $providerClass($options, $collaborators);

        $app['logger.system']->debug('[Members][Provider]: Created provider name: ' . $providerName, ['event' => 'extensions']);

        $this->setProviderHandler($app);
    }

    /**
     * Get a provider class object.
     *
     * @param string $providerName
     *
     * @throws Exception\InvalidProviderException
     *
     * @return AbstractProvider
     */
    public function getProvider($providerName)
    {
        $this->logger->debug('[Members][Provider]: Fetching provider object: ' . $providerName);

        if ($this->provider === null) {
            throw new Exception\InvalidProviderException(Exception\InvalidProviderException::UNSET_PROVIDER);
        }

        return $this->provider;
    }

    /**
     * Get a corrected provider name for the request.
     *
     * @param bool $lower
     *
     * @return string
     */
    public function getProviderName($lower = false)
    {
        // If the provider name is set, we assume this is called post ->before()
        if ($this->providerName !== null) {
            return $lower ? strtolower($this->providerName) : $this->providerName;
        }

        // If we have no provider name set, and no valid request, we're out of
        // cycle… and that's like bad… 'n stuff
        throw new \RuntimeException('Attempting to get provider name outside of the request cycle.');
    }

    /**
     * Get a provider config for passing to the library.
     *
     * @param string $providerName
     *
     * @throws Exception\ConfigurationException
     *
     * @return array
     */
    public function getProviderOptions($providerName)
    {
        $providerConfig = $this->config->getProvider($providerName);

        if (empty($providerConfig->getClientId())) {
            throw new Exception\ConfigurationException('Provider client ID required: ' . $providerName);
        }
        if (empty($providerConfig->getClientSecret())) {
            throw new Exception\ConfigurationException('Provider secret key required: ' . $providerName);
        }
        if (empty($providerConfig->getScopes())) {
            throw new Exception\ConfigurationException('Provider scope(s) required: ' . $providerName);
        }

        $options = [
            'clientId'     => $providerConfig->getClientId(),
            'clientSecret' => $providerConfig->getClientSecret(),
            'scope'        => $providerConfig->getScopes(),
            'redirectUri'  => $this->getCallbackUrl($providerName),
        ];
        if ($providerName === 'facebook') {
            $options['graphApiVersion'] = 'v2.5';
        }

        //if ($providerName === 'Local') {
        //    $base = $this->config->getUrlRoot() . $this->config->getUriBase() . '/';
        //    $options['urlAuthorize'] = $base . $this->config->getUriAuthorise();
        //    $options['urlAccessToken'] = $base . $this->config->getUriAccessToken();
        //    $options['urlResourceOwnerDetails'] = $base . $this->config->getUriResourceOwnerDetails();
        //}

        return $options;
    }

    /**
     * @return HandlerInterface
     */
    public function getProviderHandler()
    {
        return $this->providerHandler;
    }

    /**
     * Get the Authorisation\AuthorisationInterface class to handle the request.
     *
     * @param \Silex\Application $app
     *
     * @throws Exception\InvalidAuthorisationRequestException
     * @throws \RuntimeException
     */
    protected function setProviderHandler(Application $app)
    {
        $providerName = $this->getProviderName();
        if ($providerName === null) {
            $app['logger.system']->debug('[Members][Provider]: Request was missing a provider.', ['event' => 'extensions']);
            throw new Exception\InvalidAuthorisationRequestException('Authentication configuration error. Unable to proceed!');
        }

        $providerConfig = $this->config->getProvider($providerName);
        if ($providerConfig === null) {
            $app['logger.system']->debug('[Members][Provider]: Request provider did not match any configured providers.', ['event' => 'extensions']);
            throw new Exception\InvalidAuthorisationRequestException('Authentication configuration error. Unable to proceed!');
        }

        if (!$providerConfig->isEnabled() && $providerName !== 'Generic') {
            $app['logger.system']->debug('[Members][Provider]: Request provider was disabled.', ['event' => 'extensions']);
            throw new Exception\InvalidAuthorisationRequestException('Authentication configuration error. Unable to proceed!');
        }

        $handlerKey = $this->getHandlerKey($providerName);
        $app['members.oauth.handler'] = $app->share(
            function ($app) use ($app, $handlerKey) {
                return $app[$handlerKey]([]);
            }
        );

        $this->providerHandler = $app['members.oauth.handler'];
    }

    /**
     * Get the service key for our provider.
     *
     * @param string $providerName
     *
     * @return string
     */
    protected function getHandlerKey($providerName)
    {
        if ($providerName === 'local') {
            return 'members.oauth.handler.local';
        }

        return 'members.oauth.handler.remote';
    }

    /**
     * Construct the authorisation URL with query parameters.
     *
     * @param string $providerName
     *
     * @return string
     */
    protected function getCallbackUrl($providerName)
    {
        $url = sprintf('%s%s/oauth2/callback?provider=%s', $this->rootUrl, $this->config->getUrlAuthenticate(), $providerName);
        $this->logger->debug("[Members][Provider]: Setting callback URL: $url");

        return $url;
    }
}
