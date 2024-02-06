<?php

namespace App;

class Helper
{
    public static function contactChanged(array $bullhornContact, array $localContact) :bool
    {
        if ($bullhornContact["phone"] !==$localContact["phone"])
            return true;
        if ($bullhornContact["email"] !==$localContact["email"])
            return true;
        if ($bullhornContact["firstName"] !==$localContact["firstName"])
            return true;
        if ($bullhornContact["lastName"] !==$localContact["lastName"])
            return true;
        if ($bullhornContact["phone2"] !==$localContact["phone2"])
            return true;
        if ($bullhornContact["phone3"] !==$localContact["phone3"])
            return true;
        if ($bullhornContact["mobile"] !==$localContact["mobile"])
            return true;


        return false;
    }

    public static function checkDuplicateNumbers($numbers = [], Database $database) {

        $phones = [];
        foreach ($numbers as $number) {
            $phone = $database->searchNumber($number);
            if ($phone) {
                continue;
            }else {
                $phones[] = $number;
            }
        }

        return array_values(array_unique($phones));



    }
    public static function formatNumber($number) {
        $number =  str_replace(" ","",$number);
        $number =  str_replace("(","",$number);
        $number =  str_replace(")","",$number);
        //if start with + return the number

        if (strpos($number,"+",0) !==false) {

            echo "\r\n\r\nNumber Contain Plus Sign: $number\r\n\r\n";
            return $number;
        }
        $prefix = substr($number,0,3);
        if ($prefix=="+61") {
            return $number;
        }elseif(strpos($prefix,"61",0) !==false) {

            return "+".$number;
        }else {
            return "+61".$number;
        }
    }
    public static function removeDublicateNumber($errorMessage, $numbers) {
        $message  = json_decode($errorMessage);

        $number = Helper::getStringBetween($errorMessage,"(",")");


        $numbers = array_filter($numbers,function ($val) use($number){
            return $val != $number;
        },0);

        return array_values($numbers);

    }

    public static function getStringBetween($string, $start, $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    public static function validateEmail($email) {
        return filter_var($email,FILTER_VALIDATE_EMAIL);
    }
    public static function setNumbers($data= []) {
        $phone = [];
        if (!empty($data["phone"]))
        {
            $phone[] = Helper::formatNumber($data["phone"]);
        }
        if (!empty($data["phone2"]))
        {
            $phone[] = Helper::formatNumber($data["phone2"]);
        }
        if (!empty($data["phone3"]))
        {
            $phone[] = Helper::formatNumber($data["phone3"]);
        }
        if (!empty($data["mobile"]))
        {
            $phone[] = Helper::formatNumber($data["mobile"]);
        }

        return $phone;
    }

}