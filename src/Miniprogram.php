<?php

namespace Superzc\Miniprogram;

// use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Http;

class Miniprogram {

    protected $appid;
    protected $appsecret;

    /**
     * 构造方法
     */
    public function __construct($config)
    {
        $this->appid = $config->get('miniprogram.wechat.appid');
        $this->appsecret = $config->get('miniprogram.wechat.appsecret');
    }

    /**
     * 获取接口调用凭据
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-access-token/getAccessToken.html
     */
    public function getAccessToken()
    {
        $response = Http::get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->appsecret);

        return $this->processResponse($response);
    }

    /**
     * 获取稳定版接口调用凭据
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-access-token/getStableAccessToken.html
     */
    public function getStableAccessToken($force_refresh = false)
    {
        $postData = [
            'grant_type' => 'client_credential',
            'appid' => $this->appid,
            'secret' => $this->appsecret,
            'force_refresh' => $force_refresh,
        ];

        $response = Http::post("https://api.weixin.qq.com/cgi-bin/stable_token", json_encode($postData));

        return $this->processResponse($response);
    }
    
    /**
     * 小程序登录
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
     */
    public function codeToSession($code)
    {
        $response = Http::get("https://api.weixin.qq.com/sns/jscode2session?appid=" . $this->appid . "&secret=" . $this->appsecret . "&js_code=" . $code . "&grant_type=authorization_code");

        return $this->processResponse($response);
    }

    /**
     * 处理响应
     */
    private function processResponse($response)
    {
        $data = [];

        if ($response->successful()) {
            $data = $response->json();
        } elseif ($response->failed()) {
            // 请求失败的处理逻辑
            return false;
        } elseif ($response->clientError()) {
            // 客户端错误 4xx 的处理逻辑
            return false;
        } elseif ($response->serverError()) {
            // 服务器错误 5xx 的处理逻辑
            return false;
        }

        return $data;
    }
 
}