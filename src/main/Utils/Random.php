<?php

namespace Main\Utils;

class Random
{
    public static function generate($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        return substr(str_shuffle($characters), 0, $length);
    }
    public static function generateApiKey($length = 48)
    {
        //create a randomvalue for public key in the token and 
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        return substr(str_shuffle($characters), 0, $length);
    }
}