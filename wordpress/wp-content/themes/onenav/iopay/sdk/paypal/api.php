<?php

class PayPal
{
    var $version = 87.0;

    var $endpoint;
    var $user;
    var $pass;
    var $sig;
    var $subject = '';
    var $auth_token= '';
    var $auth_signature='';
    var $auth_timestamp='';

    /** 代理 */
    var $use_proxy = false; 
    var $proxy_host = '127.0.0.1'; 
    var $proxy_port = '80'; 
    /** 代理 end */

    public function __construct($user, $pass, $signature, $sandbox = false)
    {
        $this->user       = $user;
        $this->pass       = $pass;
        $this->sig        = $signature;
        $this->endpoint   = ($sandbox) ? "https://api-3t.sandbox.paypal.com/nvp" : "https://api-3t.paypal.com/nvp";
    }

    private function EncodeNvpString($fields)
    {
        $nvpstr = "";
        foreach ($fields as $key => $value) {
            $nvpstr .= sprintf("%s=%s&", urlencode(strtoupper($key)), urlencode($value));
        }
        return $nvpstr;
    }

    private function DecodeNvpString($nvpstr)
    {
        $pairs = explode("&", $nvpstr);
        $fields = array();
        foreach ($pairs as $pair) {
            $items                        = explode("=", $pair);
            $fields[urldecode($items[0])] = urldecode($items[1]);
        }
        return $fields;
    }

    /**
     * Summary of hash_call
     * @param mixed $methodName
     * @param mixed $nvpStr
     * @return array
     */
    private function hash_call($methodName, $nvpStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $nvpheader = $this->nvpHeader();
        $nvpStr = $this->EncodeNvpString($nvpStr);

        if (!empty($this->auth_token) && !empty($this->auth_signature) && !empty($this->auth_timestamp)) {
            $headers_array   = array();
            $headers_array[] = "X-PP-AUTHORIZATION: " . $nvpheader;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
            curl_setopt($ch, CURLOPT_HEADER, false);
        } else {
            $nvpStr = $nvpheader . '&' . $nvpStr;
        }
        if ($this->use_proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy_host . ":" . $this->proxy_port);
        }
        if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
            $nvpStr = "&VERSION=" . urlencode($this->version) . $nvpStr;
        }
        $nvpreq = "METHOD=" . urlencode($methodName) . $nvpStr;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return array('error' => 1, 'msg' => curl_error($ch), 'code' => curl_errno($ch));
        }
        $nvpResArray = $this->DecodeNvpString($response);
        curl_close($ch);
        return $nvpResArray;
    }
    /**
     * Summary of nvpHeader
     * @return string
     */
    private function nvpHeader(){
        $nvpHeaderStr = "";
        if ((!empty($this->user)) && (!empty($this->pass)) && (!empty($this->sig)) && (!empty($this->subject))) {
            $AuthMode = "THIRDPARTY";
        } else if ((!empty($this->user)) && (!empty($this->pass)) && (!empty($this->sig))) {
            $AuthMode = "3TOKEN";
        } elseif (!empty($this->auth_token) && !empty($this->auth_signature) && !empty($this->auth_timestamp)) {
            $AuthMode = "PERMISSION";
        } elseif (!empty($this->subject)) {
            $AuthMode = "FIRSTPARTY";
        }
        switch ($AuthMode) {
            case "3TOKEN":
                $nvpHeaderStr = "&PWD=" . urlencode($this->pass) . "&USER=" . urlencode($this->user) . "&SIGNATURE=" . urlencode($this->sig);
                break;
            case "FIRSTPARTY":
                $nvpHeaderStr = "&SUBJECT=" . urlencode($this->subject);
                break;
            case "THIRDPARTY":
                $nvpHeaderStr = "&PWD=" . urlencode($this->pass) . "&USER=" . urlencode($this->user) . "&SIGNATURE=" . urlencode($this->sig) . "&SUBJECT=" . urlencode($this->subject);
                break;
            case "PERMISSION":
                $nvpHeaderStr = $this->formAutorization($this->auth_token, $this->auth_signature, $this->auth_timestamp);
                break;
        }
        return $nvpHeaderStr;
    }

    private function formAutorization($auth_token, $auth_signature, $auth_timestamp)
    {
        $authString = "token=$auth_token,signature=$auth_signature,timestamp=$auth_timestamp";
        return $authString;
    }

    //Implicit convenience functions helper
    //Calling $PayPal->MethodName($fields) automatically translates to an NVP call
    public function __call($method, $fields)
    {
        return $this->hash_call($method, $fields);
    }
}