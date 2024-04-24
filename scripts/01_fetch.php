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
    if ($page === 1) {
        $totalPage = $data['TotalPages'];
    }
    $targetFile = $rawPath . '/' . $page . '.json';

    file_put_contents($targetFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $page++;
}
