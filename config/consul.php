<?php

    return [
        'addr' => '127.0.0.1:8500',
        'service' => [
            'id' => 'my-service-01',
            'name' => 'my-service',
            'addr' => '127.0.0.1',
            'port' => 80,
            'health_check_url' => 'http://127.0.0.1:80/api/health',
            'health_check_interval' => 30
        ],
        /**
         * 依赖的其他服务名
         */
        'consul_services' => [

        ],
        'service_list_cache_prefix' => 'service_list',
    ];
