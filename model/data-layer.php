<?php

function getSearchTestResults(){
    $path = 'model/testData.json';
    $jsonString = file_get_contents($path);
    return json_decode($jsonString);
}

function getMyBorrowsData(){
    $path = 'model/testDataBorrows.json';
    $jsonString = file_get_contents($path);
    return json_decode($jsonString);
}