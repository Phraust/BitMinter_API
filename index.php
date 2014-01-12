<?php

//  BitMinter_API
//
//  A quick PHP / Gumby Framework Interface I slapped together for BitMinter's API.
//  Just add in your API Key and preferred refresh rate.
//
//  This is a work in progress, totally thrown together and very messy.
//  I'll be trying to clean it up, but that's no promise since it works.
//  
//  Need help?  Ping me on Freenode in #Bitminter (I'm Phraust).


//////////////////////////////////////////////////
// BEGIN Configuration

// Enter in your API Key
// Get this from bitminter.com

// $API_Key = '################################';

// Script Timeout in Seconds

$Timeout        =       300;    // 5 Minutes


// Cache File Path (Must be writable / chmod 777)
$Cache_File     =       'cache.txt';

// END Configuration
//////////////////////////////////////////////////

include('cache.txt');

$Fresh_Output	=	array();
$Old_Output =	json_decode($Old_Output,true);
$Output		=	array();

function Get_Curl($Key) {
	global	$Cache_File,
			$Fresh_Output,
			$Output;
	
	// Initializing curl
	$curl = curl_init();
	 
	// Configuring curl options
	$options 	= 	array(
				CURLOPT_URL		=>	'https://bitminter.com/api/users',
				CURLOPT_SSL_VERIFYPEER	=>	0,
				CURLOPT_SSL_VERIFYHOST	=>	0,
				CURLOPT_HEADER		=>	0,
				CURLOPT_RETURNTRANSFER 	=> 	1,
				CURLOPT_HTTPGET		=>	1,
				CURLOPT_HTTPHEADER 	=> 	array('Authorization: key='.$Key)
				);
	 
	// Setting curl options
	curl_setopt_array( $curl, $options );
	
	// Initiate Curl
	$result =  curl_exec($curl);
	
	if ($result == false) {
		error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
	}
	
	curl_close($curl);

	// Get JSON Data
	$result	=	json_decode($result, true); 
	
	// Flatten JSON Data into 1D Array
	$stack = &$result;
	$separator = '.';
	
	while ($stack) {
		list($key, $value) = each($stack);
		unset($stack[$key]);
		if (is_array($value)) {
			$build = array($key => ''); # numbering without a title.
			foreach ($value as $subKey => $node)
				$build[$key . $separator . $subKey] = $node;
			$stack = $build + $stack;
			continue;
		}
		$Fresh_Output[$key] = $value;
	}
	
	// Set Timestamp
	$Fresh_Output	=	array_merge(array("timestamp"=>time()),$Fresh_Output);
	$Output			=	$Fresh_Output;
	
	// Write Cache File
	$Write_Output		=	array();
	$Write_Output		=	'<?php $Old_Output = \''.json_encode($Fresh_Output).'\' ?>';
	
	$cache = fopen($Cache_File, 'w') or die("Can't open file!");
	fwrite($cache, $Write_Output);
	fclose($cache);
}

// Check for Timeout

$Timer	=	time()-$Timeout;

if ($Old_Output["timestamp"] <= $Timer) {
	Get_Curl($API_Key);
}
else {
	$Output =	$Old_Output;
}

// Display Variables

$Timeleft	=	$Timeout-(time()-$Output["timestamp"]);

$User_Name		=	$Output["name"];
$User_Hashrate	=	$Output["hash_rate"];

$BTC_Bal		=	number_format($Output['balances.BTC'],6);
$NMC_Bal		=	number_format($Output['balances.NMC'],6);

$Active_Workers	=	$Output["active_workers"].' ONLINE';
$Total_Workers	=	Get_Workers($Output);


if ($Output["active_workers"] >= 1) {
	$User_Status	=	"ok_dark";
}
elseif ($Output["active_workers"] < 1){
	$User_Status	=	"no_dark";
}

// Get Total Number of Workers

function Get_Workers($array){

	$i		=	0;
	
	while (array_key_exists('workers.'.$i, $array)){
		$i++;
	}
	
	return $i;

}
// Get Percentage

function Get_Percentage($acc, $rej) {

		// Divide by Zero Fixes

		if ($acc >= 1){
			return number_format(($rej/$acc)*100,2)." %";
		}
		else {
			return '0.00 %';
		}

}

// Get Hashrate

function Get_Hashrate($hash) {
	
	// Hashrate Given in MH
	
	if($hash > 1000) {
		$rate	=	' GH';
		$hashed	=	$hash/1000;
	}
	elseif($hash < 1000 || $hash > 0) {
		$rate	=	' MH';
		$hashed	=	$hash;
	}
	elseif($hash <= 0 || !hash) {
		$rate	=	' KH';
		$hashed	=	$hash*10;	
	}
	
	return number_format($hashed,2).$rate;
}


// Display Workers

function Display_Workers($Workers,$Array) {
	for ($i=0; $i<= $Workers; $i++) {
		
		$worker_name		=	$Array['workers.'.$i.'.name'];
		$worker_hashrate	=	$Array['workers.'.$i.'.hash_rate'];
		
		$workers_alive		=	$Array['workers.'.$i.'.alive'];

		$worker_btc_t_acc	=	$Array['workers.'.$i.'.work.BTC.total_accepted'];
		$worker_btc_t_rej	=	$Array['workers.'.$i.'.work.BTC.total_rejected'];
		$worker_btc_t_per	=	Get_Percentage($worker_btc_t_acc,$worker_btc_t_rej);
		
		$worker_btc_c_acc	=	$Array['workers.'.$i.'.work.BTC.checkpoint_accepted'];
		$worker_btc_c_rej	=	$Array['workers.'.$i.'.work.BTC.checkpoint_rejected'];
		$worker_btc_c_per	=	Get_Percentage($worker_btc_c_acc,$worker_btc_c_rej);
		
		$worker_btc_r_acc	=	$Array['workers.'.$i.'.work.BTC.round_accepted'];
		$worker_btc_r_rej		=	$Array['workers.'.$i.'.work.BTC.round_rejected'];
		$worker_btc_r_per	=	Get_Percentage($worker_btc_r_acc,$worker_btc_r_rej);

		$worker_nmc_t_acc	=	$Array['workers.'.$i.'.work.NMC.total_accepted'];
		$worker_nmc_t_rej	=	$Array['workers.'.$i.'.work.NMC.total_rejected'];
		$worker_nmc_t_per	=	Get_Percentage($worker_nmc_t_acc,$worker_nmc_t_rej);
		
		$worker_nmc_c_acc	=	$Array['workers.'.$i.'.work.NMC.checkpoint_accepted'];
		$worker_nmc_c_rej	=	$Array['workers.'.$i.'.work.NMC.checkpoint_rejected'];
		$worker_nmc_c_per	=	Get_Percentage($worker_nmc_c_acc,$worker_nmc_c_rej);
		
		$worker_nmc_r_acc	=	$Array['workers.'.$i.'.work.NMC.round_accepted'];
		$worker_nmc_r_rej	=	$Array['workers.'.$i.'.work.NMC.round_rejected'];
		$worker_nmc_r_per	=	Get_Percentage($worker_nmc_r_acc,$worker_nmc_r_rej);
		
		// Set Green for active worker
		
		if (!$workers_alive) {
			$highlight	=	" no";
		}
		else {
			$highlight	=	" ok";
		}
		
		echo	'<div class="row workers">'."\n";
		
		echo	'	<div class="fourteen columns light_arrow">'."\n";
		echo	'		<div class="worker_name">'."\n";
		echo	'			<span class="tiny">WORKER</span><br>'."\n";
		echo	'			'.$worker_name."\n";
		echo	'		</div>'."\n";
		echo	'	</div>'."\n";
		
		echo	'	<div class="two columns'.$highlight.'">'."\n";
		echo	'		<div class="worker_hashrate">'."\n";
		echo	'    		<span class="tiny">HASHRATE</span><br>'."\n";
		echo	'       	 '.Get_Hashrate($worker_hashrate)."\n";
		echo	'		</div>'."\n";
        echo	'	</div>'."\n";
		
		echo	'</div>'."\n";


		echo	'<div class="row worker_stats">'."\n";
		
		echo	'		<div class="seven columns">'."\n";
		echo	'			<div class="btc_arrow"><b>BTC STATS</b><span class="stats"><span class="ok_green">ACCEPTED</span> / <span class="no_red">REJECTED</span> / <span>PERCENT</span></span></div>'."\n";
		echo	'			<div class="staggered">TOTAL <span class="stats"><span class="ok_green">'.$worker_btc_t_acc.'</span> / <span class="no_red">'.$worker_btc_t_rej.'</span> / <span>'.$worker_btc_t_per.'</span></span></div>'."\n";
		echo	'			<div class="staggered">ROUND <span class="stats"><span class="ok_green">'.$worker_btc_r_acc.'</span> / <span class="no_red">'.$worker_btc_r_rej.'</span> / <span>'.$worker_btc_r_per.'</span></span></div>'."\n";
		echo	'			<div class="staggered">CHECKPOINT <span class="stats"><span class="ok_green">'.$worker_btc_c_acc.'</span> / <span class="no_red">'.$worker_btc_c_rej.'</span> / <span>'.$worker_btc_c_per.'</span></span></div>'."\n";
		echo	'		</div>'."\n";
		
		echo	'		<div class="seven columns">'."\n";
		echo	'			<div class="nmc_arrow"><b>NMC STATS</b><span class="stats"><span class="ok_green">ACCEPTED</span> / <span class="no_red">REJECTED</span> / <span>PERCENT</span></span></div>'."\n";
		echo	'			<div class="staggered">TOTAL <span class="stats" style="float:right;"><span class="ok_green">'.$worker_nmc_t_acc.'</span> / <span class="no_red">'.$worker_nmc_t_rej.'</span> / <span>'.$worker_nmc_t_per.'</span></span></div>'."\n";
		echo	'			<div class="staggered">ROUND <span class="stats" style="float:right;"><span class="ok_green">'.$worker_nmc_r_acc.'</span> / <span class="no_red">'.$worker_nmc_r_rej.'</span> / <span>'.$worker_nmc_r_per.'</span></span></div>'."\n";
		echo	'			<div class="staggered">CHECKPOINT <span class="stats" style="float:right;"><span class="ok_green">'.$worker_nmc_c_acc.'</span> / <span class="no_red">'.$worker_nmc_c_rej.'</span> / <span>'.$worker_nmc_c_per.'</span></span></div>'."\n";		
		echo	'		</div>'."\n";
		
		echo	'</div>'."\n";
	}
}

?><!doctype html>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" itemscope itemtype="http://schema.org/Product"> <!--<![endif]-->

<html>
<head>

	<meta charset="utf-8">
    <meta http-equiv="refresh" content="<?php echo $Timeleft; ?>">
    
    <!-- Use the .htaccess and remove these lines to avoid edge case issues. More info: h5bp.com/b/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>BitMinter : <?php echo $User_Name; ?> : <?php echo Get_Hashrate($User_Hashrate); ?></title>

    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="humans.txt">

	<link rel="shortcut icon" href="favicon.png" type="image/x-icon" />

    <!-- Mobile viewport optimized: j.mp/bplateviewport -->
    <meta name="viewport" content="width=device-width,initial-scale=1">
    
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <!-- CSS: implied media=all -->
    <!-- CSS concatenated and minified via ant build script-->
    <!-- <link rel="stylesheet" href="css/minified.css"> -->
    
    <!-- CSS imports non-minified for staging, minify before moving to production-->
    <link rel="stylesheet" href="css/imports.css">    
    <!-- end CSS-->

    <!-- All JavaScript at the bottom, except for Modernizr / Respond.
       Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
       For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
    
	<script src="js/libs/modernizr-2.0.6.min.js"></script>   
	
</head>

<body>

    <div id="head" class="row">
        <div class="ten columns dark_arrow">
            <div class="user_name">
                <span class="tiny">USER</span><br>
                <?php echo $User_Name; ?>
            </div>
		</div>
        <div class="two columns btc_arrow">
            <div class="btc_bal">
                <span class="tiny">BTC</span><br>
                <?php echo $BTC_Bal; ?>
            </div>
        </div>
		<div class="two columns nmc_arrow">
            <div class="nmc_bal">
                <span class="tiny">NMC</span><br>
                <?php echo $NMC_Bal; ?>
            </div>
		</div>
        <div class="two columns <?php echo $User_Status; ?>">
			<div class="summary">
            	<span class="tiny"><?php echo $Active_Workers; ?></span><br><?php echo Get_Hashrate($User_Hashrate); ?>
			</div>
		</div>
    </div>
    
<?php Display_Workers($Total_Workers-1,$Output); ?>

<?php

// Catch Debug in Url
if ($_GET['Debug'] == '1') {

?>

<!-- Begin DEBUG -->
<div id="debug">

<div class="row">
<pre class="sixteen columns">

<b>Timer Variables:</b>

Time: <?php echo time(); ?>

Timeout: <?php echo $Timeout; ?>

Timeleft: <?php echo $Timeleft; ?>


<b>Display Variables:</b>

$Cache_File: <?php echo $Cache_File; ?>

$User_Name: <?php echo $User_Name; ?>

$User_Hashrate: <?php echo $User_Hashrate; ?>

$User_Status: <?php echo $User_Status; ?>

$Active_Workers: <?php echo $Active_Workers; ?>

$Total_Workers: <?php echo $Total_Workers; ?>

$BTC_Bal: <?php echo $BTC_Bal; ?>

$NMC_Bal: <?php echo $NMC_Bal; ?>


<b>Arrays:</b>

<b>$Old_Output:</b>

<?php

print_r($Old_Output);

?>

<b>$Fresh_Output:</b>

<?php

print_r($Fresh_Output);

?>

<b>$Output:</b>

<?php

print_r($Output);

?>

</pre>
</div>

</div>

</div>
<!-- End DEBUG -->

<?php

}	// END DEBUG

?>

    <!-- JavaScript at the bottom for fast page loading -->
    
    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')</script>
    
    <script src="js/libs/gumby.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
    <!-- end scripts-->

    <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
    <!--[if lt IE 7 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
    <![endif]-->

</body>
</html>
