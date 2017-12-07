<?php
namespace merchpay;

use merchpay\common;

class bouns{
    
    private $appid = '';
    private $mchid = '';
    private $key = '';
    private $appsecret = '';
    private $send_name = ''; 
    private $total_num = 1;
    private $wishing = '';
    private $scene_id = 0;
    private $sslcert_path = '';
    private $sslkey_path = '';

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
    public function bouns($openid='',$trade_no='',$money='',$desc=''){
        
        if(empty($openid) || empty($trade_no) || empty($money)){
            return false;
        }
        $data = array(  
            'wxappid' => $this->appid,  
            'mch_id'     => $this->mchid,  
            'nonce_str' => common::getNonceStr(16),
            'send_name' => $this->send_name,  
            'mch_billno' => $trade_no, //商户订单号，需要唯一  
            're_openid'    => $openid,  
            'check_name'=> $this->check_name, //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：  
            'act_name' => $this->act_name, //活动名称  
            'total_amount'    => $money * 100, //付款金额单位为分  
            'total_num'    => $this->total_num,
            'remark'      => $desc,  
            'scene_id' => $scene_id,
            'client_ip' => common::getIp()  
        );  
        
        //生成签名  
        $data['sign'] = $this->MakeSign($data); 
      
        //构造XML数据  
        $xmldata = common::array2xml($data);  
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';  
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
            'nonce_str'        => $content['nonce_str'],  
            'partner_trade_no' => $content['partner_trade_no'],  
            'payment_no'       => $content['payment_no'],  
            'payment_time'     => $content['payment_time'],  
        );  
        return $resdata;  

    }

    /**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign($data)
	{
		//签名步骤一：按字典序排序参数
		ksort($data);
        $string = common::ToUrlParams($data);
        
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
}