<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait CanGetClassTrait.
 */
trait CanGetClassTrait
{
    /**
     * Because PHP 5.4 doesn't have ::class yet
     *
     * @return string
     */
    public static function getClass()
    {
        return get_called_class();
    }
}
