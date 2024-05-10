<?php

function getSearchTestResults(){
    $path = 'model/testData.json';
    $jsonString = file_get_contents($path);
    return json_decode($jsonString);
}