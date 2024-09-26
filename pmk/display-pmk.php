<?php 
include '../koneksi.php';

// Ambil status dari query parameter
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($status === 'berhasil') {
    $statusMessage = "<h3 class='status-success'>Proses Upload Berhasil!</h3>";
} elseif ($status === 'gagal') {
    $statusMessage = "<h3 class='status-error'>Proses Upload Gagal!</h3>";
} else {
    $statusMessage = "<h3>Status tidak dikenal</h3>";
}

// Ambil nama validator, instansi kerja, status usulan, dan tahun untuk dropdown
$validatorQuery = "SELECT DISTINCT nama_validator FROM pmk ORDER BY nama_validator";
$validatorResult = $conn->query($validatorQuery);

$instansiQuery = "SELECT DISTINCT instansi_kerja FROM pmk ORDER BY instansi_kerja";
$instansiResult = $conn->query($instansiQuery);

$statusUsulanQuery = "SELECT DISTINCT status_usulan FROM pmk ORDER BY status_usulan";
$statusUsulanResult = $conn->query($statusUsulanQuery);

$tahunQuery = "SELECT DISTINCT YEAR(tanggal_usulan) AS tahun FROM pmk ORDER BY tahun";
$tahunResult = $conn->query($tahunQuery);

$bulanQuery = "SELECT DISTINCT MONTH(tanggal_usulan) AS bulan FROM pmk ORDER BY bulan";
$bulanResult = $conn->query($bulanQuery);

// Fungsi untuk konversi angka bulan menjadi nama bulan
function getNamaBulan($bulan) {
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return isset($namaBulan[$bulan]) ? $namaBulan[$bulan] : '';
}

// Filter data
$filterValidator = isset($_POST['validator']) ? $_POST['validator'] : '';
// $filterInstansi = isset($_POST['instansi']) ? $_POST['instansi'] : '';
$filterStatusUsulan = isset($_POST['status_usulan']) ? $_POST['status_usulan'] : '';
$filterTahun = isset($_POST['tahun']) ? $_POST['tahun'] : '';
$filterBulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
$filterTanggalAwal = isset($_POST['tanggal_awal']) ? $_POST['tanggal_awal'] : '';
$filterTanggalAkhir = isset($_POST['tanggal_akhir']) ? $_POST['tanggal_akhir'] : '';

$errorMessage = '';
if ($filterTanggalAwal && $filterTanggalAkhir) {
    // Mengonversi format ke timestamp
    $timestampAwal = strtotime($filterTanggalAwal);
    $timestampAkhir = strtotime($filterTanggalAkhir);
    
    if ($timestampAwal === false || $timestampAkhir === false || $timestampAwal > $timestampAkhir) {
        $errorMessage = "Tanggal awal tidak boleh lebih besar dari tanggal akhir.";
    }
}

// Query untuk total data verifikasi berdasarkan filter
$totalDataQuery = "SELECT COUNT(*) AS total_data FROM pmk WHERE 1=1";
if ($filterValidator) {
    $totalDataQuery .= " AND nama_validator = '" . $conn->real_escape_string($filterValidator) . "'";
}
// if ($filterInstansi) {
//     $totalDataQuery .= " AND instansi_kerja = '" . $conn->real_escape_string($filterInstansi) . "'";
// }
if ($filterStatusUsulan) {
    $totalDataQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    // Menambahkan filter bulan
    $totalDataQuery .= " AND MONTH(STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i')) = '" . $conn->real_escape_string($filterTahun) . "'";
}
if ($filterBulan) {
    // Menambahkan filter bulan
    $totalDataQuery .= " AND MONTH(STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i')) = '" . $conn->real_escape_string($filterBulan) . "'";
}
if ($filterTanggalAwal) {
    // Format tanggal untuk MySQL
    $formattedTanggalAwal = date('Y-m-d H:i:s', strtotime($filterTanggalAwal));
    $totalDataQuery .= " AND STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i') >= STR_TO_DATE('" . $conn->real_escape_string($formattedTanggalAwal) . "', '%Y-%m-%d %H:%i')";
}
if ($filterTanggalAkhir) {
    // Format tanggal untuk MySQL
    $formattedTanggalAkhir = date('Y-m-d H:i:s', strtotime($filterTanggalAkhir));
    $totalDataQuery .= " AND STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i') <= STR_TO_DATE('" . $conn->real_escape_string($formattedTanggalAkhir) . "', '%Y-%m-%d %H:%i')";
}

// Validasi tanggal
$errorMessage = '';
if ($filterTanggalAwal && $filterTanggalAkhir) {
    if ($filterTanggalAwal > $filterTanggalAkhir) {
        $errorMessage = "Tanggal awal tidak boleh lebih besar dari tanggal akhir.";
    }
}

// Mendapatkan total data jika tidak ada error
$totalData = 0;
if (empty($errorMessage)) {
    $totalDataResult = $conn->query($totalDataQuery);
    $totalData = $totalDataResult->fetch_assoc()['total_data'];
}

// Query untuk total verifikasi per instansi (semua validator)
$instansiTotalQuery = "SELECT instansi_kerja, COUNT(*) AS total_verifikasi FROM pmk WHERE 1=1";
if ($filterStatusUsulan) {
    $instansiTotalQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    // Menambahkan filter bulan
    $instansiTotalQuery .= " AND MONTH(STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i')) = '" . $conn->real_escape_string($filterTahun) . "'";
}
if ($filterBulan) {
    // Menambahkan filter bulan
    $instansiTotalQuery .= " AND MONTH(STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i')) = '" . $conn->real_escape_string($filterBulan) . "'";
}
if ($filterTanggalAwal) {
    // Format tanggal untuk MySQL
    $formattedTanggalAwal = date('Y-m-d H:i:s', strtotime($filterTanggalAwal));
    $instansiTotalQuery .= " AND STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i') >= STR_TO_DATE('" . $conn->real_escape_string($formattedTanggalAwal) . "', '%Y-%m-%d %H:%i')";
}
if ($filterTanggalAkhir) {
    // Format tanggal untuk MySQL
    $formattedTanggalAkhir = date('Y-m-d H:i:s', strtotime($filterTanggalAkhir));
    $instansiTotalQuery .= " AND STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i') <= STR_TO_DATE('" . $conn->real_escape_string($formattedTanggalAkhir) . "', '%Y-%m-%d %H:%i')";
}
$instansiTotalQuery .= " GROUP BY instansi_kerja ORDER BY total_verifikasi DESC";
$instansiTotalResult = $conn->query($instansiTotalQuery);

// Query untuk total verifikasi per instansi berdasarkan filter validator
$instansiValidatorQuery = "SELECT instansi_kerja, COUNT(*) AS total_verifikasi_per_validator FROM pmk WHERE 1=1";
if ($filterValidator) {
    $instansiValidatorQuery .= " AND nama_validator = '" . $conn->real_escape_string($filterValidator) . "'";
}
if ($filterStatusUsulan) {
    $instansiValidatorQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    // Menambahkan filter bulan
    $instansiValidatorQuery .= " AND MONTH(STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i')) = '" . $conn->real_escape_string($filterTahun) . "'";
}
if ($filterBulan) {
    // Menambahkan filter bulan
    $instansiValidatorQuery .= " AND MONTH(STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i')) = '" . $conn->real_escape_string($filterBulan) . "'";
}
if ($filterTanggalAwal) {
    // Format tanggal untuk MySQL
    $formattedTanggalAwal = date('Y-m-d H:i:s', strtotime($filterTanggalAwal));
    $instansiValidatorQuery .= " AND STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i') >= STR_TO_DATE('" . $conn->real_escape_string($formattedTanggalAwal) . "', '%Y-%m-%d %H:%i')";
}
if ($filterTanggalAkhir) {
    // Format tanggal untuk MySQL
    $formattedTanggalAkhir = date('Y-m-d H:i:s', strtotime($filterTanggalAkhir));
    $instansiValidatorQuery .= " AND STR_TO_DATE(tanggal_usulan, '%m/%d/%Y %H:%i') <= STR_TO_DATE('" . $conn->real_escape_string($formattedTanggalAkhir) . "', '%Y-%m-%d %H:%i')";
}
$errorMessage = '';
if ($filterTanggalAwal && $filterTanggalAkhir) {
    if (strtotime($filterTanggalAwal) > strtotime($filterTanggalAkhir)) {
        $errorMessage = "Tanggal awal tidak boleh lebih besar dari tanggal akhir.";
    }
}
// Hanya jalankan query jika tidak ada error
if (empty($errorMessage)) {
    $instansiValidatorQuery .= " GROUP BY instansi_kerja ORDER BY total_verifikasi_per_validator DESC";
    $instansiValidatorResult = $conn->query($instansiValidatorQuery);
}

// Keterangan filter yang diterapkan
$filterDescription = "Filter aktif: ";
$filterDescription .= $filterValidator ? "Nama Validator: " . htmlspecialchars($filterValidator) . "; " : "";
// $filterDescription .= $filterInstansi ? "Instansi Kerja: " . htmlspecialchars($filterInstansi) . "; " : "";
$filterDescription .= $filterStatusUsulan ? "Status Usulan: " . htmlspecialchars($filterStatusUsulan) . "; " : "";
$filterDescription .= $filterTahun ? "Tahun: " . htmlspecialchars($filterTahun) . ";" : "";
$filterDescription .= $filterBulan ? "Bulan: " . htmlspecialchars(getNamaBulan($filterBulan)) . "; " : "";
$filterDescription .= $filterTanggalAwal ? "Tahun: " . htmlspecialchars($filterTanggalAwal) . ";" : "";
$filterDescription .= $filterTanggalAkhir ? "Tahun: " . htmlspecialchars($filterTanggalAkhir) . ";" : "";

if (empty($filterDescription)) {
    $filterDescription = "Tidak ada filter aktif.";
}

// Tangani permintaan AJAX untuk detail instansi
if (isset($_GET['action']) && $_GET['action'] == 'getInstansiDetails') {
    $instansi_kerja = isset($_GET['instansi_kerja']) ? $_GET['instansi_kerja'] : '';
    $nama_validator = isset($_GET['nama_validator']) ? $_GET['nama_validator'] : '';

    // Query untuk mendapatkan nama, NIP, dan alasan tolak dokumen berdasarkan instansi dan validator
    $detailsQuery = "SELECT nama, nip, alasan_tolak FROM pmk WHERE 1=1";
    
    if ($instansi_kerja) {
        $detailsQuery .= " AND instansi_kerja = '" . $conn->real_escape_string($instansi_kerja) . "'";
    }
    
    if ($nama_validator) {
        $detailsQuery .= " AND nama_validator = '" . $conn->real_escape_string($nama_validator) . "'";
    }
    
    $detailsQuery .= " AND status_usulan = 'Perbaikan Dokumen'";
    
    $detailsResult = $conn->query($detailsQuery);

    $details = [];
    while ($row = $detailsResult->fetch_assoc()) {
        $details[] = $row;
    }

    echo json_encode($details);
    exit; // Pastikan untuk keluar setelah memberikan respon
}

if (isset($_POST['delete_all'])) {
    // Konfirmasi sebelum menghapus
    $confirm = true;

    if ($confirm) {
        $deleteAllQuery = "DELETE FROM pmk";
        if ($conn->query($deleteAllQuery) === TRUE) {
            echo "<h3 class='status-success'>Semua data berhasil dihapus!</h3>";

            // Reset data
            $instansiTotalData = [];
            $instansiValidatorData = [];
            $totalData = 0;

            // Ambil data terbaru setelah penghapusan
            $instansiTotalResult = $conn->query("SELECT instansi_kerja, COUNT(*) as total_verifikasi FROM pmk GROUP BY instansi_kerja");
            $instansiValidatorResult = $conn->query("SELECT instansi_kerja, COUNT(*) as total_verifikasi_per_validator FROM pmk GROUP BY instansi_kerja");
        } else {
            echo "<h3 class='status-error'>Gagal menghapus data: " . $conn->error . "</h3>";
        }
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <title>Data yang Diunggah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        .status-success {
            color: #4CAF50;
            background-color: #e8f5e9;
            padding: 10px;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            display: inline-block;
        }
        .status-error {
            color: #F44336;
            background-color: #ffebee;
            padding: 10px;
            border: 1px solid #F44336;
            border-radius: 5px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .filter-form {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .filter-form input[type="submit"] {
            margin-right: 10px;
        }

        .filter-form input[name="delete_all"] {
            background-color: #ff4d4d; /* Warna merah */
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .form-group {
        margin-bottom: 15px;
        }
        .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        }
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .date-range {
            display: flex;
            justify-content: space-between;
        }

        .date-range label {
            flex: 1;
            margin-right: 10px;
        }

        .date-range input {
            flex: 1;
            margin-right: 10px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .total-data {
            font-size: 18px;
            margin: 20px 0;
        }
        .instansi-summary, .instansi-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .instansi-summary h3, .instansi-details h3 {
            margin-top: 0;
        }
        .instansi-link {
            color: #4CAF50;
            text-decoration: none;
        }
        .instansi-link:hover {
            text-decoration: underline;
        }
        .instansi-details ul {
            list-style-type: none;
            padding: 0;
        }
        .instansi-details li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .instansi-details li:last-child {
            border-bottom: none;
        }
        .button-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button-link:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>

    <h1>Data Verifikasi PMK</h1>
    <a href="import-pmk.php" class="button-link">Upload Excel</a>
    <a href="../index.php" class="button-link">Home</a>
    <?php echo $statusMessage; ?>

    <div class="filter-form">
        <form method="POST" action="">
            <div class="form-group">
                <label for="validator">Nama Validator:</label>
                <select name="validator" id="validator">
                    <option value="">Semua</option>
                    <?php while ($row = $validatorResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['nama_validator']); ?>" <?php echo $filterValidator == $row['nama_validator'] ? 'selected' : ''; ?>> 
                            <?php echo htmlspecialchars($row['nama_validator']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- <div class="form-group">
                <label for="instansi">Instansi Kerja:</label>
                <select name="instansi" id="instansi">
                    <option value="">Semua</option>
                    <?php while ($row = $instansiResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['instansi_kerja']); ?>" <?php echo $filterInstansi == $row['instansi_kerja'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['instansi_kerja']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div> -->

            <div class="form-group">
                <label for="status_usulan">Status Usulan:</label>
                <select name="status_usulan" id="status_usulan">
                    <option value="">Semua</option>
                    <?php while ($row = $statusUsulanResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['status_usulan']); ?>" <?php echo $filterStatusUsulan == $row['status_usulan'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['status_usulan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- <div class="form-group">
                <label for="tahun">Tahun:</label>
                <select name="tahun" id="tahun">
                    <option value="">Semua</option>
                    <?php while ($row = $tahunResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['tahun']); ?>" <?php echo $filterTahun == $row['tahun'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['tahun']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="bulan">Bulan Verifikasi:</label>
                <select name="bulan" id="bulan">
                    <option value="">Semua</option>
                    <?php while ($row = $bulanResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['bulan']); ?>" <?php echo $filterBulan == $row['bulan'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(getNamaBulan($row['bulan'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div> -->

            <label for="tanggal_awal">Tanggal Awal:</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" 
                    value="<?php echo isset($_POST['tanggal_awal']) ? htmlspecialchars($_POST['tanggal_awal']) : ''; ?>">

                <label for="tanggal_akhir">Tanggal Akhir:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" 
                    value="<?php echo isset($_POST['tanggal_akhir']) ? htmlspecialchars($_POST['tanggal_akhir']) : ''; ?>">

            <input type="submit" value="Filter">
            <input type="submit" name="delete_all" value="Hapus Semua" style="float: right; margin-left: 10px;">
        </form>
    </div>



    <div class="total-data">
        <p>Total Data Verifikasi: <span><?php echo $totalData; ?></span></p>
    </div>

    <div class="instansi-summary">
        <h3>Detail Total Verifikasi Per Instansi:</h3>
        <p><?php echo $filterDescription; ?></p>
        <table>
            <tr>
                <th>Instansi Kerja</th>
                <th>Total Usulan</th>
                <th>Jumlah Diverifikasi</th>
                <th>Persentase Pengerjaan</th>
            </tr>
            <?php
            if ($instansiTotalResult->num_rows > 0) {
                $instansiTotalData = [];
                while ($row = $instansiTotalResult->fetch_assoc()) {
                    $instansiTotalData[$row['instansi_kerja']] = $row['total_verifikasi'];
                }
                
                while ($row = $instansiValidatorResult->fetch_assoc()) {
                    $instansiValidatorData[$row['instansi_kerja']] = $row['total_verifikasi_per_validator'];
                }
                
                foreach ($instansiTotalData as $instansi => $totalVerifikasi) {
                    $totalVerifikasiValidator = isset($instansiValidatorData[$instansi]) ? $instansiValidatorData[$instansi] : 0;
                    $persentase = $totalVerifikasi > 0 ? ($totalVerifikasiValidator / $totalVerifikasi) * 100 : 0;
                    
                    echo "<tr>";
                    echo "<td><a href='#' class='instansi-link' data-instansi='" . htmlspecialchars($instansi) . "'>" . htmlspecialchars($instansi) . "</a></td>";
                    echo "<td>" . htmlspecialchars($totalVerifikasi) . "</td>";
                    echo "<td>" . htmlspecialchars($totalVerifikasiValidator) . "</td>";
                    echo "<td>" . number_format($persentase, 2) . "%</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Tidak ada data yang ditemukan</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Perbaikan tampilkan detail BTS dalam tabel -->
    <div class="instansi-details" id="instansi-details" style="display:none;">
        <h3>Detail Data Perbaikan:</h3>
        <table id="details-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Alasan Tolak</th>
                </tr>
            </thead>
            <tbody id="details-list">
            </tbody>
        </table>
    </div>


    <script>
       document.addEventListener('DOMContentLoaded', function () {
        const instansiLinks = document.querySelectorAll('.instansi-link');
        const detailsTable = document.getElementById('details-table');
        const detailsList = document.getElementById('details-list');
        const detailsDiv = document.getElementById('instansi-details');

        instansiLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const instansi = this.getAttribute('data-instansi');
                const validator = document.getElementById('validator').value; // Mengambil nilai dari dropdown validator

                // Clear previous details
                detailsList.innerHTML = '';

                // Fetch new details
                fetch('?action=getInstansiDetails&instansi_kerja=' + encodeURIComponent(instansi) + '&nama_validator=' + encodeURIComponent(validator))
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(item => {
                                const row = document.createElement('tr');

                                const nameCell = document.createElement('td');
                                nameCell.textContent = item.nama;
                                row.appendChild(nameCell);

                                const nipCell = document.createElement('td');
                                nipCell.textContent = item.nip;
                                row.appendChild(nipCell);

                                const reasonCell = document.createElement('td');
                                reasonCell.textContent = item.alasan_tolak;
                                row.appendChild(reasonCell);

                                detailsList.appendChild(row);
                            });
                            detailsTable.style.display = 'table';
                            detailsDiv.style.display = 'block';
                        } else {
                            const row = document.createElement('tr');
                            const cell = document.createElement('td');
                            cell.colSpan = 3;
                            cell.textContent = 'Tidak ada data yang ditemukan';
                            row.appendChild(cell);
                            detailsList.appendChild(row);
                            detailsTable.style.display = 'table';
                            detailsDiv.style.display = 'block';
                        }
                    });
            });
        });
    });

        // document.querySelector('form').addEventListener('submit', function(event) {
        //     const tanggalAwal = new Date(document.getElementById('tanggal_awal').value);
        //     const tanggalAkhir = new Date(document.getElementById('tanggal_akhir').value);
            
        //     if (tanggalAwal > tanggalAkhir) {
        //         event.preventDefault(); // Mencegah pengiriman form
        //         alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
        //     }
        // });
    </script>
</body>
</html>