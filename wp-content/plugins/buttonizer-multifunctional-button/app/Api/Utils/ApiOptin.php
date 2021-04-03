<?php
/* 
 * SOFTWARE LICENSE INFORMATION
 * 
 * Copyright (c) 2017 Buttonizer, all rights reserved.
 * 
 * This file is part of Buttonizer
 * 
 * For detailed information regarding to the licensing of
 * this software, please review the license.txt or visit:
 * https://buttonizer.pro/license/
 */

namespace Buttonizer\Api\Utils;

use Buttonizer\Utils\Maintain;

/**
 * Optin API
 * 
 * @endpoint /wp-json/buttonizer/optin
 * @methods GET
 */
class ApiOptin {
    /**
     * Register route
     */
    public function registerRoute() {
        register_rest_route('buttonizer', '/optin', [
            [
                'methods'  => ['POST'],
                'args' => [
                    'nonce' => [
                        'validate_callback' => function($value) {
                            return wp_verify_nonce($value, 'wp_rest');
                        },        
                        'required' => true
                    ],        
                ],
                'callback' => [$this, 'getUrl'],
                'permission_callback' => function() {
                    return Maintain::hasPermission();
                }
            ]
        ]);
    }

    /**
     * Publish buttons
     */
    public function getUrl() {
        return [
            'url' => ButtonizerLicense()->get_reconnect_url()
        ];
    }
}