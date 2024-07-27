<?php
require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

function getGSArticles($user, $sortby = null) {
    $client = new Client(HttpClient::create(['timeout' => 60]));

    // Menentukan URL berdasarkan parameter sortby
    if (isset($sortby)) {
        $url = 'https://scholar.google.com/citations?hl=en&user=' . $user . '&view_op=list_works&sortby=' . $sortby;
    } else {
        $url = 'https://scholar.google.com/citations?hl=en&user=' . $user . '&view_op=list_works';
    }

    // Mengirim permintaan HTTP ke URL
    $crawler = $client->request('GET', $url);
    $articles = [];

    // Memproses setiap baris artikel
    $crawler->filter('#gsc_a_b .gsc_a_tr')->each(function ($node) use (&$articles) {
        // Mengambil judul artikel
        $title = $node->filter('.gsc_a_t a')->text();

        // Mengambil penulis artikel
        $authors = $node->filter('.gs_gray')->eq(0)->text();

        // Mengambil penerbit artikel dan menghilangkan tahun
        $publisherFull = $node->filter('.gs_gray')->eq(1)->text();
        $publisher = preg_replace('/, \d{4}$/', '', $publisherFull);

        // Mengambil jumlah sitasi artikel
        $citedRaw = $node->filter('.gsc_a_c a')->text();
        $cited = $citedRaw ? (int)$citedRaw : 0;

        // Mengambil tahun publikasi artikel
        $year = $node->filter('.gsc_a_y span')->text();

        // Menyimpan informasi artikel dalam array
        $articles[] = [
            'title' => $title,
            'authors' => $authors,
            'publisher' => $publisher,
            'year' => $year,
            'cited' => $cited,
        ];
    });

    // Mengembalikan data dalam format JSON
    return json_encode($articles, JSON_PRETTY_PRINT);
}

// Contoh penggunaan fungsi:
header('Content-Type: application/json');
if (isset($_GET['user'])) {
    $sortby = isset($_GET['sortby']) ? $_GET['sortby'] : null;
    echo getGSArticles($_GET['user'], $sortby);
} else {
    echo json_encode(['error' => 'Missing user parameter.']);
}
