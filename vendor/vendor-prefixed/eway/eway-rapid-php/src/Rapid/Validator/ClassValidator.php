<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Validator;

/**
 * Class ClassValidator.
 */
abstract class ClassValidator
{
    /**
     * @param $class
     * @param $instance
     *
     * @return mixed
     */
    public static function getInstance($class, $instance)
    {
        if (is_a($instance, $class)) {
            return $instance;
        }

        return new $class($instance);
    }
}
