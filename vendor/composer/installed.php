<?php return array(
    'root' => array(
        'name' => 'woocommerce/woocommerce-gateway-eway',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'eway/eway-rapid-php' => array(
            'pretty_version' => '1.4.1',
            'version' => '1.4.1.0',
            'reference' => '3ccc17406101db7e6670b2e76b088124c6955c14',
            'type' => 'library',
            'install_path' => __DIR__ . '/../eway/eway-rapid-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'woocommerce/woocommerce-gateway-eway' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
