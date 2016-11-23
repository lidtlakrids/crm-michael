<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;


class LeadsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
    public function index()
    {
        return view('leads.index');
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
		$cont = new RestController();

		$countries = CountriesController::countriesList();
		$countries = withEmpty($countries);
		$statuses = $cont->getEnumProperties(['LeadType','LeadSource','LeadStatus']);
        $leadTypes = $statuses['LeadType'];
		$leadSources = $statuses['LeadSource'];
		$leadStatuses = $statuses['LeadStatus'];
		$users= UsersController::listByRoles(['Sales','Meet Booking']);
		$partners = PartnersController::partnersList();

		return view('leads.create',compact('countries','leadTypes','leadSources','leadStatuses','users','partners'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return View
	 */
	public function show($id)
	{
		$cont = new RestController();
		$lead = $cont->getRequest("Leads($id)?".'$expand=User($select=Id,UserName,FullName),Phone,Ads($orderby=Id+desc;$top=50),Country($select=CountryCode)');
		if($lead instanceof View)
		{
			return $lead;
		}
		if(!$this->isOwner($lead)){
			return view('errors.denied');
		}
		// check if the lead is client already
		$replaceWeb = ['www.','http://','https://'];
		$leadWeb = str_replace($replaceWeb,"",$lead->Website);

        $existing = false;
        if($leadWeb != ''){
            $existingHomepage = $cont->getRequest('ClientAlias?$filter='.urlencode("contains(Homepage,'".$leadWeb."')").'&$top=1');
            if(!$existingHomepage instanceof View){
                if(!empty($existingHomepage->value)) $existing['Homepage'] = $existingHomepage->value[0];

            }
        }
        if(isset($lead->PhoneNumber)){
            // check if the lead is client already by phone
            $replacePhone = ['+','+45'];
            $leadPhone= str_replace($replacePhone,"",$lead->PhoneNumber);
            $phoneExisting = $cont->getRequest('ClientAlias?$filter='.urlencode("contains(PhoneNumber,'".$leadPhone."')").'&$top=1');
            if(!$phoneExisting instanceof View){
                if(!empty($phoneExisting->value)) $existing['Phone'] = $phoneExisting->value[0];
            }
        }




        // fix for leads without websites, this will make the view show a input field
        if(in_array($lead->Website,['',"#"])){
            $lead->Website = null;
        }
        $appointments = false;
        $app = $cont->getRequest('CalendarEvents?$top=5&$filter='.urlencode("Model eq 'Lead' and ModelId eq $id and not Activity/any(d:d/ActivityType eq 'Completed' or d/ActivityType eq 'Cancel')").'&$expand=User($select=FullName)&$orderby=Start+desc');
        if(!$app instanceof View)
        {
            $appointments = $app->value;
        }
        JavaScriptFacade::put(['lead'=>$lead]);

        return view('leads.show',compact('lead','existing','appointments'));
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$cont = new RestController();

        $lead = $cont->getRequest("Leads($id)".'?$expand=Phone,User($select=Id,UserName),Taxonomy,UserSource($select=Id,UserName)');
		if($lead instanceof View){
			return $lead;
		}
		
        $countries = CountriesController::countriesList();
		$statuses = $cont->getEnumProperties(['LeadType','LeadSource','LeadStatus']);
		$leadTypes = withEmpty($statuses['LeadType']);
		$leadSources = withEmpty($statuses['LeadSource']);
		$leadStatuses = withEmpty($statuses['LeadStatus']);
		$users = UsersController::listByRoles(['Meet Booking','Sales','Administrator']);
		$partners = PartnersController::partnersList();

		return view('leads.edit',compact('lead','countries','leadTypes','leadSources','leadStatuses','users','partners'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	/**
	 * Shows all leads in a table
	 */
	public function all(){
		$users = UsersController::queryListByRoles(['Meet Booking','Sales','Administration']);
		$cont = new RestController();
		$leadStatuses = $cont->getEnumQuerySelect('LeadStatus','Status');
		$bookers = UsersController::queryListByRoles(['Meet Booking'],'Booker_Id');

        $weekAgo = date('c',strtotime('-7 days'));
        $monthAgo= date('c',strtotime('-30 days'));
        $adsFilters = [
            'Ads/any()' => 'With Ads',
            'not Ads/any()' => 'Without Ads',
//            "Ads/any(d:d/Created ge $weekAgo)" => "Ads last 7 days",
//            "Ads/any(d:d/Created ge $monthAgo)" => "Ads last 30 days", too slow
        ];

		return view('leads.all',compact('users','leadStatuses','bookers','adsFilters'));
	}


	/**
	 * Assigning leads menu
	 */
	public function assign(){
		$users = UsersController::usersList('false');
		//put the users array for js use
		JavaScriptFacade::put(['users' =>$users]);
		$users = UsersController::listByRoles(['Sales','Meet Booking']);

		$cont = new RestController();
		$leadStatuses = $cont->getEnumProperties(['LeadStatus']);

		return view('leads.assign',compact('users','leadStatuses'));
	}

	//moving leads from a person or a type to another person
	public function move()
	{
		$input = Input::all();
        $cont = new RestController();
		$userQuery = ($input['user_id']=="null")?" and User_Id eq null":" and User_Id eq '".$input['user_id']."'";
		$user = UsersController::getUserNameById($input['user_id']);
        JavaScriptFacade::put(['filters'=>$input]);
		$users = UsersController::listByRoles(['Meet Booking','Sales','Administrator']);
		$statuses = $cont->getEnumProperties(['LeadStatus']);

        $weekAgo = date('c',strtotime('-7 days'));
        $monthAgo= date('c',strtotime('-30 days'));
        $adsFilters = [
            'Ads/any()' => 'With Ads',
            'not Ads/any()' => 'Without Ads',
//            "Ads/any(d:d/Created ge $weekAgo)" => "Ads last 7 days",
//            "Ads/any(d:d/Created ge $monthAgo)" => "Ads last 30 days", too slow
        ];
		return view('leads.move',compact('users','leads','statuses'));
	}

	/**
	 * checks for entity ownership depending on a role
	 * @param $item
	 * @return bool
	 */
	public function isOwner($item)
	{
		$roles = Session::get('roles');
		$userId = Auth::user()->externalId;

		$result = !empty(array_intersect($roles, ['Administrator','Accounting','Developer']));
		if ($result) {
			return true;
		}
		// In leads, role doesn't matter, you either created or are assigned to the lead, otherwise you can't see it
		return in_array($userId,[$item->UserSource_Id,$item->User_Id,$item->Booker_Id]);
	}


    public function bot(){

        $ip = $_SERVER['SERVER_ADDR'];
// Output the IP address of your box
        return view('leads.bot',compact('ip'));
    }

    public function botsearch(){
//        ini_set('memory_limit', '-1');  //increase size as you need
//        $string = file_get_contents(public_path('shitwords.json'));
//        $json_a = json_decode($string, true);
        $data = Input::all();
        $oWord = $data[0];
        $word = $en_keyword = urlencode(trim($data[0]));
        //always wait 30 seconds for the requests, spam protection
        sleep(16);
        $content = get_web_page("www.google.bg/search?q=$word&oq=$word");
//        $pageDom = str_get_html($content);
//        $ads =  $pageDom->find('li[class=ads-ad]');
////



//        foreach ($ads as $ad){
//            $text = $ad->find('cite')->plaintext;
//        }
        $response = ['new' =>[],'existing' => [],'agent'=>$content['agent'],'html' => $content['content']];
        preg_match_all('#class="ads-ad(.*?)class="ads-creative(.*?)(</div><div>(.*?)</div>|</div>)#is',$content['content'],$matches);
        $cont = new RestController();
        foreach($matches[0] as $ad){
            preg_match('#<h3(.*?)</h3>#is',$ad,$head);
//            $dom = str_get_html($content);
//            $ad = $dom->find('li[class=ads-ad]');
            $head = trim(strip_tags($head[0]));
            preg_match('#&amp;adurl=(.*?)"#is',$ad,$dest);
            $dest = urldecode(trim(strip_tags($dest[1])));
            preg_match('#class="ads-creative(.*?)>(.*?)</div>#is',$ad,$line1);
            preg_match('#class="ads-creative(.*?)</div><div>(.*?)</div>#is',$ad,$line2);
            $line1 = trim(strip_tags($line1[2]));
            $line2 ='';
            if(isset($line2[2])) $line2 = trim(strip_tags($line2[2]));
            preg_match('#<cite(.*?)>(.*?)</cite>#is',$ad,$disp);
            $disp = trim(strip_tags(str_replace(['www.'],'',$disp[2])));
            $domain = get_domain($disp);
            if($domain){
                $domain = str_replace(['www.'],'',$domain);
                $domain = strtok($domain, '/');
                $exists = $cont->getRequest('Leads?$select=Id,Website&$filter='.urlencode("contains(Website,'".$domain."')"));
                if($exists instanceof View){
                    $cont->refreshToken();
                    return $response;
                }
                if(empty($exists->value)){
                    $result = $cont->postRequest('Leads',
                        [
                            'Website'=>$domain,
                            'User_Id'=>null,
                            'Ads'=>[[
                                'SearchWord'=>$oWord,
                                'AdText'=>"<b>".$head.'</b><br>'.$line1.'<br>'.$line2,
                                'ShowUri'=>$disp,
                                'DestUri'=>null
                            ]
                            ]
                        ]);
                    if(!$result instanceof View){
                        array_push($response['new'],$result);
                    }
                }else{
                    array_push($response['existing'],$exists->value[0]);

                }
            }
        }
//             if we found adds, go over the other pages
        if(!empty($matches[0])){
            // find the google pages, up to page 5
            $html = str_get_html($content['content']); // Parse the HTML, stored as a string in $string
            $table = $html->find('table[id=nav]');
            if(isset($table[0])){
                $links = $table[0]->find('a');
            }else{
                $links = [];
            }
            if(!empty($links)){
                $pages = array_slice($links, 1, 4);
                $i=2;
                foreach($pages as $link) {
                    $href = $link->href;
                    sleep(5);
                    $pagedContent = get_web_page("http://www.google.bg".$href);
                    preg_match_all('#class="ads-ad(.*?)class="ads-creative(.*?)(</div><div>(.*?)</div>|</div>)#is',$pagedContent['content'],$matches);
                    foreach($matches[0] as $ad) {
                        preg_match('#<h3(.*?)</h3>#is', $ad, $head);
                        $head = trim(strip_tags($head[0]));
                        preg_match('#&amp;adurl=(.*?)"#is', $ad, $dest);
                        $dest = urldecode(trim(strip_tags($dest[1])));
                        preg_match('#class="ads-creative(.*?)>(.*?)</div>#is', $ad, $line1);
                        preg_match('#class="ads-creative(.*?)</div><div>(.*?)</div>#is', $ad, $line2);
                        $line1 = trim(strip_tags($line1[2]));
                        $line2 = '';
                        if (isset($line2[2])) $line2 = trim(strip_tags($line2[2]));
                        preg_match('#<cite(.*?)>(.*?)</cite>#is', $ad, $disp);
                        $disp = trim(strip_tags($disp[2]));
                        $domain = get_domain($disp);
                        if($domain){
                            $domain = str_replace(['www.'],'',$domain);
                            $domain = strtok($domain, '/');

                            $exists = $cont->getRequest('Leads?$select=Id,Website&$filter='.urlencode("Website eq '".$domain."'"));
                            if($exists instanceof View){
                                $cont->refreshToken();
                                return $response;
                            }
                            if(empty($exists->value)){
                                $result = $cont->postRequest('Leads',
                                    [
                                        'Website'=>$domain,
                                        'User_Id'=>null,
                                        'Ads'=>[[
                                            'SearchWord'=>$oWord,
                                            'AdText'=>"<b>".$head.'</b><br>'.$line1.'<br>'.$line2,
                                            'ShowUri'=>$disp,
                                            'DestUri'=>null
                                        ]
                                        ]
                                    ]);
                                if(!$result instanceof View){
                                    array_push($response['new'],$result);
                                }
                            }else{
                                $exists->value[0]->Website .= ' page :'.$i;
                                array_push($response['existing'],$exists->value[0]);

                            }
                        }
                    }
                    $i++;
                }
            }
        }

        return response()->json($response);


    }
}
