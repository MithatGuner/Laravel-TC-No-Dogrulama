<?php

namespace MithatGuner\TcDogrula;


class TcDogrula
{
    private static $validationfields = ["tcno","isim","soyisim","dogumyili"];

    public static function formatDogrula($tc){
        $tcno = $tc;
        if(is_array($tc) && !empty($input['tcno'])) $tcno = $input['tcno'];

        if(!preg_match('/^[1-9]{1}[0-9]{9}[0,2,4,6,8]{1}$/', $tcno)){
            return false;
        }

        $odd = $tcno[0] + $tcno[2] + $tcno[4] + $tcno[6] + $tcno[8];
        $even = $tcno[1] + $tcno[3] + $tcno[5] + $tcno[7];
        $digit10 = ($odd * 7 - $even) % 10;
        $total = ($odd + $even + $tcno[9]) % 10;

        if ($digit10 != $tcno[9] ||  $total != $tcno[10]){
            return false;
        }

        return true;
    }

    public static function dogrula(Array $data, $OtomatikBuyuk = true, $soap = true, $url = "https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL"){

        if(!self::formatDogrula($data)) return false;

        if (count(array_diff(self::$validationfields, array_keys($data))) != 0) {
            return false;
        }

        if($OtomatikBuyuk){
            foreach(self::$validationfields as $field){
                $data[$field] = self::trKarakter($data[$field]);
            }
        }


        if($soap) {
            try {
                $istek = new SoapClient($url);
                $sonuc = $istek->TCKimlikNoDogrula(array(
                    'TCKimlikNo' => $data['tcno'],
                    'Ad' => $data['isim'],
                    'Soyad' => $data['soyisim'],
                    'DogumYili' => $data['dogumyili']
                ));

                return ($sonuc->TCKimlikNoDogrulaResult == "1") ? true : false;
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
        }

    }

    public static function trKarakter($text)
    {
        $search=array("ç","i","ı","ğ","ö","ş","ü");
        $replace=array("Ç","İ","I","Ğ","Ö","Ş","Ü");
        $text=str_replace($search,$replace,$text);
        $text=strtoupper($text);
        return $text;
    }
	

}
