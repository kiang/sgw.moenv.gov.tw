<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

$browser = new HttpBrowser(HttpClient::create());

$browser->request('GET', 'https://sgw.moenv.gov.tw/SgwSiteInfo/SituationMap/');

$rawPath = $basePath . '/raw/SiteList';
if (!file_exists($rawPath)) {
    mkdir($rawPath, 0777, true);
}

$page = 1;
$totalPage = 100;
while ($page <= $totalPage) {
    $targetFile = $rawPath . '/' . $page . '.json';
    if (!file_exists($targetFile)) {
        $browser->request('POST', 'https://sgw.moenv.gov.tw/SgwSiteInfo/SituationMap/GetSiteListHandler.ashx', [
            'NewAreaNo' => 'A,F,B,D,E,C,H,O,J,K,N,M,P,I,Q,T,G,U,V,X,W,Z',
            'Township' => '',
            'SiteKind' => '',
            'SituationType' => '',
            'PollutionUnion' => 'true',
            'Soil' => '',
            'Gw' => '',
            'SDate' => '',
            'EDate' => '',
            'Address' => '',
            'CoorX' => '',
            'CoorY' => '',
            'SiteNo' => '',
            'PageNo' => $page,
            'PageSize' => 20,
        ]);

        $response = $browser->getResponse()->getContent();
        $data = json_decode($response, true);
        file_put_contents($targetFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    } else {
        $data = json_decode(file_get_contents($targetFile), true);
    }

    if ($page === 1) {
        $totalPage = $data['TotalPages'];
    }

    foreach ($data['Data'] as $site) {
        $type = substr($site['SiteNo'], 0, 1);
        $siteInfoPath = $basePath . '/raw/SiteInfo/' . $type;
        if (!file_exists($siteInfoPath)) {
            mkdir($siteInfoPath, 0777, true);
        }
        $siteInfoFile = $siteInfoPath . '/' . $site['SiteNo'] . '.html';
        file_put_contents($siteInfoFile, file_get_contents('https://sgw.moenv.gov.tw/SgwSiteInfo/SituationMap/Info?k=' . $site['SiteNo']));
    }

    $page++;
}
