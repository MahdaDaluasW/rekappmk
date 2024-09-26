<?php
include '../koneksi.php';

require '../vendor/autoload.php'; // Menggunakan PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Status awal
$status = "gagal";

if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['excelFile']['tmp_name'];

    try {
        // Memuat file Excel menggunakan PhpSpreadsheet
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        // Abaikan dua baris pertama
        $sheetData = array_slice($sheetData, 2);

        // Ambil header dari baris ketiga (setelah dua baris dihapus)
        $header = $sheetData[0];

        // Tangani duplikasi kolom dengan cara menambahkan angka di akhir nama kolom yang duplikat
        $fieldCount = [];
        foreach ($header as $index => $field) {
            if (empty(trim($field))) {
                // Jika nama kolom kosong, beri nama default
                $field = "kolom_" . ($index + 1);
            } else {
                // Ubah semua kolom menjadi huruf kecil dan ganti spasi dengan underscore
                $field = strtolower(str_replace(' ', '_', $field));
            }

            // Jika kolom sudah ada, tambahkan angka untuk menghindari duplikasi
            if (isset($fieldCount[$field])) {
                $fieldCount[$field]++;
                $header[$index] = $field . "_" . $fieldCount[$field];
            } else {
                $fieldCount[$field] = 0;
                $header[$index] = $field;
            }
        }

        // Siapkan query untuk membuat tabel secara dinamis (jika tabel belum ada)
        $createTableQuery = "CREATE TABLE IF NOT EXISTS pmk (id INT AUTO_INCREMENT PRIMARY KEY, ";
        foreach ($header as $field) {
            $createTableQuery .= "`$field` VARCHAR(255), ";
        }
        $createTableQuery = rtrim($createTableQuery, ', ') . ')';

        if ($conn->query($createTableQuery) === TRUE) {
            // Memasukkan data dari Excel ke database
            $dataInserted = true; // Flag untuk melacak status
            for ($i = 1; $i < count($sheetData); $i++) {  // Mulai dari baris pertama setelah header
                $row = $sheetData[$i];
                $insertQuery = "INSERT INTO pmk (";

                foreach ($header as $field) {
                    $insertQuery .= "`$field`, ";
                }

                $insertQuery = rtrim($insertQuery, ', ') . ') VALUES (';

                foreach ($row as $value) {
                    $insertQuery .= "'" . $conn->real_escape_string($value) . "', ";
                }

                $insertQuery = rtrim($insertQuery, ', ') . ')';

                if ($conn->query($insertQuery) !== TRUE) {
                    $dataInserted = false;
                    break;
                }
            }

            if ($dataInserted) {
                $status = "berhasil";
            } else {
                $status = "gagal";
            }
        }

    } catch (Exception $e) {
        $status = "gagal";
    }

    // Redirect ke halaman display-pmk.php dengan status sebagai query parameter
    header("Location: display-pmk.php?status=$status");
    exit();
} else {
    // Redirect ke halaman display-pmk.php dengan status gagal jika tidak ada file yang diunggah
    header("Location: display-pmk.php?status=gagal");
    exit();
}

$conn->close();
?>
