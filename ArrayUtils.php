<?php
class ArrayUtils
{
    public static function wrap_implode( $array, $before = '', $after = '', $separator = '' )
    {
        if(!$array)
            return '';
        
        return $before . implode($separator, $array ) . $after;
    }

    public static function fully_wrap_implode( $array, $before = '', $after = '', $separator = '' )
    {
        if(!$array)
            return '';
        
        return $before . implode("{$before}{$separator}{$after}", $array ) . $after;
    } 
}

?>