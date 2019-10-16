<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 9:56
 */

namespace App\Service;

use Zend\Expressive\Session\SessionInterface;

/**
 * Class FlashMessage
 *
 * @package App\Service
 */
class FlashMessage
{
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'danger';

    /**
     * Messages from previous request
     *
     * @var string[]
     */
    protected $fromPrevious = [];

    /**
     * Messages for current request
     *
     * @var string[]
     */
    protected $forNow = [];

    /**
     * Messages for next request
     *
     * @var string[]
     */
    protected $forNext = [];

    /**
     * Message storage
     *
     * @var null|SessionInterface
     */
    protected $storage;

    /**
     * Message storage key
     *
     * @var string
     */
    protected $storageKey = 'flashMessage';

    /**
     * FlashMessage constructor.
     *
     * @param SessionInterface $storage
     * @param null|string $storageKey
     */
    public function __construct(SessionInterface $storage, ?string $storageKey = null)
    {
        if (\is_string($storageKey) && $storageKey) {
            $this->storageKey = $storageKey;
        }

        // Set storage
        $this->storage = $storage;

        // Load messages from previous request
        if ($this->storage->has($this->storageKey) && \is_array($this->storage->get($this->storageKey))) {
            $this->fromPrevious = $this->storage->get($this->storageKey);
        }

        $this->storage->set($this->storageKey, []);
    }

    /**
     * Add flash message for the next request
     *
     * @param string $key The key to store the message under
     * @param string $message Message to show on next request
     */
    public function addMessage(string $key, string $message): void
    {
        $messages = $this->storage->get($this->storageKey, []);
        $messages[$key][] = $message;

        $this->storage->set($this->storageKey, $messages);
    }

    /**
     * Add success flash message for the next request
     *
     * @param string $message
     */
    public function addSuccessMessage(string $message): void
    {
        $this->addMessage(self::SUCCESS, $message);
    }

    /**
     * Add info flash message for the next request
     *
     * @param string $message
     */
    public function addInfoMessage(string $message): void
    {
        $this->addMessage(self::INFO, $message);
    }

    /**
     * Add warning flash message for the next request
     *
     * @param string $message
     */
    public function addWarningMessage(string $message): void
    {
        $this->addMessage(self::INFO, $message);
    }

    /**
     * Add error flash message for the next request
     *
     * @param string $message
     */
    public function addErrorMessage(string $message): void
    {
        $this->addMessage(self::ERROR, $message);
    }

    /**
     * Add flash message for current request
     *
     * @param string $key The key to store the message under
     * @param string $message Message to show on next request
     */
    public function addMessageNow(string $key, string $message): void
    {
        // Create Array for this key
        if (!isset($this->forNow[$key])) {
            $this->forNow[$key] = [];
        }

        // Push onto the array
        $this->forNow[$key][] = $message;
    }

    /**
     * Get flash messages
     *
     * @return array Messages to show for current request
     */
    public function getMessages(): array
    {
        $messages = $this->fromPrevious;

        foreach ($this->forNow as $key => $values) {
            if (!isset($messages[$key])) {
                $messages[$key] = [];
            }

            foreach ($values as $value) {
                $messages[$key][] = $value;
            }
        }

        return $messages;
    }

    /**
     * Get Flash Message
     *
     * @param string $key The key to get the message from
     *
     * @return mixed|null Returns the message
     */
    public function getMessage(string $key)
    {
        $messages = $this->getMessages();

        // If the key exists then return all messages or null
        return $messages[$key] ?? null;
    }

    /**
     * Get the first Flash message
     *
     * @param  string $key The key to get the message from
     * @param  string $default Default value if key doesn't exist
     *
     * @return mixed Returns the message
     */
    public function getFirstMessage(string $key, ?string $default = null)
    {
        $messages = $this->getMessage($key);
        if (\is_array($messages) && \count($messages) > 0) {
            return $messages[0];
        }

        return $default;
    }

    /**
     * Has Flash Message
     *
     * @param string $key The key to get the message from
     *
     * @return bool Whether the message is set or not
     */
    public function hasMessage(string $key): bool
    {
        $messages = $this->getMessages();

        return isset($messages[$key]);
    }

    /**
     * Clear all messages
     *
     * @return void
     */
    public function clearMessages(): void
    {
        if ($this->storage->has($this->storageKey)) {
            $this->storage->unset($this->storageKey);
        }

        $this->fromPrevious = [];
        $this->forNow = [];
    }

    /**
     * Clear specific message
     *
     * @param  string $key The key to clear
     *
     * @return void
     */
    public function clearMessage(string $key): void
    {
        $messages = $this->storage->get($this->storageKey, []);
        if (isset($messages[$key])) {
            unset($messages[$key]);
            $this->storage->set($key, $messages);
        }

        if (isset($this->fromPrevious[$key])) {
            unset($this->fromPrevious[$key]);
        }

        if (isset($this->forNow[$key])) {
            unset($this->forNow[$key]);
        }
    }
}
