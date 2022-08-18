<?php
class ArrayUtils
{
    public static function wrapImplode( $array, $before = '', $after = '', $separator = '' )
    {
        if(!$array)
            return '';
        
        return $before . implode($separator, $array ) . $after;
    }

    public static function fullyWrapImplode( $array, $before = '', $after = '', $separator = '' )
    {
        if(!$array)
            return '';
        
        return $before . implode("{$before}{$separator}{$after}", $array ) . $after;
    } 
}

?>