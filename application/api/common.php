<?php
use think\Request;


function domain()
    {
        //获取当前域名
        $request = Request::instance();
        $domain=$request->domain();
        return $domain;
    }

 /**
     * 验证身份证
     */
     function validateIDCard($idcard) {
        if(empty($idcard)){
            return false;
        }else{
            $idcard = strtoupper($idcard); # 如果是小写x,转化为大写X
            if(strlen($idcard) != 18 && strlen($idcard) != 15){
                return false;
            }
            # 如果是15位身份证，则转化为18位
            if(strlen($idcard) == 15){
                # 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码 
                if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false) {
                    $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
                } else {
                    $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
                }
                # 加权因子 
                $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                # 校验码对应值 
                $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $checksum = 0;
                for ($i = 0; $i < strlen($idcard); $i++) {
                    $checksum += substr($idcard, $i, 1) * $factor[$i];
                }
                $idcard = $idcard . $code[$checksum % 11];
            }
            # 验证身份证开始
            $IDCardBody = substr($idcard, 0, 17); # 身份证主体
            $IDCardCode = strtoupper(substr($idcard, 17, 1)); # 身份证最后一位的验证码
            
            # 加权因子 
            $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            # 校验码对应值 
            $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $checksum = 0;
            for ($i = 0; $i < strlen($IDCardBody); $i++) {
                $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
            }
            $validateIdcard = $code[$checksum % 11];    # 判断身份证是否合理
            if($validateIdcard != $IDCardCode){
                return false;
            }else{
                return true;
            }
        }
    }


/*
 * Notes: 判断车牌号是否合法
 * @param: $license 车牌号
 * return bool true:合法 false:不合法
 */
function isCarLicense($license)
{
    //参数判断
    if (empty($license))
    {
        return false;
    }
    //匹配民用车牌和使馆车牌
    //判断标准
    //1.第一位为汉子省份缩写
    //2.第二位为大写字母城市编码
    //3.后面是5位仅含字母和数字的组合
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0]))
    {
        return true;
    }
    //匹配特种车牌(挂,警,学,领,港,澳)
    $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
    preg_match($regular, $license, $match);
    if (isset($match[0]))
    {
        return true;
    }
    //匹配武警车牌
    $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
    preg_match($regular, $license, $match);
    if (isset($match[0]))
    {
        return true;
    }
    //匹配军牌
    $regular = "/[A-Z]{2}[0-9]{5}$/";
    preg_match($regular, $license, $match);
    if (isset($match[0]))
    {
        return true;
    }
    //匹配新能源车辆6位车牌
    //小型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0]))
    {
        return true;
    }
    //大型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0]))
    {
        return true;
    }
    return false;
}