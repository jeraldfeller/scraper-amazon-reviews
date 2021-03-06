<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();
$locale = 'it';
$reviewLocale = '';
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
    if ($reviewLink == '' || $reviewLink == null) {
        $htmlData = $scraper->curlTo($url);
        if ($htmlData['html']) {
            $html = str_get_html($htmlData['html']);
            if($html){
                $reviewLinkNode = $html->find('#dp-summary-see-all-reviews', 0);
                if($reviewLinkNode){
                    try{
                        $href = $reviewLinkNode->getAttribute('href');
                        $reviewLink = 'https://www.amazon.'.$locale.$href.'&sortBy=recent';
                        echo $href . "\n";
                        $scraper->insertAsinLink($id, $reviewLink);
                    }catch(Exception $e) { // I guess its InvalidArgumentException in this case
                        // Node list is empty
                        echo $e . "\n";
                        $continue = false;
                    }
                }else{
                    $reviewListContainer = $html->find('#cr-medley-cmps-wrapper', 0);
                    if($reviewListContainer){
                        $reviewHead = $reviewListContainer->find('#reviews-medley-cmps-expand-head', 0);
                        if($reviewHead){
                            $reviewLinkNode = $reviewListContainer->find('.a-link-child', 0);
                            if($reviewLinkNode){
                                try{
                                    $href = $reviewLinkNode->getAttribute('href');
                                    $reviewLink = $href.'&sortBy=recent';
                                    $scraper->insertAsinLink($id, $reviewLink);
                                }catch(Exception $e) { // I guess its InvalidArgumentException in this case
                                    // Node list is empty
                                    $continue = false;
                                }
                            }else{
                                $continue = false;
                            }
                        }else{
                            $continue = false;
                        }

                    }else{
                        $continue = false;
                    }

                }
            }

        }
    }
    echo $reviewLink . "\n";
    $pg = 1;
    while ($continue == true) {
        // check review domain location
        $parseUrl = parse_url($reviewLink);
        $host = explode('.', $parseUrl['host']);
        $domainLocation = $host[count($host) - 1];

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
                        if($locale == $domainLocation){
                            $day = $datePosted[1];
                            $month = translateMonth($datePosted[2], $locale);
                            $year = $datePosted[3];
                        }else{
                            $day = str_replace(',', '', $datePosted[2]);
                            $month = $datePosted[1];
                            $year = $datePosted[3];
                        }
                        if ($year <= 2016) {
                            $continue == false;
                        }

                        $date = date('Y-m-d', strtotime($month.' ' . $day .', '. $year));

                        echo $reviewId."\n";
                        if($scraper->checkReviewId($reviewId) == 0){
                            $reviews[] = array(
                                $id,
                                $asin,
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
                            //$continue = false;
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