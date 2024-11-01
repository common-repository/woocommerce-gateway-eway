<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */


namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait HasBeagleVerificationTrait.
 */
trait HasBeagleVerificationTrait
{
    /**
     * @param $beagleVerification
     *
     * @return $this
     */
    public function setBeagleVerificationAttribute($beagleVerification)
    {
        $this->validateInstance('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Verification', 'BeagleVerification', $beagleVerification);

        return $this;
    }
}
