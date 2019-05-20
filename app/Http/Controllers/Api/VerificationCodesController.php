<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use App\Http\Requests\Api\VerificationCodeRequest;
use function GuzzleHttp\json_decode;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {

        $phone = $request->phone;

        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            //生成4位随机数,左侧补0
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            $templateId = 334564;

            try {
                // $request = $easySms->send($phone, [
                //     'content' => "【Lbbs社区】您的验证码是{$code}。如非本人操作，请忽略本短信"
                // ]);
                $request = $easySms->send($phone, ['template' => $templateId, 'data' => [$code, 10,],]);
            } catch (\GuzzleHttp\Exception\ClientException $exception) {
                $response = $exception->getResponse();
                $result = json_decode($response->getBody()->getContents(), true);
                return $this->response->errorInternal($request['msg'] ?? '短信发送异常');
            }
        }



        $key = 'verificationCode_' . str_random(15);
        $expireAt = now()->addMinutes(10);
        //缓存验证码10分钟过期
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expireAt);

        return $this->response->array([
            'key' => $key,
            'expired_at' => $expireAt->toDateTimeString(),
        ])->setStatusCode(201);
    }
}
