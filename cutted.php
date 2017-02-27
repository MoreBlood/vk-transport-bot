<?php

if ($data->GetUserMessage() == 'привет') $this->responceForMessage->SendMessage('Привет, ' . $user->GetFN(), $data->GetUserID(), null, true);
if (strpos($data->GetUserMessage(), 'спасибо') !== false || $data->GetUserMessage() == 'спс' || $data->GetUserMessage() == 'красава') $this->responceForMessage->SendMessage("Пожалуйста, " . $user->GetFN(), $data->GetUserID(), null, true);
if (strpos($data->GetUserMessage(), 'как дела') !== false || $data->GetUserMessage() == 'как сам?') $this->responceForMessage->SendMessage("Все отлично, а ты как, " . $user->GetFN() . "?", $data->GetUserID(), null, true);
if ($data->GetUserMessage() == 'нормально' || $data->GetUserMessage() == 'хорошо' || $data->GetUserMessage() == 'збс' || $data->GetUserMessage() == 'отлично' || $data->GetUserMessage() == 'норм') $this->responceForMessage->SendMessage("Круто!", $data->GetUserID(), null, true);;
/*if ($data->GetUserMessage() == 'time') $request_params['message'] = type_of_day_rus_now();
if ($data->GetUserMessage() == 'помощь' || $data->GetUserMessage() == 'что ты умеешь') $request_params['message'] = "Что я умею: \n - Поиск с текущим временем: АВТОБУС ОСТАНОВКА \n - Поиск всех остановок: ОСТАНОВКИ АВТОБУС \n - Расписание на остановке: АВТОБУС ОСТАНОВКА РАСПИСАНИЕ \n- Остановки необязательно дописывать, если части хватает, то выведется ответ \n Примеры: \n остановки 17\n 1Т цум (для троллейбуса допиши Т без пробела!)\n 5 стадион бре расписание\n 17 цум 21:00\n чтобы бот не отвечал добавь _ в любом месте сообщения";
if ($data->GetUserMessage() == 'ты пидор') $request_params['message'] = "Только пидора могли назвать таким именем -  $user->GetFN(). ";
if (strpos($data->GetUserMessage(), 'кто тебя ') !== false) $request_params['message'] = "Authors: \nvk.com/googlebox - code, idea\nvk.com/evgen_vagabund  - database, debug ";
//if ($data->GetUserMessage() == 'да' && substr_count($messages_history_bot[0], ',') != 0) $request_params['message'] = "Что значит да?";
if ($data->object->attachments[0]->type == "sticker") {
    $request_params['message'] = "Классный стикер, жаль я не умею их кидать :(";
    //$request_params['sticker_id'] = "12";
}*/