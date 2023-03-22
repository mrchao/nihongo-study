<?php

namespace Devine\NihongoStudySdk\Utils;

class StrUtil
{
    /**
     * 下划线转小驼峰, 数组是对键名进行转换，数组值不变
     * 转换结果举例
     * little_boy => littleBoy
     * _title => title
     * title_ => title
     * _title_ => title
     * little__boy => littleBoy
     * title_boy__girl_中文_title => titleBoyGirl中文Title
     * @param string|array $data
     * @return mixed
     * @throws
     */
    public static function toCamel($data, $separator = "_")
    {
        if (is_string($data)) {
            $_data = explode($separator, $data);
            $count = count($_data);
            if ($count == 1) {
                // 没有“_”情况， 原样返回
                return $data;
            }
            // 下划线分隔的单词，除第一个外，其余首字母转大写就实现了下划线转驼峰需求。特殊字符不变。
            $new_str = $_data[0];
            $start = 1;
            // 处理第一个字符是 “_” 的情况, 不能转换成大驼峰。示例：_title 要转成 title 而不是 Title
            if ($data[0] == $separator) {
                $new_str .= $_data[1];
                $start = 2;
            }
            for ($i = $start; $i < $count; $i++) {
                $_data[$i] = ucfirst($_data[$i]);
                $new_str .= $_data[$i];
            }
            return $new_str;
        } elseif (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    // 如果是多维数组，递归转换
                    $_k = self::toCamel($k);
                    $data[$_k] = self::toCamel($v);
                } else {
                    // 数组下标如果是数字，则不处理
                    if (is_numeric($k)) {
                        continue;
                    }
                    $_k = self::toCamel($k);
                    $data[$_k] = $v;
                }
                unset($data[$k]);
            }
            return $data;
        } else {
            return $data;
            // throw new \Exception('不支持的数据类型：' . gettype($data));
        }
    }
}