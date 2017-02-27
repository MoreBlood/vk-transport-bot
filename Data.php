<?php

/**
 * Created by PhpStorm.
 * User: moreblood
 * Date: 21.02.17
 * Time: 0:46
 */
date_default_timezone_set("Europe/Minsk");
include 'tr.php';

function re ($str){
    return str_replace("ё", "е", $str);
}

class Data
{
    private $DataObject = NULL;
    private $UserID = NULL;
    private $UserMessage = NULL;

    function __construct()
    {

        //$data = file_get_contents('php://input');
        $data = file_get_contents('Data.json');

        if ($data == FALSE)
            throw new Exception('Data input is empty');
        else {
            $this->DataObject = json_decode($data);
            $this->UserID = $this->DataObject->object->user_id;
            $this->UserMessage = (mb_strtolower($this->DataObject->object->body));
        }

    }

    function __destruct()
    {
        $this->DataObject;
    }

    public function GetUserID()
    {

        if ($this->UserID == NULL) {
            return "empty";
        } else
            return $this->UserID;

    }

    public function GetUserMessage()
    {
        if ($this->UserMessage == NULL) {
            return "empty";
        } else
            return $this->UserMessage;

    }

    public function GetDataType()
    {
        if ($this->UserMessage == NULL) {
            return "empty";
        } else
            return $this->type;

    }

    public function IsMessageSticker()
    {
        if ($this->DataObject == NULL)
            throw new Exception('DataObject is null');
        else
            if (isset($this->DataObject->object->attachments))
                return $this->DataObject->object->attachments[0]->type == "sticker" ? true : false;
            else return false;

    }
}
class User{
    private $firstName;
    private $lastName;
    function __construct($user_id)
    {
        $data = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0&lang=0"));
        $this->firstName = $data->response[0]->first_name;
        $this->lastName = $data->response[0]->last_name;
    }

    public function GetFN(){
        return $this->firstName;
    }
    public function GetLN(){
        return $this->lastName;
    }
}

class Transport{

    private $TransportName;
    private $TransportStops;

    function __construct($name)
    {
        $this->TransportName = mb_strtoupper($name);
        $this->TransportStops = new TransportStops($name);

    }
    public function GetBusName(){
        return $this->TransportName;
    }
    public function GetTransportStops(){
        return $this->TransportStops;
    }

    public function GetStopsForward(){
        $data = array();
        foreach ($this->TransportStops->GetForward() as $key) {
            $data[] =  $key->GetStop()->GetStopName();
        }
        return $data;
    }
    public function GetStopsBack(){
        $data = array();
        foreach ($this->TransportStops->GetBack() as $key) {
            $data[] =  $key->GetStop()->GetStopName();
        }
        return $data;
    }
    public function GetAllStops(){
        return array_unique(array_merge($this->GetStopsBack(), $this->GetStopsForward()));
    }
    public function GetRouteForward(){
        return array(current($this->GetStopsForward()), @end($this->GetStopsForward()));

    }
    public function GetRouteBack(){
        return array(current($this->GetStopsBack()), @end($this->GetStopsBack()));
    }



}

class TransportStops{
    private $Forward = array();
    private $Back = array();

    function __construct($transportName)
    {
        $this->Forward = $this->CreateMap($this->GetAllStops($transportName, "Туда", "Рабочий"), $transportName, "Туда");
        $this->Back = $this->CreateMap($this->GetAllStops($transportName, "Обратно", "Рабочий"), $transportName, "Обратно");
    }

    private function GetAllStops($name, $route, $day){
        $data= @file_get_contents('bus/' . $name . '.' . $route . '.' . $day . '.json');
        if ($data !== FALSE) return array_keys(json_decode($data, true));
        //else return array("Нет такого транспорта");
    }

    private function CreateMap($stops, $transportName, $route){

        $data = array();

        foreach ($stops as $key) {
            array_push($data, new StopTime($key, $transportName, $route));
        }

        return $data;
    }
    public function GetForward(){

        return $this->Forward;
    }

    public function GetBack(){

        return $this->Back;
    }
    public function GetForwardStop($stopName){

        return $this->pGetStop($this->Forward, $stopName);
        //тут будут ошибки
    }

    public function GetBackStop($stopName){

        return $this->pGetStop($this->Back, $stopName);

    }
    private function pGetStop($route, $stopName){
        foreach ($route as $stopTime){
            if ($stopTime->GetStop()->GetStopName() == $stopName) return $stopTime;
        }
    }


}

class Stop{
    private $StopName;
    private $Transport;


    function __construct($stopName)
    {
        $this->StopName = $stopName;
        $this->Transport = $this->GetAllTransport($stopName);
    }
    public function GetStopName(){
        return $this->StopName;
    }
    public function GetSTransport(){
        return $this->Transport;
    }

    private function GetAllTransport($stop){
        if ($stop === 0) return "Нет такой остановки";
        $bus_array = array();

        $path = array_diff(scandir("bus/"), array('..', '.'));
        foreach ($path as $key){
            $cur_file = explode(".", $key);
            $cur_bus = $cur_file[0];
            $cur_route = $cur_file[1];
            $cur_day = $cur_file[2];
            if (array_search(re($stop), $this->AllStopsByRoute($cur_bus, $cur_route, $cur_day)))
                array_push($bus_array, $cur_bus);
        }
        return array_unique($bus_array);

    }

    private function AllStopsByRoute($bus, $route, $day)
    {
        $data= @file_get_contents('bus/' . $bus . '.' . $route . '.' . $day . '.json');
        if ($data !== FALSE) return array_keys(json_decode($data, true));
        else return array();

    }


}

class StopTime
{

    private $Stop;
    private $WorkTime;
    private $DayOffTime;

    function __construct($stopName, $transportName, $route)
    {
        $this->DayOffTime = new Time($stopName, "Рабочий", $transportName, $route);
        $this->WorkTime = new Time($stopName, "Выходной", $transportName, $route);
        $this->Stop = new Stop($stopName);
    }

    public function GetStop()
    {
        return $this->Stop;
    }

    public function GetWorkTime()
    {
        return $this->WorkTime->GetTimeArray();
    }

    public function GetDayOffTime()
    {
        return $this->DayOffTime->GetTimeArray();
    }

    public function GetClosestTime($requested_time, $type, $shift)
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

}


class Time{
    private $TimeArray;

    function __construct($stopName, $typeOfDay, $transportName, $route)
    {

        $data = @file_get_contents('bus/' . $transportName . '.' . $route . '.' . $typeOfDay . '.json');
        if ($data === FALSE) return 0;
        if (json_decode($data, true)[$stopName] == NULL) return 0;
        $this->TimeArray =  array_filter(json_decode($data, true)[$stopName]);

    }
    public function GetTimeArray(){
        return $this->TimeArray;
    }

}

class DecodeMessage{
    
    private $responseForMessage;
    private $t;

    function __construct()
    {
        $data = new Data();
        $this->t = time();

        switch ($data->GetDataType()) {
            case 'message_new':
                $user = new User($data->GetUserID());
                $token = '512459e4b869fb4271ddd9b2010cb747167e6e3bbc97b6ff0ff4ff11a41acee2e84b83ca0a91dd5470e52';
                $this->responseForMessage = new Message($token, "5.62");


                if ($data->GetUserMessage() == 'кинь музыку') {
                    $this->responseForMessage->SendMessage('Держи!',$data->GetUserID(), 'audio179667459_456239214', true);
                    break;
                }
                if ($data->GetUserMessage() == 'кинь мем') {
                    $memes = array("_456239018", "_456239021", "_456239022", "_456239023", "_456239024", "_456239025");
                    $this->responseForMessage->SendMessage('Мемыыыы, наканецтааааа', $data->GetUserID(), 'photo-139467761' . $memes[array_rand($memes)], true);
                    break;
                }

                if (strpos($data->GetUserMessage(), 'остановки') !== false){
                    $bus = new Transport($this->RemoveKeyWord("остановки", $data->GetUserMessage()));
                    $this->responseForMessage->SendMessage(implode(", ", $bus->GetAllStops()), $data->GetUserID(), null, true);
                    break;
                }
                if (strpos($data->GetUserMessage(), 'транспорт') !== false){
                    $stop = new Stop(RemoveKeyWord("транспорт", $data->GetUserMessage()));
                    $this->responseForMessage->SendMessage(implode(", ", $stop->GetSTransport()), $data->GetUserID(), null, true);
                    break;
                }
                if ((count($current = explode(' ', $data->GetUserMessage())) >= 2) && $data->GetUserMessage() == "") {
                    $this->responseForMessage->SendMessage(BuStopResp($this->SortRightWay($current)), $data->GetUserID(), null, true);
                }

                $this->responseForMessage->SendMessage("Если нужна помощь, напиши: помощь", $data->GetUserID(), null, true);



        }
    }


    function try_to_find_stop($stop, $bus)
    {

        $stops_low = array_map('mb_strtolower', all_stops($bus));

        $found_res = array_filter($stops_low, function ($el) use ($stop) {
            return (mb_strpos($el, $stop) !== false);//удаляем все что не подходит
        });

        if (count($found_res) == 1) {
            //SendMessage("нашли по части " . implode(", ", $found_res), false);
            return implode("", $found_res);
        } else return 0;

    }

    function RemoveKeyWord($keyword,$message){

        if ($message == "" || $keyword == "" ) return 0;
        $message = str_replace($keyword,"",$message);
        $fragments = array_filter(explode(" ", $message));

        return implode(" ", $fragments);

    }
    function SortRightWay($message_ex){
        $bus_array = array_map('mb_strtolower', array(1,"2","3","5","6","7","8","9","10","11","12","13","14","16","17","18","19","20","21","22","23","24","25","26","27","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","46","11А","12А","13А","15А","15Б","15В","1А","1Т","21А","21Б","23А","23Б","24А","2А","2Т","37А","39А","39Б","3Т","44А","4Т","5Т","6Т","7Т","8Т"));

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

    function type_of_day_rus_now()
    {
        global $t;
        if ((date('N', $t) >= 6)) return "Выходной";
        else return "Рабочий";
    }

    function convert_time($time)
    { //returns time in seconds

        return strtotime("1970-01-01 $time UTC");
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

    public function BuStopResp ($current){

        if (get_route(mb_strtoupper($current[0]), "Туда")) {
            //дописать проверку пар
            $normal_stop = "";
            $rasp_checker = "";
            $bus = new Transport($current[0]);
            $current_bus = $bus->GetBusName();

            $current_date = date("G:i", $this->t);

            if (end($current) == "расписание")
                $rasp_checker = array_pop($current);
            if (strpos(end($current), ":")!== false)
                $current_date = array_pop($current);
            if ($normal_stop = $this->try_to_find_stop(mb_strtolower(implode(" ", array_slice($current, 1))), $current_bus)) $normal_stop = $this->GetUpperStopName($current_bus, $normal_stop);
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

}

class Message{
    protected $token;
    protected $version;


    function __construct($token, $version)
    {
        $this->token =  $token;
        $this->version = $version;
    }

    public function SendMessage($text, $userID, $attachment, $okFlag){
        $request_params = array(
            'user_id' => $userID,
            'access_token' => $this->token,
            'v' => $this->version
        );
        if ($attachment != null)
            $request_params['message'] = $text;
        else
            throw new Exception("Message text is null");
        if ($attachment != null)
            $request_params['attachment'] = $attachment;


        file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
        if ($okFlag) echo('ok');

    }

}

class PreviousMessages extends Message {

    public function getMessages($author, $count, $type) //если тип 1 то сообщения пользователя
    {

        $request_mess = array(
            'count' => $count * 2,
            'user_id' => $author,
            'access_token' => $this->token,
            'v' => $this->version
        );

        $get_mess = http_build_query($request_mess);
        $prev_messages = file_get_contents('https://api.vk.com/method/messages.getHistory?' . $get_mess);

        if ($prev_messages == FALSE) return 0;
        $prev_messages = json_decode($prev_messages);

        $messages_history = array();
        $messages_history_bot = array();

        foreach ($prev_messages->response->items as $key => $value) {
            if ($value->from_id == $author) array_push($messages_history, $value->body);
            else array_push($messages_history_bot, $value->body);
        }
        unset($value);

        if ($type) return $messages_history;
        else return $messages_history_bot;

    }

}

        $bus = new Transport("17");
        echo implode(" - ", $bus->GetRouteForward()), "<br>";
        echo implode(", ", $bus->GetStopsForward()), "<br>";
        echo implode(" - ", $bus->GetRouteBack()), "<br>";
        echo implode(", ", $bus->GetStopsBack()), "<br>";

        echo implode(", ", $bus->GetTransportStops()->GetBackStop("Зубачева")->GetWorkTime());
