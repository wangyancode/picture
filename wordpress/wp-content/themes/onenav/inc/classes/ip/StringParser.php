<?php


namespace itbdw\Ip;


class StringParser
{
    /**
     * @var
     */
    private static $location;

    /**
     * 运营商词典
     *
     * @var array
     */
    private static $dictIsp = [
        '联通',
        '移动',
        '铁通',
        '电信',
        '长城',
        '聚友',
    ];

    /**
     * 中国直辖市
     *
     * @var array
     */
    private static $dictCityDirectly = [
        '北京',
        '天津',
        '重庆',
        '上海',
    ];

    private static $dictDistrictBlackTails = [
        '校区',
        '学区',
    ];

    /**
     * 中国省份
     *
     * @var array
     */
    private static $dictProvince = [
        '北京',
        '天津',
        '重庆',
        '上海',
        '河北',
        '山西',
        '辽宁',
        '吉林',
        '黑龙江',
        '江苏',
        '浙江',
        '安徽',
        '福建',
        '江西',
        '山东',
        '河南',
        '湖北',
        '湖南',
        '广东',
        '海南',
        '四川',
        '贵州',
        '云南',
        '陕西',
        '甘肃',
        '青海',
        '台湾',
        '内蒙古',
        '广西',
        '宁夏',
        '新疆',
        '西藏',
        '香港',
        '澳门',
    ];

    /**
     * 解析元数据
     * $_location = [
     *  'country', 'area', 'ip'
     * ];
     * @param $location
     * @param bool $withOriginal debug 用，是否返回原始数据
     * @return array
     */
    public static function parse($_location, $withOriginal = false)
    {
        self::$location     = $_location;
        $org                = $_location;
        $result             = [];
        $isChina            = false;
        $separatorProvince  = '省';  //分割符
        $separatorCity      = '市';  //分割符
        $separatorCounty    = '县';  //分割符
        $separatorDistrict  = '区';  //分割符

        if (!$_location) {
            $result['error'] = 'file open failed';
            return $result;
        }

        //ipv6 会包含中国 故意去掉
        if (strpos($_location['country'], "中国") === 0) {
            $_location['country'] = str_replace("中国", "", $_location['country']);
        }

        self::$location['org_country']  = $_location['country']; //国家地区
        self::$location['org_area']     = $_location['area']; // 组织
        self::$location['province']     = '';
        self::$location['city']         = '';
        self::$location['county']       = '';

        $_tmp_province = explode($separatorProvince, $_location['country']);
        //存在 省 标志 xxx省yyyy 中的yyyy
        if (isset($_tmp_province[1])) {
            $isChina = true;
            //添加省
            self::$location['province'] = self::lTrim($_tmp_province[0]); //河北
            //查找市
            self::getCity($_tmp_province[1], $separatorCity, $separatorCounty, $separatorDistrict);
        } else {
            //处理内蒙古不带省份类型的 和 直辖市
            foreach (self::$dictProvince as $value) {
                if (false !== strpos($_location['country'], $value)) {
                    $isChina = true;
                    self::$location['province'] = $value;

                    //直辖市
                    if (in_array($value, self::$dictCityDirectly)) {
                        //直辖市
                        $_tmp_province = explode($value, $_location['country']);
                        //市辖区
                        if (isset($_tmp_province[1])) {
                            $_tmp_province[1] = self::lTrim($_tmp_province[1], $separatorCity);
                            if (strpos($_tmp_province[1], $separatorDistrict) !== false) {
                                $_tmp_qu = explode($separatorDistrict, $_tmp_province[1]);
                                //解决 休息休息校区 变成城市区域
                                $isHitBlackTail = false;
                                foreach (self::$dictDistrictBlackTails as $blackTail) {
                                    //尾
                                    if (mb_substr($_tmp_qu[0], -mb_strlen($blackTail)) == $blackTail) {
                                        $isHitBlackTail = true;
                                        break;
                                    }
                                }
                                //校区，学区
                                if ((!$isHitBlackTail) && mb_strlen($_tmp_qu[0]) < 5) {
                                    //有点尴尬
                                    self::$location['city'] = self::lTrim($_tmp_qu[0]) . $separatorDistrict;
                                }
                            }
                        }
                    } else {
                        //没有省份标志 只能替换
                        $_tmp_city = str_replace(self::$location['province'], '', $_location['country']);
                        //防止直辖市捣乱 上海市xxx区 =》 市xx区
                        $_tmp_city = self::lTrim($_tmp_city, $separatorCity);
                        //内蒙古 类型的 获取市县信息
                        self::getCity($_tmp_city, $separatorCity, $separatorCounty, $separatorDistrict);
                    }
                    break;
                }
            }
        }

        if ($isChina) {
            self::$location['country'] = '中国';
        }
        $_area  = self::$location['country'] . self::$location['province'] . self::$location['city'] . self::$location['county'] . ' ' . self::$location['org_area'];
        $result = array(
            'ip'        => self::$location['ip'],
            'country'   => self::$location['country'],
            'province'  => self::$location['province'],
            'city'      => self::$location['city'],
            'county'    => self::$location['county'],
            'area'      => trim($_area),
            'isp'       => self::getIsp($_location['area']),
        );
        if ($withOriginal) {
            $result['org'] = $org;
        }
        return $result;
    }

    /**
     * 解析市、县、区
     * @param mixed $city
     * @param mixed $separatorCity
     * @param mixed $separatorCounty
     * @param mixed $separatorDistrict
     * @return void
     */
    private static function getCity($city, $separatorCity, $separatorCounty, $separatorDistrict)
    {
        if (strpos($city, $separatorCity) !== false) {
            $_tmp_city = explode($separatorCity, $city);
            //添加市
            self::$location['city'] = self::lTrim($_tmp_city[0] . $separatorCity);

            //查找县/区
            if (isset($_tmp_city[1])) {
                //县
                if (strpos($_tmp_city[1], $separatorCounty) !== false) {
                    $_tmp_county = explode($separatorCounty, $_tmp_city[1]);
                    self::$location['county'] = self::lTrim($_tmp_county[0] . $separatorCounty);
                }
                //区
                if (!self::$location['county'] && strpos($_tmp_city[1], $separatorDistrict) !== false) {
                    $_tmp_qu = explode($separatorDistrict, $_tmp_city[1]);
                    self::$location['county'] = self::lTrim($_tmp_qu[0] . $separatorDistrict);
                }
            }
        }
    }

    /**
     * @param $str
     * @return string
     */
    private static function getIsp($str)
    {
        $ret = $str;

        foreach (self::$dictIsp as $v) {
            if (false !== strpos($str, $v)) {
                $ret = $v;
                break;
            }
        }

        return $ret;
    }
    /**
     * 修剪左边
     * @param mixed $word
     * @param mixed $w
     * @return mixed
     */
    private static function lTrim($word, $w="\t") {
        $pos = mb_stripos($word, $w);
        if ($pos === 0) {
            $word = mb_substr($word, 1);
        }
        return $word;
    }


}