<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Service\Http;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Contract\Http\ResponseInterface;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\CanGetClassTrait;

/**
 * Class Response.
 */
class Response implements ResponseInterface
{
    use CanGetClassTrait;

    /** @var int */
    private $statusCode = 200;

    /**
     * @param int    $status Status code for the response, if any.
     * @param string $body   Response body.
     */
    public function __construct($status = 200, $body = null, $error = null)
    {
        $this->statusCode = (int)$status;
        $this->body = $body;
        $this->error = $error;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getError()
    {
        return $this->error;
    }
}
