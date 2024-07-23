<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

function getClient($pathToCredentials) {
    $client = new Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
    $client->setAuthConfig($pathToCredentials);
    return $client;
}

function getSpreadsheetData($service, $spreadsheetId, $range) {
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    return $response->getValues();
}

function processData($values, $npmFilter = null) {
    $data = [];
    $ipk_distribution = [
        ['range' => '(2, 2.2)', 'min' => 2.0, 'max' => 2.2, 'count' => 0],
        ['range' => '(2.2, 2.4)', 'min' => 2.2, 'max' => 2.4, 'count' => 0],
        ['range' => '(2.4, 2.6)', 'min' => 2.4, 'max' => 2.6, 'count' => 0],
        ['range' => '(2.6, 2.8)', 'min' => 2.6, 'max' => 2.8, 'count' => 0],
        ['range' => '(2.8, 3)', 'min' => 2.8, 'max' => 3.0, 'count' => 0],
        ['range' => '(3, 3.2)', 'min' => 3.0, 'max' => 3.2, 'count' => 0],
        ['range' => '(3.2, 3.4)', 'min' => 3.2, 'max' => 3.4, 'count' => 0],
        ['range' => '(3.4, 3.6)', 'min' => 3.4, 'max' => 3.6, 'count' => 0],
        ['range' => '(3.6, 3.8)', 'min' => 3.6, 'max' => 3.8, 'count' => 0],
        ['range' => '(3.8, 4)', 'min' => 3.8, 'max' => 4.0, 'count' => 0],
    ];

    $ipk_npm = null;

    if (!empty($values)) {
        foreach ($values as $row) {
            $ipk = (float)$row[9]; // Assumes IPK is in the 10th column (index 9)
            $data[] = [
                'npm' => $row[1],
                'nama' => $row[2],
                'ipk' => $ipk,
                'sks' => $row[18],
                'toefl' => $row[19],
                'lama_studi' => $row[17]
            ];

            // Update IPK distribution count
            foreach ($ipk_distribution as &$range) {
                if ($ipk > $range['min'] && $ipk <= $range['max']) {
                    $range['count']++;
                    break;
                }
            }

            // Get IPK for the specified NPM
            if ($npmFilter !== null && $npmFilter === $row[1]) {
                $ipk_npm = $ipk;
            }
        }
    }

    return [$data, $ipk_distribution, $ipk_npm];
}

function generateChart($ipk_distribution, $ipk_npm = null) {
    require_once ('jpgraph/src/jpgraph.php');
    require_once ('jpgraph/src/jpgraph_bar.php');
    require_once ('jpgraph/src/jpgraph_line.php');

    // Extract ranges and counts
    $labels = array_column($ipk_distribution, 'range');
    $values = array_column($ipk_distribution, 'count');

    // Create the graph and set its properties
    $graph = new Graph(1200, 800);
    $graph->SetScale('textlin');
    $graph->SetMargin(80, 30, 50, 100);
    $graph->title->Set('Distribusi IPK / GPA Distribution');
    $graph->xaxis->title->Set('Rentang IPK / GPA Range');
    $graph->xaxis->SetTickLabels($labels);
    $graph->yaxis->title->Set('Frekuensi / Frequency');

    // Create the bar plot
    $bplot = new BarPlot($values);
    $bplot->SetFillColor('navy');
    $bplot->value->Show();
    $bplot->value->SetFormat('%d');
    $graph->Add($bplot);

    // Draw the IPK line if provided
    if ($ipk_npm !== null) {
        $index = null;
        foreach ($ipk_distribution as $i => $range) {
            if ($ipk_npm > $range['min'] && $ipk_npm <= $range['max']) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            $xPosition = $index + 1; // JpGraph index starts from 1 for the line plot
            $lineplot = new LinePlot([$values[$index]]);
            $lineplot->SetLegend('IPK');
            $lineplot->SetColor('orange');
            $lineplot->mark->SetType(MARK_FILLEDCIRCLE);
            $lineplot->mark->SetFillColor('orange');
            $lineplot->mark->SetWidth(10);
            $graph->Add($lineplot);
        }
    }

    // Output the graph
    header('Content-Type: image/png');
    $graph->Stroke();
}

$pathToCredentials = 'storied-precept-243308-adf6b0bb18cb.json';
$spreadsheetId = '119wjg7Z_jjZi0v7tmHpxOLZWaPhbzeV3k9I4daclJdI';
$range = 'Data Wisuda!A2:AF';  // Adjust range based on your sheet structure

$client = getClient($pathToCredentials);
$service = new Sheets($client);
$values = getSpreadsheetData($service, $spreadsheetId, $range);

$npmFilter = isset($_GET['npm']) ? $_GET['npm'] : null;
list($data, $ipk_distribution, $ipk_npm) = processData($values, $npmFilter);

if (isset($_GET['chart']) && $_GET['chart'] === 'true') {
    generateChart($ipk_distribution, $ipk_npm);
    exit;
}
?>
