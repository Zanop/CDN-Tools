#!/usr/bin/php
<?
/*
* $params = array('url' => '',
*                    'host' => '',
*                   'header' => '',
*                   'method' => '',
*                   'referer' => '',
*                   'cookie' => '',
*                   'post_fields' => '',
*                    ['login' => '',]
*                    ['password' => '',]
*                   'timeout' => 0
*                   );
*/

require_once('surl.config.php');

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function report($stats)
{
	global $nr_requests;
	echo "Time[s]\t \tPercent(Hits)\n";
	foreach ( $stats as $time => $count )
	{
		$percent = round(100*($count/$nr_requests), 2);
		echo "${time}\t<=\t${percent}% (${count} hits)\n";
	}
}

function hit_($url, $vhost=NULL, $login=NULL, $password=NULL)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	if(!empty($login) and !empty($password) )
		curl_setopt($ch, CURLOPT_USERPWD, "${login}:${password}");
	if(!empty($vhost))
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ${vhost}"));
	curl_setopt($ch, CURLOPT_NOBODY, true);
	$start=microtime_float();
	if(!curl_exec($ch)) die("Cant exec curl request!");
	//print_r(curl_getinfo($ch));
	$end=microtime_float();
	$delta=round($end-$start, 2);
	curl_close($ch);
	if($delta < 1 )
	{
		echo ".";
	}
	else
	{
		echo "-${delta}-";
		$delta = round($delta,0);
	}
	return($delta);
}

$testfile=$files[2];

for($i=0;$i<$nr_requests;$i++)
{

	$delta = (string)hit_("http://${origin1}${testfile}", '',  "", "");
	if(empty($o1stats[$delta])) $o1stats[$delta]=0;
	$o1stats[$delta]++;
	$delta = (string)hit_("http://${origin2}${files[1]}", '',  "", "");
	if(empty($o2stats[$delta])) $o2stats[$delta]=0;
	$o2stats[$delta]++;
	$delta = (string)hit_("http://${l2}${testfile}?cdn_hash=" . md5("${testfile}${zonepass}") , 'a7-57.clients.cdn13.com');
	if(empty($l2stats[$delta])) $l2stats[$delta]=0;
	$l2stats[$delta]++;
	$delta = (string)hit_("http://${l1}${testfile}?cdn_hash=" . md5("${testfile}${zonepass}") , 'a7-57.clients.cdn13.com');
	if(empty($l1stats[$delta])) $l1stats[$delta]=0;
	$l1stats[$delta]++;
	usleep(200000);
}
/* Print results */
echo "\nOrigin1:\n";
ksort($o1stats);
report($o1stats);
echo "Origin2:\n";
ksort($o2stats);
report($o2stats);
echo "L1:\n";
ksort($l1stats);
report($l1stats);
echo "L2:\n";
ksort($l2stats);
report($l2stats);


?>
