<?php
//"(if) *(\( *)[a-zA-Z0-9]+ *((!=)|(&)) * *[a-zA-Z0-9]+ *(\) *) *(then) * [a-zA-Z0-9]+ *(= *)[a-zA-Z0-9]+ * (else *[a-zA-Z0-9] * = * [a-zA-Z0-9]+)?";

$dictionary = array();

$dictionary["key_word"] = "Ключевое слово ";
$dictionary["id"] = "Идентификатор ";
$dictionary["equal"] = "Знак присваивания ";
$dictionary["logic"] = "Логический оператор ";
$dictionary["const"] = "Константа ";
$dictionary["qou"] = "Разделитель ";

$str = "if ( a == b && b != 2 && a > 4 ) then ( a = 1 ) else ( b = 5 )";

$str_comp = explode(" ", $str);
$out = array();

foreach ($str_comp as $it => $lex) {
    if (cmp($lex, ["if", "then", "else"])) $out [$it . ". " . $lex]= $dictionary["key_word"];
    elseif (cmp($lex, ["="])) $out [$it . ". " . $lex]= $dictionary["equal"];
    elseif (cmp($lex, ["&&", "!=", "==", "<", ">"])) $out [$it . ". " . $lex] = $dictionary["logic"];
    elseif (cmp($lex, ["(", ")"])) $out [$it . ". " . $lex] =  $dictionary["qou"];
    elseif (is_numeric($lex)) $out [$it . ". " . $lex] = $dictionary["const"];
    else $out [$it . ". " . $lex] = $dictionary["id"];

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

