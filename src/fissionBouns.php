<?php
namespace merchpay;

use merchpay\common;

class fissionBouns{
    
    private $total_num = 1;
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
     * 企业支付（红包） 
     * @param string $openid    用户openID 
     * @param string $trade_no  单号 
     * @param string $money     金额 
     * @param string $desc      描述 
     * @return string   XML 结构的字符串 
     */  
    public function pay($openid='',$trade_no='',$money='',$desc=''){
        
        $data = array(  
            'wxappid' => $this->appid,  
            'mch_id'     => $this->mchid,  
            'nonce_str' => common::getNonceStr(16),
            'send_name' => $this->send_name,  
            'mch_billno' => $trade_no, //商户订单号，需要唯一  
            're_openid'    => $openid,  
            'check_name'=> $this->check_name, //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：  
            'act_name' => $this->act_name, //活动名称  
            'amt_type' => $this->amt_type, //红包金额设置方式  
            'total_amount'    => $money * 100, //付款金额单位为分  
            'total_num'    => $this->total_num,
            'wishing' => $this->wishing,
            'remark'      => $desc,  
            'scene_id' => $scene_id,
            'client_ip' => common::getIp()  
        );  
        
        //生成签名  
        $data['sign'] = common::MakeSign($data,$this->key); 
      
        //构造XML数据  
        $xmldata = common::array2xml($data);  
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';  
        //发送post请求  
        $res = common::curl_post_ssl($url, $xmldata,$second=30,[],$this->sslcert_path,$this->sslkey_path);  
        if(!$res){  
            return ['code'=>403, 'msg'=>"Can't connect the server"];  
        }  

        //付款结果分析  
        $content = common::xml2array($res);  

        if($content['return_code'] == 'FAIL'){  
            return ['return_code'=>$content['return_code'], 'msg'=>$content['return_msg']];  
        }  
        if($content['result_code'] == 'FAIL'){  
            return ['return_code'=>$content['err_code'], 'msg'=>$content['err_code_des']];  
        }  
        $resdata = array(  
            'return_code'      => $content['return_code'],  
            'result_code'      => $content['result_code'], 
            'partner_trade_no' => $content['mch_billno'],  
            'payment_no'       => $content['send_listid'] 
        );  
        return $resdata;  

    }

}