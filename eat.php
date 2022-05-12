<?php
header('Content-Type: application/json; charset=utf-8');
$config = require_once "config.php";
$jsonDataPath = $config["eat"]["jsonPath"];
$url = $config["eat"]["url"];

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

    $parseNodes = ["day-1", "day-2", "day-3", "day-4", "day-5", "day-6", "day-7"];

    $foods = [
        ["date" => date('d.m.Y', strtotime('monday this week')), "day" => "Pondelok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('tuesday this week')), "day" => "Utorok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('wednesday this week')), "day" => "Streda", "menu" => []],
        ["date" => date('d.m.Y', strtotime('thursday this week')), "day" => "Štvrtok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('friday this week')), "day" => "Piatok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('saturday this week')), "day" => "Sobota", "menu" => []],
        ["date" => date('d.m.Y', strtotime('sunday this week')), "day" => "Nedeľa", "menu" => []],
    ];

    foreach ($parseNodes as $index => $nodeId) {
        $node = $dom->getElementById($nodeId);
        foreach ($node->childNodes as $menuItem) {
            if ($menuItem && $menuItem->childNodes->item(1) && $menuItem->childNodes->item(1)->childNodes->item(3)) {
                $nazov = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(1)->nodeValue);
                $cena = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(3)->nodeValue);
                $popis = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(3)->nodeValue);
                $foods[$index]["menu"][] = "$nazov ($popis): $cena";
            }
        }
    }

    return ["timestamp" => (new DateTime())->getTimestamp(), "data" => $foods];
}