<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model;

/**
 * @property string Cryptogram
 * @property string ECI
 * @property string XID
 * @property string AuthStatus
 * @property string Version
 * @property string dsTransactionId
 */
class ThreeDSecureAuth extends AbstractModel
{
    protected $fillable = [
        'Cryptogram',
        'ECI',
        'XID',
        'AuthStatus',
        'Version',
        'dsTransactionId',
    ];
}
