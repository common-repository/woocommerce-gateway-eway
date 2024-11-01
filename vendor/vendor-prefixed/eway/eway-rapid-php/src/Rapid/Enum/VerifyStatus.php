<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Enum;

/**
 * Possible values returned from the payment providers with regards to verification of card/user details
 */
abstract class VerifyStatus extends AbstractEnum
{
    const UNCHECKED = 0;
    const VALID = 1;
    const INVALID = 2;
}
