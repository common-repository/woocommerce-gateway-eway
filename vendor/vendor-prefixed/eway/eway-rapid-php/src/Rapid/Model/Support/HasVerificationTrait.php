<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait HasVerificationTrait.
 */
trait HasVerificationTrait
{
    /**
     * @param mixed $verification
     *
     * @return $this
     */
    public function setVerificationAttribute($verification)
    {
        $this->validateInstance('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Verification', 'Verification', $verification);

        return $this;
    }
}
