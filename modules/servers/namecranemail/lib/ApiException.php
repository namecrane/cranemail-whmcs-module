<?php

namespace WHMCS\Module\Server\Namecranemail;

/**
 * A refusal or failure from the NameCrane API, with its message ready to show.
 */
class ApiException extends \Exception
{
    /** @var array */
    private $body;

    /**
     * @param string $message
     * @param int $status
     * @param array $body
     */
    public function __construct($message, $status, array $body = array())
    {
        parent::__construct($message, (int) $status);
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function status()
    {
        return $this->getCode();
    }

    /**
     * @return array
     */
    public function body()
    {
        return $this->body;
    }
}
