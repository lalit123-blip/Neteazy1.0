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

namespace Buttonizer\Api;

/**
 * Initialize api
 */
class Api {
    private static $authenticated = false;

    /**
     * Register API endpoints
     */
    public function __construct() {
        // Frontend api endpoints
        (new Buttons\ApiButtons())->registerRoute();

        // Backend api
        (new Settings\ApiSettings())->registerRoute();
        (new Dashboard\ApiDashboard())->registerRoute();
        (new Utils\ApiPublish())->registerRoute();
        (new Utils\ApiRevert())->registerRoute();
        (new Utils\ApiReset())->registerRoute();
        (new Utils\ApiOptin())->registerRoute();
        (new PageRules\ApiPageRules())->registerRoute();
        (new TimeSchedules\ApiTimeSchedules())->registerRoute();
        (new Wordpress\ApiCustomCss())->registerRoute();
    }

    /**
     * Return error, need Buttonizer pro
     */
    public static function needButtonizerPremium() {
        return new \WP_Error('missing_premium_license', 'You do not have Buttonizer Pro.', [ 
            'status' => 403 
        ]);
    }
}