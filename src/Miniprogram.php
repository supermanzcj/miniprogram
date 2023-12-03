<?php

namespace Superzc\Miniprogram;

// use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Miniprogram
{
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

        $response = Http::post("https://api.weixin.qq.com/cgi-bin/stable_token", $postData);

        return $this->processResponse($response);
    }

    /**
     * 小程序登录
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
     */
    public function code2Session($code)
    {
        $response = Http::get("https://api.weixin.qq.com/sns/jscode2session?appid=" . $this->appid . "&secret=" . $this->appsecret . "&js_code=" . $code . "&grant_type=authorization_code");

        return $this->processResponse($response);
    }

    /**
     * 检验登录态
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/checkSessionKey.html
     */
    public function checkSessionKey()
    {
        $access_token = $this->requestAccessToken();

        $response = Http::get("https://api.weixin.qq.com/wxa/checksession?access_token=" . $access_token);

        return $this->processResponse($response);
    }

    /**
     * 获取用户encryptKey
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/internet/getUserEncryptKey.html
     */
    public function getUserEncryptKey($openid, $session_key)
    {
        
        $signature = hash_hmac('sha256', '', $session_key);

        $postData = [
            'openid' => $openid,
            'signature' => $signature,
            'sig_method' => 'hmac_sha256',
        ];

        $access_token = $this->requestAccessToken();

        $response = Http::get("https://api.weixin.qq.com/wxa/business/getuserencryptkey?access_token=" . $access_token . "&openid=" . $openid . "&signature=" . $signature . "&sig_method=hmac_sha256");

        return $this->processResponse($response);
    }

    /**
     * 获取手机号
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/phone-number/getPhoneNumber.html
     */
    public function getPhoneNumber($code, $openid = '')
    {
        $postData = [
            'code' => $code,
            'openid' => $openid,
        ];

        $access_token = $this->requestAccessToken();

        $response = Http::post("https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=" . $access_token, $postData);

        return $this->processResponse($response);
    }

    /**
     * 加密数据
     */
    public function encryptData($data)
    {
        require_once("xxtea.php");

        $result = $this->getUserEncryptKey($openid, $session_key);
        
        $version = '';
        $create_time = '';
        $encrypt_str = '';

        if (isset($result['errcode']) && $result['errcode'] === 0) {
            $keyInfoList = $result['key_info_list'];
            $encrypt_key = $keyInfoList[0]['encrypt_key'];
            $version = $keyInfoList[0]['version'];

            $encrypt_str = xxtea_encrypt(json_encode($data), $encrypt_key);
            $encrypt_str = base64_encode($encrypt_str);
        }

        return [
            'version' => $version,
            'encrypt_str' => $encrypt_str,
        ];
    }

    /**
     * 解密数据
     */
    public function decryptData($encrypt_str, $version)
    {
        require_once("xxtea.php");

        $result = $this->getUserEncryptKey($openid, $session_key);
        
        $encrypt_key = '';
        $data = [];
        if (isset($result['errcode']) && $result['errcode'] === 0) {
            foreach ($result['key_info_list'] as $key=>$value) {
                if ($value['version'] == $version && $value['expire_in'] > 0) {
                    $encrypt_key = $value['encrypt_key'];
                    break;
                }
            }

            if ($encrypt_key) {
                $encrypt_str = base64_decode($encrypt_str);
                $data = xxtea_decrypt($encrypt_str, $encrypt_key);
            } else {
                return false;
            }
        } else {
            return false;
        }

        return $data;
    }

    /**
     * 解密数据
     *
     */
    public function decryptData() {}

    /**
     * 获取登录调用凭据
     */
    private function requestAccessToken()
    {
        $access_token = "";

        // 获取接口调用凭据
        $accessTokenRet = $this->getStableAccessToken();
        Log::info("requestAccessToken: ", [$accessTokenRet]);
        if ($accessTokenRet !== false) {
            $access_token = $accessTokenRet['access_token'];
        }
        Log::info("access_token: ", [$access_token]);

        return $access_token;
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
