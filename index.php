<?php

//lists all media
$medias['libe']['name'] = 'Libération';
$medias['libe']['name_short'] = 'libe';
$medias['libe']['start_date'] = '1994-01-01';

$medias['lemonde']['name'] = 'Le Monde';
$medias['lemonde']['name_short'] = 'lemonde';
$medias['lemonde']['start_date'] = '1987-01-01';

$medias['lefigaro']['name'] = 'Le Figaro';
$medias['lefigaro']['name_short'] = 'lefigaro';
$medias['lefigaro']['start_date'] = '1998-01-01';


$html = "<div id='medias'>";
foreach ($medias as $media) {
    $html .= "<div class='media'>";
    $html .= "<div class='titre'>" . $media['name'] . "</div>";
    $html .= "<p>Start date<br/> <input class='input_media' id='" . $media['name_short'] . "_date' type='text' value='" . $media['start_date'] . "'/>";
    $today = date('Y-m-d');
    $html .= "<p>End date<br/> <input class='input_media' id='" . $media['name_short'] . "_end_date' type='text' value='" . $today . "'/>";
    $html .= "<p>Search term<br/><input class='input_media' id='" . $media['name_short'] . "_search_term' type='text' value='terrorisme'/>";
    //$html .= "<div class='comment'>(" . $media['comment'] . ")</div>";
    $html .= "<div class='button_submit'><button class='start_scrape skip' name='" . $media['name_short'] . "' id='" . $media['name_short'] . "' class='scrape'>Scrape " . $media['name'] . "!</button>";

    $html .= "</div></div>";
}
$html .= "</div>";
?>
<html>
    <head>
        <title>Scrape any media</title>

        <link href='style.css' rel='stylesheet' type='text/css'>

        <script type="text/javascript" src="http://www.google.com/jsapi"></script>

        <script type="text/javascript">
            google.load("jquery", "1.6.1");
        </script>

        <script type="text/javascript">
            $(document).ready(function () {

                $("#close_csv").click(function(){
                    $("#csv").hide();
                });

                $(".start_scrape").click(function(){

                    //init the div where the results will be displayed
                    $('#result').show();
                    $('#result_view').html("");
                    $('#result_view').append("<button id='showcsv' onClick='$(\"#csv\").show();'>Show data as CSV</button>");

                    var name = $(this).attr('id');

                    var start_date;

                    start_date = $("#"+name+"_date").val();

                    var end_date;

                    end_date = $("#"+name+"_end_date").val();

                    var search_term;

                    search_term = $("#"+name+"_search_term").val();

                    //init the csv repository
                    $('#csv_holder').append("date, 'Number of articles about "+search_term+"'\n");

                    var date = new Date(start_date);

                    var date_end = new Date(end_date);

                    var ONE_DAY = 1000 * 60 * 60 * 24

                    // Calculate the difference between beginning and end dates in milliseconds
                    var difference_ms = Math.abs(date - date_end)

                    // Convert back to days and return
                    var interval = Math.round(difference_ms/ONE_DAY)

                    var index = 0;
                    
                    next();

                    function next() {

                        setTimeout(function() {
                            if (index == interval) {
                                return;
                            }

                            var d = date.getDate();
                            var m = date.getMonth();
                            var m = m+1;
                            var Y = date.getFullYear();

                            $('#result_view').prepend("<div id='"+Y+m+d+"' class='result_item'>"+Y+"-"+m+"-"+d+": fetching ...</div>");
                            $('#csv_holder').append("<div id='csv"+Y+m+d+"'></div>");

                            scrape(name, search_term, m, d, Y);
                        
                            //increments the date by 1 day
                            date.setDate(date.getDate()+1);
                        
                            index++;

                            next();

                        }, 500);
                    }
                });

               

                function scrape(name, search_term, m, d, Y){
                    $.post("scrape.php", { name: name, search_term: escape(search_term), m: m, d: d, Y: Y }, function(data) {
                        var html_add = Y+"-"+m+"-"+d+": "+data+" articles found.";
                        $('#'+Y+m+d).html(html_add);
                        $('#csv'+Y+m+d).html(Y+"-"+m+"-"+d+", "+data);
                    });
                }

                function usleep(microseconds) {
                    // Delay for a given number of micro seconds
                    //
                    // version: 902.122
                    // discuss at: http://phpjs.org/functions/usleep
                    // // +   original by: Brett Zamir
                    // %        note 1: For study purposes. Current implementation could lock up the user's browser.
                    // %        note 1: Consider using setTimeout() instead.
                    // %        note 2: Note that this function's argument, contrary to the PHP name, does not
                    // %        note 2: start being significant until 1,000 microseconds (1 millisecond)
                    // *     example 1: usleep(2000000); // delays for 2 seconds
                    // *     returns 1: true
                    var start = new Date().getTime();
                    while (new Date() < (start + microseconds/1000));
                    return true;
                }
            });
        </script>
    </head>
    <body>
        <?php echo $html ?>
        <div id="result" style="display:none"><div id="result_view"></div></div>
        <p><div id="csv" style="display:none"><div id="close_csv">close [x]</div><div id="csv_holder"></div></div>
    </body>
</html>