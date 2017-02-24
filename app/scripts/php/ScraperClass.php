<?php
require_once('simple_html_dom.php');
ini_set('memory_limit','1024M');
ini_set('max_execution_time', 300);

libxml_use_internal_errors(true);
require_once('class.dbLoader.php');
/*
 * Load Initial Settings from Config File
 */
function loadSettings(){
    $count = 0;
    $settingsArray = [];

    $file = htmlspecialchars(file_get_contents('appSettings.conf'));
    $settings = explode("\n", $file);

    foreach($settings as $row){

        if(strpos($row,'URL_BASE') !== false){
            $rowCurr = str_replace("URL_BASE = ",'',$row);

            $settingsArray['URL_BASE'] = $rowCurr;
        }
        elseif(strpos($row, "SCRAPE_URL_HOME") !== false){
            $rowCurr = str_replace("SCRAPE_URL_HOME = ",'',$row);

            $settingsArray['SCRAPE_URL_HOME'] = $rowCurr;

        }
        elseif(strpos($row, 'CATEGORY_HREF_STRING') !== false){

            $rowCurr = str_replace("CATEGORY_HREF_STRING = ",'',$row);

            $settingsArray['CATEGORY_HREF_STRING'] = $rowCurr;
        }
        $count++;
    }

    return $settingsArray;
}

/*
 * Scrape Amazon
 */
function scrape($settings)
{
    $url = $settings['SCRAPE_URL_HOME'];
    $category_url = $settings['CATEGORY_HREF_STRING'];

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);

    $amzResp = curl_exec($curl);

    curl_close($curl);
    $html = str_get_html($amzResp);
    $links = $html->find('div.fsdDeptCol .fsdDeptLink');

    foreach($links as $link){

        $values = array(
            'title' => $link->innertext,
            'href' => $link->attr['href']
        );

        store_categories_in_db($values);
    };

}

function store_categories_in_db($data){
    $db = new dbLoader('localhost', 'root', '', 'amzebay');

    $title = mysqli_real_escape_string($db->connection,$data['title']);
    $href = mysqli_real_escape_string($db->connection,$data['href']);

    // Insert links to Amazon category pages into the database
    // @TODO: Apply conditional check to see if the value exists
    // @TODO: Impement proper update procedure & how to check

    var_dump($href);
    
    // Begin declaration of conditional check for category insertion
    // with the business logic being that we only care about storing
    // the results that contain an Amazon Prime refinement link,
    // which will enable quicker parsing as we don't need to maintain
    // digital distribution records, apps, video, music, etc.
    
    if($href)
    
//    $query = "INSERT INTO Category_Pages
//    (title, href, exclude) VALUES ('$title','$href', 1);
//    ";

//    if (!$db->custSQL($query)) {
//        echo $db->connection->error;
//    }
}

// Using the hrefs retrieved from initial scrape,
// identify 'Amazon Prime Only' link
function call_cat_page($base){
//    var_dump($base);
    $db = new dbLoader('localhost', 'root', '', 'amzebay');
    $pages = array();

    $query = "SELECT href from Category_Pages WHERE exclude = 0;";

    if($result = $db->custSQL($query)){
        foreach($result as $row){
            $url = $base . $row['href'];
            
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);

            $amzResp = curl_exec($curl);

            curl_close($curl);

            $html = str_get_html($amzResp);

            foreach($html->find('ul[data-typeid]') as $prime_link){
                echo "Returned a link for the page<br/>";
                foreach($prime_link->find('li') as $li){

                    $url = $base . htmlspecialchars_decode($li->first_child()->href);

                    if(htmlspecialchars_decode($li->first_child()->href != '')){
                        $curl = curl_init();

                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HEADER, false);

                        $amzResp = curl_exec($curl);

                        curl_close($curl);

                        $html = str_get_html($amzResp);

                        $pages[] = $html;
                    }

                }

            }

        }
        return $pages;
    }
    else{
        echo $db->connection->error;
    }
}

function PrimePageMain($pages){
    foreach($pages as $page){
        if($page) {
            var_dump($page->find('categoryRefinementsSection'));
            echo "<br/>";
        }
    }
}

$config = loadSettings();

//scrape($config);
$results = call_cat_page($config['URL_BASE']);
PrimePageMain($results);

/s/ref=lp_502394_nr_p_85_0?fst=as%3Aoff&rh=n%3A172282%2Cn%3A%21493964%2Cn%3A502394%2Cp_85%3A2470955011&bbn=502394&ie=UTF8&qid=1478917412&rnid=2470954011
?>