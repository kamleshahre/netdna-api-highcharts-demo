<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bootstrap, from Twitter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/css/bootstrap-combined.min.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons 
    <link rel="shortcut icon" href="../assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
-->
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Demo</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li><a href="index.php">Column</a></li>
              <li><a href="bar.php">Bar</a></li>
              <li><a href="line.php">Line</a></li>
			  <li class="active"><a href="area.php">Area</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

      <h1>Area Chart</h1>

	<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
​
    <hr> 
	<footer class="footer">
	   <p class="pull-left">&copy; NetDNA 2012</p>
	   <p class="pull-right"><a href="#">Back to top</a></p>
	</footer>
    </div> <!--end container-->
    </div> <!-- /container -->
<?php

/*
 * NetDNA API Sample Code - PHP
 * Version 1.0a
 */

// Get it here: https://raw.github.com/gist/2791330/64b7007ab9d4d4cbb77efd107bb45e16fc6c8cdf/OAuth.php

require_once("OAuth.php");
require_once("config.php");


// create an OAuth consumer with your key and secret
$consumer = new OAuthConsumer($key, $secret, NULL);

// method type: GET, POST, etc
$method_type   = "GET";

//url to send request to (everything after alias/ in endpoint)
$selected_call = "reports/nodes.json/stats";

// the endpoint for your request
$endpoint = "https://rws.netdna.com/$alias/$selected_call"; //this endpoint will pull the account information for the provided alias

//parse endpoint before creating OAuth request
$parsed = parse_url($endpoint);
if(array_key_exists("parsed", $parsed))
{
    parse_str($parsed['query'], $params);
}


//generate a request from your consumer
$req_req = OAuthRequest::from_consumer_and_token($consumer, NULL, $method_type, $endpoint, $params);

//sign your OAuth request using hmac_sha1
$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
$req_req->sign_request($sig_method, $consumer, NULL);

// create curl resource 
$ch = curl_init(); 
// set url 
curl_setopt($ch, CURLOPT_URL, $req_req); 
//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);

// set curl custom request type if not standard
if ($method_type != "GET" && $method_type != "POST") {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method_type);
}

// not sure what this is doing
if ($method_type == "POST" || $method_type == "PUT" || $method_type == "DELETE") {
    $query_str = OAuthUtil::build_http_query($params);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:', 'Content-Length: ' . strlen($query_str)));
    curl_setopt($ch, CURLOPT_POSTFIELDS,  $query_str);
}

//tell curl to grab headers
//curl_setopt($ch, CURLOPT_HEADER, true);

// $output contains the output string 
$json_output = curl_exec($ch);

//print_r($json_output);
// $headers contains the output headers
//$headers = curl_getinfo($ch);

// close curl resource to free up system resources 
curl_close($ch);

//convert json response into multi-demensional array
$json_o = json_decode($json_output);
$json_a = json_decode($json_output,true);

$array_locations = array();
$array_cachehits = array();

if(array_key_exists("code",$json_o))
{

    if($json_o->code == 200 || $json_o->code == 201)
    {

		foreach ($json_o->data->stats as $o) {
		
			array_push($array_locations,$o->pop_description);
			array_push($array_cachehits,$o->cache_hit);
		}	
    }

    // else, spit out the error received
    else
    {
        echo "Error: " . $json_o->code . ":";
        $elements = $json_o->code;
        foreach($elements as $key => $value)
        {
            echo "$key = $value";
        }
    }
}
else
{
    echo "No return code given";
}

?>
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap.min.js"></script>
	<script src="http://code.highcharts.com/highcharts.js"></script>
	<script src="http://code.highcharts.com/modules/exporting.js"></script>
	<script type="text/javascript">

    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'area'
            },
            title: {
                text: 'Cache Hits By Location'
            },

            xAxis: {
                categories: [
<?php
			   foreach ($array_locations as $key => $location) {
			   		echo "'$location',\n";
			   }

?>

					],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Number of Requests',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.series.name +': '+ this.y +' requests';
                }
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -100,
                y: 100,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF',
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'Cache Hits',
                data: [
<?php
			   foreach ($array_cachehits as $key => $hits) {
			   		echo "$hits,\n";
			   }

?>
				]
            }]
        });
    });
    
	</script>


  </body>
</html>