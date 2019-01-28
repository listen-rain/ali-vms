# Ali Vms
Ali Vms Package For Laravel5

## Usage

Composer Install

```sh
composer require listen/ali-vms
```

Publish And Update Config File

```sh
php artisan vendor:publish
```

Then Edit config/ali-vms.php

```php
<?php

return [
    'uri'         => env('ALI_VMS_URI', 'http://nls-gateway.cn-shanghai.aliyuncs.com/stream/v1/asr'),  // Request Uri
    'appkey'      => env('ALI_VMS_APPKEY', ''),   // APP KEY                                      
    'host'        => env('ALI_VMS_HOST', 'nls-gateway.cn-shanghai.aliyuncs.com'),  // Host
    'timeout'     => 120,
    'log_file'    => storage_path('logs/alivms.log'), // Log File Path
    'log_channel' => 'ALI-VMS',
];
```

Update config/app.php
```
providers => [
    ......
    Listen\AliVms\AliVmsServiceProvider::class,
],

......

aliases => [
    ......
    'AliVms' => Listen\AliVms\Facades\AliVms::class,
] 
```

Send Request

```php
# use tmp file
$file = $request->file('audio');
dd(AliVms::voiceDetection($file->getRealPath()));

# use file 
dd(AliVms::voiceDetection('path/to/file.pcm'));
```

## Contact Me

Email : zhufengwei@aliyun.com

Wechat: w15275049388



