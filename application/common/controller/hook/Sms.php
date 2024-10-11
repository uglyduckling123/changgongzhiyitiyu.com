<?php
/**
 * hook钩子触发短信发送
 */
namespace app\common\controller\hook;

//阿里云短信发送相关类 composer安装 
//composer require alibabacloud/sdk
//use Swoft\Task\Bean\Annotation\Task;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use think\Log;

class Sms
{

	//发送验证码
	//@sms  验证码发送记录数数据库实例
	//$sms->mobile 手机号码
	//['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]
    function smsSend($sms)
    {	
    	return $this->sendMsg($sms->mobile,['code'=>$sms->code],'code');
    }

    /*function smsCheck($sms)
    {	
    	return $this->sendMsg($sms->mobile,['code'=>$sms->code],'code');
    }*/


    //@mobile 手机号码
    //@param  短信发送参数
    //@template 短信发送模板编号key值
    private function sendMsg($mobile,$param,$template){
    	$config = \config('alisms');
    	if(empty($param)||!isset($config)||empty($config)){
    		return false;
    	}
        if(!$config['is_send']){
            return true;
        }
        AlibabaCloud::accessKeyClient($config['accessKeyId'], $config['accessSecret'])
            ->regionId($config['regionId'])
            ->asGlobalClient();

        $TemplateCode = isset($config['TemplateCode'][$template])?$config['TemplateCode'][$template]:$config['TemplateCode']['code'];

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $mobile,
                        'SignName' => $config['SignName'],
                        'TemplateCode' => $TemplateCode,
                        'TemplateParam' => json_encode($param)
                    ],
                ])
                ->request();
            Log::info('发送短信:'.json_encode($result));
            if($result->Code == "OK"){
	            return true;
	        }else{
	            return false;
	        } 
        } catch (ClientException $e) {
            Log::info('发送短信:'.$e->getErrorMessage());
        	return false;
        } catch (ServerException $e) {
            Log::info('发送短信:'.$e->getErrorMessage());
        	return false;
        }
    }

}
