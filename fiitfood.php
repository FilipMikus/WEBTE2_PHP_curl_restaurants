<?php
header('Content-Type: application/json; charset=utf-8');
$config = require_once "config.php";
$jsonDataPath = $config["fiitfood"]["jsonPath"];
$url = $config["fiitfood"]["url"];

if (file_exists($jsonDataPath)) {
    $fileJSON = file_get_contents($jsonDataPath);
    $dataJSON = json_decode($fileJSON, true);
    $timeDifference = (new DateTime())->getTimestamp() - $dataJSON['timestamp'];
    if ($timeDifference > 1200) {
        $data = getDataCURL($url);
        $fp = fopen($jsonDataPath, 'w');
        fwrite($fp, json_encode($data));
        fclose($fp);
    } else {
        $data = json_decode($fileJSON, true);
    }
} else {
    $data = getDataCURL($url);
    $fp = fopen($jsonDataPath, 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
}

echo json_encode($data);


function getDataCURL($url) {
    // CURL:
    $ch = curl_init();                                                                            // curl initialization
    curl_setopt($ch, CURLOPT_URL, $url); // set curl url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                         // return the transfer as a string
    $output = curl_exec($ch);                                                      // $output contains the output string
    curl_close($ch);                                                  // close curl resource to free up system resources

    // SOURCE PARSING:
    $dom = new DOMDocument();

    @$dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;

    $div = $dom->getElementById("fiit-food");

    $foods = [
        ["date" => date('d.m.Y', strtotime('monday this week')), "day" => "Pondelok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('tuesday this week')), "day" => "Utorok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('wednesday this week')), "day" => "Streda", "menu" => []],
        ["date" => date('d.m.Y', strtotime('thursday this week')), "day" => "Štvrtok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('friday this week')), "day" => "Piatok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('saturday this week')), "day" => "Sobota", "menu" => []],
        ["date" => date('d.m.Y', strtotime('sunday this week')), "day" => "Nedeľa", "menu" => []],
    ];

    for ($i = 1; $i < 6; $i++) {
        for ($j = 0; $j < 4; $j++) {
            $nazov = trim($div->childNodes->item(3)->childNodes->item(3)->childNodes->item($i)->childNodes->item(1)->childNodes->item($j)->childNodes->item(1)->nodeValue);
            $cena = trim($div->childNodes->item(3)->childNodes->item(3)->childNodes->item($i)->childNodes->item(1)->childNodes->item($j)->childNodes->item(2)->nodeValue);
            $foods[$i - 1]["menu"][] = "$nazov : $cena";
        }
    }

    return ["timestamp" => (new DateTime())->getTimestamp(), "data" => $foods];
}