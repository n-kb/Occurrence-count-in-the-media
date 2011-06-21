<?php


//gets the POST variables
import_request_variables('p');

//calls the scraping function
$args = array($search_term, $d, $m, $Y);

$func_name = 'scrape_' . $name;

call_user_func_array($func_name, $args);

function scrape_lemonde($search_term, $d, $m, $y) {
    $url = "http://www.lemonde.fr/web/recherche_resultats/1,13-0,1-0,0.html";
    
    $fields_string = "G_NBARCHIVES=1+107+196&num_page=1&query=$search_term&booleen=et&query2=&dans=dansarticle&auteur=&periode=dateprecise&debutjour=$d&debutmois=$m&debutannee=$y&finjour=$d&finmois=$m&finannee=$y&cat=&artparpage=10&ordre=pertinence";

    $html = run_curl($url, $fields_string);

    $pattern = "/<b>(\d*) élément[s|]<\/b>  publi&#233;s/";

    if (preg_match($pattern, $html, $matches))
        $num_articles = $matches[1];
    else
        $num_articles = 0;

    echo $num_articles;
}

function scrape_lefigaro($search_term, $d, $m, $y){
    $url = "http://recherche.lefigaro.fr/recherche/recherche.php?&date=$y-$m-$d&ecrivez=$search_term&page=articles&tri[0]=Date&typedate=dateprecise";

    $html = utf8_decode(run_curl($url));
    
    $pattern = "/<p>Il y a <span class=\"orange-string\" >(\d*)<\/span> r.sultats<\/p>/";

    if (preg_match($pattern, $html, $matches))
        $num_articles = $matches[1];
    else
        $num_articles = 0;

    echo $num_articles;
}

function scrape_libe($search_term, $d, $m, $y) {
    //finds the day after
    $next_day = getDayAfter($d, $m, $y);
    $d_next = $next_day[0];
    $m_next = $next_day[1];
    $y_next = $next_day[2];

    //splits the different keywords, if there are several
    $search_term = urldecode($search_term);
    $keywords = explode(", ", $search_term);

    $num_articles = 0;

    foreach ($keywords as $term) {

        $term = urlencode(trim($term));

        $url = "http://recherche.liberation.fr/recherche/?q=$term&period=custom&period_start_day=$d&period_start_month=$m&period_start_year=$y&period_end_day=$d_next&period_end_month=$m_next&period_end_year=$y_next&editorial_source=&paper_channel=&sort=-publication_date_time";

        $html = utf8_decode(run_curl($url));

        $pattern = "/<p>(\d*) r.sultat/s";

        $preg = preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE);

        if ($preg)
            $num_articles += $matches[1][0];
        else
            $num_articles = 0;
        
    }
    
    echo $num_articles;
}

function run_curl($url, $fields_string="", $ref="http://www.google.fr") {

    //just in case, used as camouflage
    usleep(rand(0, 2000));

    //open connection
    $ch = curl_init();

    //If we're dealing with a form
    if ($fields_string != "") {

        rtrim($fields_string, '&');

        $fields_array = explode("&", $fields_string);
        
        curl_setopt($ch, CURLOPT_POST, count($fields_array));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    }


    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $userAgent = "Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6";
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_REFERER, $ref);


    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);

    return $result;
}

function getDayAfter($d, $m, $y) {
    $tomorrow = strtotime($y . "-" . $m . "-" . $d . " + 1 day");

    $d = date('d', $tomorrow);
    $m = date('m', $tomorrow);
    $y = date('Y', $tomorrow);

    return array($d, $m, $y);
}

?>