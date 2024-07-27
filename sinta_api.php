<?php
require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

function getResearchs($sid = 6021756) {
    $client = new Client(HttpClient::create(['timeout' => 60]));
    $url = 'https://sinta.kemdikbud.go.id/authors/profile/' . $sid . '/?view=researches';
    $crawler = $client->request('GET', $url);

    $researches = [];

    $crawler->filter('.ar-list-item')->each(function ($node) use (&$researches) {
        $title = $node->filter('.ar-title a')->text();
        $leader = $node->filter('.ar-meta a')->eq(0)->text();
        $fundingSource = $node->filter('.ar-meta a.ar-pub')->text();
        $members = $node->filter('.ar-meta')->eq(1)->filter('a')->each(function ($memberNode) {
            // Mengambil hanya nama anggota, mengabaikan teks 'Personils : '
            if (strpos($memberNode->text(), 'Personils : ') === false) {
                return $memberNode->text();
            }
        });
        $members = array_filter($members); // Menghapus nilai null yang dihasilkan oleh teks deskriptif

        $year = $node->filter('.ar-year')->text();
        $amount = $node->filter('.ar-quartile')->eq(0)->text();

        $researches[] = [
            'title' => $title,
            'leader' => $leader,
            'members' => $members,
            'funding_source' => $fundingSource,
            'year' => $year,
            'amount' => $amount,
        ];
    });

    return json_encode($researches, JSON_PRETTY_PRINT);
}
function getCSes($sid) {
    $client = new Client(HttpClient::create(['timeout' => 60]));
    $url = 'https://sinta.kemdikbud.go.id/authors/profile/' . $sid . '/?view=services';
    $crawler = $client->request('GET', $url);

    $services = [];

    $crawler->filter('.ar-list-item')->each(function ($node) use (&$services) {
        $title = $node->filter('.ar-title a')->text();
        $leader = $node->filter('.ar-meta a')->eq(0)->text();
        $fundingSource = $node->filter('.ar-meta a.ar-pub')->text();
        $members = $node->filter('.ar-meta')->eq(1)->filter('a')->each(function ($memberNode) {
            // Mengambil hanya nama anggota, mengabaikan teks 'Personils : '
            if (strpos($memberNode->text(), 'Personils : ') === false) {
                return $memberNode->text();
            }
        });
        $members = array_filter($members); // Menghapus nilai null yang dihasilkan oleh teks deskriptif

        $year = $node->filter('.ar-year')->text();
        $amount = $node->filter('.ar-quartile')->eq(0)->text();

        $services[] = [
            'title' => $title,
            'leader' => $leader,
            'members' => $members,
            'funding_source' => $fundingSource,
            'year' => $year,
            'amount' => $amount,
        ];
    });

    return json_encode($services, JSON_PRETTY_PRINT);
}
function getIPRs($sid) {
    $client = new Client(HttpClient::create(['timeout' => 60]));
    $url = 'https://sinta.kemdikbud.go.id/authors/profile/' . $sid . '/?view=iprs';
    $crawler = $client->request('GET', $url);

    $iprs = [];

    $crawler->filter('.ar-list-item')->each(function ($node) use (&$iprs) {
        $title = trim($node->filter('.ar-title a')->text());
        $inventorsRaw = $node->filter('.ar-meta a')->eq(0)->text();
        $inventors = trim(str_replace('Inventor : ', '', $inventorsRaw));
        $year = $node->filter('.ar-year')->text();
        $applicationNumber = $node->filter('.ar-cited')->text();
        $iprType = $node->filter('.ar-quartile')->text();

        $iprs[] = [
            'title' => $title,
            'inventors' => $inventors,
            'year' => $year,
            'application_number' => $applicationNumber,
            'ipr_type' => $iprType,
        ];
    });

    return json_encode($iprs, JSON_PRETTY_PRINT);
}

function getScopusArticles($sid) {
    $client = new Client(HttpClient::create(['timeout' => 60]));
    $url = 'https://sinta.kemdikbud.go.id/authors/profile/' . $sid . '/?view=scopus';
    $crawler = $client->request('GET', $url);

    $articles = [];

    $crawler->filter('.ar-list-item')->each(function ($node) use (&$articles) {
        $title = trim($node->filter('.ar-title a')->text());
        $quartile = trim($node->filter('.ar-quartile')->text());
        $publisher = trim($node->filter('.ar-pub')->text());
        $authorOrderRaw = $node->filter('.ar-meta a')->eq(2)->text();
        $authorOrder = trim(str_replace('Author Order : ', '', $authorOrderRaw));
        $year = trim($node->filter('.ar-year')->text());
        $citedRaw = $node->filter('.ar-cited')->text();
        preg_match('/(\d+) cited/', $citedRaw, $matches);
        $cited = isset($matches[1]) ? (int)$matches[1] : 0;

        $articles[] = [
            'title' => $title,
            'author_order' => $authorOrder,
            'quartile' => $quartile,
            'publisher' => $publisher,
            'year' => $year,
            'cited' => $cited,
        ];
    });

    return json_encode($articles, JSON_PRETTY_PRINT);
}

function sintaSummary($sid) {
    $client = new Client(HttpClient::create(['timeout' => 60]));
    $url = 'https://sinta.kemdikbud.go.id/authors/profile/' . $sid;
    $crawler = $client->request('GET', $url);

    // Mengambil informasi profil
    $name = trim($crawler->filter('h3 a')->text());
    $profileImageSrc = $crawler->filter('img.img-thumbnail')->attr('src');
    $university = trim($crawler->filter('.meta-profile a')->eq(0)->text());
    $department = trim($crawler->filter('.meta-profile a')->eq(1)->text());
    $sintaId = trim(str_replace('SINTA ID : ', '', $crawler->filter('.meta-profile a')->eq(2)->text()));
    $subjectList = $crawler->filter('.subject-list li a')->each(function ($node) {
        return trim($node->text());
    });

    // Mengambil tabel ringkasan
    $summary = [
        'Scopus' => [],
        'GScholar' => [],
        'WOS' => []
    ];

    $crawler->filter('.stat-table tbody tr')->each(function ($row) use (&$summary) {
        $metric = trim($row->filter('td')->eq(0)->text());
        $scopusValue = trim($row->filter('td')->eq(1)->text());
        $gScholarValue = trim($row->filter('td')->eq(2)->text());
        $wosValue = trim($row->filter('td')->eq(3)->text());

        $summary['Scopus'][$metric] = $scopusValue;
        $summary['GScholar'][$metric] = $gScholarValue;
        $summary['WOS'][$metric] = $wosValue;
    });

    // Menambahkan informasi profil ke dalam ringkasan
    $result = [
        'name' => $name,
        'profile_image' => $profileImageSrc,
        'university' => $university,
        'department' => $department,
        'sinta_id' => $sintaId,
        'subjects' => $subjectList,
        'summary' => $summary
    ];

    return json_encode($result, JSON_PRETTY_PRINT);
}

// Contoh penggunaan fungsi:
if (isset($_GET['sid']) && isset($_GET['type'])) {
    $sid = $_GET['sid'];
    $type = $_GET['type'];

    header('Content-Type: application/json');

    switch ($type) {
        case 'research':
            echo getResearchs($sid);
            break;
        case 'cs':
            echo getCSes($sid);
            break;
        case 'ipr':
            echo getIPRs($sid);
            break;
        case 'scopus':
            echo getScopusArticles($sid);
            break;
        case 'summary':
            echo sintaSummary($sid);
            break;
        default:
            echo json_encode(['error' => 'Invalid type specified.']);
    }
} else {
    echo json_encode(['error' => 'Missing sid or type parameter.']);
}