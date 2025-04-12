<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produkList = [
            ['nama_produk' => 'Sajadah Turki Premium', 'id_kategori' => 1, 'id_satuan' => 1, 'stok_minimum' => rand(3, 10)], // Pcs
            ['nama_produk' => 'Sorban Putih Madinah', 'id_kategori' => 2, 'id_satuan' => 1, 'stok_minimum' => rand(5, 15)], // Pcs
            ['nama_produk' => 'Kurma Ajwa Madinah', 'id_kategori' => 3, 'id_satuan' => 2, 'stok_minimum' => rand(10, 20)], // Kg
            ['nama_produk' => 'Air Zamzam Asli', 'id_kategori' => 4, 'id_satuan' => 3, 'stok_minimum' => rand(15, 30)], // Liter
            ['nama_produk' => 'Peci Rajut Haji', 'id_kategori' => 5, 'id_satuan' => 1, 'stok_minimum' => rand(5, 15)], // Pcs
            ['nama_produk' => 'Mukena Bordir Mekah', 'id_kategori' => 6, 'id_satuan' => 1, 'stok_minimum' => rand(5, 10)], // Pcs
            ['nama_produk' => 'Tasbih Kayu Kokka', 'id_kategori' => 7, 'id_satuan' => 1, 'stok_minimum' => rand(8, 20)], // Pcs
            ['nama_produk' => 'Parfum Non Alkohol Mekkah', 'id_kategori' => 7, 'id_satuan' => 1, 'stok_minimum' => rand(10, 25)], // Pcs
            ['nama_produk' => 'Kismis Iran Premium', 'id_kategori' => 3, 'id_satuan' => 2, 'stok_minimum' => rand(5, 15)], // Kg
            ['nama_produk' => 'Baju Ihram Laki-Laki', 'id_kategori' => 7, 'id_satuan' => 1, 'stok_minimum' => rand(5, 15)], // Pcs
            ['nama_produk' => 'Kurma Medjool Premium', 'id_kategori' => 3, 'id_satuan' => 2, 'stok_minimum' => rand(8, 18)], // Kg
            ['nama_produk' => 'Habbatussauda Kapsul', 'id_kategori' => 7, 'id_satuan' => 5, 'stok_minimum' => rand(15, 30)], // Sachet
            ['nama_produk' => 'Madu Arab Asli', 'id_kategori' => 3, 'id_satuan' => 3, 'stok_minimum' => rand(5, 15)], // Liter
            ['nama_produk' => 'Siwak Natural', 'id_kategori' => 7, 'id_satuan' => 1, 'stok_minimum' => rand(10, 30)], // Pcs
            ['nama_produk' => 'Kurma Sukari Box', 'id_kategori' => 3, 'id_satuan' => 4, 'stok_minimum' => rand(3, 8)], // Dus
        ];

        $data = [];

        foreach ($produkList as $item) {
            // Hasil akhir selalu kelipatan 1000
            $hargaBeli = rand(10, 200) * 1000;

            $data[] = [
                'nama_produk' => $item['nama_produk'],
                'id_kategori' => $item['id_kategori'],
                'id_satuan' => $item['id_satuan'],
                'harga_beli' => $hargaBeli,
                'id_pemilik' => 1,
                'stok_minimum' => $item['stok_minimum'],
                'deskripsi' => $this->generateDeskripsi($item['nama_produk']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Produk::insert($data);
    }

    /**
     * Generate deskripsi yang lebih spesifik untuk setiap produk
     *
     * @param string $namaProduk
     * @return string
     */
    private function generateDeskripsi($namaProduk)
    {
        $deskripsi = [
            'Sajadah Turki Premium' => 'Sajadah berkualitas tinggi import langsung dari Turki dengan bahan lembut dan nyaman untuk ibadah.',
            'Sorban Putih Madinah' => 'Sorban putih asli dari Madinah dengan bahan katun halus dan nyaman dipakai.',
            'Kurma Ajwa Madinah' => 'Kurma Ajwa asli Madinah dengan rasa manis dan tekstur lembut serta kaya manfaat.',
            'Air Zamzam Asli' => 'Air Zamzam asli yang didatangkan langsung dari Mekkah dengan kualitas terjamin.',
            'Peci Rajut Haji' => 'Peci rajut khas untuk ibadah haji dengan bahan nyaman dan tidak mudah kusut.',
            'Mukena Bordir Mekah' => 'Mukena dengan bordir khas Mekah, elegan dan nyaman untuk beribadah.',
            'Tasbih Kayu Kokka' => 'Tasbih dari kayu Kokka berkualitas dengan ukiran dan butiran halus.',
            'Parfum Non Alkohol Mekkah' => 'Parfum non alkohol dari Mekkah dengan aroma khas timur tengah yang tahan lama.',
            'Kismis Iran Premium' => 'Kismis premium asal Iran dengan rasa manis alami dan kaya akan nutrisi.',
            'Baju Ihram Laki-Laki' => 'Baju ihram untuk laki-laki dengan bahan katun yang nyaman digunakan saat ibadah haji dan umrah.',
            'Kurma Medjool Premium' => 'Kurma Medjool besar dengan tekstur lembut dan rasa manis alami yang khas.',
            'Habbatussauda Kapsul' => 'Suplemen habbatussauda dalam bentuk kapsul yang mudah dikonsumsi untuk kesehatan.',
            'Madu Arab Asli' => 'Madu asli dari Arab dengan kualitas premium dan khasiat tinggi untuk kesehatan.',
            'Siwak Natural' => 'Siwak alami untuk kebersihan gigi dan mulut sesuai sunnah Rasulullah SAW.',
            'Kurma Sukari Box' => 'Kurma Sukari kualitas premium dalam kemasan box eksklusif, cocok untuk hadiah.',
        ];

        return $deskripsi[$namaProduk] ?? fake()->sentence(8);
    }
}
