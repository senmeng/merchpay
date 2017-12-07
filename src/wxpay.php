<?php
namespace merchpay;

use merchpay\common;

class wxpay{
 
    private $check_name = 'FORCE_CHECK';
    private $error_code = '';
    private $error_msg = '';

    //__set()方法用来设置私有属性 
    public function __set($name,$value){ 
        $this->$name = $value; 
    } 

    //__get()方法用来获取私有属性 
    public function __get($name){ 
        return $this->$name; 
    } 

    /** 
     * 企业支付（零钱） 
     * @param string $openid    用户openID 
     * @param string $trade_no  单号 
     * @param string $money     金额 
     * @param string $desc      描述 
     * @return string   XML 结构的字符串 
     */  
    public function pay($openid='',$trade_no='',$money='',$desc=''){
        
        $data = array(  
            'mch_appid' => $this->appid,  
            'mchid'     => $this->mchid,  
            'nonce_str' => common::getNonceStr(16), 
            'partner_trade_no' => $trade_no, //商户订单号，需要唯一  
            'openid'    => $openid,  
            'check_name'=> $this->check_name, //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：  
            're_user_name' => $this->real_name, //收款人用户姓名  
            'amount'    => $money * 100, //付款金额单位为分  
            'desc'      => $desc,  
            'spbill_create_ip' => common::getIp()  
        );  
        
        //生成签名  
        $data['sign'] = common::MakeSign($data,$this->key); 
      
        //构造XML数据  
        $xmldata = common::array2xml($data);  
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';  
        //发送post请求  
        $res = common::curl_post_ssl($url, $xmldata,$second=30,[],$this->sslcert_path,$this->sslkey_path);  
        if(!$res){  
            return ['code'=>403, 'msg'=>"Can't connect the server"];  
        }  

        //付款结果分析  
        $content = common::xml2array($res);  
        
        if($content['return_code'] == 'FAIL'){  
            return ['return_code'=>$content['return_code'], 'msg'=>$content['return_msg'],'err_code'=>$content['err_code'],'err_code_des'=>$content['err_code_des']];  
        }  
        if($content['result_code'] == 'FAIL'){  
            return ['return_code'=>$content['err_code'], 'msg'=>$content['err_code_des'],'err_code'=>$content['err_code'],'err_code_des'=>$content['err_code_des']];  
        }  
        $resdata = array(  
            'return_code'      => $content['return_code'],  
            'result_code'      => $content['result_code'],  
            'err_code'         => $content['err_code'],
            'err_code_des'     => $content['err_code_des'],
            'partner_trade_no' => $content['partner_trade_no'],  
            'payment_no'       => $content['payment_no'],  
            'payment_time'     => $content['payment_time'],  
        );  
        return $resdata;  

    }

}