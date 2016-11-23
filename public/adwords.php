<?php
/**
 * Created by PhpStorm.
 * User: dib
 * Date: 28-Apr-16
 * Time: 1:22 PM
 */
require_once '../vendor/googleads/googleads-php-lib/examples/AdWords/v201603/init.php';

$user = new AdWordsUser('../config/google-ads.ini');
$user->SetClientCustomerId('484-456-3504');
$managedCustomerService = $user->GetService('ManagedCustomerService');

$accountSelector = new Selector();
$accountSelector->fields = array('CustomerId','CurrencyCode');

$campaingSelector = new Selector();
$campaingSelector->fields = array('Id','Name');

$adgroupsSelector = new Selector();
$adgroupsSelector->fields = array('Id');
$adgroupsSelector->predicates[] =
    new Predicate('CampaignId', 'IN', array());
$output = fopen("words.csv", "w");

$graph = $managedCustomerService->get($accountSelector);
$words = [];
//$item = null;
//$lastId = '8516456393';
//
//foreach($graph->entries as $id=>$entry) {
//    if ($lastId == $entry->customerId) {
//        $item = $id;
//        break;
//    }
//}
//$graph->entries = array_splice($graph->entries,$item);
// get all managed customers
foreach ($graph->entries as $id=>$entry){
//    if($entry->currencyCode != "DKK") continue;
    echo $entry->customerId;
    //initialize the
    $user->SetClientCustomerId($entry->customerId);

    $campaignService = $user->GetService('CampaignService');
    $adGroupService = $user->GetService('AdGroupService');
    $adGroupCriService = $user->GetService('AdGroupCriterionService');

    $campaigns = $campaignService->get($campaingSelector);
    // Create selector.
    foreach ($campaigns->entries as $campaign){
        $campaignId = $campaign->id;
        $adgroupSelect = new Selector();
        $adgroupSelect->fields = array('Id', 'Name');
        $adgroupSelect->predicates[] =
            new Predicate('CampaignId', 'IN', array($campaignId));
        $adgroups = $adGroupService->get($adgroupSelect);
        if($adgroups->entries != null){
            foreach ($adgroups->entries as $adgroup) {
                $kwSelector = new Selector();
                $kwSelector->fields = array('Id', 'CriteriaType', 'KeywordMatchType',
                    'KeywordText');
                // Create predicates.
                $kwSelector->predicates[] = new Predicate('AdGroupId','IN', array($adgroup->id));
                $kwSelector->predicates[] = new Predicate('CriteriaType','IN', array('KEYWORD'));

                $keywords = $adGroupCriService->get($kwSelector);
                if($keywords->entries != null) {
                    foreach ($keywords->entries as $keyword) {
                        $word = $keyword->criterion->text;
                        $replace = ["/[+]/","/[[]/","/[]]/"];
                        $word = preg_replace($replace,'',$word);
                        //str_replace(['+', '[', ']'], '', $word);
                        if (!in_array($word, $words)) {
                            array_push($words, $word);
                            fputcsv($output, [$word]); // here you can change delimiter/enclosure
                        };
                    }
                }
            }
        }
    }
}
fclose($output);

//$out = fopen('words.csv', 'w');
//fputcsv($out, $words);
//fclose($out);
?>
