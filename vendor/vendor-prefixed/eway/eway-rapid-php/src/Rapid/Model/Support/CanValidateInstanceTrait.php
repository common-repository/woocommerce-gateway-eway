<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Validator\ClassValidator;

/**
 * Class CanValidateInstanceTrait.
 */
trait CanValidateInstanceTrait
{
    /**
     * @param string $class
     * @param string $field
     * @param mixed  $value
     */
    protected function validateInstance($class, $field, $value)
    {
        if (is_null($value)) {
            $this->attributes[$field] = null;
        } else {
            $this->attributes[$field] = ClassValidator::getInstance($class, $value);
        }
    }
}
