<?php

//Utilities Funcs
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function getResourcesInfo($url){
    $resource_info = array();
    preg_match_all('/[[:alnum:]]{8}-{1}[[:alnum:]]{4}-{1}[[:alnum:]]{4}-{1}[[:alnum:]]{4}-{1}[[:alnum:]]{12}/', $url, $resource_info);
    $string_to_search = '/' . $resource_info[0][0] . '/resource/' . $resource_info[0][1] . '/download';
    $resource_url = str_replace($string_to_search, '', $url);
    $resource_url = str_replace('.csv', '', $resource_url);
    array_push($resource_info[0], $resource_url);
    return $resource_info[0];
}

function checkDownloadable($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (curl_exec($ch) !== FALSE) {
        return true;
    } else {
        return false;
    }
}

function getCSVContent($url){
    $data = file_get_contents($url);
    $rows = explode("\n",$data);
    $rows[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rows[0]);
    $headers = explode(";",$rows[0]);
    array_shift($rows);
    $records = array();
    foreach($rows as $row) {
        $values = explode(";",$row);
        $record = new stdClass();
        for($i=0; $i < count($headers); $i++){
            $headers[$i] = trim($headers[$i], '""');
            $values[$i]  = trim($values[$i], '""');
            $record->$headers[$i] = $values[$i];
        }
        array_push($records, $record);
    }

    $resource_info = getResourcesInfo($url);

    $result = new stdClass();
    $result->success = true;
    $result->resource_url = $resource_info[2];
    $result->result  = new stdClass();
    $result->result->resource_id = $resource_info[1];
    $result->result->records     = $records;

    print_r(json_encode($result));
}

$request_method   = $_SERVER['REQUEST_METHOD'];
$request_to_proxy = $_GET['url'];

if(endsWith($request_to_proxy, ".csv")){

    if(checkDownloadable($request_to_proxy)){
        getCSVContent($request_to_proxy);
    }
}