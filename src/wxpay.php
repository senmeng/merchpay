<?php
namespace merchpay;

use merchpay\common;

class wxpay{
    
    private $appid = '';
    private $mchid = '';
    private $key = '';
    private $appsecret = '';
    private $check_name = 'FORCE_CHECK';
    private $real_name = '';//真实姓名

    public function __construct($appid,$mchid){

        $this->appid = $appid;
        $this->mchid = $mchid;
    }

    //__set()方法用来设置私有属性 
    public function __set($name,$value){ 
        $this->$name = $value; 
    } 

    //__get()方法用来获取私有属性 
    public function __get($name){ 
        return $this->$name; 
    } 

    /** 
     * 企业支付 
     * @param string $openid    用户openID 
     * @param string $trade_no  单号 
     * @param string $money     金额 
     * @param string $desc      描述 
     * @return string   XML 结构的字符串 
     */  
    public function pay($openid,$trade_no,$money,$desc){
       
        $data = array(  
            'mch_appid' => $this->appid,  
            'mchid'     => $this->mchid,  
            'nonce_str' => common::getNonceStr(16),
            //'device_info' => '1000',  
            'partner_trade_no' => $trade_no, //商户订单号，需要唯一  
            'openid'    => $openid,  
            'check_name'=> $this->check_name, //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：  
            're_user_name' => $this->real_name, //收款人用户姓名  
            'amount'    => $money * 100, //付款金额单位为分  
            'desc'      => $desc,  
            'spbill_create_ip' => common::getIp()  
        );  
         
        //生成签名  
        $data['sign'] = $this->MakeSign($data);  
        //构造XML数据  
        $xmldata = common::array2xml($data);  
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';  
        //发送post请求  
        $res = common::curl_post_ssl($url, $xmldata);  
        if(!$res){  
            return array('status'=>1, 'msg'=>"Can't connect the server" );  
        }  
        // 这句file_put_contents是用来查看服务器返回的结果 测试完可以删除了  
        file_put_contents(APP_ROOT.'/Api/wxpay/logs/log2.txt',$res,FILE_APPEND);  
          
        //付款结果分析  
        $content = self::xml2array($res);  
        if(strval($content['return_code']) == 'FAIL'){  
            return array('status'=>1, 'msg'=>strval($content['return_msg']));  
        }  
        if(strval($content['result_code']) == 'FAIL'){  
            return array('status'=>1, 'msg'=>strval($content['err_code']),':'.strval($content['err_code_des']));  
        }  
        $resdata = array(  
            'return_code'      => strval($content['return_code']),  
            'result_code'      => strval($content['result_code']),  
            'nonce_str'        => strval($content['nonce_str']),  
            'partner_trade_no' => strval($content['partner_trade_no']),  
            'payment_no'       => strval($content['payment_no']),  
            'payment_time'     => strval($content['payment_time']),  
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