<?php

namespace Superzc\Miniprogram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Superzc\Miniprogram\Exceptions\DefaultException;
use Superzc\Miniprogram\Constants\ErrorCodes;

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
        // 校验参数
        if ($this->appid == '') {
            throw new DefaultException('缺少配置appid', ErrorCodes::INVALID_PARAMS);
        }
        if ($this->appsecret == '') {
            throw new DefaultException('缺少配置appsecret', ErrorCodes::INVALID_PARAMS);
        }

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
        // 校验参数
        if ($code == '') {
            throw new DefaultException('缺少参数code', ErrorCodes::INVALID_PARAMS);
        }

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
        // 校验参数
        if ($openid == '') {
            throw new DefaultException('缺少参数openid', ErrorCodes::INVALID_PARAMS);
        }
        if ($session_key == '') {
            throw new DefaultException('缺少参数session_key', ErrorCodes::INVALID_PARAMS);
        }

        $signature = hash_hmac('sha256', '', $session_key);

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
        // 校验参数
        if ($code == '') {
            throw new DefaultException('缺少参数code', ErrorCodes::INVALID_PARAMS);
        }

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
     *
     * @param openid string
     * @param session_key string
     * @param data array
     *
     * @return array
     */
    public function encryptData($openid, $session_key, $data)
    {
        require_once("xxtea.php");

        $result = $this->getUserEncryptKey($openid, $session_key);

        $version = '';
        $encrypt_str = '';

        if (isset($result['errcode']) && $result['errcode'] === 0) {
            $keyInfoList = $result['key_info_list'];
            $encrypt_key = $keyInfoList[0]['encrypt_key'];
            $version = $keyInfoList[0]['version'];

            $encrypt_str = xxtea_encrypt(json_encode($data, JSON_UNESCAPED_UNICODE), $encrypt_key);
            $encrypt_str = base64_encode($encrypt_str);
        } else {
            throw new DefaultException('获取用户encryptKey失败', ErrorCodes::ERROR);
        }

        return [
            'version' => $version,
            'encrypt_str' => $encrypt_str,
        ];
    }

    /**
     * 解密数据
     *
     * @param openid string
     * @param session_key string
     * @param version int
     * @param encrypt_str string
     *
     * @return array
     */
    public function decryptData($openid, $session_key, $version, $encrypt_str)
    {
        require_once("xxtea.php");

        $result = $this->getUserEncryptKey($openid, $session_key);

        $encrypt_key = '';
        $data = [];
        if (isset($result['errcode']) && $result['errcode'] === 0) {
            foreach ($result['key_info_list'] as $key => $value) {
                if ($value['version'] == $version && $value['expire_in'] > 0) {
                    $encrypt_key = $value['encrypt_key'];
                    break;
                }
            }

            if ($encrypt_key) {
                $encrypt_str = base64_decode($encrypt_str);
                $data = xxtea_decrypt($encrypt_str, $encrypt_key);
                $data = json_decode($data, true);
            } else {
                throw new DefaultException("用户encryptKey已失效", ErrorCodes::ERROR);
            }
        } else {
            throw new DefaultException("获取用户encryptKey失败", ErrorCodes::ERROR);
        }

        return $data;
    }

    /**
     * 文本内容安全识别
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/sec-center/sec-check/msgSecCheck.html
     * result.suggest: 建议，有risky、pass、review三种值
     * result.label: 命中标签枚举值，100 正常；10001 广告；20001 时政；20002 色情；20003 辱骂；20006 违法犯罪；20008 欺诈；20012 低俗；20013 版权；21000 其他
     *
     * @param content string 需检测的文本内容，文本字数的上限为2500字，需使用UTF-8编码
     * @param scene string 场景枚举值（1 资料；2 评论；3 论坛；4 社交日志）
     * @param openid string 用户的openid（用户需在近两小时访问过小程序）
     * @param encrypt_str string
     *
     * @return array
     */
    public function msgSecCheck($content, $scene, $openid, $session_key, $title = '', $nickname = '')
    {
        // 校验参数
        if ($content == '') {
            throw new DefaultException('缺少参数content', ErrorCodes::INVALID_PARAMS);
        }
        if (!in_array($scene, ['1', '2', '3', '4'])) {
            throw new DefaultException('参数scene不合法', ErrorCodes::INVALID_PARAMS);
        }
        if ($openid == '') {
            throw new DefaultException('缺少参数openid', ErrorCodes::INVALID_PARAMS);
        }
        if ($session_key == '') {
            throw new DefaultException('缺少参数session_key', ErrorCodes::INVALID_PARAMS);
        }

        $postData = [
            'content' => $content,
            'version' => 2,
            'scene' => $scene,
            'openid' => $openid,
        ];

        // 非必填参数
        if ($title) {
            $postData['title'] = $title;
        }
        if ($nickname) {
            $postData['nickname'] = $nickname;
        }
        if ($scene == 1) {
            $signature = hash_hmac('sha256', '', $session_key);
            $postData['signature'] = $signature;
        }

        $access_token = $this->requestAccessToken();

        $response = Http::post("https://api.weixin.qq.com/wxa/msg_sec_check?access_token=" . $access_token, $postData);

        return $this->processResponse($response);
    }

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
            throw new DefaultException($response->failed(), ErrorCodes::SERVICE_UNAVAILABLE);
        } elseif ($response->clientError()) {
            // 客户端错误 4xx 的处理逻辑
            throw new DefaultException($response->clientError(), ErrorCodes::SERVICE_UNAVAILABLE);
        } elseif ($response->serverError()) {
            // 服务器错误 5xx 的处理逻辑
            throw new DefaultException($response->serverError(), ErrorCodes::SERVICE_UNAVAILABLE);
        }

        return $data;
    }

}
