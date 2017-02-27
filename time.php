<?php
include 'index.php';
date_default_timezone_set("Europe/Minsk");
$t=time();

function ConvertToASCII ($str){
    $str_ascii = "";
    for ($i = 0; $i < mb_strlen($str); $i++){
        $str_ascii .= ord($str[$i]) . " ";

    }
    return $str_ascii;
}


echo implode("", (GetLastMessages("bot", 1)));




//echo answer_for_bus_stop(try_to_find_from_all(RemoveKeyWord("транспорт", "цум транспорт")),"Туда", type_of_day_rus_now());


?>