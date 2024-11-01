<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Class HasCardDetailTrait.
 *
 * @property CardDetails $CardDetails
 */
trait HasCardDetailTrait
{
    /**
     * @param $cardDetails
     *
     * @return $this
     */
    public function setCardDetailsAttribute($cardDetails)
    {
        $this->validateInstance('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\CardDetails', 'CardDetails', $cardDetails);

        return $this;
    }
}
