<?php

use Fairy\HttpCrontab;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/*
 * * * * * * * *    //格式 :秒 分 时 天 月 年 周
  10 * * * * * *    //表示每一分钟的第10秒运行
 /10 * * * * * * //表示每10秒运行
 /1 * 15,16 * * * * //表示 每天的15点,16点的每一秒运行
 * */

class Crontab
{

    private $baseUri;
    private $safeKey;

    public function __construct($baseUri = 'http://127.0.0.1:2345', $safeKey = null)
    {
        $this->baseUri = config("crontab.base_url") ? config("crontab.base_url") : $baseUri;
        $this->safeKey = config("crontab.safe_key") ? config("crontab.safe_key") : $safeKey;
    }

    public function index($query)
    {
        return $this->httpRequest(HttpCrontab::INDEX_PATH . '?' . $query);
    }

    public function add($post)
    {
        return $this->httpRequest(HttpCrontab::ADD_PATH, 'POST', $post);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $form
     * @return array
     * @throws GuzzleException
     */
    public function httpRequest($url, $method = 'GET', array $form = [])
    {
        try {
            $client = new Client([
                'base_uri' => $this->baseUri,
                'headers' => [
                    'key' => $this->safeKey
                ]
            ]);
            $response = $client->request($method, $url, ['form_params' => $form]);
            $data = [
                'ok' => true,
                'data' => json_decode($response->getBody()->getContents(), true)['data'],
                'msg' => 'success',
            ];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $msg = json_decode($e->getResponse()->getBody()->getContents(), true)['msg'];
            } else {
                $msg = $e->getMessage();
            }
            $data = [
                'ok' => false,
                'data' => [],
                'msg' => $msg
            ];
        }

        return $data;
    }
}