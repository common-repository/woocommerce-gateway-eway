<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Enum;

/**
 * Class ApiMethod.
 */
abstract class ApiMethod extends AbstractEnum
{
    const DIRECT = 'Direct';
    const RESPONSIVE_SHARED = 'ResponsiveShared';
    const TRANSPARENT_REDIRECT = 'TransparentRedirect';
    const WALLET = 'Wallet';
    const AUTHORISATION = 'Authorisation';
}
