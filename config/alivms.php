<?php

return [
    'uri'         => env('ALI_VMS_URI', 'http://nls-gateway.cn-shanghai.aliyuncs.com/stream/v1/asr'),
    'appkey'      => env('ALI_VMS_APPKEY', ''),
    'host'        => env('ALI_VMS_HOST', 'nls-gateway.cn-shanghai.aliyuncs.com'),
    'timeout'     => 3,
    'log_file'    => storage_path('logs/alivms.log'),
    'log_channel' => 'ALI-VMS',
];
