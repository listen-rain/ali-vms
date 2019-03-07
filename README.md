# Ali Vms
Ali Vms Package For Laravel5

[![Latest Stable Version](https://poser.pugx.org/listen/ali-vms/v/stable)](https://packagist.org/packages/listen/ali-vms)
[![Total Downloads](https://poser.pugx.org/listen/ali-vms/downloads)](https://packagist.org/packages/listen/ali-vms)
[![Latest Unstable Version](https://poser.pugx.org/listen/ali-vms/v/unstable)](https://packagist.org/packages/listen/ali-vms)
[![License](https://poser.pugx.org/listen/ali-vms/license)](https://packagist.org/packages/listen/ali-vms)
[![Monthly Downloads](https://poser.pugx.org/listen/ali-vms/d/monthly)](https://packagist.org/packages/listen/ali-vms)
[![Daily Downloads](https://poser.pugx.org/listen/ali-vms/d/daily)](https://packagist.org/packages/listen/ali-vms)
[![composer.lock](https://poser.pugx.org/listen/ali-vms/composerlock)](https://packagist.org/packages/listen/ali-vms)

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
    'log_channel'  => env('ALIVMS_LOG_CHANNEL', 'alivms'),
    'log_mode'     => env('ALIVMS_LOG_MODE', 'single')
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

push Exception callback
```
\AliVms::pushExceptionCallback('dingtalk', function ($module, $message, $code, $otherParams) {
    // https://github.com/listen-rain/dingtalk
    sendByDingtalk($message . "\n\n Code: {$code}", "{$module}.error");
});
```

## Contact Me

Email : zhufengwei@aliyun.com

Wechat: w15275049388



