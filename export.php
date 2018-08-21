<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
$scraper = new Scraper();

$export = $scraper->exportReviews();


$date = date('Y-m-d_H-i-s');
$csv = ROOT_DIR.'review-'.$date.'.csv';
$data[] = implode('","', array(
    'Date Time',
    'Review ID',
    'Rating',
    'Title',
    'Author',
    'Review Date',
    'Review Body',
    'ASIN',
    'Total',
    'Locale',
    'Amazon Link'
));
foreach ($export as $row){
    $data[] = implode('","', array(
        $row['date_created'],
        $row['review_id'],
        $row['review_star_rating'],
        stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['review_title']))))),
        stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['review_author']))))),
        $row['review_date'],
            stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['review_body']))))),
        $row['item_asin'],
        $row['total_review_count'],
        $row['locale'],
        $row['asin_review_url']
    ));
}

$file = fopen($csv,"a");
foreach ($data as $line){
    fputcsv($file, explode('","',$line));
}
fclose($file);



// Output CSV-specific headers

header('Content-Type: text/csv; charset=utf-8');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . basename($csv) . "\"");
readfile($csv);
