<?php

namespace Maruamyu\Core\Xrd;

/**
 * WebFinger Client (RFC7033)
 */
class WebFingerClient
{
    /**
     * @var Data\HostMeta[]
     */
    protected $hostMetaCache;

    /**
     * @var Data\WebFinger[]
     */
    protected $webFingerCache;

    /**
     * initialize
     */
    public function __construct()
    {
        $this->hostMetaCache = [];
        $this->webFingerCache = [];
    }

    /**
     * get webfinger data
     *
     * @param string $resourceUri resource URI
     * @return Data\WebFinger|null webfinger data object
     * @throws \UnexpectedValueException if invalid resource URI
     * @throws \RuntimeException if failed fetch webfinger
     */
    public function get($resourceUri)
    {
        $resourceUri = strval($resourceUri);
        if (empty($resourceUri)) {  # '0' is invalid URI
            throw new \UnexpectedValueException('resource URI is empty.');
        }

        if (isset($this->webFingerCache[$resourceUri])) {
            return $this->webFingerCache[$resourceUri];
        }

        $webFinger = $this->fetch($resourceUri);
        $this->webFingerCache[$resourceUri] = $webFinger;
        return $webFinger;
    }

    /**
     * fetch webfinger on http connection
     *
     * @param string $resourceUri resource URI
     * @return Data\WebFinger webfinger data object
     * @throws \UnexpectedValueException if invalid resource URI
     * @throws \RuntimeException if failed fetch webfinger
     */
    public function fetch($resourceUri)
    {
        $host = static::extractHostFromResourceUri($resourceUri);
        if (empty($host)) {  # '0' is invalid host
            throw new \UnexpectedValueException('invalid resource URI. (host is empty.)');
        }
        $hostMeta = $this->getHostMeta($host);
        if ($hostMeta) {
            $webFingerUrl = str_replace('{uri}', rawurlencode($resourceUri), $hostMeta->getLrdd());
        } else {
            # fallback
            $webFingerUrl = 'https://' . $host . '/.well-known/webfinger?resource=' . rawurlencode($resourceUri);
        }
        $webFingerXrd = static::fetchXrd($webFingerUrl);
        if (strlen($webFingerXrd) < 1) {
            throw new \RuntimeException('webfinger fetch failed.');
        }
        return Data\WebFinger::fromXml($webFingerXrd);
    }

    /**
     * get host-meta data
     *
     * @param string $host domain
     * @return Data\HostMeta|null host-meta data object
     * @throws \UnexpectedValueException if invalid host name
     */
    public function getHostMeta($host)
    {
        if (empty($host)) {  # '0' is invalid host
            throw new \UnexpectedValueException('host is empty.');
        }

        if (isset($this->hostMetaCache[$host])) {
            return $this->hostMetaCache[$host];
        }

        $hostMeta = $this->fetchHostMeta($host);
        $this->hostMetaCache[$host] = $hostMeta;
        return $hostMeta;
    }

    /**
     * fetch host-meta on http connection
     *
     * @param string $host domain
     * @return Data\HostMeta host-meta data object
     * @throws \UnexpectedValueException if invalid host name
     * @throws \RuntimeException if failed fetch host-meta
     */
    public function fetchHostMeta($host)
    {
        if (empty($host)) {  # '0' is invalid host
            throw new \UnexpectedValueException('host is empty.');
        }

        $hostMetaUrl = static::getHostMetaUrl($host);
        $hostMetaXrd = static::fetchXrd($hostMetaUrl);
        if (strlen($hostMetaXrd) < 1) {
            # fallback
            $hostMetaUrl = static::getHostMetaUrl($host, true);
            $hostMetaXrd = static::fetchXrd($hostMetaUrl);
        }
        if (strlen($hostMetaXrd) < 1) {
            return null;
        }
        return Data\HostMeta::fromXml($hostMetaXrd);
    }

    /**
     * @param string $host host
     * @param boolean $isHttp HTTP URL
     * @return string host-meta URL
     */
    public static function getHostMetaUrl($host, $isHttp = false)
    {
        $scheme = ($isHttp) ? 'http' : 'https';
        return $scheme . '://' . $host . '/.well-known/host-meta';
    }

    /**
     * @param string $url XRD URL
     * @return string XRD
     */
    protected static function fetchXrd($url)
    {
        $contextOpts = [
            'http' => [
                'header' => 'Accept: ' . Data\General::XRD_CONTENT_TYPE,
            ],
        ];
        $context = stream_context_create($contextOpts);
        return @file_get_contents($url, false, $context);
    }

    /**
     * @param string $resourceUri resoure URI
     * @return string host
     */
    protected static function extractHostFromResourceUri($resourceUri)
    {
        $host = '';

        $parsed = parse_url($resourceUri);
        if (isset($parsed['scheme']) && $parsed['scheme'] === 'acct') {
            list(, $host) = explode('@', $parsed['path'], 2);
        } elseif (isset($parsed['host'])) {
            $host = $parsed['host'];
        }

        return $host;
    }
}
