<?php

namespace Listen\AliVms;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use AliOpenapi;
use Illuminate\Config\Repository;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class AliVms
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Array
     */
    protected $headers = [];

    /**
     * @var Array
     */
    protected $options = [];

    /**
     * @var FilePath
     */
    protected $file;

    /**
     * AliVms constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        $this->client = new Client(
            [
                'base_uri' => '',
                'timeout'  => $this->config->get('alivms.timeout'),
            ]);
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@100tal.com>
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
     * @date   2019/1/4
     * @author <zhufengwei@100tal.com>
     *
     * @param string $token
     *
     * @return $this
     */
    public function setHeader(string $token = '')
    {
        $this->headers = [
            'X-NLS-Token'    => $token ?: AliOpenapi::getToken()->Id,
            'Content-type'   => 'application/octet-stream',
            'Content-Length' => strval(strlen(file_get_contents($this->file))),
            'Host'           => $this->config->get('alivms.host') ?: 'nls-gateway.cn-shanghai.aliyuncs.com'
        ];

        return $this;
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@100tal.com>
     *
     * @param string $appkey
     *
     * @return $this
     */
    public function setOption(string $appkey = '')
    {
        $this->options = [
            'multipart' => [
                [
                    'name'     => 'audio',
                    'contents' => file_get_contents($this->file)
                ]
            ],
            'query'     => [
                'appkey' => $appkey ?: $this->config->get('alivms.appkey')
            ],
            'timeout'   => $this->config->get('alivms.timeout'),
            'headers'   => $this->headers
        ];

        return $this;
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@100tal.com>
     *
     * @param string $file
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function voiceDetection(string $file = '')
    {
        if ($file) {
            $this->setFile($file)->setHeader()->setOption();
        }

        try {
            // first
            $request = new Request('POST', $this->config->get('alivms.uri'), $this->headers);
            $result  = $this->client->send($request, $this->options);

            // secounds
            // $result = $this->client->request('POST', $this->config->get('alivms.uri'), $this->options);

            return json_decode($result->getBody()->getContents());

        } catch (\Exception $e) {

            $this->addlog('alivms', $this->config->get('alivms.uri'), [$file], $e->getMessage(), $e->getCode());

            return false;
        }
    }

    /**
     * @date   2019/1/4
     * @author <zhufengwei@100tal.com>
     *
     * @param $module
     * @param $uri
     * @param $request
     * @param $response
     * @param $code
     */
    public function addlog(string $module, string $uri, array $request, string $response, int $code)
    {
        $logger      = new Logger($this->config->get('alivms.log_channel'));
        $eventRotate = new RotatingFileHandler($this->config->get('alivms.log_file'), Logger::INFO);
        $eventRotate->setFormatter(new LineFormatter("[%datetime%] [%level_name%] %channel% - %message% %extra%\n"));
        $logger->pushHandler($eventRotate);
        $logger->pushProcessor(function ($record) use ($request, $uri, $response, $code) {
            $record['extra'] = [
                'uri'      => $uri,
                'request'  => $request,
                'response' => $response,
                'code'     => $code
            ];

            return $record;
        });

        $logger->addInfo($module);
    }
}
