<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();
$locale = 'it';
$asins = $scraper->getAsins();
$dateNow = date('Y-m-d');
foreach($asins as $row){
    $id = $row['id'];
    $asin = $row['asin'];
    $brand = $row['brand'];
    $client = $row['client'];
    $url = "https://www.amazon.$locale/dp/$asin";
    $reviewLink = $row['asin_review_url'];
    $continue = true;
    if ($reviewLink == '') {
        $htmlData = $scraper->curlTo($url);
        if ($htmlData['html']) {
            $html = str_get_html($htmlData['html']);
            if($html){
                $reviewLinkNode = $html->find('#dp-summary-see-all-reviews', 0);
                if($reviewLinkNode){
                    try{
                        $href = $reviewLinkNode->getAttribute('href');
                        $reviewLink = 'https://www.amazon.'.$locale.$href.'&sortBy=recent';
                        $scraper->insertAsinLink($id, $reviewLink);
                    }catch(Exception $e) { // I guess its InvalidArgumentException in this case
                        // Node list is empty
                        $continue = false;
                    }
                }else{
                    $continue = false;
                }
            }

        }
    }

    $pg = 1;
    while ($continue == true) {
        // new client for review url
        $htmlData = $scraper->curlTo($reviewLink . '&pageNumber=' . $pg);
        if ($htmlData['html']) {
            $html = str_get_html($htmlData['html']);
            if($html){
                echo 'URL: ' . $reviewLink . '&pageNumber=' . $pg, 'Page: ' . $pg."\n";
                // get total number of reviews
                if($html->find('.totalReviewCount', 0)){
                    $totalReviewCount = str_replace('.', '', $html->find('.totalReviewCount', 0)->plaintext);
                    echo 'Review count: ' . $totalReviewCount. "\n";
                    // get reviews
                    $reviews = array();
                    $list = $html->find('#cm_cr-review_list', 0)->find('.review');
                    for($x = 0; $x < count($list); $x++){
                        $reviewId = $list[$x]->getAttribute('id');
                        $rating = trim($list[$x]->find('.review-rating', 0)->plaintext)[0];
                        $title = $list[$x]->find('.review-title', 0)->plaintext;
                        $author = $list[$x]->find('.author', 0)->plaintext;
                        $date = $list[$x]->find('.review-date', 0)->plaintext;
                        $message = $list[$x]->find('.review-text', 0)->plaintext;

                        $datePosted = explode(' ', $date);
                        $day = $datePosted[1];
                        $month = translateMonth($datePosted[2], $locale);
                        $year = $datePosted[3];
                        if ($year <= 2016) {
                            $continue == false;
                        } else {

                        }

                        $date = date('d/m/Y', strtotime($month.' ' . $day .', '. $year));

                        echo $reviewId."\n";
                        if($scraper->checkReviewId($reviewId) == 0){
                            $reviews[] = array(
                                $id,
                                $client,
                                $reviewId,
                                $rating,
                                addslashes($title),
                                addslashes($author),
                                $date,
                                addslashes($message),
                                $dateNow,
                                $brand,
                                $client
                            );
                        }else{
                            $continue = false;
                        }
                    }
                    if (count($reviews) == 0 || count($reviews) < 10) {
                        $continue = false;
                    }

                    if(count($reviews) > 0){
                        $scraper->addReviews($id, $reviews);
                    }

                    // record total number of reviews
                    if ($pg == 1) {
                        $scraper->updateTotalReviewCount($id, $locale, $totalReviewCount);
                    }
                }else{
                    $continue = false;
                }
            }
        }
        $pg++;
        sleep(mt_rand(1, 3));
    }
    sleep(mt_rand(1, 3));
}


function translateMonth($month, $locale){
    switch ($locale){
        case 'it':
            switch ($month){
                case 'gennaio':
                    return 'january';
                break;
                case 'febbraio':
                    return 'february';
                    break;
                case 'marzo':
                    return 'march';
                    break;
                case 'aprile':
                    return 'april';
                    break;
                case 'maggio':
                    return 'may';
                    break;
                case 'giugno':
                    return 'june';
                    break;
                case 'luglio':
                    return 'july';
                    break;
                case 'agosto':
                    return 'august';
                    break;
                case 'settembre':
                    return 'september';
                    break;
                case 'ottobre':
                    return 'october';
                    break;
                case 'novembre':
                    return 'november';
                    break;
                case 'dicembre':
                    return 'december';
                    break;
            }
        break;
    }
}