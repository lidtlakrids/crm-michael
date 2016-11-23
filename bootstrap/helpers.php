<?php
/**
 * Created by PhpStorm.
 * User: dib
 * Date: 23-Oct-15
 * Time: 9:55 AM
 */
use Illuminate\View\View;

/**
 *  Function to add empty option to select lists
 *
 * @param $selectList
 * @param string $emptyLabel
 * @return array
 */
function withEmpty($selectList, $emptyLabel='') {
    if($emptyLabel == '') {
        $emptyLabel = Lang::get('labels.select');
    }
    return array(''=>$emptyLabel) + $selectList;
}

function workWeeksInMonth($year,$month){

// loop through month days
    for ($i = 1; $i <= 31; $i++) {

        // given month timestamp
        $timestamp = mktime(0, 0, 0, $month, $i, $year);

        // to be sure we have not gone to the next month
        if (date("n", $timestamp) == $month) {

            // current day in the loop
            $day = date("N", $timestamp);

            // if this is between 1 to 5, weekdays, 1 = Monday, 5 = Friday
            if ($day == 1 OR $day <= 5) {

                // write it down now
                $days[] = [date("j", $timestamp),$day];
            }
        }
    }
    return $days;

}


/**
 * Because asp.net web api returns the enum types with their names and we save them with their enum numbers, when generating selects
 * we need to point the selected option based on the text in it
 *
 *
 * @param $enumOptions
 * @param null $enumValue
 * return enumNumber
 *
 * @return mixed
 */
function findEnumNumber($enumOptions,$enumValue = null){
    $enumNumber = array_search($enumValue,$enumOptions);
    return $enumNumber;
}

/**
 * Checks if a user is allowed to access an action
 *
 * @param $controller
 * @param $action
 * @return bool
 */
function isAllowed($controller,$action){

    // if permissions are null, it is likely we are accessing something while not being authenticated, so let them log in
    $perms = \Illuminate\Support\Facades\Session::get('acl');
    if($perms == null){
        //just in case, to destroy sessions and cookies
        \Illuminate\Support\Facades\Auth::logout();
        \Illuminate\Support\Facades\Redirect::to('auth/login');
    }else{

    $req = studly_case($controller)."/".studly_case($action);

        if(in_array($req,$perms)){
            return true;
        }else{
            return false;
        }
    }
};

/**
 * checks if user has a role, regardless of admin overwrite
 */
function inRoleNeutral($roleName){
    $roles = Session::get('roles');
    return  in_array($roleName,$roles);
}

/**
 * checks if a user is in group
 *
 * @param $groupName
 * @return bool
 */
function inRole($groupName){
    $roles = Session::get('roles');
    return !empty(array_intersect(['Administrator',"Developer",'Accounting'], $roles)) ? true : in_array($groupName,$roles);
}
function isAdmin(){
    return !empty(array_intersect(['Administrator','Developer','Accounting'],Session::get('roles')));
}
function isDev(){
    return in_array('Developer',Session::get('roles'));
}
/**
 * Adds http if it's missing to a domain
 * @param $address
 * @return string
 */
function addHttp($address)
{
    if ($address != null){
        if ($ret = parse_url($address)) {

            if (!isset($ret["scheme"])) {
                $address = "http://{$address}";
            }
        }
    }else{
        $address = "#";
    }
    return $address;
}


/**
 * Creates a link to an item
 * @param $model
 * @param $modelId
 * @param null $hrefOnly
 * @param null $text
 * @return string
 */
function linkToItem($model = null,$modelId = null,$hrefOnly = null,$text = null){

    $models = ['Appointment'=>'appointments','Contract'=>'contracts',
        'ClientAlias'=>'clientAlias','Invoice'=>'invoices',
        'Order'=>'orders','Draft'=>'drafts','Adwords'=>'adwords',
        'Seo'=>'seo','Lead'=>'leads','TaskList'=>'tasks'];

    if($model == null){
        return "";
    }else{
        if($modelId == null){
            if($hrefOnly == null){
                return '<a class="btn btn-'.$models[$model].'" href="'.url($models[$model]).'">'.$text== null? 'Link':$text.'</a>';
            }else{
                return url($models[$model]);
            }
        }else{
            if($hrefOnly == null){
            return '<a class="btn btn-'.$models[$model].'" href="'.url($models[$model].'/show',$modelId).'">'.$text== null? 'Link':$text.'</a>';
            }else{
                return url($models[$model].'/show',$modelId);
            }
        }
    }

}

/**
 * replaces array key, while maintaning the value
 *
 * @param $array
 * @param $key1
 * @param $key2
 * @return array
 */
function replace_key_function($array, $key1, $key2)
{
    $keys = array_keys($array);
    $index = array_search($key1, $keys);

    if ($index !== false) {
        $keys[$index] = $key2;
        $array = array_combine($keys, $array);
    }

    return $array;
}

/**
 * formats number to danish currency format
 * @param $number
 * @param null $rounded
 * @return string
 */
function formatMoney($number,$rounded=null){
    return number_format($number,($rounded === null ? 2 : $rounded),',','.');
}

/**
 * sums a properties in array of objects
 *
 * @param array $arr
 * @param $property
 * @param null $propCondition sometimes we want only property equal to something
 * @return int
 */
function sumProperties(array $arr, $property,$propCondition = null) {

    $sum = 0;

    if($propCondition == null){
        foreach($arr as $object) {
            $sum += isset($object->{$property}) ? $object->{$property} : 0;
        }
    }else{
        foreach($arr as $object) {
            $sum += (isset($object->{$property}) && $object->{$property} == $propCondition )? $object->{$property} : 0;
        }

    }
    return $sum;
}

/**
 * takes array of lines and calculates their value from the product price
 * adding a discount
 * @param array $drafts
 * @return int
 * @internal param $lines
 */
function draftLinesSum(array $drafts){

    $sum = 0;
    foreach ($drafts as $draft){
        if(isset($draft->DraftLine)){
            foreach ($draft->DraftLine as $line){
                $price = $line->Quantity * $line->UnitPrice;
                if(isset($line->Discount)){
                    $price = $price - ($price*($line->Discount/100));
                }
                $sum += $price ;
            }
        }
    }
    return $sum;
}

// makes a quick text input
function quickInput($name,$attributes= null){
    $result = '<input name="'.$name.'" '.join(' ', array_map(function($key) use ($attributes)
        {
            if(is_bool($attributes[$key]))
            {
                return $attributes[$key]?$key:'';
            }
            return $key.'="'.$attributes[$key].'"';
        }, array_keys($attributes))).' />';
    return $result;
}

/**
 * calculates line total with discount if applied
 *
 * @param $line
 * @return int
 */
function calculateLineDiscount($line){

    if($line->Discount > 0){
        $line->UnitPrice = $line->UnitPrice - ($line->UnitPrice * ($line->Discount/100));
    }
    return $line->UnitPrice*$line->Quantity;
}

/**
 * calculates line unit price with discount
 * @param $price
 * @param $discount
 * @return mixed
 */
function calculateLineUnitPrice($price,$discount){

    return $discount > 0 ? $price - ($price*($discount/100)) : $price;
}

/**
 * returns the number of days between the current day and target date
 * @param $date
 * @return mixed
 * @internal param $date1
 * @internal param $date2
 */
function daysBetween($date){
    $date1 = new DateTime();
    $date2     = new DateTime($date);
    $diff = $date2->diff($date1)->format('%a');
    return $diff;
}

function stripDashes($str){
    return str_replace("-",'',$str);
}
//returns a list with -2 / + 2 years from the current one, or a starting one
function yearsSelect($startingYear = null){
    $year =  date('Y');

    $startingYear = $startingYear== null ? $year-2 : $startingYear;
    $years = [];
    while( $startingYear <= $year+2){
        $years[$startingYear] = $startingYear;
        $startingYear++;
    }
    return $years;
}

function monthsSelect(){
    return [
        1=>Lang::get('labels.january'),
        2=>Lang::get('labels.february'),
        3=>Lang::get('labels.march'),
        4=>Lang::get('labels.april'),
        5=>Lang::get('labels.may'),
        6=>Lang::get('labels.june'),
        7=>Lang::get('labels.july'),
        8=>Lang::get('labels.august'),
        9=>Lang::get('labels.september'),
        10=>Lang::get('labels.october'),
        11=>Lang::get('labels.november'),
        12=>Lang::get('labels.december'),
    ];
}

function queryMonthPeriods($propertyStart,$propertyEnd,$properties = null){
    if(!isset($properties['monthsBehind'])){
        $monthsBehind = 12;
    }else{
        $monthsBehind = $properties['monthsBehind'];
    }
    $months = [];
    for($i=0;$i<=$monthsBehind;$i++){
        $start = date('Y-m-01 00:00:00',strtotime($i==0 ? "This month":'-'.$i.' month'));
        $end   = date('Y-m-t 23:59:59',strtotime($start));
        $offsetStart = date('c',strtotime($start));
        $offsetEnd   = date('c',strtotime($end));
        // if we want separate, it will just send it as StartDate,EndDate format
        if(isset($properties['separate'])){
            $months[$offsetStart.','.$offsetEnd] = date("F Y",strtotime($end));
        }else{
            $months["$propertyStart ge $offsetStart and $propertyEnd le $offsetEnd"] = date("F Y",strtotime($end));
        }
//        $months["$propertyStart gt $offsetStart and $propertyEnd lt $offsetEnd"] = date("F Y",strtotime($start));
    }

    if($properties['separate']){
        $months = [
            '2016-11-01T00:00:00+02:00,2016-11-30T23:59:59+01:00'=>'November 2016',
            '2016-10-01T00:00:00+02:00,2016-10-31T23:59:59+01:00'=>'October 2016',
            '2016-09-01T00:00:00+02:00,2016-09-30T23:59:59+01:00'=>'September 2016',
            '2016-08-01T00:00:00+02:00,2016-08-31T23:59:59+01:00'=>'August 2016',
            '2016-07-01T00:00:00+02:00,2016-07-31T23:59:59+01:00'=>'July 2016',
        ];
    }
    return $months;
}

/**
 * Returns the start and end of a specified date or the current one
 *
 * @param null $date
 * @return null
 */
function dayStartEnd($date = null){

    $date = $date == null ? Carbon::now() : Carbon::parse($date);

    $formatted['DayStart'] = $date->startOfDay()->toAtomString();
    $formatted['DayEnd']   = $date->endOfDay()->toAtomString();
    return $formatted;
}

/**
 * Generates a list of months,
 * depending on the properties, will output the array with different formats
 * The Date format is always "c" or DateTimeOffset;
 * @param $propertyStart
 * @param $propertyEnd
 * @param null $properties
 * @return array
 */
function querySellerPeriods($propertyStart,$propertyEnd,$properties = null){
    if(!isset($properties['monthsBehind'])){
        $monthsBehind = 12;
    }else{
        $monthsBehind = $properties['monthsBehind'];
    }
    $months = [];
    for($i=1;$i<=$monthsBehind;$i++){
        $start = date('Y-m-21 00:00:00',strtotime('-'. $i .' month'));
        $end   = date('Y-m-20 23:59:59',strtotime($i==1? "This month" :'-'.($i-1).' month'));
        $offsetStart = date('c',strtotime($start));
        $offsetEnd   = date('c',strtotime($end));
        // if we want separate, it will just send it as StartDate,EndDate format
        if(isset($properties['separate'])){
            $months[$offsetStart.','.$offsetEnd] = date("F Y",strtotime($end));
        }else{
            $months["$propertyStart gt $offsetStart and $propertyEnd lt $offsetEnd"] = date("F Y",strtotime($end));
        }
    }

    return $months;
}

function calculateOrderProductDiscount($orderProduct){
}

/**
 * Takes the ad text and extract the headline from it,removing any js and useless urls
 *
 * @param $ad
 * @return mixed
 */
function adParser($ad){
    preg_match( '#<a[^>]*>(.*?)</a>#i', $ad, $match );
    if(!empty($match)) {
        $ad = preg_replace('#<h3[^>]*>(.*?)</h3>#i', '<h3>' . $match[1] . '</h3>', $ad);
    }
    return $ad;
}

function isWhitelisted(){
    $cont = new \App\Http\Controllers\RestController();
    $result = $cont->isWhitelist();
    return $result->value;
}

function getIp(){
    //check for cloudflare address
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function toDate($date){
    return date('d-m-Y',strtotime($date));
}

function toDateTime($date){
    return date('d-m-Y H:i',strtotime($date));
}


function toIsoDateString($date){
    return date('c',strtotime($date));
}

function endOfDay($date){
    return date('c',strtotime(date('Y-m-d 23:59',strtotime($date))));
}
function load_tabbed_file($filepath, $load_keys=false){
    $array = array();

    if (!file_exists($filepath)){ return $array; }
    $content = file($filepath);

    for ($x=0; $x < count($content); $x++){
        if (trim($content[$x]) != ''){
            $line = explode("\t", trim($content[$x]));
            if ($load_keys){
                $key = array_shift($line);
                $array[$key] = $line;
            }
            else { $array[] = $line; }
        }
    }
    return $array;
}


function past12months(){
    $today = date('Y-m-d');
    $months = array();
    $monthStart = date('Y-m-01',strtotime($today));
    $monthEnd   = date('Y-m-t',strtotime($today));
    $months[date('Y-n',strtotime($monthStart))]['start']= $monthStart;
    $months[date('Y-n',strtotime($monthEnd))]['end']= $monthEnd;
    for($i=1;$i<=11;$i++){
        $monthStart = date('Y-m-01', strtotime('-'.$i.' month', strtotime($today)));
        $monthEnd= date('Y-m-t', strtotime($monthStart));
        $months[date('Y-n',strtotime($monthStart))]= array();
        $months[date('Y-n',strtotime($monthStart))]['start']= $monthStart;
        $months[date('Y-n',strtotime($monthStart))]['end']= $monthEnd;
    }
    return $months;
}

function next12months(){
    $today = date('Y-m-d');
    $months = array();
    $monthStart = date('Y-m-01',strtotime($today));
    $monthEnd   = date('Y-m-t',strtotime($today));
    $months[date('Y-n',strtotime($monthStart))]['start']= $monthStart;
    $months[date('Y-n',strtotime($monthEnd))]['end']= $monthEnd;
    for($i=1;$i<=11;$i++){
        $monthStart = date('Y-m-01', strtotime('first day of next month', strtotime($monthStart)));
        $monthEnd= date('Y-m-t', strtotime($monthStart));
        $months[date('Y-n',strtotime($monthStart))]= array();
        $months[date('Y-n',strtotime($monthStart))]['start']= $monthStart;
        $months[date('Y-n',strtotime($monthStart))]['end']= $monthEnd;
    }
    return $months;
}


function getMonthListFromDates($startDate,$endDate)
{
    $start    = new DateTime($startDate); // Today date
    $end      = new DateTime($endDate->toDateTimeString()); // Create a datetime object from your Carbon object
    $interval = DateInterval::createFromDateString('1 month'); // 1 month interval
    $period   = new DatePeriod($start, $interval, $end); // Get a set of date beetween the 2 period

    $months = array();

    foreach ($period as $dt) {
        $months[$dt->format('Y-m')] = $dt->format("Y-n");
    }

    return $months;
}

function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}
function calculateSalaryBonus($commission,$salaryBonus)
{

    if (isset($salaryBonus->BonusProcentage)){
        $percentage = $salaryBonus->BonusProcentage / 100; // make it into %
    }else{
        $percentage = 0;
    }
    $oversoldAmount = $commission - $salaryBonus->MinimumTurnover;

    $bonus = $oversoldAmount * $percentage;
    return $bonus;
}
function date_compare($a, $b)
{
    $t1 = strtotime($a->Created);
    $t2 = strtotime($b->Created);
    return $t2 - $t1;
}

function validateEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function get_web_page( $url )
{

    $proxies_array	=	array(
        '178.239.232.95:8080',
        '77.76.134.120:8080',
        '82.119.86.58:80',
        '213.169.35.246:8080',
        '95.87.227.203:8080',
        '212.91.189.162:8000', // ok
        '109.121.146.159:8080',
        '212.91.188.166:3128',
    );

    $random_key 		= array_rand($proxies_array);
    $random_proxy 		= $proxies_array[$random_key];
    $useragents_array	=	array(
        'Mozilla/5.0 (compatible; Konqueror/4.0; bg-BG;  Microsoft Windows) KHTML/4.0.80 (like Gecko)',
        'Mozilla/5.0 (X11; U; Linux i686; bg-BG; rv:1.9.0.11) Gecko Kazehakase/0.5.4 Debian/0.5.4-2.1ubuntu3',
        'Mozilla/5.0 (X11; U; Linux i686; bg-BG; rv:1.8.1.13) Gecko/20080311 (Debian-1.8.1.13+nobinonly-0ubuntu1) Kazehakase/0.5.2',
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; bg-BG; rv:1.9) Gecko/2008052906 K-MeleonCCFME 0.09',
        'Mozilla/5.0 (Windows; U; Windows NT 5.0; bg-BG; rv:1.8.0.7) Gecko/20060917 K-Meleon/1.02',
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; bg-BG; rv:1.5) Gecko/20031016 K-Meleon/0.8.2',
        'Mozilla/5.0 (Windows; U; Win98; bg-BG; rv:1.5) Gecko/20031016 K-Meleon/0.8.2',
        'Mozilla/5.0(Windows;N;Win98;m18;bg-BG)Gecko/20010124',
        'Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)',
        'Mozilla/5.0 (compatible; Konqueror/3.5; GNU/kFreeBSD) KHTML/3.5.9 (like Gecko) (Debian)'
    );

    $random_key 		= array_rand($useragents_array);
    $random_useragent 	= $useragents_array[$random_key];

    $options = array(
        CURLOPT_RETURNTRANSFER 	=> true,     			// return web page
        CURLOPT_HEADER         	=> false,    			// don't return headers
//        CURLOPT_PROXY 			=> $random_proxy,     		// the HTTP proxy to tunnel request through
//        CURLOPT_HTTPPROXYTUNNEL => 1,    				// tunnel through a given HTTP proxy
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_FOLLOWLOCATION 	=> true,     			// follow redirects
        CURLOPT_ENCODING       	=> "",       			// handle compressed
        CURLOPT_USERAGENT      	=> $random_useragent, 	// who am i
        CURLOPT_AUTOREFERER    	=> true,     			// set referer on redirect
        CURLOPT_CONNECTTIMEOUT 	=> 20,      			// timeout on connect
        CURLOPT_TIMEOUT        	=> 30,      			// timeout on response
        CURLOPT_MAXREDIRS      	=> 10,       			// stop after 10 redirects,
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    if($errno = curl_errno($ch)) {
        $error_message = curl_strerror($errno);
        echo "cURL error ({$errno}):\n {$error_message}";
    }
    curl_close( $ch );

    return ['content'=>$content,'agent'=>$random_useragent];
}

function get_domain($domain, $debug = false)
{
    $original = $domain = strtolower($domain);
    if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }
    $debug ? print('<strong style="color:green">&raquo;</strong> Parsing: '.$original) : false;
    $arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
        return $value !== 'www';
    }), 0); //rebuild array indexes
    if (count($arr) > 2)
    {
        $count = count($arr);
        $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);
        $debug ? print(" (parts count: {$count})") : false;
        if (count($_sub) === 2) // two level TLD
        {
            $removed = array_shift($arr);
            if ($count === 4) // got a subdomain acting as a domain
            {
                $removed = array_shift($arr);
            }
            $debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
        }
        elseif (count($_sub) === 1) // one level TLD
        {
            $removed = array_shift($arr); //remove the subdomain
            if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
            {
                array_unshift($arr, $removed);
            }
            else
            {
                // non country TLD according to IANA
                $tlds = array(
                    'aero',
                    'arpa',
                    'asia',
                    'biz',
                    'cat',
                    'com',
                    'coop',
                    'edu',
                    'gov',
                    'info',
                    'jobs',
                    'mil',
                    'mobi',
                    'museum',
                    'name',
                    'net',
                    'org',
                    'post',
                    'pro',
                    'tel',
                    'travel',
                    'xxx',
                );
                if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
                {
                    array_shift($arr);
                }
            }
            $debug ? print("<br>\n" .'[*] One level TLD: <strong>'.join('.', $_sub).'</strong> ') : false;
        }
        else // more than 3 levels, something is wrong
        {
            for ($i = count($_sub); $i > 1; $i--)
            {
                $removed = array_shift($arr);
            }
            $debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
        }
    }
    elseif (count($arr) === 2)
    {
        $arr0 = array_shift($arr);
        if (strpos(join('.', $arr), '.') === false
            && in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
        {
            $debug ? print("<br>\n" .'Seems invalid domain: <strong>'.join('.', $arr).'</strong> re-adding: <strong>'.$arr0.'</strong> ') : false;
            // seems invalid domain, restore it
            array_unshift($arr, $arr0);
        }
    }
    $debug ? print("<br>\n".'<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">'. join('.', $arr) ."</span><br>\n") : false;
    return join('.', $arr);
}
