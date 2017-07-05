<?php

include "Manticorp/ProgressUpdater.php";
include_once 'conf/db.conf.php';
include_once 'main.php';
//include_once 'save.php';

set_time_limit(600);
$tanggal = date("Y-m-d");

$options = array(
    'filename' => __DIR__.DIRECTORY_SEPARATOR.'progress.json',
    'autoCalc' => True,
    'totalStages' => 4
);
$pu = new Manticorp\ProgressUpdater($options);

//$totaldomains = count($domains);
$stageOptions = array(
    'name' => 'Grabbing Domain',
    'message' => 'Grabbing Domain from expireddomains.net, may be it can take to long time, please standby',
    'totalItems' => 1,
);

$pu->nextStage($stageOptions);

for($i = 0; $i <= $stageOptions['totalItems']; $i++){
    usleep(50*1000);
    $data = Main::GrabDomains('ahgryd','jadilahlegenda');
    $domains = Main::ExplodeDomain($data);
    $count = count($domains);
    $pu->incrementStageItems(1, true);
}


$stageOptions = array(
    'name' => 'Save Domain to txt',
    'message' => 'Please Wait, ShadowDomains save domain',
    'totalItems' => 1,
);

$pu->nextStage($stageOptions);

for($i = 0; $i <= $stageOptions['totalItems']; $i++){
  usleep(50*1000);
  Main::SaveDomainTxt($domains);
}

$lines = preg_split('/\r\n|\n|\r/', trim(file_get_contents("dump/$tanggal.txt")));
$stageOptions = array(
    'name' => 'Moz Checking',
    'message' => 'I Love Moz, Than please Wait',
    'totalItems' => 4,
);

$pu->nextStage($stageOptions);

for($i = 0; $i <= $stageOptions['totalItems']; $i++){
  sleep(10);
  $com = $lines[$i].".com <br>";
  $net = $lines[$i].".net <br>";
  $co = $lines[$i].".co <br>";
  $us = $lines[$i].".us <br>";
  $org = $lines[$i].".org <br>";
  Main::MozCheck($com);
  //Main::MozFilter($MozCheck);

    $pu->setStageMessage("Processing Item $com");
    $pu->incrementStageItems(1, true);
}

$buy = preg_split('/\r\n|\n|\r/', trim(file_get_contents("moz/Buy-$tanggal.txt")));
$stageOptions = array(
    'name' => 'Buying Domain',
    'message' => 'Loading ..... ',
    'totalItems' => 5,
);

$pu->nextStage($stageOptions);

for($i = 0; $i <= $stageOptions['totalItems']; $i++){
    usleep(50*1000);
    Main::BuyDomain($buy[$i]);
    $pu->setStageMessage("Processing Item $buy[$i]");
    $pu->incrementStageItems(1, true);
}

$pu->totallyComplete();
