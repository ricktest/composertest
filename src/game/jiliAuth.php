<?php

namespace game;

class JiliAuth
{

    var $sandbox = 0;
    var $demo    = 0;

    var $currency = "";
    var $prefix   = ""; //"win";

    /*Sandbox*/
    var $api_url_sandbox = 'https://uat-wb-api.jlfafafa2.com/api1/';
    var $agent_id_sandbox = 'worthysky_seamless';
    var $agent_key_sandbox = '608fea358e6d60a0a5ec1f9801aa7ba1952bd399';

    /*Prodution*/
    var $api_url   = ""; //'https://wb-api.jlfafafa2.com/api1/';
    var $agent_id  = ''; //'worthysky_seamless';
    var $agent_key = ''; //'7e7b5d1fefe23275a202e8ac4c43070c1759ab83';

    // 构造函数
    public function __construct($key)
    {
        //$key = json_decode($key,true);
        $this->currency  = $key['currency'];
        $this->prefix    = $key['prefix'];
        $this->api_url   = $key['api_url'];
        $this->agent_id  = $key['agent_id'];
        $this->agent_key = $key['agent_key'];
    }

    private function getRequestCurl($raw_data = NULL, $action = NULL, $method = 'POST', $isjson = 0)
    {

        $config['sandbox']     = $this->sandbox;
        $config['demo']        = $this->demo;

        if ($config['sandbox']) {
            $config['api_url'] = $this->api_url_sandbox;
            $config['agent_id'] = $this->agent_id_sandbox;
            $config['agent_key'] = $this->agent_key_sandbox;
        } else {
            $config['api_url'] = $this->api_url;
            $config['agent_id'] = $this->agent_id;
            $config['agent_key'] = $this->agent_key;
        }


        $url = $config['api_url'] . $action;


        //print_r ($url);echo '<br>';

        if ($isjson == 1) {
            $data = json_encode($raw_data);
            $content_type = 'application/json';
        } else {
            $data = $raw_data;
            $content_type = 'application/x-www-form-urlencoded';
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); //https請求 不驗證證書和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); //從證書中檢查SSL加密演算法是否存在(預設不需要驗證）

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: " . $content_type . ";charset=utf-8",
            "Accept: application/json",
        ));

        $response = curl_exec($curl); //echo $response;
        $err = curl_error($curl);

        curl_close($curl);

        if ($this->demo) {
            echo '/*--getRequestCurlResult--*/<br>';
            if ($raw_data != NULL) {
                echo 'Parameters: ' . $data . '<br>';
            }
            echo 'Api Url: '    . $url .  '<br>';
            echo '/*------------------------*/<br>';
            echo '<br>';
        }

        if ($err) {
            echo "cURL Error #:" . $err;
            exit;
        } else {
            return json_decode($response, true);
            //return $response;
        }
    }

    #【取得当前毫秒时间戳】
    public function getUnixTimestamp()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    #【隨機字串】
    private function getRandomStr()
    {
        $str = "qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM";
        str_shuffle($str);
        $new_str = substr(str_shuffle($str), 0, 6);
        return $new_str;
    }

    #【隨機數】
    private function getRandomInt()
    {
        $int = "1234567890";
        str_shuffle($int);
        $new_int = (int)substr(str_shuffle($int), 0, 6);
        return $new_int;
    }

    #【環境設定（正式or測試）】1為測試環境 0 為正式
    public function getEnvironment()
    {

        $config['sandbox'] = $this->sandbox;

        if ($config['sandbox'] == 1 || $config['sandbox'] == 0) {
            if ($config['sandbox']) {
                $config['api_url'] = $this->api_url_sandbox;
                $config['agent_id'] = $this->agent_id_sandbox;
                $config['agent_key'] = $this->agent_key_sandbox;
            } else {
                $config['api_url'] = $this->api_url;
                $config['agent_id'] = $this->agent_id;
                $config['agent_key'] = $this->agent_key;
            }
        } else {
            $config = array(
                'errorCode' => 5,
                'message' => 'Sandbox number not equal to 1 and 0 ',
            );
        }

        return $config;
    }

    #【台灣時間 轉 UTC-4時間】
    public function getTime()
    {

        $tw_time = time();
        $test_time = date('Y-m-d', $tw_time) . date(' H:i:s', $tw_time);
        $now_time = date('Y-m-d', $tw_time - 43200) . 'T' . date('H:i:s', $tw_time - 43200) . '-04:00';
        $tf_time = date("ymj", $tw_time - 43200);
        $utc_Unixt = $tw_time - 43200;

        $params = array(
            "tw_UnixtTime" => $tw_time,
            "tw_Time" => $test_time,
            "utc-4_time" => $now_time,
            "tf_time" => $tf_time,
            "utc_Unixt" => $utc_Unixt,
        );
        return $params;
    }

    #【1.1.3 產生key】
    public function getKey()
    {

        $config = $this->getEnvironment();
        $now_time = $this->getTime();

        $keyG = MD5($now_time['tf_time'] . $config['agent_id'] . $config['agent_key']);

        return $keyG;
    }

    #【3.1.6 取得游戏列表】
    public function gameList()
    {

        $config = $this->getEnvironment();

        $keyG = $this->getKey();

        $params = array(
            "AgentId" => $config['agent_id'],
        );

        $data = http_build_query($params);

        $md5string = MD5($data . $keyG);

        $key =  $this->getRandomInt() . $md5string . $this->getRandomStr();

        $action = "GetGameList" . "?" . "AgentId=" . $config['agent_id'] . "&Key=" . $key;

        $result = $this->getRequestCurl($data, $action, 'POST', 0);

        return $result;
    }

    #【2.1.1 登入】
    public function login($Token, $GameId, $Lang)
    {

        $config = $this->getEnvironment();

        $keyG = $this->getKey();

        $params = array(
            "Token" => $Token, //营运商 api 玩家access token
            "GameId" => $GameId, //游戏唯一识别值
            "Lang" => $Lang, //UI语系:简中 zh-CN, 繁中 zh-TW, 英语 en-US, 泰文 th-TH, 印度尼西亚 id-ID, 越南 vi-VN, 印度 hi-IN, 坦米尔文 ta-IN
            "AgentId" => $config['agent_id']
        );

        $data = http_build_query($params);

        $md5string = MD5($data . $keyG);

        $key =  $this->getRandomInt() . $md5string . $this->getRandomStr();

        $action = "singleWallet/Login" . "?" . $data . "&Key=" . $key;

        $result = $this->getRequestCurl($data, $action, 'POST', 0);

        return $result;
    }

    #【2.1.2 回传登入网址】
    public function loginURL($Token, $GameId, $Lang, $isJPEnabled)
    {

        $config = $this->getEnvironment();

        $keyG = $this->getKey();
        //print_r($keyG);
        //"isJPEnabled"=>$isJPEnabled,//是請帶入1，否則帶入0
        $params = array(
            "Token" => $Token, //营运商 api 玩家access token
            "GameId" => $GameId, //游戏唯一识别值
            "Lang" => $Lang, //UI语系:简中 zh-CN, 繁中 zh-TW, 英语 en-US, 泰文 th-TH, 印度尼西亚 id-ID, 越南 vi-VN, 印度 hi-IN, 坦米尔文 ta-IN
            "AgentId" => $config['agent_id']
        );

        $data = http_build_query($params);

        $md5string = MD5($data . $keyG);

        $key =  $this->getRandomStr() . $md5string . $this->getRandomStr();

        $jp = array(
            "isJPEnabled" => $isJPEnabled
        );

        $result = array_merge($params, $jp);

        $data2 = http_build_query($result);

        $action = "singleWallet/LoginWithoutRedirect" . "?" . $data2 . "&Key=" . $key;

        //echo $keyG;echo '<br>';
        //echo $this->getRandomStr();echo '<br>';
        //echo $this->getRandomStr();echo '<br>';		
        //$action = "singleWallet/LoginWithoutRedirect"."?".$data."&isJPEnabled=".$isJPEnabled."&Key=".$key;


        $result = $this->getRequestCurl($data2, $action, 'POST', 0);

        return $result;
    }
}
