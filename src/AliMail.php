<?php
/**
 * Created by PhpStorm.
 * User: guojianhang
 * Date: 6/13/18
 * Time: 19:23
 */

namespace Ccb\AliMail;
include_once __DIR__.'/./Sdk/aliyun-php-sdk-core/Config.php';
//include_once __DIR__."./Sdk/aliyun-php-sdk-core/Config.php";
//use ClientException;
//use DefaultAcsClient;
//use DefaultProfile;
use DefaultAcsClient;
use DefaultProfile;
use Dm\Request\V20151123 as Dm;
use Illuminate\Mail\Transport\Transport;
//use ServerException;
use Log;
use Swift_Mime_SimpleMessage;

class AliMail extends Transport
{
    private function sendMail($key,$secret,Swift_Mime_SimpleMessage $swift_message,$mail) {
//        dd(__DIR__.'./Sdk/aliyun-php-sdk-core/Config.php');
        //需要设置对应的region名称，如华东1（杭州）设为cn-hangzhou，新加坡Region设为ap-southeast-1，澳洲Region设为ap-southeast-2。
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $key, $secret);
        //新加坡或澳洲region需要设置服务器地址，华东1（杭州）不需要设置。
        //$iClientProfile::addEndpoint("ap-southeast-1","ap-southeast-1","Dm","dm.ap-southeast-1.aliyuncs.com");
        //$iClientProfile::addEndpoint("ap-southeast-2","ap-southeast-2","Dm","dm.ap-southeast-2.aliyuncs.com");
        $client = new DefaultAcsClient($iClientProfile);
        $request = new Dm\SingleSendMailRequest();
        //新加坡或澳洲region需要设置SDK的版本，华东1（杭州）不需要设置。
        //$request->setVersion("2017-06-22");
        foreach ($swift_message->getFrom() as $fro=>$n){
            $request->setAccountName($fro);
            $request->setFromAlias($n);
        }
        $request->setAddressType(1);
        $request->setTagName("verify");
        $request->setReplyToAddress("false");
        $request->setToAddress($mail->getTo($swift_message));
        $request->setSubject($swift_message->getSubject());
//    $mail = new \App\Mail\RegisterMail("123456");

//    dd($swift_message->getFrom(),$swift_message->getSubject());
        $request->setHtmlBody($swift_message->getBody());
//    dd($request);
        try {
            $response = $client->getAcsResponse($request);
            Log::debug('发送成功',(array)$response);
        }
        catch (ClientException  $e) {
//        print_r($e->getErrorCode());
//        print_r($e->getErrorMessage());
            Log::debug($e);
        }
        catch (ServerException  $e) {
//        print_r($e->getErrorCode());
//        print_r($e->getErrorMessage());
            Log::debug($e);
        }
    }
    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param string[]                 $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(
        Swift_Mime_SimpleMessage $message,
        &$failedRecipients = null
    ) {
        // TODO: Implement send() method.
        $this->sendMail(
            config("ali-mail.ali-mail.key"),
            config("ali-mail.ali-mail.secret"),
            $message,$this);
    }

    public function getTo(Swift_Mime_SimpleMessage $message)
    {
        $email = '';
        if (is_null($message->getTo())){
            $toList = [];
        }else{
            $toList  = array_keys($message->getTo());
        }
        if (is_null($message->getBcc())){
            $bccList = [];
        }else{
            $bccList  = array_keys($message->getBcc());
        }
        if (is_null($message->getCc())){
            $ccList = [];
        }else{
            $ccList  = array_keys($message->getCc());
        }

        $list = [
            implode(',',$toList),
            implode(',',$ccList),
            implode(',',$bccList)
        ];
        return $email.implode(',',$list);
    }
    public function __construct()
    {

    }
}
