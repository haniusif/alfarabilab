<?php

return [

    /*
     * CORS لمسارات الـAPI — يستهلكها تطبيق الطبيب (Flutter) عبر رمز Bearer.
     * لا نستخدم كوكيز هنا، لذا يكفي السماح لأي أصل دون اعتماد الكوكيز.
     */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
