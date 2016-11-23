<?php namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

define('APPLICATION_NAME', 'Green.Click CRM');
define('CREDENTIALS_PATH', '/resources/asset/creditentials.json');
define('CLIENT_SECRET_PATH', 'resources/asset/google_secret.json');

class GoogleCalendar
{

    protected $client;

    protected $service;

    public function initService($impersonate = null)
    {
        $impersonate = $impersonate == null ? Auth::user()->email : $impersonate;
        /* Get config variables */
        $client_id = Config::get('google.client_id');
        $service_account_name = Config::get('google.service_account_name');
        $key_file_location = base_path() . Config::get('google.key_file_location');

        $key = file_get_contents($key_file_location);
        /* Add the scopes you need */
        $scopes = array('https://www.googleapis.com/auth/calendar');

        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_name,
            $scopes,
            $key,
            'notasecret',                                 // Default P12 password
            'http://oauth.net/grant_type/jwt/1.0/bearer', // Default grant type
            $impersonate

        );
        $this->client = new \Google_Client();
        $this->client->setApplicationName("Green.Click CRM");

        $this->client->setAssertionCredentials($cred);
        $auth = $this->client->getAuth();

        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion($cred);
        }
        $this->service = new \Google_Service_Calendar($this->client);
        return $this;
    }

    public function get($calendarId)
    {
        $results = $this->service->calendars->get($calendarId);
        return $results;
    }

    public function calendarList()
    {
        return $this->service->calendarList->listCalendarList();
    }

    public function getEvents($userEmail)
    {
        $service = $this->initService();
        return $service->service->events->listEvents($userEmail)->getItems();
    }

    public function searchEvents($params){

        $service = $this->initService();
        $params['timeMin']= date('c');
        $events = $service->service->events->listEvents('primary',$params)->getItems();
        $a=[];
        foreach ($events as $event){
            array_push($a,['creator'=>$event->getCreator(),'start'=>$event->getStart(),'source'=>$event->getSource(),'summary'=>$event->getSummary(),'description'=>$event->getDescription(),'htmlLink'=>$event->getHtmlLink()]);
        }
        return $a;
    }

    public function createEvent($data)
    {
        
        $calendarId = Auth::user()->email;
        $event = new \Google_Service_Calendar_Event(
            $data
        );
        $service = $this->initService();
        $event = $service->service->events->insert($calendarId, $event);
        return $event;
    }

}