<?php
//"(if) *(\( *)[a-zA-Z0-9]+ *((!=)|(&)) * *[a-zA-Z0-9]+ *(\) *) *(then) * [a-zA-Z0-9]+ *(= *)[a-zA-Z0-9]+ * (else *[a-zA-Z0-9] * = * [a-zA-Z0-9]+)?";

$dictionary = array();

$dictionary["key_word"] = "Ключевое слово ";
$dictionary["id"] = "Идентификатор ";
$dictionary["equal"] = "Знак присваивания ";
$dictionary["logic"] = "Логический оператор ";
$dictionary["const"] = "Константа ";
$dictionary["qou"] = "Разделитель ";

$str = "if ( aasdsa == b && DSDDb != 23123 && a >= 4 ) then ( a = 1 ) else ( b = 5 )";

$str_comp = explode(" ", $str);
$out = array();

foreach ($str_comp as $it => $lex) {
    if (preg_match('/if|then|else/', $lex)) $out [$it . ". " . $lex]= $dictionary["key_word"];
    elseif (preg_match('/^=$/', $lex)) $out [$it . ". " . $lex]= $dictionary["equal"];
    elseif (preg_match('/&&|!=|==|<|>|=>|<=/', $lex)) $out [$it . ". " . $lex] = $dictionary["logic"];
    elseif (preg_match('/\(|\)/', $lex)) $out [$it . ". " . $lex] =  $dictionary["qou"];
    elseif (preg_match('/\d+/', $lex)) $out [$it . ". " . $lex] = $dictionary["const"];
    elseif (preg_match('/(?!if|then|else)\w+/', $lex)) $out [$it . ". " . $lex] = $dictionary["id"];

}

function cmp ($what, $with){
    foreach ($with as $item){
        if ($what == $item) return true;
    }
    return false;
}
echo $str . "<br><table>";

foreach ($out as $item=>$value){
    echo "<tr><td>" . $item . "</td> <td>" . $value . "</td></tr>";
}

echo "</table>";

