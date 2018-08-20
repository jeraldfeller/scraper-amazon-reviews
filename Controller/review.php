<?php
require '../Model/Init.php';
require '../Model/Scraper.php';
$scraper = new Scraper();

switch ($_GET['action']){
    case 'import':
        if (isset($_FILES['importFile']['tmp_name'])) {
            if (pathinfo($_FILES['importFile']['name'], PATHINFO_EXTENSION) == 'csv') {
                $file = $_FILES['importFile']['tmp_name'];
                $fileName = $_FILES['importFile']['name'];
                $flag = true;
                $fileHandle = fopen($_FILES['importFile']['tmp_name'], "r");
                while (($data = fgetcsv($fileHandle, 10000, ",")) !== FALSE) {
                    if ($flag) {
                        $flag = false;
                        continue;
                    }
                    $asin = $data[0];
                    $scraper->insertAsin($asin);
                }

                fclose($fileHandle);
            }
            echo true;
        }else{
            echo false;
        }
    break;
}