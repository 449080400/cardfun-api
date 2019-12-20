<?php

function per_page($default = null)
{
    $max_per_page = config('api.max_per_page');
    $per_page = (Request::input('per_page') ?: $default) ?: config('api.default_per_page');

    return (int) ($per_page < $max_per_page ? $per_page : $max_per_page);
}

function secToTime($times) {
    $result = '00:00:00';
    if ($times > 0) {
        //$hour = floor($times / 3600);
        $minute = floor($times / 60);
        if (strlen($minute) == 1) {
            $minute = "0".$minute;
        }
        $second = floor(($times - (60 * $minute)) % 60);
        if (strlen($second) == 1) {
            $second = "0".$second;
        }
        $result = $minute . ':' . $second;
    }
    return $result;
}


function shiftVideoTime($str){
    $second = 0;
    $str = str_replace("：", ":", $str);

    $arr = explode(':', $str);
    if (count($arr)>3){
        return 0;
    }

    $y = 0;
    for ($i=count($arr)-1;$i>=0;$i--){
        if ($arr[$i]>60){
            //return 0;
        }
        $second += intval($arr[$i])*pow(60,$y++);
    }
    return (int) $second;
}

function checkToken($token){
    $secret = env('JWT_SECRET');
    $parse = (new \Lcobucci\JWT\Parser())->parse($token);
    $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
    $rst = $parse->verify($signer, $secret);
    if(!$rst){
        return false;
    }else{
        return true;
    }
}