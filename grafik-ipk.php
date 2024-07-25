<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Set path to the credentials file
$pathToCredentials = 'storied-precept-243308-adf6b0bb18cb.json';

// Create client object and set application name
$client = new Client();
$client->setApplicationName('Google Sheets API PHP');
$client->setScopes([Sheets::SPREADSHEETS_READONLY]);
$client->setAuthConfig($pathToCredentials);

// Create service object
$service = new Sheets($client);

// The ID of the spreadsheet to retrieve data from
$spreadsheetId = '119wjg7Z_jjZi0v7tmHpxOLZWaPhbzeV3k9I4daclJdI';
// The range of cells to retrieve
$range = 'Data Wisuda!A2:AF';  // Adjust range based on your sheet structure

$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

$data = [];
$ipk_distribution = [
    ['range' => '(2, 2.2]', 'min' => 2.0, 'max' => 2.2, 'count' => 0],
    ['range' => '(2.2, 2.4]', 'min' => 2.2, 'max' => 2.4, 'count' => 0],
    ['range' => '(2.4, 2.6]', 'min' => 2.4, 'max' => 2.6, 'count' => 0],
    ['range' => '(2.6, 2.8]', 'min' => 2.6, 'max' => 2.8, 'count' => 0],
    ['range' => '(2.8, 3]', 'min' => 2.8, 'max' => 3.0, 'count' => 0],
    ['range' => '(3, 3.2]', 'min' => 3.0, 'max' => 3.2, 'count' => 0],
    ['range' => '(3.2, 3.4]', 'min' => 3.2, 'max' => 3.4, 'count' => 0],
    ['range' => '(3.4, 3.6]', 'min' => 3.4, 'max' => 3.6, 'count' => 0],
    ['range' => '(3.6, 3.8]', 'min' => 3.6, 'max' => 3.8, 'count' => 0],
    ['range' => '(3.8, 4]', 'min' => 3.8, 'max' => 4.0, 'count' => 0],
];

$ipk_npm = null;
$npm_target = isset($_GET['npm']) ? $_GET['npm'] : null;
$tanggal_transkrip_target = null;

if (!empty($values)) {
    // Find the target Tanggal Transkrip if npm is specified
    if ($npm_target) {
        foreach ($values as $row) {
            if ($row[1] === $npm_target) {
                $tanggal_transkrip_target = strtotime($row[7]); // Tanggal Transkrip is in column H (index 7)
                break;
            }
        }
    }

    foreach ($values as $row) {
        $ipk = (float)$row[9]; // Assumes IPK is in the 10th column (index 9)
        $tanggal_transkrip = strtotime($row[7]);

        // Check if the row matches the criteria
        if (!$npm_target || ($tanggal_transkrip_target && $tanggal_transkrip <= $tanggal_transkrip_target)) {
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
        }

        // Get IPK for the specified NPM
        if ($npm_target === $row[1]) {
            $ipk_npm = $ipk;
        }
    }
}

// print_r($ipk_distribution);
// die();

function generateChart($ipk_distribution, $ipk_npm = null) {
    $labels = array_column($ipk_distribution, 'range');
    $values = array_column($ipk_distribution, 'count');
    $nb_bars = count($values);

    $width = 1200;  // Increased width
    $height = 800;  // Increased height
    $bar_width = 50;  // Decreased bar width
    $bar_gap = 100;  // Adjusted gap between bars
    $x_start = 100;  // Increased starting x position
    $y_start = 100;  // Increased starting y position
    $bar_color = [0, 0, 128]; // Navy color for bars
    $line_color = [255, 140, 0]; // Orange color for vertical line
    $line_thickness = 5; // Line thickness in pixels

    // Create image
    $image = imagecreatetruecolor($width, $height);
    $background_color = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

    // Allocate colors
    $bar_color = imagecolorallocate($image, $bar_color[0], $bar_color[1], $bar_color[2]);
    $line_color = imagecolorallocate($image, $line_color[0], $line_color[1], $line_color[2]);
    $text_color = imagecolorallocate($image, 0, 0, 0);

    // Draw bar chart
    $max_value = max($values);
    $scale = ($height - $y_start * 2) / $max_value;

    foreach ($values as $i => $value) {
        $x1 = intval($x_start + $i * $bar_gap);
        $x2 = intval($x1 + $bar_width);
        $y1 = intval($height - $y_start);
        $y2 = intval($y1 - round($value * $scale)); // Ensure integer value for y2

        imagefilledrectangle($image, $x1, $y1, $x2, $y2, $bar_color);
    }

    // Draw vertical line if IPK is provided
    if ($ipk_npm !== null) {
        $index = null;
        foreach ($ipk_distribution as $i => $range) {
            if ($ipk_npm > $range['min'] && $ipk_npm <= $range['max']) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            $x = intval($x_start + ($index * $bar_gap) + ($bar_width / 2));
            // Draw thick vertical line by drawing multiple lines
            for ($thickness = -($line_thickness / 2); $thickness <= ($line_thickness / 2); $thickness++) {
                imageline($image, intval($x + $thickness), $y_start, intval($x + $thickness), $height - $y_start, $line_color);
            }
        }
    }

    // Draw labels and add spacing
    foreach ($labels as $i => $label) {
        $text_width = imagefontwidth(5) * strlen($label); // Using default font size 5
        $x = intval($x_start + $i * $bar_gap + ($bar_width - $text_width) / 2);
        $y = intval($height - $y_start + 30); // Position label below the bar

        imagestring($image, 5, $x, $y, $label, $text_color);
    }

    // Draw the value on top of the bar
    foreach ($values as $i => $value) {
        $x1 = intval($x_start + $i * $bar_gap);
        $x2 = intval($x1 + $bar_width);
        $y1 = intval($height - $y_start);
        $y2 = intval($y1 - round($value * $scale)); // Ensure integer value for y2

        $text = (string)$value;
        $text_width = imagefontwidth(5) * strlen($text); // Using default font size 5
        $text_x = intval(($x1 + $x2) / 2 - $text_width / 2);
        imagestring($image, 5, $text_x, intval($y2 - 15), $text, $text_color);
    }

    // Draw title and axis labels
    $title = 'Distribusi IPK / GPA Distribution';
    $title_width = imagefontwidth(5) * strlen($title);
    imagestring($image, 5, intval($width / 2 - $title_width / 2), 30, $title, $text_color);

    // Draw Y-axis label
    $y_axis_label = 'Frekuensi / Frequency';
    $y_axis_label_width = imagefontwidth(5) * strlen($y_axis_label);
    imagestringup($image, 5, 30, intval($height / 2 + $y_axis_label_width / 2), $y_axis_label, $text_color);

    // Draw X-axis label
    $x_axis_label = 'Rentang IPK / GPA Range';
    $x_axis_label_width = imagefontwidth(5) * strlen($x_axis_label);
    imagestring($image, 5, intval($width / 2 - $x_axis_label_width / 2), $height - 50, $x_axis_label, $text_color);

    // Draw X and Y axis lines
    imageline($image, $x_start, $height - $y_start, $width - $x_start, $height - $y_start, $text_color); // X-axis
    imageline($image, $x_start, $y_start, $x_start, $height - $y_start, $text_color); // Y-axis

    // Output the image
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

function distribusi_ipk_to_image($ipk_distribution) {
    // Settings for the image
    $column_width = 80;
    $padding = 5;
    $row_height = 20;

    // Calculate the size of the image
    $width = $column_width * (count($ipk_distribution) + 1); // +1 for labels
    $height = $row_height * 2 + $padding * 2; // Two rows (Range, Frequency)

    // Create the image
    $image = imagecreatetruecolor($width, $height);
    $background_color = imagecolorallocate($image, 255, 255, 255); // White background
    $text_color = imagecolorallocate($image, 0, 0, 0); // Black text
    $border_color = imagecolorallocate($image, 0, 0, 0); // Black border

    // Fill the background
    imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

    // Set the font size and path (using built-in font)
    $font_size = 3; // Using built-in font size (1 to 5, 1 being smallest)
    $font_width = imagefontwidth($font_size);
    $font_height = imagefontheight($font_size);

    // Calculate horizontal positions for each column
    $x_positions = [];
    for ($i = 0; $i <= count($ipk_distribution); $i++) { // +1 for labels
        $x_positions[] = $i * $column_width;
    }

    // Add labels
    $label_range = 'Range';
    $label_frequency = 'Frequency';
    $range_label_x = (int)($padding + ($column_width - strlen($label_range) * $font_width) / 2);
    $frequency_label_x = (int)($padding + ($column_width - strlen($label_frequency) * $font_width) / 2);
    imagestring($image, $font_size, $range_label_x, $padding, $label_range, $text_color);
    imagestring($image, $font_size, $frequency_label_x, $row_height + $padding, $label_frequency, $text_color);

    // Add headers and data
    foreach ($ipk_distribution as $index => $range) {
        // Center the text for 'Rentang IPK'
        $rentang_text = $range['range'];
        $rentang_x = (int)($x_positions[$index + 1] + ($column_width - strlen($rentang_text) * $font_width) / 2);
        imagestring($image, $font_size, $rentang_x, $padding, $rentang_text, $text_color);

        // Center the text for 'Frekuensi'
        $frekuensi_text = (string)$range['count'];
        $frekuensi_x = (int)($x_positions[$index + 1] + ($column_width - strlen($frekuensi_text) * $font_width) / 2);
        imagestring($image, $font_size, $frekuensi_x, $row_height + $padding, $frekuensi_text, $text_color);
    }

    // Draw the table borders
    for ($i = 0; $i <= count($x_positions); $i++) {
        $x = $x_positions[$i] ?? $width;
        imageline($image, $x, 0, $x, $height, $border_color); // Left and right borders
    }

    // Draw top border
    imageline($image, 0, 0, $width, 0, $border_color);
    // Draw bottom border
    imageline($image, 0, $height - 1, $width, $height - 1, $border_color);
    // Draw middle border separating labels and data
    imageline($image, 0, $row_height + $padding - 2, $width, $row_height + $padding - 2, $border_color);
    // Draw right border
    imageline($image, $width - 1, 0, $width - 1, $height, $border_color);

    // Output the image as a PNG
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// Generate the chart image

if (isset($_GET['chart']) && $_GET['chart'] === 'true') {
    $chart_data=$ipk_distribution;
    generateChart($chart_data, $ipk_npm);
    exit;
}
if (isset($_GET['tableimage']) && $_GET['tableimage'] === 'true') {
    $tabel_data=$ipk_distribution;
    distribusi_ipk_to_image($tabel_data);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>Data Mahasiswa</title>
</head>
<body>
    <table>
        <tr>
            <td><center><a href="index.php"><img src="Logo_UnivLampung.png" style="width:100px;height:100px;"></a></center></td>
            <td><b>UNIVERSITY OF LAMPUNG <br/>
            FACULTY OF MATHEMATICS AND NATURAL SCIENCE <br/>
            Department of Computer Science</b> <br/>
            Jl. Prof. Dr. Soemantri Brodjonegoro No. 1 Bandar Lampung 35145</td>
        </tr>
    </table>

    <h2>Distribusi IPK / GPA Distribution</h2>

    <?php 
    $srcgrafik = (!empty($_GET['npm'])) ? ("?chart=true&npm=".$_GET['npm']) : ("?chart=true" );
    $srctabimg = (!empty($_GET['npm'])) ? ("?tableimage=true&npm=".$_GET['npm']) : ("?chart=true" );
   ?>
        <img src="<?php echo $srcgrafik; ?>" alt="Grafik Distribusi IPK">
        <img src="<?php echo $srctabimg; ?>" alt="Tabel Distribusi IPK">
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Rentang IPK / GPA Range</th>
                <th>Frekuensi / Frequency</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($ipk_distribution as $index => $ipkrange): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($index + 1); ?></td>
                        <td><?php echo htmlspecialchars($ipkrange['range']); ?></td>
                        <td><?php echo htmlspecialchars($ipkrange['count']); ?></td>
                    </tr>
            <?php endforeach; ?>
            
        </tbody>
    </table>

    <h2>Detail</h2>
    <table>
        <thead>
            <tr>
                <th>NPM</th>
                <th>Nama / Name</th>
                <th>IPK / GPA</th>
                <th>SKS / Credit</th>
                <th>TOEFL</th>
                <th>Lama Studi (Tahun) / Length of Study (Year)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['npm']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars($row['ipk']); ?></td>
                    <td><?php echo htmlspecialchars($row['sks']); ?></td>
                    <td><?php echo htmlspecialchars($row['toefl']); ?></td>
                    <td><?php echo htmlspecialchars($row['lama_studi']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

