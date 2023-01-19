<?php

namespace App\Http\Controllers;

//Set max execution time to 300 seconds to cater all the pages.
//May increase depends on how many pages
ini_set('max_execution_time', 300);

use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Illuminate\Http\Request;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Ramsey\Collection\Exception\NoSuchElementException as ExceptionNoSuchElementException;

class ScrapeController extends Controller
{
    public function scrape() 
    {
        $wait = new WebDriverWait($this->webDriver, 10);
        //Go to URL
        $this->webDriver->get($this->url);

        //filter search from 14 days
        $this->fillDateFrom($wait , "01/01/2022"); // <------------ You may change the date 
        $this->fillDateTo($wait, "19/01/2023"); // <------------ You may change the date
        $this->clickSearchBtn($wait);
        
        //call get table data then echo json result
        $tableData = $this->navigateThroughPagesAndGetData($wait);
        echo "<pre>" . $tableData . "</pre>";

        $this->webDriver->quit();
    }

    public function getTableData($wait) {
        $tableRows = $wait->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("//table[@cellpadding='3']//tr")));
        
        //Array results of all table rows
        $permitResultsArray = array();

        //Iterate per row and get data
        //loop started at 2 so table header will not be included
        for($i = 2; $i <= count($tableRows); $i++) {
            if ($i < count($tableRows)- 1) {
                $elements = $this->webDriver->findElements(WebDriverBy::xpath("(//table[@cellpadding='3']//tr)". "[" . $i . "]/td"));
                
                // Array of data per row
                $rowData = array();
                $keys = array("Application","Lodged","Formatted Address","Description");
                if ($i === count($tableRows)) { continue; }
                for($j = 0; $j < count($elements); $j++){
                    if(isset($keys[$j]) && $j < count($elements)){
                        $rowData[$keys[$j]] = $elements[$j]->getText();
                    }
                }
                $permitResultsArray[] = $rowData;
            }
        }
        
        return $permitResultsArray;
    }

    public function fillDateFrom($wait ,$date) {
        //Wait until the element is located then input date to
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//label[text()='Date From']/following::input)[1]")))->sendKeys($date);
    }

    public function fillDateTo($wait, $date) {
        //Wait until the element is located then input date from
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//label[text()='Date To']/following::input)[1]")))->sendKeys($date);
    }

    public function clickSearchBtn($wait) {
        //Wait until the element is clickable then click
        $wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("(//input[@class='SearchButton'])[2]")))->click();
    }

    public function navigateThroughPagesAndGetData($wait) {
        $fullResult = array();
        
        //try if the result only contain one page
        // try if there is pagination available
        try {
            $tablePages = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//table[@id='ctl00_Content_cusResultsGrid_repWebGrid_ctl00_grdWebGridTabularView']/tbody[1]/tr[17]")));
            //Get the first page
            $fullResult[] = $this->getTableData($wait);
            
            //Indicator of what page are you on
            //Starts clicking on page 2
            $page = 2;

            while(true) {
                //try to find the next page and catch if the next is the spread sign or the triple dot
                // Used xpath contains text to determine the page we are on
                // After click next page store it on the array declared above
                try {
                    $currentTd = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//td[@colspan='4']/table/tbody/tr/td/a[contains(text(),'". $page ."')]")));
                    if ($currentTd->isDisplayed()) {
                        $currentTd->click();
                        $fullResult[] = $this->getTableData($wait);
                        $page++;
                    }
                } catch(NoSuchElementException $e) {
                    $nextSetOfPage = $wait->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("//td[@colspan='4']/table/tbody/tr/td/a[contains(text(),'...')]")));
                    if (count($nextSetOfPage) > 1) {
                        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("(//td[@colspan='4']/table/tbody/tr/td/a[contains(text(),'...')])[2]")))->click();
                    } else {
                        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//td[@colspan='4']/table/tbody/tr/td/a[contains(text(),'...')]")))->click();
                    }
                    $fullResult[] = $this->getTableData($wait);
                    $page++;
                }
                // Check if the last part of the navigation row if it is not a triple dot then the page is greater than the last pagination number
                $lastPages = $wait->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("//td[@colspan='4']/table/tbody/tr/td")));
                $lastPagination = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//td[@colspan='4']/table/tbody/tr/td[". count($lastPages) . "]")));
                if ($lastPagination->getText() < $page && $lastPagination->getText() != "...") {
                    break;
                }
            }

            // Merged all arrays from pages to pages to one
            $mergedArray = array();
            foreach ($fullResult as $array) {
                $mergedArray = array_merge($mergedArray, $array);
            }
            return json_encode($mergedArray, JSON_PRETTY_PRINT);
        } catch(NoSuchElementException $e) {
            return json_encode($this->getTableData($wait), JSON_PRETTY_PRINT);
            
        }

    }
}
