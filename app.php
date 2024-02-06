<?php


use App\Processor;


require_once 'vendor/autoload.php';
//Load the configuration
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$dotenv->required(
    [
        'DATABASE_HOST',
        'DATABASE_PORT',
        'DATABASE_USERNAME',
        'DATABASE_NAME',
        'DATABASE_PASSWORD',
        'BULLHORN_CLIENT_ID',
        'BULLHORN_CLIENT_SECRET',
        'BULLHORN_CLIENT_USERNAME',
        'BULLHORN_CLIENT_PASSWORD',
        'ZOOM_ACCOUNT_ID',
        'ZOOM_CLIENT_ID',
        'ZOOM_SECRET',
    ]
);



$processor = new Processor();
$processor->syncContacts();
$processor->syncLeads();
$processor->syncCandidates();
//$res = $zoom->deleteContact("-EdwlCF5QOOBo3aQa1GmuA");
////
//echo "Zoom Response : ".json_encode($res)."\r\n";

//print_r($contacts);
//
//$zoom = new \App\ZoomApiClient();
//
//$res =  $zoom->getContact("eeEt6DvkRFO8iDA1E_uetw");
//echo "Zoom Response : ".json_encode($res)."\r\n";