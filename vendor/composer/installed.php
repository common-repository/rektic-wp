<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0',
        'version' => '1.0.0.0',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => 'rektic/rektic-wp',
        'dev' => true,
    ),
    'versions' => array(
        'micropackage/internationalization' => array(
            'pretty_version' => '1.0.1',
            'version' => '1.0.1.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../micropackage/internationalization',
            'aliases' => array(),
            'reference' => '8de158b8fc71557a8310f0d98d2b8ad9f4f44dd5',
            'dev_requirement' => false,
        ),
        'micropackage/requirements' => array(
            'pretty_version' => '1.2.0',
            'version' => '1.2.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../micropackage/requirements',
            'aliases' => array(),
            'reference' => '114d49df7c5860108c008853b514d9a7eccaedf3',
            'dev_requirement' => false,
        ),
        'rektic/rektic-wp' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => NULL,
            'dev_requirement' => false,
        ),
    ),
);
