<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Response;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\ThreeDSecureAuth;

/**
 * @property string Errors
 * @property string AccessCode
 * @property bool Enrolled
 * @property ThreeDSecureAuth ThreeDSecureAuth
 */
class Verification3dsEnrolmentResponse extends AbstractResponse
{
    protected $fillable = [
        'Errors',
        'AccessCode',
        'Enrolled',
        'ThreeDSecureAuth',
    ];
}
