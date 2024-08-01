<?php
namespace Ratchet\Http;
use Ratchet\MessageInterface;
use Ratchet\ConnectionInterface;
use GuzzleHttp\Psr7\Message;

/**
 * This class receives streaming data from a client request
 * and parses HTTP headers, returning a PSR-7 Request object
 * once it's been buffered
 */
class HttpRequestParser implements MessageInterface {
    const EOM = "\r\n\r\n";

    /**
     * @param \Ratchet\ConnectionInterface $context
     * @param string                       $data Data stream to buffer
     * @return \Psr\Http\Message\RequestInterface
     * @throws \OverflowException If the message buffer has become too large
     */
    public function onMessage(ConnectionInterface $context, $data) {
        if (!isset($context->httpBuffer)) {
            $context->httpBuffer = '';
        }

        $context->httpBuffer .= $data;

        if ($this->isEom($context->httpBuffer)) {
            $request = $this->parse($context->httpBuffer);

            unset($context->httpBuffer);

            return $request;
        }
    }

    /**
     * Determine if the message has been buffered as per the HTTP specification
     * @param  string  $message
     * @return boolean
     */
    public function isEom($message) {
        return (boolean)strpos($message, static::EOM);
    }

    /**
     * @param string $headers
     * @return \Psr\Http\Message\RequestInterface
     */
    public function parse($headers) {
        return Message::parseRequest($headers);
    }
}
