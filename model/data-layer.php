<?php

class DataLayer
{
    /**
     *
     * Get dummy data for books borrowed for default user
     *
     * @return  string  test dummy data
     *
     */
    static function getMyBorrowsData(){
        $path = 'model/testDataBorrows.json';
        $jsonString = file_get_contents($path);
        return json_decode($jsonString);
    }

    /**
     *
     * Get data from Google Books API using supplied search term
     *
     * @param   string  $searchTerm The string search term to search for
     * @param   string  $printType type of book to search (all, books, magazines)
     * @return  string  search result data
     *
     */
    static function getSearchResultsCurl($searchTerm, $printType){
        // Create the search url string
        $url = "https://www.googleapis.com/books/v1/volumes?q=" . $searchTerm . "&printType=" . $printType;

        // create & initialize a curl session
        $curl = curl_init();

        // set our url with curl_setopt()
        curl_setopt($curl, CURLOPT_URL, $url);

        // return the transfer as a string, also with setopt()
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // curl_exec() executes the started curl session
        // $output contains the output string
        $output = curl_exec($curl);

        // close curl resource to free up system resources
        // (deletes the variable made by curl_init)
        curl_close($curl);

        // decode the json
        return json_decode($output);
    }
}

