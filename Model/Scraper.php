<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 8/20/2018
 * Time: 5:30 AM
 */
class Scraper
{
    public $debug = TRUE;
    protected $db_pdo;

    public function exportReviews(){
        $pdo = $this->getPdo();
        $sql = 'SELECT r.*, a.asin_review_url, t.total_review_count
                FROM `reviews` r, `asins` a, `total_reviews` t WHERE r.asins_id = a.id AND t.asins_id = a.id ORDER BY r.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;
        return $result;
    }

    public function exportInputs(){
        $pdo = $this->getPdo();
        $sql = 'SELECT `asin`, `brand`
                FROM `asins` ORDER BY `id` DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;
        return $result;
    }

    public function insertAsin($asin, $brand, $client){
        $pdo = $this->getPdo();
        $sql = 'SELECT count(`id`) AS rowCount FROM `asins` WHERE `asin` = "'.$asin.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->fetch(PDO::FETCH_ASSOC)['rowCount'] == 0){
            $sql = 'INSERT INTO `asins` SET `asin` = "'.$asin.'", `brand` = "'.$brand.'", `client` = "'.$client.'", `status` = 0';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $pdo = null;
    }

    public function getAsins($offset = 0, $limit = 10){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `asins` WHERE `status` = 0 ORDER BY id ASC LIMIT '.$offset.','.$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
            $sql = 'UPDATE `asins` SET `status` = 1 WHERE `id` = '.$row['id'];
            $stmtU = $pdo->prepare($sql);
            $stmtU->execute();
        }
        $pdo = null;
        return $result;
    }

    public function insertAsinLink($id, $url){
        $pdo = $this->getPdo();
        $sql = 'UPDATE `asins` SET `asin_review_url` = "'.$url.'" WHERE `id` = '.$id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function addReviews($id, $data){
        $pdo = $this->getPdo();
        $values = '';
        for($x = 0; $x < count($data); $x++){
            if($x == count($data) -1 ){
                $values .= '('.$data[$x][0].',
                "'.$data[$x][1].'",
                1,
                "'.$data[$x][2].'",
                "'.$data[$x][3].'",
                "'.$data[$x][4].'",
                "'.$data[$x][5].'",
                "'.$data[$x][6].'",
                "'.$data[$x][7].'",
                "'.$data[$x][8].'",
                "'.$data[$x][9].'",
                "'.$data[$x][10].'"
                )';
            }else{
                $values .= '('.$data[$x][0].',
                "'.$data[$x][1].'",
                1,
                "'.$data[$x][2].'",
                "'.$data[$x][3].'",
                "'.$data[$x][4].'",
                "'.$data[$x][5].'",
                "'.$data[$x][6].'",
                "'.$data[$x][7].'",
                "'.$data[$x][8].'",
                "'.$data[$x][9].'",
                "'.$data[$x][10].'"
                ),';
            }
        }

        $sql = 'INSERT INTO `reviews`
                  (`asins_id`, `item_asin`, `status`, `review_id`, `review_star_rating`, `review_title`, `review_author`, `review_date`, `review_body`, `date_created`, `brand`, `client`)
                VALUES '.$values.'
               ';


        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    function checkReviewId($reviewId){
        $pdo = $this->getPdo();
        $sql = 'SELECT count(`id`) as matchCount FROM `reviews` WHERE `review_id` = "'.$reviewId.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['matchCount'];
        $pdo = null;

        return $count;
    }

    public function updateTotalReviewCount($id, $locale, $count){
        $pdo = $this->getPdo();
        $sql = 'SELECT id FROM `total_reviews` WHERE `asins_id` = '.$id.' AND `locale` = "'.$locale.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

        if($result > 0){
            $sql = 'UPDATE  `total_reviews` SET `asins_id` = '.$id.', `locale` = "'.$locale.'", `total_review_count` = '.$count .' WHERE `id` = '.$result;
        }else{
            $sql = 'INSERT INTO `total_reviews` SET `asins_id` = '.$id.', `locale` = "'.$locale.'", `total_review_count` = '.$count;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function reset(){
        $pdo = $this->getPdo();
        $sql = 'UPDATE `asins` SET `status` = 0';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function curlTo($url){

        $port1 = '56362';
        $port2 = '43848';
        $proxy[] = array(
            '213.184.110.82',
            '213.184.112.53',
            '213.184.114.168',
            '213.184.114.175',
            '196.16.224.156',
            '196.16.224.158',
            '196.16.224.168',
            '196.16.224.170',
            '196.16.246.146',
            '196.16.246.147',
            '196.16.246.157',
            '196.16.246.99'
        );
        $proxy[] = array(
            '213.184.109.142',
            '213.184.110.15',
            '213.184.112.145',
            '213.184.114.178',
            '196.16.224.25',
            '196.16.246.29'
        );

        $proxyIndex = rand(0, 1);
        if($proxyIndex == 0){
            $port = $port1;
        }else{
            $port = $port2;
        }

        $ip = $proxy[$proxyIndex][mt_rand(0,count($proxy) - 1)];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
            CURLOPT_PROXY => $ip,
            CURLOPT_PROXYPORT => $port,
            CURLOPT_PROXYUSERPWD => 'amznscp:dfab7c358',
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Postman-Token: 85969a77-227f-4da2-ab22-81feaa26c0c4"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return array('html' => $err);
        } else {
            return array('html' => $response, 'ip' => $ip);
        }
    }

    public function getPdo()
    {
        if (!$this->db_pdo)
        {
            if ($this->debug)
            {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            }
            else
            {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD);
            }
        }
        return $this->db_pdo;
    }
}