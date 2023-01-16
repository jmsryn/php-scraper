<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class ScrapeController extends Controller
{
    public function test() 
    {
        $serverUrl = 'http://localhost:4444';
        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability('acceptSslCerts', false);

        
        $driver = RemoteWebDriver::create($serverUrl, $desiredCapabilities);
        $driver->manage()->window()->maximize();

        // Go to URL
        $driver->get('https://eservices.corangamite.vic.gov.au/T1PRProd/WebApps/eProperty/P1/eTrack/eTrackApplicationSearch.aspx?r=CSC.WEBGUEST&f=%24P1.ETR.SEARCH.ENQ');

        // Find search element by its id, write 'PHP' inside and submit
        $driver->findElement(WebDriverBy::xpath("(//input[@class='textField'])[1]")) // find search input element
            ->sendKeys('02/01/2023'); // fill the search box
        $driver->findElement(WebDriverBy::xpath("(//input[@class='textField'])[2]")) // find search input element
            ->sendKeys('16/01/2023'); // fill the search box

        // Make sure to always call quit() at the end to terminate the browser session
        $driver->findElement(WebDriverBy::xpath("(//input[@class='SearchButton'])[2]"))->click();
        $driver->quit();

        // $driver->quit();
    }
}
