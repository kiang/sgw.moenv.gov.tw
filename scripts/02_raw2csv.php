<?php
require __DIR__ . '/vendor/autoload.php';

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;

$basePath = dirname(__DIR__);

$cities = [
    'A' => '臺北市',
    'F' => '新北市',
    'B' => '臺中市',
    'L' => '臺中市',
    'D' => '臺南市',
    'R' => '臺南市',
    'E' => '高雄市',
    'S' => '高雄市',
    'C' => '基隆市',
    'H' => '桃園市',
    'O' => '新竹市',
    'J' => '新竹縣',
    'K' => '苗栗縣',
    'N' => '彰化縣',
    'M' => '南投縣',
    'P' => '雲林縣',
    'I' => '嘉義市',
    'Q' => '嘉義縣',
    'T' => '屏東縣',
    'G' => '宜蘭縣',
    'U' => '花蓮縣',
    'V' => '臺東縣',
    'X' => '澎湖縣',
    'W' => '金門縣',
    'Z' => '連江縣',
];

$pool = [];
$proj4 = new Proj4php();
$projTWD97    = new Proj('EPSG:3826', $proj4);
$projWGS84  = new Proj('EPSG:4326', $proj4);
$csvPath = $basePath . '/docs/csv';
if (!file_exists($csvPath)) {
    mkdir($csvPath, 0777, true);
}

foreach (glob($basePath . '/raw/SiteInfo/*/*.html') as $htmlFile) {
    $p = pathinfo($htmlFile);
    $p = pathinfo($p['dirname']);
    $city = $cities[$p['filename']];
    $html = file_get_contents($htmlFile);
    $pos = strpos($html, '<tbody>');
    if (false !== $pos) {
        $posEnd = strpos($html, '</tbody>', $pos);
        $lines = explode('</tr>', substr($html, $pos, $posEnd - $pos));
        $info = [];
        foreach ($lines as $line) {
            $cols = explode('</th>', $line);
            if (count($cols) !== 2) {
                continue;
            }
            foreach ($cols as $k => $v) {
                $cols[$k] = trim(strip_tags($v));
                $cols[$k] = preg_replace("/[\n\r ]/", "", $cols[$k]);
            }
            $info[$cols[0]] = $cols[1];
        }
        $twd97 = preg_split('/[^0-9]+/', $info['座標']);
        $pointSrc = new Point($twd97[1], $twd97[2], $projTWD97);
        $pointDest = $proj4->transform($projWGS84, $pointSrc);
        $lnglat = $pointDest->toArray();
        $info['經度'] = $lnglat[0];
        $info['緯度'] = $lnglat[1];
        unset($info['座標']);

        if (!isset($pool[$city])) {
            $pool[$city] = fopen($csvPath . '/' . $city . '.csv', 'w');
            fputcsv($pool[$city], array_keys($info));
        }
        fputcsv($pool[$city], $info);
    }
}
