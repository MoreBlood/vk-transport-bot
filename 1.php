<?php
include 'index.php';

//echo implode("<br>",explode("\n",getRevisorsFromGroup(1000000, 10000)));

$short = array(
    'url' => DeleteTrash('short google.com', array('short ')),
    'access_token' => $token,
    'private' => 0,
    'v' => '5.62'
);
echo json_decode(file_get_contents('https://api.vk.com/method/utils.getShortLink?' . http_build_query($short)), true)['response']['short_url'];


