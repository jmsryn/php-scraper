<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Js;

class ScrapeController extends Controller
{
    public function scrape() 
    {
        //Go to URL
        $this->webDriver->get($this->url);

        $wait = new WebDriverWait($this->webDriver, 10);

        $dateFrom = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//label[text()='Date From']/following::input)[1]")))->sendKeys('02/01/2023');
        $dateTo = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//label[text()='Date To']/following::input)[1]")))->sendKeys('16/01/2023');
        $searchBtn = $wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("(//input[@class='SearchButton'])[2]")))->click();
        $tableRows = $wait->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("//table[@cellpadding='3']//tr")));

        //Array results of all table rows
        $permitResultsArray = array();

        for($i = 2; $i <= count($tableRows); $i++) {
            $elements = $this->webDriver->findElements(WebDriverBy::xpath("(//table[@cellpadding='3']//tr)". "[" . $i . "]/td"));
            
            // Array of data per row
            $rowData = array();
            $rowData["Application"] = $elements[0]->getText();
            $rowData["Lodged"] = $elements[1]->getText();
            $rowData["Formatted Address"] = $elements[2]->getText();
            $rowData["Description"] = $elements[3]->getText();
            $permitResultsArray[] = $rowData;
        }

        $jsonPermitResult = json_encode($permitResultsArray);

        echo $jsonPermitResult;


        $this->webDriver->quit();
    }
}
