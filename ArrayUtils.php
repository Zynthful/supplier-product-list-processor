<?php
class ArrayUtils
{
    public static function wrap_implode($array, $before = '', $after = '', $separator = '')
    {
        if(!$array)
            return '';
        
        return $before . implode($separator, $array)  . $after;
    }

    public static function fully_wrap_implode($array, $before = '', $after = '', $separator = '')
    {
        if(!$array)
            return '';
        
        return $before . implode("{$before}{$separator}{$after}", $array) . $after;
    }

    public static function unset_empty_lines(&$stringArray)
    {
        for ($i = 0; $i < count($stringArray); $i++)
        {
            if (empty($stringArray[$i]))
            {
                unset($stringArray[$i]);
            }
        }
    }

    public static function filter_empty_lines(&$stringArray)
    {
        $stringArray = array_filter($stringArray, static function ($element)
        {
            return $element !== "";
        });
    }
}

?>