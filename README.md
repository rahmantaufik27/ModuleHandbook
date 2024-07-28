
# SINTA API

API ini menyediakan akses ke berbagai data profil peneliti dari portal SINTA (Science and Technology Index) berdasarkan `SINTA ID` yang diberikan. Data yang dapat diambil meliputi penelitian, layanan masyarakat, kekayaan intelektual, artikel Scopus, buku, dan ringkasan profil.

## Cara Penggunaan

Endpoint API dapat diakses dengan menambahkan parameter `sid` (SINTA ID) dan `type` (tipe data) pada URL. Berikut adalah tipe data yang tersedia:

-   `research` - Untuk mengambil data penelitian
-   `cs` - Untuk mengambil data PkM (Community Service)
-   `ipr` - Untuk mengambil data kekayaan intelektual (Intellectual Property Rights)
-   `scopus` - Untuk mengambil data artikel yang terindeks di Scopus
-   `books` - Untuk mengambil data buku
-   `summary` - Untuk mengambil ringkasan profil peneliti

### Contoh Penggunaan

#### 1. Mendapatkan Data Penelitian

Endpoint: `/sinta_api.php?sid=6021756&type=research`

`[
    {
        "title": "Judul Penelitian",
        "leader": "Nama Pemimpin",
        "members": ["Anggota 1", "Anggota 2"],
        "funding_source": "Sumber Dana",
        "year": "Tahun",
        "amount": "Jumlah"
    }
    ...
]` 

#### 2. Mendapatkan Data PkM

Endpoint: `/sinta_api.php?sid=6021756&type=cs`

`[
    {
        "title": "Judul Layanan",
        "leader": "Nama Pemimpin",
        "members": ["Anggota 1", "Anggota 2"],
        "funding_source": "Sumber Dana",
        "year": "Tahun",
        "amount": "Jumlah"
    }
    ...
]` 

#### 3. Mendapatkan Data Kekayaan Intelektual

Endpoint: `/sinta_api.php?sid=6021756&type=ipr`

``[
    {
        "title": "Judul IPR",
        "inventors": "Nama Inventor",
        "year": "Tahun",
        "application_number": "Nomor Aplikasi",
        "ipr_type": "Jenis IPR"
    }
    ...
]``

#### 4. Mendapatkan Data Artikel Scopus

Endpoint: `/sinta_api.php?sid=6021756&type=scopus`
``[
    {
        "title": "Judul Artikel",
        "author_order": "Urutan Penulis",
        "quartile": "Kuartil",
        "publisher": "Penerbit",
        "year": "Tahun",
        "cited": "Jumlah Sitasi"
    }
    ...
]`` 

#### 5. Mendapatkan Data Buku

Endpoint: `/sinta_api.php?sid=6021756&type=books`

``[
    {
        "title": "Judul Buku",
        "author": "Penulis",
        "publisher": "Penerbit",
        "year": "Tahun",
        "city": "Kota",
        "isbn": "ISBN"
    }
    ...
]`` 

#### 6. Mendapatkan Ringkasan Profil

Endpoint: `/sinta_api.php?sid=6021756&type=summary`

`{
    "name": "Nama Peneliti",
    "profile_image": "URL Gambar Profil",
    "university": "Universitas",
    "department": "Departemen",
    "sinta_id": "SINTA ID",
    "subjects": ["Subjek 1", "Subjek 2"],
    "summary": {
        "Scopus": {
            "Jumlah Dokumen": "123",
            "H-index": "10"
        },
        "GScholar": {
            "Jumlah Dokumen": "456",
            "H-index": "15"
        },
        "WOS": {
            "Jumlah Dokumen": "78",
            "H-index": "5"
        }
    }
}` 

## Error Handling

Jika parameter `sid` atau `type` tidak diberikan atau tipe data yang diberikan tidak valid, API akan mengembalikan pesan kesalahan dalam format JSON:

``{
    "error": "Missing sid or type parameter."
}`` 
``{
    "error": "Invalid type specified."
}``

## Dependencies

-   **Goutte**: Sebuah library PHP untuk web scraping
-   **Symfony HttpClient**: Digunakan untuk membuat permintaan HTTP

Instalasi dapat dilakukan dengan menggunakan Composer:

`composer require fabpot/goutte
composer require symfony/http-client` 

## Lisensi

Proyek ini berlisensi di bawah MIT License - lihat file LICENSE untuk detailnya.
