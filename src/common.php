<?php
namespace merchpay;

class common{

    /**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
    }
    
    /**
	 * 格式化参数格式化成url参数
	 */
	public static function ToUrlParams($data)
	{
		$buff = "";
		foreach ($data as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
    }

    /**
	 * 输出xml字符
	 * @throws WxPayException
	**/
	public static function array2xml($data)
	{
		if(!is_array($data) 
			|| count($data) <= 0)
		{
    		throw new WxPayException("数组数据异常！");
    	}
    	
    	$xml = "<xml>";
    	foreach ($data as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
    }
    
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
	public static function xml2array($xml)
	{	
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $data;
	}

    //获取用户真实IP 
    static function getIp() { 
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
            $ip = getenv("HTTP_CLIENT_IP"); 
        else 
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
                $ip = getenv("HTTP_X_FORWARDED_FOR"); 
            else 
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
                    $ip = getenv("REMOTE_ADDR"); 
                else 
                    if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
                        $ip = $_SERVER['REMOTE_ADDR']; 
                    else 
                        $ip = "unknown"; 
        return ($ip); 
    }

    /** 
     * 企业付款发起请求 
     * 此函数来自:https://pay.weixin.qq.com/wiki/doc/api/download/cert.zip 
     */  
    static function curl_post_ssl($url, $xmldata, $second=30,$aHeader=array(),$sslcert_path,$sslkey_path){  
        $ch = curl_init();  
        //超时时间  
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);  
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);  
        //这里设置代理，如果有的话  
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');  
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);  
        curl_setopt($ch,CURLOPT_URL,$url);  
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);  
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);  
        
        //以下两种方式需选择一种  
        
        //第一种方法，cert 与 key 分别属于两个.pem文件  
        //默认格式为PEM，可以注释  
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');  
        curl_setopt($ch,CURLOPT_SSLCERT,$sslcert_path);  
        //默认格式为PEM，可以注释  
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');  
        curl_setopt($ch,CURLOPT_SSLKEY,$sslkey_path);  
        
        //第二种方式，两个文件合成一个.pem文件  
        //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');  
    
        if( count($aHeader) >= 1 ){  
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);  
        }  
    
        curl_setopt($ch,CURLOPT_POST, 1);  
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xmldata);  
        $data = curl_exec($ch);  
        if($data){  
            curl_close($ch);  
            return $data;  
        }  
        else {   
            $error = curl_errno($ch);  
            echo "call faild, errorCode:$error\n";   
            curl_close($ch);  
            return false;  
        }  
    }  
     
}