<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-12-06 23:15:33
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-17 21:38:02
 * @FilePath: \onenav\inc\classes\ip\IpParser\Ip2Region.php
 * @Description: 每个 ip 数据段的 region 信息都固定了格式：国家|区域|省份|城市|ISP，
 */

namespace itbdw\Ip\IpParser;

class Ip2Region implements IpParserInterface
{

    private $filePath;

    private $searcher;
    
    
    public const HeaderInfoLength = 256;
    public const VectorIndexRows = 256;
    public const VectorIndexCols = 256;
    public const VectorIndexSize = 8;
    public const SegmentIndexSize = 14;

    // xdb file handle
    private $handle = null;

    private $ioCount = 0;
    
    public function setDBPath($filePath)
    {

        $this->filePath = $filePath;
    }

    /**
     * @param $ip
     * @return array
     */
    public function getIp($ip)
    {
        if(!file_exists($this->filePath)){
            return array('error'=>1,'msg'=>'请前往「其他功能/其他杂项」IP归属地下载数据库！');
        }
        try {
            $this->newWithFileOnly($this->filePath);
            $tmp = $this->memorySearch($ip);//self::query($ip);
        } catch (\Exception $exception) {
            return [
                'error' => $exception->getMessage(),
            ];
        }
        $return = [
            'show' => true,
            'data' => $tmp
        ];
        return $return;
    }


    /**
     * memorySearch 查询
     * @param mixed $ip
     * @return array
     */
    public function memorySearch($ip)
    {
        $data       = $this->search($ip);
        $tmp        = explode('|', $data);
        $country    = $tmp[0]?:'';
        $province   = $tmp[2]?:'';
        $city       = $tmp[3]?:'';
        $isp        = $tmp[4]?:'';
        $area       = $country . $province . $city . ' ' . $isp;
        $return     = array(
            'ip'        => $ip,
            'country'   => $country,
            'province'  => $province,
            'city'      => $city,
            'county'    => '',
            'area'      => trim($area),
            'isp'       => $isp,
        );
        return $return;
    }



    public function newWithFileOnly($dbFile)
    {
        // 打开xdb二进制文件
        $this->handle = fopen($dbFile, "r");
        if ($this->handle === false) {
            throw new \Exception(sprintf("failed to open xdb file '%s'", $dbFile));
        }
    }

    /**
     * 析构函数，用于在页面执行结束后自动关闭打开的文件。
     */
    public function __destruct()
    {
        if ($this->handle != null) {
            fclose($this->handle);
        }
    }

    public function getIOCount()
    {
        return $this->ioCount;
    }

    /**
     * 查找指定 ip 地址的归属地信息
     * @throws \Exception
     */
    public function search($ip)
    {
        // check and convert the sting ip to a 4-bytes long
        if (is_string($ip)) {
            $t = self::ip2long($ip);
            if ($t === null) {
                throw new \Exception("invalid ip address `$ip`");
            }
            $ip = $t;
        }

        // reset the global counter
        $this->ioCount = 0;

        // locate the segment index block based on the vector index
        $il0 = ($ip >> 24) & 0xFF;
        $il1 = ($ip >> 16) & 0xFF;
        $idx = $il0 * self::VectorIndexCols * self::VectorIndexSize + $il1 * self::VectorIndexSize;
        // read the vector index block
        $buff = $this->read(self::HeaderInfoLength + $idx, 8);
        if ($buff === null) {
            throw new \Exception("failed to read vector index at {$idx}");
        }
        
        $sPtr = self::getLong($buff, 0);
        $ePtr = self::getLong($buff, 4);

        // printf("sPtr: %d, ePtr: %d\n", $sPtr, $ePtr);

        // binary search the segment index to get the region info
        $dataLen = 0;
        $dataPtr = null;
        $l = 0;
        $h = ($ePtr - $sPtr) / self::SegmentIndexSize;
        while ($l <= $h) {
            $m = ($l + $h) >> 1;
            $p = $sPtr + $m * self::SegmentIndexSize;

            // read the segment index
            $buff = $this->read($p, self::SegmentIndexSize);
            if ($buff == null) {
                throw new \Exception("failed to read segment index at {$p}");
            }

            $sip = self::getLong($buff, 0);
            if ($ip < $sip) {
                $h = $m - 1;
            } else {
                $eip = self::getLong($buff, 4);
                if ($ip > $eip) {
                    $l = $m + 1;
                } else {
                    $dataLen = self::getShort($buff, 8);
                    $dataPtr = self::getLong($buff, 10);
                    break;
                }
            }
        }

        // match nothing interception.
        // @TODO: could this even be a case ?
        // printf("dataLen: %d, dataPtr: %d\n", $dataLen, $dataPtr);
        if ($dataPtr == null) {
            return null;
        }

        // load and return the region data
        $buff = $this->read($dataPtr, $dataLen);
        if ($buff == null) {
            return null;
        }

        return $buff;
    }

    // read specified bytes from the specified index
    private function read($offset, $len)
    {
        // read from the file
        $r = fseek($this->handle, $offset);
        if ($r == -1) {
            return null;
        }

        $this->ioCount++;
        $buff = fread($this->handle, $len);
        if ($buff === false) {
            return null;
        }

        if (strlen($buff) != $len) {
            return null;
        }

        return $buff;
    }

    // --- static util functions ----

    // convert a string ip to long
    public static function ip2long($ip)
    {
        $ip = ip2long($ip);
        if ($ip === false) {
            return null;
        }

        // convert signed int to unsigned int if on 32 bit operating system
        if ($ip < 0 && PHP_INT_SIZE == 4) {
            $ip = sprintf("%u", $ip);
        }

        return $ip;
    }

    // read a 4bytes long from a byte buffer
    public static function getLong($b, $idx)
    {
        $val = (ord($b[$idx])) | (ord($b[$idx + 1]) << 8)
            | (ord($b[$idx + 2]) << 16) | (ord($b[$idx + 3]) << 24);

        // convert signed int to unsigned int if on 32 bit operating system
        if ($val < 0 && PHP_INT_SIZE == 4) {
            $val = sprintf("%u", $val);
        }

        return $val;
    }

    // read a 2bytes short from a byte buffer
    public static function getShort($b, $idx)
    {
        return ((ord($b[$idx])) | (ord($b[$idx + 1]) << 8));
    }
}