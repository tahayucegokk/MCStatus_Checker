<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['username']) && !empty($_GET['username'])) {
    $username = trim(htmlspecialchars(addslashes($_GET['username'])));

    function offlinePlayerUuid($username) {
    $data = hex2bin(md5("OfflinePlayer:" . $username));
    $data[6] = chr(ord($data[6]) & 0x0f | 0x30);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return createJavaUuid(bin2hex($data));
}

function createJavaUuid($striped) {
    $components = array(
        substr($striped, 0, 8),
        substr($striped, 8, 4),
        substr($striped, 12, 4),
        substr($striped, 16, 4),
        substr($striped, 20),
    );
    return implode('-', $components);
}
    $API_PROFILE_URL = "https://api.mojang.com/users/profiles/minecraft/";

    $profile = file_get_contents($API_PROFILE_URL . $username);
    $profileJson = json_decode($profile, true);

    $sessionId = $profileJson["id"];

    $API_SESSION_URL = "https://sessionserver.mojang.com/session/minecraft/profile/";

    $session = @file_get_contents($API_SESSION_URL . $sessionId ."?unsigned=true");
    $sessionJson = json_decode($session, true);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => "https://api.mojang.com/users/profiles/minecraft/".$username,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $response = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $result = json_decode($response, true);

    curl_close($ch);

    if ($http_code == 204) {
        echo json_encode(array("name" => $username, "status" => "paid", "id" => offlinePlayerUuid($username)), JSON_PRETTY_PRINT);

    } else if ($http_code == 200) {
        echo json_encode(array("name" => $result['name'] , "status" => "crack" , "id" => $result['id']),  JSON_PRETTY_PRINT);
    }

} else {
  echo '
 // *******************************************************************************************/
 // * MojangAPI.                                                                              */
 // * This PHP script checks whether a Minecraft user is a (paid) user or a (cracked) user    */
 // * @author    Taha Yücegök - <tyucegok@gmail.com>                                          */
 // * @license   https://github.com/tahayucegokk/MojangAPI/blob/main/LICENSE MIT License      */
 // * @// NOTE:                                                                               */
 // * Any distribution of this program may have legal consequences.                           */
 // *******************************************************************************************/';
}
