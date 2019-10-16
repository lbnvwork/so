<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ofd
 *
 * @ORM\Table(name="ofd")
 * @ORM\Entity
 */
class Ofd
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="server", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $server;

    /**
     * @var string|null
     *
     * @ORM\Column(name="port", type="string", length=4, precision=0, scale=0, nullable=true, unique=false)
     */
    private $port;

    /**
     * @var string|null
     *
     * @ORM\Column(name="timeout", type="string", length=12, precision=0, scale=0, nullable=true, unique=false)
     */
    private $timeout;

    /**
     * @var string|null
     *
     * @ORM\Column(name="inn", type="string", length=18, precision=0, scale=0, nullable=true, unique=false)
     */
    private $inn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $url;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_nalog", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $urlNalog;

    /**
     * @var string
     *
     * @ORM\Column(name="timer_connect", type="string", length=3, precision=0, scale=0, nullable=false, unique=false)
     */
    private $timerConnect;

    /**
     * @var string
     *
     * @ORM\Column(name="timer_request", type="string", length=3, precision=0, scale=0, nullable=false, unique=false)
     */
    private $timerRequest;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isEnabled = false;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set server.
     *
     * @param string|null $server
     *
     * @return Ofd
     */
    public function setServer($server = null)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server.
     *
     * @return string|null
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set port.
     *
     * @param string|null $port
     *
     * @return Ofd
     */
    public function setPort($port = null)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     *
     * @return string|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set timeout.
     *
     * @param string|null $timeout
     *
     * @return Ofd
     */
    public function setTimeout($timeout = null)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get timeout.
     *
     * @return string|null
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set inn.
     *
     * @param string|null $inn
     *
     * @return Ofd
     */
    public function setInn($inn = null)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * Get inn.
     *
     * @return string|null
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return Ofd
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return Ofd
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set urlNalog.
     *
     * @param string|null $urlNalog
     *
     * @return Ofd
     */
    public function setUrlNalog($urlNalog = null)
    {
        $this->urlNalog = $urlNalog;

        return $this;
    }

    /**
     * Get urlNalog.
     *
     * @return string|null
     */
    public function getUrlNalog()
    {
        return $this->urlNalog;
    }

    /**
     * Set timerConnect.
     *
     * @param string $timerConnect
     *
     * @return Ofd
     */
    public function setTimerConnect($timerConnect)
    {
        $this->timerConnect = $timerConnect;

        return $this;
    }

    /**
     * Get timerConnect.
     *
     * @return string
     */
    public function getTimerConnect()
    {
        return $this->timerConnect;
    }

    /**
     * Set timerRequest.
     *
     * @param string $timerRequest
     *
     * @return Ofd
     */
    public function setTimerRequest($timerRequest)
    {
        $this->timerRequest = $timerRequest;

        return $this;
    }

    /**
     * Get timerRequest.
     *
     * @return string
     */
    public function getTimerRequest()
    {
        return $this->timerRequest;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Ofd
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }
}
