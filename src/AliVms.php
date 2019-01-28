<?php

namespace Listen\AliVms;

use Illuminate\Config\Repository;
use Mockery\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AliVms
{
    const BASENAME = 'alivms.';

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var array
     */
    private $setable = [
        'appkey',
        'format',
        'sample_rate',
        'enable_punctuation_prediction',
        'enable_inverse_text_normalization',
        'enable_voice_detection'
    ];

    /**
     * @var bool
     */
    private $dingUrl = '';

    /**
     * AliVms constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config      = $config;
        $this->uri         = $this->config->get('alivms.uri', 'http://nls-gateway.cn-shanghai.aliyuncs.com/stream/v1/asr');
        $this->appkey      = $this->setAppkey();
        $this->format      = 'pcm';
        $this->sample_rate = '16000';
        $this->timeout     = $this->config->get('alivms.timeout', 120);
    }

    /**
     * @date   2019/1/28
     * @author <zhufengwei@aliyun.com>
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @date   2019/1/28
     * @author <zhufengwei@aliyun.com>
     *
     * @param string $url
     *
     * @return $this
     */
    public function setDingDingUrl(string $url)
    {
        $this->dingUrl = $url;

        return $this;
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@aliyun.com>
     *
     * @param string $file
     *
     * @return $this
     */
    public function setFile(string $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @date   2019/1/28
     * @author <zhufengwei@aliyun.com>
     * @return string
     */
    private function getRequest(): string
    {
        return $this->uri . "?appkey={$this->appkey}"
            . "&format={$this->format}"
            . "&sample_rate={$this->sample_rate}"
            . "&enable_punctuation_prediction={$this->enable_punctuation_prediction}"
            . "&enable_inverse_text_normalization={$this->enable_inverse_text_normalization}"
            . "&enable_voice_detection={$this->enable_voice_detection}";
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@aliyun.com>
     *
     * @param string $token
     *
     * @return $this
     */
    public function setHeader()
    {
        if (!$this->token) {
            throw new \Exception('Token Con\'t Be Null !');
        }

        $this->headers = [
            'X-NLS-Token:' . $this->token,
            'Content-type:' . 'application/octet-stream',
            'Content-Length:' . strval(strlen(file_get_contents($this->file))),
            'Host:' . $this->config->get('alivms.host') ?: 'nls-gateway.cn-shanghai.aliyuncs.com'
        ];

        return $this;
    }

    /**
     * @date   2019/1/22
     * @author <zhufengwei@aliyun.com>
     *
     * @return string
     */
    private function setAppkey(): string
    {
        $appkeys = $this->config->get('alivms.appkey');

        if (!is_array($appkeys)) {
            $appkeysArr = explode(',', $appkeys);
            if (empty($appkeysArr)) {
                throw new Exception('AppKey Con\'t Empty!');
            }

            return $appkeysArr[array_rand($appkeysArr, 1)];
        }
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@aliyun.com>
     *
     * @param string $file
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function voiceDetection(string $file = '', $token = '')
    {
        if ($token) {
            $this->setToken($token);
        }

        if ($file) {
            $this->setFile($file)->setHeader();
        }

        try {
            $request  = $this->getRequest();
            $response = $this->send($request);

            return json_decode($response, true);
        } catch (\Exception $e) {

            $this->addlog('alivms', $this->config->get('alivms.uri'), [$file], $e->getMessage(), $e->getCode());

            return false;
        }
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@aliyun.com>
     *
     * @param $module
     * @param $uri
     * @param $request
     * @param $response
     * @param $code
     */
    public function addlog(string $module, string $uri, array $request, string $response, int $code)
    {
        $logger    = new Logger($this->config->get('alivms.log_channel'));
        $file_name = $this->config->get('alivms.log_file');

        try {
            $logger->pushHandler(new StreamHandler($file_name, Logger::INFO, false));
        } catch (\Exception $e) {
            $logger->info('pushHandlerError', $e->getMessage());
        }

        $logger->pushProcessor(function ($record) use ($request, $uri, $response, $code) {
            $record['extra'] = compact('uri', 'request', 'response', 'code');
            return $record;
        });

        $logger->addError(self::BASENAME . $module);
    }

    /**
     * @date   2019/1/28
     * @author <zhufengwei@aliyun.com>
     *
     * @param string $request
     */
    public function send(string $request)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, file_get_contents($this->file));
        curl_setopt($curl, CURLOPT_NOBODY, FALSE);
        $returnData = curl_exec($curl);
        curl_close($curl);

        return $returnData;
    }

    /**
     * @date   2019/1/28
     * @author <zhufengwei@aliyun.com>
     *
     * @param $name
     * @param $value
     *
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->setable)) {
            $this->query[$name] = $value;
        }

        $this->$name = $value;

        return $this;
    }

    /**
     * @date   2019/1/28
     * @author <zhufengwei@aliyun.com>
     *
     * @param $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (in_array($name, $this->setable)) {
            return $this->query[$name] ?? 'false';
        }

        return $this->$name;
    }
}
