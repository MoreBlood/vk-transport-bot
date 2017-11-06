<?php
ini_set('always_populate_raw_post_data','-1');
include 'tr.php';
error_reporting(0);

date_default_timezone_set("Europe/Minsk");
$t = time();

$access_token = getenv('ACCESS_TOKEN') ?: $_SERVER['ACCESS_TOKEN'];
$confirmation_token = getenv('CONFIRMATION_TOKEN') ?: $_SERVER['CONFIRMATION_TOKEN'];
$token = getenv('TOKEN') ?: $_SERVER['TOKEN'];


function CmpArrayWithStr($array, $str){
    foreach ($array as $cmp){
        if ($cmp == $str) return true;
    }
    return false;
}

function getMemes($count, $owner, $album){

    $request_params = array(
        'owner_id' => $owner,
        'album_id' => $album,
        'access_token' => $access_token,
        'count' => $count,
        'v' => '5.62'
    );

    $memes = json_decode(file_get_contents('https://api.vk.com/method/photos.get?' . http_build_query($request_params)));

    return'photo' . $owner . '_' . $memes->response->items[array_rand($memes->response->items)]->id;


}

function getRevisorsFromGroup($time, $count){
    $request_params = array(
        'owner_id' => -72869598,
        'access_token' => $access_token,
        'count' => $count,
        'filter' => 'others',
        'v' => '5.62'
    );

    $wall = json_decode(file_get_contents('https://api.vk.com/method/wall.get?' . http_build_query($request_params)), true);
    $clear_text = array();
    foreach ($wall['response']['items'] as $mess){
        array_push($clear_text, array('message' => re(mb_strtolower($mess['text'])), 'time' => $mess['date']));
    }

    if ($clear_text) $clear_text = array_filter($clear_text, function ($obj) use ($time) {
        if ((time() - $obj['time']) < $time && !(MultipleSearchInString($obj['message'], array("нет", "нету", "?", "никого", "где", "есть кто", "чисто","до", "как", "дармоеды", "гады","ничего", "давайте", "будем","фоткать", "народ","люди")))) {
            return TRUE;
        }
        return FALSE;
    }
    );

    $final_mes = array();

    foreach ($clear_text as $ms){
        $ms['message'] = remove_emoji(DeleteTrash($ms['message'], array("еще", "стоят", ".", " .", "!","написать сообщение","собаки","опять","проверяют","не проверяют")));
       array_push($final_mes,my_mb_ucfirst($ms['message']) . " (" . round((time() - $ms['time']) / 60) . " мин)");
    }

    if (!count($final_mes)) $final_mes[0] = "В последний час не было замечено контроля";
    return implode("\n", $final_mes) . "\nНа основе сообщений пользователей группы vk.com/kontroler_brest";
}

function getStopFromMessage($object){

}

function remove_emoji($text){
    return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
}

function my_mb_ucfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);
}

function MultipleSearchInString($string, $needles){
    foreach ($needles as $needle){
        if (strpos($string, $needle) !==false) return true;
    }
    return false;
}
function DeleteTrash($string, $needles){
    foreach ($needles as $needle){
        $string = str_replace($needle,"", $string);
    }
    return $string;
}

function addRevisors($stop)
{

    global $user_id;

    $stopsObject = array();

    $json = json_decode(file_get_contents("revisors.json"), true);

    if ($json) $stopsObject = $json;

    $new_stop = array(
        'stop' => $stop,
        'time' => time(),
        'ids' => array($user_id)
    );

    if ($stopsObject) $stopsObject = array_filter($stopsObject, function ($obj) use ($stop) {
        if ((time() - $obj['time']) < 3600) {
            return TRUE;
        }
        return FALSE;
    }
    );

    $b = false;

    for ($i = 0; $i < count($stopsObject); $i++) {
        if ($stopsObject[$i]['stop'] === $stop) {
            if (!in_array($user_id, $stopsObject[$i]['ids'])) {
                array_push($stopsObject[$i]['ids'], $user_id);
            }
            $stopsObject[$i]['time'] = time();
            $b = true;
        }
    }
    if (!$b) array_push($stopsObject, $new_stop);

    $myfile = fopen("revisors.json", "w") or die("Unable to open file!");

    fwrite($myfile, json_encode($stopsObject));
    fclose($myfile);


}

function ClearRevisors()
{

    global $user_id;

    if ($user_id !== 179667459 && $user_id !== 50259075) return 0;

    $myfile = fopen("revisors.json", "w") or die("Unable to open file!");

    fwrite($myfile, "");
    fclose($myfile);

    return 1;

}

function getRevisors()
{
    $stopsObject = json_decode(file_get_contents("revisors.json"), true);
    $stops = array();

    if ($stopsObject) foreach ($stopsObject as $el) {
        if ((time() - $el['time']) < 3600) {
            array_push($stops, $el['stop'] . " (" . round((time() - $el['time']) / 60) . " минут назад, отметили: " . count($el['ids']) . " чел.)");
        }
    }
    if (!$stops) return "Контролеров нигде нет :)\nЛибо их просто не отметили\nПробей лучше талон";
    return implode("\n", $stops);
}

function getUsers($id){

    global $token;
    $request_params = array(
        'group_id' => $id,
        'sort' => "time_asc",
        'access_token' => $token,
        'count' => 1000,
        'v' => '5.62'
    );

    $users = json_decode(file_get_contents('https://api.vk.com/method/groups.getMembers?' . http_build_query($request_params)));

    return $users->response->items;

}

function getMessageUsers(){

    global $token;
    $request_params = array(
        'access_token' => $token,
        'count' => 200,
        'offset' => 0,
        'v' => '5.62'
    );

    $user_items = array();
    for ($i = 0; $i < 3; $i++ ){//get count
        $data = json_decode(file_get_contents('https://api.vk.com/method/messages.getDialogs?' . http_build_query($request_params)));
        $user_items = array_merge($user_items, $data->response->items);
        $request_params['offset'] += 200;
    }



    $users_ids = array();
    foreach ($user_items as $dialog){
        array_push($users_ids, $dialog->message->user_id);
    }
    return $users_ids;

}



function SortRightWay($message_ex){
    $bus_array = array_map('mb_strtolower', array("1","2","3","5","6","7","8","9","10","11","12","13","14","16","17","18","19","20","21","22","23","24","25","26","27","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","46","11А","12А","13А","15А","15Б","15В","1А","1Т","21А","21Б","23А","23Б","24А","2А","2Т","37А","39А","39Б","3Т","44А","4Т","5Т","6Т","7Т","8Т"));

    foreach($message_ex as $key=>$value){
        if (array_search($value, $bus_array)){
            array_unshift($message_ex, $value);
            unset($message_ex[$key+1]);
            $message_ex = array_merge($message_ex);
            break;
        }
    }
    return $message_ex;

}

function BuStopResp ($current){

    global $t;
    if (get_route(mb_strtoupper($current[0]), "Туда")) {
        //дописать проверку пар
        $normal_stop = "";
        $rasp_checker = "";
        $current_bus = mb_strtoupper($current[0]);
        $current_date = date("G:i", $t);
        if (end($current) == "расписание")
            $rasp_checker = array_pop($current);
        if (strpos(end($current), ":")!== false)
            $current_date = array_pop($current);
        if ($normal_stop = try_to_find_stop(mb_strtolower(implode(" ", array_slice($current, 1))), $current_bus)) $normal_stop = GetUpperStopName($current_bus, $normal_stop);
        if ($normal_stop !== "multiple"  && check_one_stop($current_bus, "Туда", $normal_stop)) {
            $request_params = "";
            $rasp = 0;
            $shift = 0;

            if ($rasp_checker == "расписание") $rasp = 1; //проверка на одно направление, чиститм чтобы мусора не было, ибо добавляем в строку, а не присваеваем
            if ($time = closest_time_new($current_bus, "Туда", $normal_stop, $current_date, $rasp, $shift))
                $request_params = $current_bus . ". " . get_route($current_bus, "Туда") . " на остановке " . $normal_stop . " будет в " . $time . "\n";
            if ($time = closest_time_new($current_bus, "Обратно", $normal_stop, $current_date, $rasp, $shift))
                $request_params .= $current_bus . ". " . get_route($current_bus, "Обратно") . " на остановке " . $normal_stop . " будет в " . $time;
            return $request_params;
        }

    }

    return "";




}

function re ($str){
    return str_replace("ё", "е", $str);
}

function RemoveKeyWord($keyword,$message){

    if ($message == "" || $keyword == "" ) return 0;
    $message = str_replace($keyword,"",$message);
    $fragments = array_filter(explode(" ", $message));

    return implode(" ", $fragments);

}

function deleteSpace($str){
    if ($str != "") return str_replace(" ","",$str);
    else return 0;
}

function GetUpperStopName($bus, $stop)
{
    if ($stop === "multiple") return $stop;
    $all_stops_for_bus = all_stops($bus);
    //SendMessage("зашли", false);
    foreach ($all_stops_for_bus as $key) {
        if (mb_strtolower($key) == $stop) {
            //SendMessage("нашли" . $key, false);
            return $key;
        }
    }
    return 0;
}

function answer_for_bus_stop($stop, $route, $day)
{
    if ($stop === 0) return "Нет такой остановки";
    $bus_array = array();

    $path = array_diff(scandir("bus/"), array('..', '.'));
    //echo  $route, $day;

    foreach ($path as $key){
        $cur_file = explode(".", $key);
        $cur_bus = $cur_file[0];
        $cur_route = $cur_file[1];
        $cur_day = $cur_file[2];
        if (array_search(re($stop), all_stops_by_route_no_error($cur_bus, $cur_route, $day)))
            array_push($bus_array, $cur_bus);
    }
    return implode(", ",array_unique($bus_array));
}

function CmpWithOffset($first, $second, $type, $offset){
    if ($type == "less"){
        if (abs($first - $second) <= $offset) return 1;
        else return 0;
    }
    else {
        if (abs($first - $second) >= $offset) return 1;
        else return 0;
    }

}

function SendMessage($text, $flag)
{
    global $user_id, $token;

    $request_params = array(
        'message' => $text,
        'user_id' => $user_id,
        'access_token' => $token,
        'v' => '5.0'
    );

    file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
    if ($flag) echo('ok');
}

function array_change_key_case_unicode($arr, $c = CASE_LOWER)
{
    $c = ($c == CASE_LOWER) ? MB_CASE_LOWER : MB_CASE_UPPER;
    foreach ($arr as $k => $v) {
        $ret[mb_convert_case($k, $c, "UTF-8")] = $v;
    }
    return $ret;
}

function try_to_find_stop($stop, $bus)
{

    $stops_low = array_map('mb_strtolower', all_stops($bus));

    if (in_array($stop, $stops_low)) return $stop;

    $found_res = array_filter($stops_low, function ($el) use ($stop) {
        return (  @mb_strpos($el, $stop) !== false);//удаляем все что не подходит
    });



    if (count ($found_res) > 1){
        SendMessage("Есть несколько вариантов, уточните что из: " . implode(", ", $found_res), false);
        return "multiple";
    }
    if (count($found_res) == 1) {
        //SendMessage("нашли по части " . implode(", ", $found_res), false);
        return implode("", $found_res);
    }

    else return 0;

}

function type_of_day_rus_now()
{   global $t;



    if ((date('N', $t) >= 6)) return "Выходной";
    else return "Рабочий";
}

function type_of_day_rus($shift)
{
    $aniver = array("01.01", "07.01", "08.03", "01.05", "08.05", "09.05", "03.07", "07.11", "25.12");
    global $t;
    if (array_search(date("d.m", $t + strtotime('+' . $shift . 'day', strtotime($t))), $aniver)) return "Выходной";
    if ((date('N', $t + strtotime('+' . $shift . 'day', strtotime($t))) >= 6)) return "Выходной";
    else return "Рабочий";
}

function found_by_stop_by_date($bus, $route, $stop, $shift)
{

    $data = @file_get_contents('bus/' . $bus . '.' . $route . '.' . type_of_day_rus($shift) . '.json');
    if ($data === FALSE) return 0;
    if (!array_key_exists($stop, json_decode($data, true))) return 0;
    return array_filter(json_decode($data, true)[$stop]);

}

function check_one_stop($bus, $route, $stop)
{
    if ($stop === "multiple") return 0;
    if (!$stop) {
        SendMessage("На даном маршруте нет такой остановки", false);
        return 0;
    }
    $second_route_to_check = "Туда";
    if ($route == "Туда") $second_route_to_check = "Обратно";

    $data = @file_get_contents('bus/' . $bus . '.' . $route . '.Рабочий.json');
    $data_for_check = @file_get_contents('bus/' . $bus . '.' . $second_route_to_check .  '.Рабочий.json');

    $data_map = json_decode($data);
    $data_for_check_map = json_decode($data_for_check);

    if ((array_key_exists($stop, $data_map) && $data !== FALSE && !array_key_exists($stop, $data_for_check_map) && $data_for_check !== FALSE) ||
        (!array_key_exists($stop, $data_map) && $data !== FALSE && array_key_exists($stop, $data_for_check_map) && $data_for_check !== FALSE)
    ) {
        SendMessage("Остановка есть только в одном направлении", false);
        return 1;
    }
    if (array_key_exists($stop, $data_map) && $data !== FALSE && array_key_exists($stop, $data_for_check_map) && $data_for_check !== FALSE) return 1;

    else {
        SendMessage("На даном маршруте нет такой остановки", false);
        return 0;
    }
}

function all_stops($bus)
{
    $data_one = @file_get_contents('bus/' . $bus . '.Туда.Рабочий.json');
    $data_two = @file_get_contents('bus/' . $bus . '.Обратно.Рабочий.json');
    if ($data_one !== FALSE || $data_two !== FALSE) return array_unique(array_merge(array_keys(json_decode($data_one, true)), array_keys(json_decode($data_two, true))));
    else return array("Нет такого транспорта");

}

function all_stops_by_route($bus, $route, $day)
{
    $data= @file_get_contents('bus/' . $bus . '.' . $route . '.' . $day . '.json');
    if ($data !== FALSE) return array_keys(json_decode($data, true));
    else return array("Нет такого транспорта");

}

function all_stops_by_route_no_error($bus, $route, $day)
{
    $data= @file_get_contents('bus/' . $bus . '.' . $route . '.' . $day . '.json');
    if ($data !== FALSE) return array_keys(json_decode($data, true));
    else return array();

}

function get_route($bus, $route)
{
    $data = @file_get_contents('bus/' . $bus . '.' . $route . '.Рабочий.json');
    if ($data === FALSE) {
        SendMessage("Нет такого транспорта", false);
        return 0;
    }
    $data = array_keys(json_decode($data, true));

    return current($data) . " - " . end($data) ;
}

function convert_time($time)
{ //returns time in seconds

    return strtotime("1970-01-01 $time UTC");
}

function closest_time_new($bus, $route, $stop, $requested_time, $type, $shift)
{
    //реализовать выдачу нескольких результатов, с переходом на нектс день
    $shift_array = array("", " завтра", " послезавтра", " через два дня", " через три дня");
    global $t;
    $offset = INF;
    $offset_return = "23:59";
    $counter = $shift;
    //if (convert_time($requested_time))
    $time_array = array();
    $error = 1; // тек. 16:47 автобус 16:44 - выведет 16:44, а не след
    while ($offset == INF && $counter < 7) {

        if ($bus == "33" && ((date('N', $t) + $counter) % 7 == 0)) $counter++; //придумать фикс для 33
        if ($time_array = found_by_stop_by_date($bus, $route, $stop, $counter))
            foreach ($time_array as $key) {
                if (convert_time($key) - convert_time($requested_time) <= $offset && convert_time($key) >= (convert_time($requested_time) - $error * 60)  ) {
                    $offset_return = $key;
                    $offset = convert_time($key) - convert_time($requested_time);
                    break;
                }
            }
        if ($offset !== INF) break;
        $counter++;
        //$requested_time = "00:00";
        if ($requested_time == date("G:i", $t)){ // придумать фикс, с перескоком на след день когда указано время, и если время больше макс. времени в дне, то предлагать предыдущее и след
            $requested_time = "00:00";
        }

    }

    if ($offset == INF) return 0;
    if ($type) return implode(", ", array_filter($time_array));
    $shift_mes = "";
    if ($counter != 0) $shift_mes = $shift_array[$counter];
    return $offset_return . $shift_mes;
}

function GetLastMessages($author, $count)
{

    global $user_id , $token;
    //$user_id  = "179667459";

    $request_mess = array(
        'count' => $count * 2,
        'user_id' => $user_id,
        'access_token' => $token,
        'v' => '5.62'
    );
    $get_mess = http_build_query($request_mess);
    $prev_messages = file_get_contents('https://api.vk.com/method/messages.getHistory?' . $get_mess);

    if ($prev_messages == FALSE) return 0;
    $prev_messages = json_decode($prev_messages);

    $messages_history = array();
    $messages_history_bot = array();

    foreach ($prev_messages->response->items as $key => $value) {
        if ($value->from_id == $user_id) array_push($messages_history, re(mb_strtolower($value->body)));
        else array_push($messages_history_bot, re(mb_strtolower($value->body)));
    }
    unset($value);

    if ($author == $user_id) return $messages_history;
    else return $messages_history_bot;

}

function try_to_find_from_all($user_message){
    global $stops_low, $stops;

    $found_res = array_filter($stops_low, function ($el) use ($user_message) {
        return (mb_strpos(re($el), $user_message) !== false);//удаляем все что не подходит если просто ввод остановки
    });

    $found_res_up = array();
    foreach ($found_res as $value) array_push($found_res_up, strval($stops[array_search($value, $stops_low)])); // добавляем в массив красивые

    if (count($found_res) == 1) {
        //SendMessage("нашли по части " . implode(", ", $found_res), false);
        return implode(", ", $found_res_up);
    } else return 0;




}

if (!isset($_REQUEST)) {
    return;
}

$stops = array("28 июля", "4-й Форт", "Абрикосовая", "Автобаза связи", "Автовокзал", "Автолюбителей", "Авторынок", "Автошкола", "Агротранс", "Адамковская", "Амбулатория", "АП", "Аркадия", "Аэродромная", "Аэроклуб", "Б.Космонавтов", "Б.Шевченко", "база «Рыбторг»", "Бауманская", "Безымянная", "Беларусбанк", "Беловежская", "Белорусская", "Белтранс Ойл", "Березовая роща", "Березовка", "Бернады", "Благовещенского", "Боброва", "Богдановича", "Богданчука", "Брест Восточный", "Брест Западный", "Брестглавснаб", "Брестоблавтотранс", "Брестсельстрой", "Брестских дивизий", "БТИ", "БТК", "БЭТЗ", "ВамРад", "Варшавский рынок", "Варшавское шоссе", "Веселая", "Ветлечебница", "Ветренная", "Внештранс", "Водозабор", "Водоканал", "Волгоградская", "Волынка", "Восточная", "Восточный микрорайон", "Вторцветмет", "Вульковская", "Гаврилова", "Гагарина", "Газоаппарат", "Гаражи ОПC", "Гастелло", "Гвардейская", "Георгиевская", "Гершоны", "Гефест-Кварц", "Гоголя", "Городская", "Городская больница №1", "ГОРТОП", "Гостиница Беларусь", "Гостиница Дружба", "Гостиница Юность", "Гребной канал", "Грибоедова", "Гузнянская", "Д.П. \"Южный городок\"", "Дворец спорта Виктория", "Дворникова", "Дворцовая", "деревня Плоска", "Детский городок", "Добрая", "Дом ветеранов", "ДСУ", "Дубровка", "Екельчика", "Есенина", "Ж/Д техникум", "Жукова", "Завод", "Завод бытовой химии", "Загородная", "Загороднева", "Заречная", "Защитников Отечества", "Зеленая", "Зона отдыха", "Зубачёва", "Инволюкс", "Инко Фуд", "Интертранс", "Интурист", "К.Боярская", "Калиновая", "Карасева", "Картодром", "Карьерная", "Катин Бор", "Кедровая", "Киевская", "Кирпичная", "Кл.Северное", "Кладбище", "кладбище Плоска", "Клары Цеткин", "Клейники", "Ковалево", "Ковельская", "Ковры Бреста", "Колесника", "Колледж торговли", "Кольцевая", "Комсомольская", "Костюшко", "Красногвардейская", "Краснознаменная", "Краснознамённая", "Красный двор", "Крепость", "Криштофовича", "Крушинская", "Купальская", "л-та Рябцева", "Лейтенанта Акимочкина", "Ленина", "Ленинградская", "Летная", "Лицей", "Луцкая", "Лысая гора", "М.р-н Заречный", "М.р-н Южный", "М.р-н Южный  ", "Маг. \"Северный\"", "Магазин", "Магазин \"Продтовары\"", "Махновича", "Маш.строит. завод", "Маяковского", "Мед. Центр \"ЛОДЭ\"", "Медицинская", "Микрорайон Вулька", "Митьки", "Мицкевича", "Молодёжная", "МОПРа", "Московское шоссе", "Мостовая", "Мошенского", "Музей Ж/д техники", "Музей спасенн.ценностей", "Мухавецкая", "Мясокомбинат", "Новая", "Новосельская", "Новые Задворцы", "ОАО Брестское пиво", "Обл.больница", "Обсерватория", "Овощебаза", "Озёрная", "Озеро", "Октябрьской революции", "Орловская", "Осипенко", "Остановка", "Парк 1 МАЯ", "Парк Воинов-интернац-ов", "Партизанский проспект", "Пер. Есенина", "пер. Калиновый", "Перекресток", "Переулок", "Пионерская", "Площадь Ленина", "Пов.Ковердяки", "Пов.Козловичи", "Поворот Жемчужина", "Подгородская", "Подсобное хозяйство", "Пожарное депо", "Поликлиника", "Почта", "Прибужская", "Пригородная", "Пригородный вокзал", "Приграничная", "Промтехника", "Пронягина", "Проспект Машерова", "Проспект Республики", "Профсоюзная", "ПСО", "Пугачево", "Пункт подготовки вагонов", "Пушкинская", "Радужная", "Рембыттехника", "Республиканская", "Речицкая", "Рокоссовского", "Рынок \"ЛАГУНА\"", "Рыньковка", "Рябиновая", "С/т Жемчужина", "С/т Светлячок", "С/т Южное 1", "С/т Южное 2", "Садовая", "Сальникова", "Санта Бремор", "Санта-53", "Санта-54", "Светлая", "Сев. Кольцо", "Сикорского", "Сиреневая", "Сквер Иконникова", "Скрипникова", "Славнефть", "Смирнова", "Советская", "Советской конституции", "Сосновая", "Спортшкола", "Средняя Школа №1", "Стадион Брестский", "Стадион Локомотив", "Стадион Строитель", "Станция юннатов", "Старые Задворцы", "Стафеева", "Стимово", "СТО-2", "Строительная", "Строительный рынок", "СЭЗ Брест", "Т.Д.ИДЕАЛ", "Театр", "Тельмы", "Тенистая", "Технический университет", "Тихая", "Торговый центр Восток", "Транспортная", "Троллейбусный парк", "Трудовая", "ТЭЦ", "Тюхиничи", "ул. Вересковая", "ул.Ландшафтная", "Университет", "Училище олимпийского резерва", "Учительская", "ФОК", "Фомина", "Форт", "Хлебпром", "Цветотрон", "ЦГБ", "Центральная", "ЦМТ", "ЦУМ", "Чернинская", "Чулочный комбинат", "Шафрановая", "Школа", "Школьная", "Шоссейная", "Электросети", "Юбилейная", "Я. Купалы", "Ямно", "Ясеневая", 'Орджоникидзе', 'К.Маркса', 'Гимназия №1', 'Промстройбанк', 'Свердлова');
$stops = array_unique(array_merge($stops, $stops_tr));
$stops_low = array_map('mb_strtolower', $stops);

//Получаем и декодируем уведомление
$data = json_decode(file_get_contents('php://input'));

switch (@$data->type) {
    //Если это уведомление для подтверждения адреса сервера...
    case 'confirmation':
        //...отправляем строку для подтверждения адреса
        echo $confirmation_token;
        break;
    case 'message_allow':
        $user_id = $data->object->user_id;

        $request_params = array(
            'message' => "- Поиск с текущим временем: АВТОБУС ОСТАНОВКА \n - Поиск всех остановок: ОСТАНОВКИ АВТОБУС \n - Расписание на остановке: АВТОБУС ОСТАНОВКА РАСПИСАНИЕ \n- Остановки необязательно дописывать, если части хватает, то выведется ответ \n Примеры: \n остановки 17\n 1Т цум (для троллейбуса допиши Т без пробела!)\n 5 стадион бре расписание\n 17 цум 21:00\n чтобы бот не отвечал добавь _ в любом месте сообщения\nмемы - кинь мем (еще)",
            'user_id' => $user_id,
            'access_token' => $token,
            'v' => '5.62'
        );

        file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));

        //...отправляем строку для подтверждения адреса
        echo('ok');
        break;
    case 'group_join':
        $user_id = $data->object->user_id;
        $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0&lang=0"));
        $user_name = $user_info->response[0]->first_name;

        $request_params = array(
            'message' => "{$user_name}, спасибо за подписку!",
            'user_id' => $user_id,
            'access_token' => $token,
            'v' => '5.62'
        );

        file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));

        //...отправляем строку для подтверждения адреса
        echo('ok');
        break;

//Если это уведомление о новом сообщении...
    case 'message_new':
        //...получаем id его автора
        $user_id = $data->object->user_id;
        //затем с помощью users.get получаем данные об авторе
        $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0&lang=0"));

//и извлекаем из ответа его имя
        $user_name = $user_info->response[0]->first_name;
        $user_message = re(mb_strtolower($data->object->body));
        $user_fwd_message = re(mb_strtolower(@$data->object->fwd_messages[0]->body));//первое пересылаемое сообщение, если текст сообщения пустой
        if ($user_message == ""  && $user_fwd_message) $user_message = $user_fwd_message;
        $response_for_message = "";

        $found_res = array_filter($stops_low, function ($el) use ($user_message) {
            return (mb_strpos($el, $user_message) !== false);//удаляем все что не подходит если просто ввод остановки
        });

        $found_res_up = array();
        foreach ($found_res as $value) array_push($found_res_up, strval($stops[array_search($value, $stops_low)])); // добавляем в массив красивые

        if (strpos($user_message, '_') !== false){
            echo('ok');
            break;

        }

        $response_for_message = implode(", ", $found_res_up);//клеим


//С помощью messages.send и токена сообщества отправляем ответное сообщение
        $request_params = array(
            'message' => "{$response_for_message}",
            'user_id' => $user_id,
            'access_token' => $token,
            'v' => '5.62'
        );

        if ($user_message == 'кинь музыку') {
            $request_params['attachment'] = 'audio179667459_456239214';
            $request_params['message'] = 'Держи!';
        }
        if (strpos($user_message, 'кинь мем') !== false || (implode("", (GetLastMessages("bot", 1))) == "мемыыыы, наканецтааааа (vk.com/kultandprosvet)" && strpos($user_message, 'еще') !== false) ) {
            $memes = array("_456239018", "_456239021", "_456239022", "_456239023", "_456239024", "_456239025");
            $request_params['attachment'] = getMemes(500,"-107855742",'wall');
            $request_params['message'] = 'Мемыыыы, наканецтааааа (vk.com/kultandprosvet)';
        }
        if (strpos($user_message, 'привет') !== false) $request_params['message'] = "Привет, {$user_name}!";
        if (strpos($user_message, 'спасибо') !== false || $user_message == 'спс' || $user_message == 'красава') $request_params['message'] = "Пожалуйста, {$user_name} 😌";
        if (strpos($user_message, 'как дела') !== false || $user_message == 'как сам?') $request_params['message'] = "Все отлично, а ты как, {$user_name}?";
        if ($user_message == 'нормально' || $user_message == 'хорошо' || $user_message == 'збс' || $user_message == 'отлично' || $user_message == 'норм') $request_params['message'] = "Круто!";
        if ($user_message == 'time') $request_params['message'] = type_of_day_rus_now() .' ' . date('H:m',time());
        if ($user_message == 'помощь' || $user_message == 'что ты умеешь') $request_params['message'] = "Что я умею: \n - Поиск с текущим временем: АВТОБУС ОСТАНОВКА \n - Поиск всех остановок: ОСТАНОВКИ АВТОБУС \n - Расписание на остановке: АВТОБУС ОСТАНОВКА РАСПИСАНИЕ \n- Остановки необязательно дописывать, если части хватает, то выведется ответ \n Примеры: \n остановки 17\n 1Т цум (для троллейбуса допиши Т без пробела!)\n 5 стадион бре расписание\n 17 цум 21:00\n чтобы бот не отвечал добавь _ в любом месте сообщения\nмемы - кинь мем (еще)";
        if (strpos($user_message, 'пидор') !== false) $request_params['message'] = "Только пидора могли назвать таким именем -  {$user_name}. ";
        if (strpos($user_message, 'кто тебя ') !== false) $request_params['message'] = "Authors: \nvk.com/googlebox - code, idea\nvk.com/evgen_vagabund  - database, debug ";
        //if ($user_message == 'да' && substr_count($messages_history_bot[0], ',') != 0) $request_params['message'] = "Что значит да?";
        if ($data->object->attachments[0]->type == "sticker") {
            $request_params['message'] = "Классный стикер, жаль я не умею их кидать 😈";
            //$request_params['sticker_id'] = "12";
        }
        if (strpos($user_message, 'остановки') !== false) $request_params['message'] = implode(", ", all_stops(mb_strtoupper(RemoveKeyWord("остановки", $user_message))));
        if (strpos($user_message, 'транспорт') !== false){

            $request_params['message'] = answer_for_bus_stop(try_to_find_from_all(RemoveKeyWord("транспорт", $user_message)),"Туда", type_of_day_rus_now());
        }
        /*if (strpos($user_message, 'контролеры') !== false && strpos($user_message, 'где контролеры') == false) {
            $current_stop = try_to_find_from_all(RemoveKeyWord("контролеры", $user_message));
            if ($current_stop) {
                addRevisors($current_stop);
                $request_params['message'] = 'Добавили контролеров на остановке: ' . $current_stop;
            }

        }*/
        if (MultipleSearchInString($user_message, array("контролеры", "контра", "контроль", "ревизоры"))) {
            $request_params['message'] = getRevisorsFromGroup(3600, 20);
        }
        if (MultipleSearchInString($user_message, array("short"))){

            $short = array(
                'url' => DeleteTrash($user_message, array('short ')),
                'access_token' => $token,
                'private' => 0,
                'v' => '5.62'
            );
            $request_params['message'] = json_decode(file_get_contents('https://api.vk.com/method/utils.getShortLink?' . http_build_query($short)), true)['response']['short_url'];
        }

        if (strpos($user_message, 'clearrev') !== false) {
            if (ClearRevisors())
            $request_params['message'] = "Revisors cleared!";
            else $request_params['message'] = "Permissions denied!";
        }
        if ((count($current = explode(' ', $user_message)) >= 2) && $request_params['message'] == "") {

            $request_params['message'] = BuStopResp(SortRightWay($current));
        }

        if(!CmpArrayWithStr(GetLastMessages("bot", 4), "если нужна помощь, напиши: помощь")) {
            if ($request_params['message'] == "") $request_params['message'] = "Если нужна помощь, напиши: помощь";
        }
        $check = array(
            'start_message_id' => $data->object->id,
            'peer_id' => $user_id,
            'access_token' => $token,
            'v' => '5.62'
        );
        file_get_contents('https://api.vk.com/method/messages.markAsRead?' . http_build_query($check));

        file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));


//Возвращаем "ok" серверу Callback API
        echo('ok');

        break;
}
