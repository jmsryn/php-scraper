<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $webDriver;

    public $url = "https://eservices.corangamite.vic.gov.au/T1PRProd/WebApps/eProperty/P1/eTrack/eTrackApplicationSearch.aspx?r=CSC.WEBGUEST&f=%24P1.ETR.SEARCH.ENQ";

    public function __construct()
    {
        $this->webDriver = RemoteWebDriver::create(
            'http://localhost:4444',
            DesiredCapabilities::chrome()
        );
        $this->webDriver->manage()->window()->maximize();
    }
}
