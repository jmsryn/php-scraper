<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

class ScrapeController extends Controller
{
    public function scrape() 
    {
        $wait = new WebDriverWait($this->webDriver, 10);
        $currentTimeStamp = time();

        //Go to URL
        $this->webDriver->get($this->url);

        //filter search from 14 days
        $this->fillDateTo($wait ,date("d/m/y", $currentTimeStamp));
        $this->fillDateFrom($wait, date("d/m/y", strtotime("-14 days", $currentTimeStamp)));
        $this->clickSearchBtn($wait);
        
        //call get table data then echo json result
        $tableData = $this->getTableData($wait);
        echo $tableData;

        $this->webDriver->quit();
    }

    public function getTableData($wait) {
        $tableRows = $wait->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("//table[@cellpadding='3']//tr")));

        //Array results of all table rows
        $permitResultsArray = array();

        //Iterate per row and get data
        //loop started at 2 so table header will not be included
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

        return json_encode($permitResultsArray);
    }

    public function fillDateTo($wait ,$date) {
        //Wait until the element is located then input date to
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//label[text()='Date From']/following::input)[1]")))->sendKeys($date);
    }

    public function fillDateFrom($wait, $date) {
        //Wait until the element is located then input date from
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//label[text()='Date To']/following::input)[1]")))->sendKeys($date);
    }

    public function clickSearchBtn($wait) {
        //Wait until the element is clickable then click
        $wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("(//input[@class='SearchButton'])[2]")))->click();
    }
}
