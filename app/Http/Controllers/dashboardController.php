<?php

namespace App\Http\Controllers;

use App\Helpers\HanaConnection;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActivityReportMail;
use Illuminate\Support\Facades\Log;
use Exception;

use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Termwind\Components\Dd;


class dashboardController extends Controller
{
   public function fetch()
{
    $koneksi = HanaConnection::getConnection();

    $reportQuery = "
        SELECT ID_ACTIVITY, TIKET
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY
        WHERE ID_DIFFICULT IS NULL
        ORDER BY TGL_ACTIVITY DESC
        
    ";

    $statusQuery = "
        SELECT ID_ACTIVITY, TIKET
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY
        WHERE ID_STATUS IS NULL
        ORDER BY TGL_STATUS DESC
        
    ";

    $solvedQuery = "
        SELECT ID_ACTIVITY, TIKET
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY
        WHERE TGL_SOLVED IS NULL
        ORDER BY TGL_SOLVED DESC
        
    ";

    $report = $koneksi->query($reportQuery)->fetchAll(\PDO::FETCH_ASSOC);
    $status = $koneksi->query($statusQuery)->fetchAll(\PDO::FETCH_ASSOC);
    $solved = $koneksi->query($solvedQuery)->fetchAll(\PDO::FETCH_ASSOC);

    return response()->json([
        'report_count' => count($report),
        'status_count' => count($status),
        'solved_count' => count($solved),
        'report' => $report,
        'status' => $status,
        'solved' => $solved,
    ]);
}

public function fetchActivityLog()
{
    try {
        $koneksi = HanaConnection::getConnection();

        $query = "
            SELECT 
                A.ID_ACTIVITY, 
                A.TIKET,
                A.NM_USER, 
                A.TGL_ACTIVITY
            FROM SBO_SUPPORT_SAPHANA.ACTIVITY A
            WHERE A.ID_ACTIVITY IS NOT NULL AND A.TGL_ACTIVITY IS NOT NULL
            ORDER BY A.TGL_ACTIVITY DESC
            LIMIT 5
        ";

        $data = $koneksi->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $logs = [];

        foreach ($data as $row) {
            $waktu = $row['TGL_ACTIVITY'];
            $logs[] = [
                'log_message' => "{$row['NM_USER']} membuat tiket <strong>{$row['TIKET']}</strong>",
                'log_time' => $waktu
            ];
        }

        return response()->json([
            'log_count' => count($logs),
            'logs' => $logs
        ]);

    } catch (Exception $e) {
        return response()->json([
            'logs' => [],
            'error' => $e->getMessage()
        ], 500);
    }
}


// This method is used to display the dashboard page
   public function index(Request $request)
{
    if (!session('admin_sap')) {
        abort(404); // Activity not found
    }

    $koneksi = HanaConnection::getConnection();

    // Filter WHERE untuk chart dan report
    $where = "WHERE 1=1";
    if ($request->bulan) {
        $where .= " AND MONTH(TGL_ACTIVITY) = " . (int) $request->bulan;
    }
    if ($request->tahun) {
        $where .= " AND YEAR(TGL_ACTIVITY) = " . (int) $request->tahun;
    }

// chart company
    $chartQuery = "
        SELECT COMPANY_SAP.NM_COMPANY, COUNT(*) AS JUMLAH 
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY
        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        $where 
        GROUP BY COMPANY_SAP.NM_COMPANY
    ";
    $dataChart = $koneksi->query($chartQuery)->fetchAll();

// tabel aktivitas
    $tabelQuery = "
        SELECT * 
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
    ";
    $aktivitas = $koneksi->query($tabelQuery)->fetchAll();

//    task report => finish & remaining
    $finishedQuery = "
        SELECT COUNT(*) AS JUMLAH FROM (
        SELECT ID_ACTIVITY, TIKET, TGL_ACTIVITY AS TANGGAL FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_DIFFICULT IS NOT NULL
        UNION ALL
        SELECT ID_ACTIVITY, TIKET, TGL_STATUS AS TANGGAL FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_STATUS IS NOT NULL
        UNION ALL
        SELECT ID_ACTIVITY, TIKET, TGL_SOLVED AS TANGGAL FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE TGL_SOLVED IS NOT NULL
    ) AS gabungan
    ";
    $finished = $koneksi->query($finishedQuery)->fetch()['JUMLAH'];

    $remainingQuery = "
        SELECT COUNT(*) AS JUMLAH FROM (
        SELECT ID_ACTIVITY, TIKET, TGL_ACTIVITY AS TANGGAL FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_DIFFICULT IS NULL
        UNION ALL
        SELECT ID_ACTIVITY, TIKET, TGL_STATUS AS TANGGAL FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_STATUS IS NULL
        UNION ALL
        SELECT ID_ACTIVITY, TIKET, TGL_SOLVED AS TANGGAL FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE TGL_SOLVED IS NULL
    ) AS gabungan
    ";
    $remaining = $koneksi->query($remainingQuery)->fetch()['JUMLAH'];

    $total = $finished + $remaining;
    $finishedPercent = $total > 0 ? round(($finished / $total) * 100) : 0;
    $remainingPercent = 100 - $finishedPercent;

    // Kirim semua ke view
    return view('admin.dashboardAdmin', compact(
        'dataChart',
        'aktivitas',
        'finished',
        'remaining',
        'finishedPercent',
        'remainingPercent'
    ));
}

public function sendEmail($id)
{
    date_default_timezone_set("Asia/Jakarta");

    try {
        $koneksi = HanaConnection::getConnection();
        $id = intval($id); // aman untuk query langsung

        // Ambil data aktivitas
        $queryAkt = "SELECT 
                        ACTIVITY.*, 
                        COMPANY_SAP.NM_COMPANY 
                    FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                    LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP 
                        ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY 
                    WHERE ID_ACTIVITY = $id";
        $stmt = $koneksi->query($queryAkt);
        $activity = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$activity) {
            return back()->with('error', 'Data aktivitas tidak ditemukan.');
        }

        // Ambil semua foto
        $queryFoto = "SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = $id";
        $stmtFoto = $koneksi->query($queryFoto);
        $fotos = $stmtFoto->fetchAll(\PDO::FETCH_ASSOC);

        // Kirim email ke support
        Mail::to('rhndkputr@gmail.com')->send(new ActivityReportMail($activity, $fotos));

        return back()->with('success', 'Email berhasil dikirim ke rhndkputr@gmail.com');
    } catch (Exception $e) {
        Log::error('Email gagal: ' . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat mengirim email.');
    }
}

// This method is used to display the activity report page
    public function activityReport()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        $koneksi = HanaConnection::getConnection();

        $sql = "
            SELECT 
                ACTIVITY.*, 
                DIFFICULT_LEVEL.NM_DIFFICULT, 
                COMPANY_SAP.NM_COMPANY 
            FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
            LEFT JOIN SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL ON ACTIVITY.ID_DIFFICULT = DIFFICULT_LEVEL.ID_DIFFICULT
            LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        ";

        $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        // Ambil foto pertama untuk setiap aktivitas
        foreach ($activities as &$activity) {
            $id = $activity['ID_ACTIVITY'];
            $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
            $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
            $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
        }

        return view('admin.activity.report', compact('activities'));
    }


            public function cetakBeritaAcara($id)
{   
    date_default_timezone_set("Asia/Jakarta");

    if (!session('admin_sap')) {
        abort(403, 'Unauthorized');
    }

    $koneksi = HanaConnection::getConnection();

    if (!is_numeric($id)) {
        abort(400, 'Invalid ID');
    }

// Ambil data activity + nama company + tingkat kesulitan
$sql = "
    SELECT 
        A.*, 
        C.NM_COMPANY,
        K.NM_DIFFICULT,
        U.JABATAN
    FROM SBO_SUPPORT_SAPHANA.ACTIVITY A
    LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP C ON A.ID_COMPANY = C.ID_COMPANY
    LEFT JOIN SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL K ON A.ID_DIFFICULT = K.ID_DIFFICULT
    LEFT JOIN SBO_SUPPORT_SAPHANA.USER_SAP U ON A.NM_USER = U.USERNAME
    WHERE A.ID_ACTIVITY = '$id'
";



    $activity = $koneksi->query($sql)->fetch(\PDO::FETCH_ASSOC);

    if (!$activity) {
        abort(404, 'Activity not found');
    }

    // Ambil satu foto pertama
    $foto = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1")
                   ->fetch(\PDO::FETCH_ASSOC);
    $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;

    // Ambil semua foto
    $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")->fetchAll(\PDO::FETCH_ASSOC);

    // Peta logo berdasarkan nama perusahaan
    $logoMap = [
        'cmnp' => public_path('layouts/assets/images/cmnp.png'),
        'cmnpproper' => public_path('layouts/assets/images/cmnproper.png'),
        'cms' => public_path('layouts/assets/images/cms.png'),
        'cpi' => public_path('layouts/assets/images/cpi.png'),
        'cmlj' => public_path('layouts/assets/images/cmlj.png'),
        'Citra Waspphutowa' => public_path('layouts/assets/images/cw.jpg'),
        'ckjt' => public_path('layouts/assets/images/ckjt.jpg'),
    ];

    // Peta alamat berdasarkan nama perusahaan
    $alamatMap = [
        'cmnp' => [
            'alamat' => 'Jl. Yos Sudarso Kavling No.28 3, RT.3/RW.11, Sunter Jaya, Kec. Tj. Priok, Jkt Utara, Daerah Khusus Ibukota Jakarta 14350',
            'telepon' => '(021) 65306930',
            'email' => 'cmnp@citra.co.id',
        ],
        'cmnpproper' => [
            'alamat' => 'Jl. Yos Sudarso Kavling No.28 3, RT.3/RW.11, Sunter Jaya, Kec. Tj. Priok, Jkt Utara, Daerah Khusus Ibukota Jakarta 14350',
            'telepon' => '(021) 88888777',
            'email' => 'cs@cmnpproper.co.id',
        ],
        'cms' => [
            'alamat' => 'Jl. Wisata Menanggal No.21, Dukuh Menanggal, Kec. Gayungan, Surabaya, Jawa Timur 60234',
            'telepon' => '(0251) 7654321',
            'email' => 'kontak@cms.co.id',
        ],
        'cpi' => [
            'alamat' => 'Jl. Angkasa No.20, RT.12/RW.2, Gn. Sahari Sel., Kec. Kemayoran, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10610',
            'telepon' => '(0267) 8881122',
            'email' => 'info@cpi.co.id',
        ],
        'cmlj' => [
            'alamat' => 'Jalan Kutawaringin Rt 01 Rw 11 No 1, Kopo, Kec. Kutawaringin, Kabupaten Bandung, Jawa Barat 40911',
            'telepon' => '(021) 5553332',
            'email' => 'admin@cmlj.co.id',
        ],
        'Citra Waspphutowa' => [
            'alamat' => 'Jl. Tol Depok - Antasari No.100, RT.4/RW.2, Cilandak Bar., Kec. Cilandak, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12450',
            'telepon' => '(0264) 432100',
            'email' => 'support@cw.co.id',
        ],
        'ckjt' => [
            'alamat' => 'Jl. Raya Cimalaka Cipadung No.115, Licin, Kec. Cimalaka, Kabupaten Sumedang, Jawa Barat 45353',
            'telepon' => '(0233) 882211',
            'email' => 'info@ckjt.co.id',
        ],
    ];

    // Tentukan key perusahaan
    $companyKey = strtolower($activity['NM_COMPANY']);
    $logoPath = $logoMap[$companyKey] ?? public_path('layouts/assets/images/cmnp.png');
    $alamatData = $alamatMap[$companyKey] ?? [
        'alamat' => 'Alamat tidak tersedia',
        'telepon' => '-',
        'email' => '-',
    ];

    // Gabungkan data alamat ke dalam activity
    $activity['ALAMAT_COMPANY'] = $alamatData['alamat'];
    $activity['TELP_COMPANY'] = $alamatData['telepon'];
    $activity['MAIL_COMPANY'] = $alamatData['email'];

    // Generate PDF
    $pdf = PDF::loadView('admin.activity.berita_acara_pdf', compact('activity', 'logoPath', 'alamatData', 'fotos'))
              ->setPaper('A4', 'portrait');

    return $pdf->stream('berita_acara_' . $id . '.pdf');
}


            public function activitydestroy($id)
            {
               if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                {
            
            $koneksi = HanaConnection::getConnection();
            

            // Ambil nama file foto utama
            $ambil_activity = $koneksi->query("SELECT ID_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_ACTIVITY='$id'");
            $pecah_activity = $ambil_activity->fetch(\PDO::FETCH_ASSOC);
            $fotoUtama = $pecah_activity['ID_ACTIVITY_FOTO'] ?? null;

            // Ambil semua foto tambahan
            $ambil_foto_tambahan = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'");
            $fotoTambahan = $ambil_foto_tambahan->fetchAll(\PDO::FETCH_COLUMN);

            // Hapus file dari storage
            foreach ($fotoTambahan as $foto) {
                $path = storage_path('app/public/uploads/' . $foto);
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            // Hapus foto utama
            if ($fotoUtama) {
                $pathUtama = storage_path('app/public/uploads/' . $fotoUtama);
                if (file_exists($pathUtama)) {
                    unlink($pathUtama);
                }
            }

            // Hapus data dari database
            $koneksi->query("DELETE FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'");
            $hapus = $koneksi->query("DELETE FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_ACTIVITY='$id'");

            if ($hapus) {
                return redirect()->route('admin.activity.report')->with('success', 'Activity dan foto berhasil dihapus.');
            } else {
                return redirect()->route('admin.activity.report')->with('error', 'Gagal menghapus activity.');
            }
        }
            }

                
            
        // This method is used to display the form for adding a new activity report
                public function activitytambah()
            {
                if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                $koneksi = HanaConnection::getConnection();
            $dataDIFFICULT = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
            $datacompany = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

            return view('admin.activity.actionreport.tambah', compact('dataDIFFICULT', 'datacompany'));
            }
// This method is used to store a new activity report
            public function activityStore(Request $request)
            {
                if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                        date_default_timezone_set("Asia/Jakarta");

                $request->validate([
                    'company' => 'required',
                    'email' => 'required|email',
                    'username' => 'required',
                    'Subject' => 'required|max:255',
                    'deskripsi' => 'required',
                    'ID_DIFFICULT' => 'required|numeric',
                    'foto.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                $koneksi = HanaConnection::getConnection();
                $TGL_ACTIVITY = date("Y-m-d H:i:s");

                // Ambil ID terakhir
                $stmt = $koneksi->query("SELECT MAX(ID_ACTIVITY) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY");
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $nextId = $row['ID'] + 1;

                $mainFotoName = null;
                $fotoNames = [];

                if ($request->hasFile('foto')) {
                    foreach ($request->file('foto') as $key => $file) {
                        $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('uploads', $uniqueName, 'public');

                        $fotoNames[] = $uniqueName;
                        if ($key === 0) {
                            $mainFotoName = $uniqueName;
                        }
                    }
                }

                // Simpan activity utama
                $stmt = $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY 
                    (ID_ACTIVITY, ID_COMPANY, MAIL_COMPANY, NM_USER, SUBJECT, DESKRIPSI, ID_ACTIVITY_FOTO, ID_DIFFICULT, TGL_ACTIVITY)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $nextId,
                    $request->company,
                    $request->email,
                    $request->username,
                    $request->Subject,
                    $request->deskripsi,
                    $mainFotoName,
                    $request->ID_DIFFICULT,
                    $TGL_ACTIVITY
                ]);

                // Simpan semua foto ke tabel ACTIVITY_FOTO
                foreach ($fotoNames as $fotoName) {
                    $stmt = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO");
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $nextFotoId = $row['ID'] + 1;

                    $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO(ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO)
                        VALUES (?, ?, ?)")
                        ->execute([$nextFotoId, $nextId, $fotoName]);
                }
                
                return redirect()->route('admin.activity.report')->with('success', 'Data berhasil disimpan!');
                
            }


            public function activityEdit($id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();

                    // Fetch activity data
                    $activity = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_ACTIVITY='$id'")->fetch(\PDO::FETCH_ASSOC);

                    if (!$activity) {
                        abort(404); // Activity not found
                    }

                    // Fetch related photos
                    $daftar_foto = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'")->fetchAll(\PDO::FETCH_ASSOC);

                    // Fetch categories and companies for dropdowns
                    $dataDIFFICULT = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
                    $datacompany = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

                    return view('admin.activity.actionreport.ubah', compact('activity', 'daftar_foto', 'dataDIFFICULT', 'datacompany'));
                }

            public function activityUpdate(Request $request, $id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();
                    date_default_timezone_set('Asia/Jakarta');

                // Handle photo deletion first if 'hapus_foto' parameter is present
                    if ($request->has('hapus_foto')) {
                $id_foto_to_delete = $request->input('hapus_foto');
                $ambil = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = '$id_foto_to_delete'");
                $foto = $ambil->fetch(\PDO::FETCH_ASSOC);

                if ($foto) {
                    $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $stmt_delete = $koneksi->prepare("DELETE FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = ?");
                    $stmt_delete->execute([$id_foto_to_delete]);
                }

                return redirect()->route('admin.activity.actionreport.ubah', ['id' => $id])->with('success', 'Foto berhasil dihapus.');
            }
                    // Validate form input for the main update
                    $request->validate([
                        'company' => 'required',
                        'email' => 'required|email',
                        'username' => 'required',
                        'subject' => 'required|max:255',
                        'deskripsi' => 'required',
                        'komentar' => 'nullable', // Assuming komentar can be empty
                        'ID_DIFFICULT' => 'required|numeric',
                        'foto_baru.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // For new photos
                    ]);

                    $TGL_KOMENTAR = date("Y-m-d H:i:s");

                    // Update main activity data
                    $stmt = $koneksi->prepare("
                        UPDATE SBO_SUPPORT_SAPHANA.ACTIVITY SET
                            ID_COMPANY = ?,
                            MAIL_COMPANY = ?,
                            NM_USER = ?,
                            SUBJECT = ?,
                            DESKRIPSI = ?,
                            KOMENTAR = ?,
                            ID_DIFFICULT = ?,
                            TGL_KOMENTAR = ?
                        WHERE ID_ACTIVITY = ?
                    ");

                    $stmt->execute([
                        $request->company,
                        $request->email,
                        $request->username,
                        $request->subject,
                        $request->deskripsi,
                        $request->komentar,
                        $request->ID_DIFFICULT,
                        $TGL_KOMENTAR,
                        $id
                    ]);

                    // Upload new photos if any (still using Storage facade for upload as it's more convenient)
                    if ($request->hasFile('foto_baru')) {
                        foreach ($request->file('foto_baru') as $file) {
                            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads', $uniqueName, 'public'); // Using Storage facade for storing

                            $stmt_max_id = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO");
                            $row_max = $stmt_max_id->fetch(\PDO::FETCH_ASSOC);
                            $next_id = $row_max['ID'] + 1;

                            $stmt_insert = $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO) VALUES (?, ?, ?)");
                            $stmt_insert->execute([$next_id, $id, $uniqueName]);
                        }
                    }

                    return redirect()->route('admin.activity.report')->with('success', 'Data berhasil diubah!');
                }


                public function activitydetail($id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection(); // gunakan koneksi ke SAP HANA

                    // Ambil detail aktivitas (query langsung)
                    $query = "
                        SELECT 
                            ACTIVITY.*, 
                            DIFFICULT_LEVEL.NM_DIFFICULT, 
                            COMPANY_SAP.NM_COMPANY 
                        FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                        LEFT JOIN SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL ON ACTIVITY.ID_DIFFICULT = DIFFICULT_LEVEL.ID_DIFFICULT
                        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                        WHERE ACTIVITY.ID_ACTIVITY = '$id'
                    ";

                    $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);

                    // Ambil foto aktivitas
                    $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
                                    ->fetchAll(\PDO::FETCH_ASSOC);

                    return view('admin.activity.actionreport.detail', compact('activity', 'fotos'));
                }


                // This method is used to display the activity status page
                public function activityStatus()
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();;
                    

                    $sql = "
                        SELECT 
                            ACTIVITY.*, 
                            STATUS_LEVEL.NM_STATUS, 
                            COMPANY_SAP.NM_COMPANY 
                        FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                        LEFT JOIN SBO_SUPPORT_SAPHANA.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                    ";

                    $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                    // Ambil foto pertama untuk setiap aktivitas
                    foreach ($activities as &$activity) {
                        $id = $activity['ID_ACTIVITY'];
                        $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
                        $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
                        $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
                    }

                    return view('admin.activity.status', compact('activities'));
                }

                public function activityDetailStatus($id)
            {
                if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                $koneksi = HanaConnection::getConnection(); // gunakan koneksi ke SAP HANA

                // Ambil detail aktivitas (query langsung)
                $query = "
                    SELECT 
                        ACTIVITY.*, 
                        STATUS_LEVEL.NM_STATUS, 
                        COMPANY_SAP.NM_COMPANY 
                    FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                    LEFT JOIN SBO_SUPPORT_SAPHANA.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                    LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                    WHERE ACTIVITY.ID_ACTIVITY = '$id'
                ";

                $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);

                // Ambil foto aktivitas
                $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
                                ->fetchAll(\PDO::FETCH_ASSOC);

                return view('admin.activity.actionstatus.detail', compact('activity', 'fotos'));
            }


                public function activityEditStatus($id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();

                    // Fetch activity data
                    $activity = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_ACTIVITY='$id'")->fetch(\PDO::FETCH_ASSOC);

                    if (!$activity) {
                        abort(404); // Activity not found
                    }

                    // Fetch related photos
                    $daftar_foto = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'")->fetchAll(\PDO::FETCH_ASSOC);

                    // Fetch categories and companies for dropdowns
                    $datastatus = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.STATUS_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
                    $datacompany = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

                    return view('admin.activity.actionstatus.ubah', compact('activity', 'daftar_foto', 'datastatus', 'datacompany'));
                }

            public function activityUpdateStatus(Request $request, $id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();
                    date_default_timezone_set('Asia/Jakarta');

                // Handle photo deletion first if 'hapus_foto' parameter is present
                    if ($request->has('hapus_foto')) {
                $id_foto_to_delete = $request->input('hapus_foto');
                $ambil = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = '$id_foto_to_delete'");
                $foto = $ambil->fetch(\PDO::FETCH_ASSOC);

                if ($foto) {
                    $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $stmt_delete = $koneksi->prepare("DELETE FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = ?");
                    $stmt_delete->execute([$id_foto_to_delete]);
                }

                return redirect()->route('admin.activity.actionstatus.ubah', ['id' => $id])->with('success', 'Foto berhasil dihapus.');
            }
                    // Validate form input for the main update
                    $request->validate([
                        'company' => 'required',
                        'email' => 'required|email',
                        'username' => 'required',
                        'subject' => 'required|max:255',
                        'deskripsi' => 'required',
                        'ID_STATUS' => 'required|numeric',
                        'foto_baru.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',// For new photos
                        'deskripsi_admin' => 'required'
                    ]);

                    $TGL_STATUS = date("Y-m-d H:i:s");

                    // Update main activity data
                    $stmt = $koneksi->prepare("
                        UPDATE SBO_SUPPORT_SAPHANA.ACTIVITY SET
                            ID_COMPANY = ?,
                            MAIL_COMPANY = ?,
                            NM_USER = ?,
                            SUBJECT = ?,
                            DESKRIPSI = ?,
                            ID_STATUS = ?,
                            TGL_STATUS = ?,
                            DESKRIPSI_ADMIN = ?
                        WHERE ID_ACTIVITY = ?
                    ");

                    $stmt->execute([
                        $request->company,
                        $request->email,
                        $request->username,
                        $request->subject,
                        $request->deskripsi,                        
                        $request->ID_STATUS,
                        $TGL_STATUS,
                        $request->deskripsi_admin, 
                        $id
                    ]);

                    // Upload new photos if any (still using Storage facade for upload as it's more convenient)
                    if ($request->hasFile('foto_baru')) {
                        foreach ($request->file('foto_baru') as $file) {
                            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads', $uniqueName, 'public'); // Using Storage facade for storing

                            $stmt_max_id = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO");
                            $row_max = $stmt_max_id->fetch(\PDO::FETCH_ASSOC);
                            $next_id = $row_max['ID'] + 1;

                            $stmt_insert = $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO) VALUES (?, ?, ?)");
                            $stmt_insert->execute([$next_id, $id, $uniqueName]);
                        }
                    }

                    return redirect()->route('admin.activity.status')->with('success', 'Data berhasil diubah!');
                }


        // This method is used to display the activity solved page
            public function activitySolved()
            {
                if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                $koneksi = HanaConnection::getConnection();;
                            

                            $sql = "
                                SELECT 
                                    ACTIVITY.*, 
                                    STATUS_LEVEL.NM_STATUS, 
                                    COMPANY_SAP.NM_COMPANY 
                                FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                                LEFT JOIN SBO_SUPPORT_SAPHANA.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                                LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                            ";

                            $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                            // Ambil foto pertama untuk setiap aktivitas
                            foreach ($activities as &$activity) {
                                $id = $activity['ID_ACTIVITY'];
                                $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
                                $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
                                $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
                            }

                            return view('admin.activity.solved', compact('activities'));
                        }


                public function activitydetailsolved($id)
            {
                if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                $koneksi = HanaConnection::getConnection(); // gunakan koneksi ke SAP HANA

                // Ambil detail aktivitas (query langsung)
                $query = "
                    SELECT 
                        ACTIVITY.*, 
                        STATUS_LEVEL.NM_STATUS, 
                        COMPANY_SAP.NM_COMPANY 
                    FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                    LEFT JOIN SBO_SUPPORT_SAPHANA.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                    LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                    WHERE ACTIVITY.ID_ACTIVITY = '$id'
                ";

                $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);

                // Ambil foto aktivitas
                $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
                                ->fetchAll(\PDO::FETCH_ASSOC);

                return view('admin.activity.actionsolved.detail', compact('activity', 'fotos'));
            }


                public function activityEditSolved($id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();

                    // Fetch activity data
                    $activity = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY WHERE ID_ACTIVITY='$id'")->fetch(\PDO::FETCH_ASSOC);

                    if (!$activity) {
                        abort(404); // Activity not found
                    }

                    // Fetch related photos
                    $daftar_foto = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'")->fetchAll(\PDO::FETCH_ASSOC);

                    // Fetch categories and companies for dropdowns
                    $datastatus = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.STATUS_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
                    $datacompany = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

                    return view('admin.activity.actionsolved.ubah', compact('activity', 'daftar_foto', 'datastatus', 'datacompany'));
                }

            public function activityUpdateSolved(Request $request, $id)
                {
                    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                    $koneksi = HanaConnection::getConnection();
                    date_default_timezone_set('Asia/Jakarta');

                // Handle photo deletion first if 'hapus_foto' parameter is present
                    if ($request->has('hapus_foto')) {
                $id_foto_to_delete = $request->input('hapus_foto');
                $ambil = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = '$id_foto_to_delete'");
                $foto = $ambil->fetch(\PDO::FETCH_ASSOC);

                if ($foto) {
                    $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $stmt_delete = $koneksi->prepare("DELETE FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = ?");
                    $stmt_delete->execute([$id_foto_to_delete]);
                }

                return redirect()->route('admin.activity.actionsolved.ubah', ['id' => $id])->with('success', 'Foto berhasil dihapus.');
            }
                    // Validate form input for the main update
                    $request->validate([
                        'company' => 'required',
                        'email' => 'required|email',
                        'username' => 'required',
                        'subject' => 'required|max:255',
                        'deskripsi' => 'required',
                        'ID_STATUS' => 'required|numeric',
                        'foto_baru.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // For new photos
                    ]);

                    $TGL_SOLVED = date("Y-m-d H:i:s");

                    // Update main activity data
                    $stmt = $koneksi->prepare("
                        UPDATE SBO_SUPPORT_SAPHANA.ACTIVITY SET
                            ID_COMPANY = ?,
                            MAIL_COMPANY = ?,
                            NM_USER = ?,
                            SUBJECT = ?,
                            DESKRIPSI_SOLVED = ?,
                            ID_STATUS = ?,
                            TGL_SOLVED = ?
                        WHERE ID_ACTIVITY = ?
                    ");

                    $stmt->execute([
                        $request->company,
                        $request->email,
                        $request->username,
                        $request->subject,
                        $request->deskripsi,
                        $request->ID_STATUS,
                        $TGL_SOLVED,
                        $id
                    ]);

                    // Upload new photos if any (still using Storage facade for upload as it's more convenient)
                    if ($request->hasFile('foto_baru')) {
                        foreach ($request->file('foto_baru') as $file) {
                            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads', $uniqueName, 'public'); // Using Storage facade for storing

                            $stmt_max_id = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO");
                            $row_max = $stmt_max_id->fetch(\PDO::FETCH_ASSOC);
                            $next_id = $row_max['ID'] + 1;

                            $stmt_insert = $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO) VALUES (?, ?, ?)");
                            $stmt_insert->execute([$next_id, $id, $uniqueName]);
                        }
                    }

                    return redirect()->route('admin.activity.solved')->with('success', 'Data berhasil diubah!');
                }




     public function email()
    {
        if (!session('admin_sap')) {
            abort(404);
        }

        $koneksi = HanaConnection::getConnection();
        $emailSettings = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP")->fetchAll(\PDO::FETCH_ASSOC);

        return view('admin.pengaturan.email', compact('emailSettings'));
    }

public function emailDestroy($id)
{
    if (!session('admin_sap')) {
        abort(404);
    }

    $koneksi = HanaConnection::getConnection();
    $id = (int) $id;

    $sqlDelete = "DELETE FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP WHERE ID_EMAIL = $id";
    $koneksi->exec($sqlDelete);

    return redirect()->route('admin.pengaturan.email')->with('success', 'Email setting deleted successfully.');
}



  public function emailUpdate(Request $request, $id)
{
    if (!session('admin_sap')) {
        abort(404);
    }

    $request->validate([
        'NM_EMAIL' => 'required|email|max:255',
    ]);

    $koneksi = HanaConnection::getConnection();
    $id = (int) $id;
    $nm_email = addslashes($request->NM_EMAIL); // atau gunakan htmlspecialchars()

    // Cek duplikat
    $sqlCek = "SELECT COUNT(*) AS JML FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP WHERE NM_EMAIL = '$nm_email' AND ID_EMAIL != $id";
    $result = $koneksi->query($sqlCek)->fetch();
    if ($result['JML'] > 0) {
        return redirect()->back()->withErrors(['Email sudah digunakan.']);
    }

    // Update langsung pakai exec()
    $sqlUpdate = "UPDATE SBO_SUPPORT_SAPHANA.EMAIL_SAP SET NM_EMAIL = '$nm_email' WHERE ID_EMAIL = $id";
    $koneksi->exec($sqlUpdate);

    return redirect()->route('admin.pengaturan.email')->with('success', 'Email berhasil diperbarui.');
}



    public function emailTambah()
    {
        if (!session('admin_sap')) {
            abort(404);
        }

        return view('admin.pengaturan.actionemail.tambah');
    }

    public function emailStore(Request $request)
{
    if (!session('admin_sap')) {
        abort(404);
    }

    $request->validate([
        'email' => 'required|email|max:255',
    ]);

    $koneksi = HanaConnection::getConnection();

    // Cek duplikat email secara langsung (query biasa)
    $email = $request->email;
    $sqlCheck = "SELECT COUNT(*) AS TOTAL FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP WHERE NM_EMAIL = '$email'";
    $result = $koneksi->query($sqlCheck)->fetch(\PDO::FETCH_ASSOC);

    if ($result['TOTAL'] > 0) {
        return redirect()->back()->withErrors(['email' => 'Email sudah digunakan.']);
    }

    // Ambil ID_EMAIL terakhir dan tambah 1
    $sqlMaxId = "SELECT MAX(ID_EMAIL) AS MAX_ID FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP";
    $resultId = $koneksi->query($sqlMaxId)->fetch(\PDO::FETCH_ASSOC);
    $nextId = (int) $resultId['MAX_ID'] + 1;

    // Simpan email baru
    $sqlInsert = "INSERT INTO SBO_SUPPORT_SAPHANA.EMAIL_SAP (ID_EMAIL, NM_EMAIL) VALUES ($nextId, '$email')";
    $koneksi->exec($sqlInsert);

    return redirect()->route('admin.pengaturan.email')->with('success', 'Email added successfully.');
}


    public function cekEmail(Request $request)
    {
        if (!session('admin_sap')) {
            return response()->json(['status' => 'unauthorized'], 403);
        }

        $email = $request->input('email');
        $excludeId = $request->input('exclude_id');

        $koneksi = HanaConnection::getConnection();

        $query = "SELECT COUNT(*) FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP WHERE NM_EMAIL = ?";
        $params = [$email];

        if ($excludeId) {
            $query .= " AND ID_EMAIL != ?";
            $params[] = $excludeId;
        }

        $stmt = $koneksi->prepare($query);
        $stmt->execute($params);
        $exists = $stmt->fetchColumn() > 0;

        return response()->json(['exists' => $exists]);
    }

    public function emailEdit($id)
{
    if (!session('admin_sap')) {
        abort(404); // Unauthorized access
    }

    $koneksi = HanaConnection::getConnection();

    // Validasi ID untuk memastikan integer
    if (!is_numeric($id)) {
        abort(400, 'Invalid ID');
    }

    // Query langsung tanpa prepare-execute
    $email = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP WHERE ID_EMAIL = {$id}")->fetch(\PDO::FETCH_ASSOC);

    if (!$email) {
        abort(404); // Data tidak ditemukan
    }

    return view('admin.pengaturan.actionemail.ubah', compact('email'));
}

    public function difficult()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        $koneksi = HanaConnection::getConnection();
        $difficultSettings = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
        
        
       
        return view('admin.pengaturan.difficult', compact('difficultSettings'));
    }

    public function difficultDestroy($id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $koneksi = HanaConnection::getConnection();
        $id = (int) $id;

        $sqlDelete = "DELETE FROM SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL WHERE ID_DIFFICULT = $id";
        $koneksi->exec($sqlDelete);

        return redirect()->route('admin.pengaturan.difficult')->with('success', 'Difficult setting deleted successfully.');
    }

    public function difficultTambah()
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        return view('admin.pengaturan.actiondifficult.tambah');
    }

    public function difficultStore(Request $request)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $request->validate([
            'nama_DIFFICULT' => 'required|max:255|min:3', // Validasi nama DIFFICULT
            
        ]);

        $koneksi = HanaConnection::getConnection();

        // Ambil ID_DIFFICULT terakhir dan tambah 1
        $sqlMaxId = "SELECT MAX(ID_DIFFICULT) AS MAX_ID FROM SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL";
        $resultId = $koneksi->query($sqlMaxId)->fetch(\PDO::FETCH_ASSOC);
        $nextId = (int) $resultId['MAX_ID'] + 1;

        // Simpan DIFFICULT baru
        $sqlInsert = "INSERT INTO SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL (ID_DIFFICULT, NM_DIFFICULT) VALUES ($nextId, '{$request->nama_DIFFICULT}')";
        $koneksi->exec($sqlInsert);

        return redirect()->route('admin.pengaturan.difficult')->with('success', 'Difficult level added successfully.');
    }

    public function difficultEdit($id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $koneksi = HanaConnection::getConnection();

        // Validasi ID untuk memastikan integer
        if (!is_numeric($id)) {
            abort(400, 'Invalid ID');
        }

        // Query langsung tanpa prepare-execute
        $difficult = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL WHERE ID_DIFFICULT = {$id}")->fetch(\PDO::FETCH_ASSOC);

        if (!$difficult) {
            abort(404); // Data tidak ditemukan
        }

        return view('admin.pengaturan.actiondifficult.ubah', compact('difficult'));
    }

    public function difficultUpdate(Request $request, $id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $request->validate([
            'nama_DIFFICULT' => 'required|max:255|min:3', // Validasi nama DIFFICULT
            // Tambahkan validasi lain sesuai kebutuhan

        ]);

        $koneksi = HanaConnection::getConnection();

        // Update DIFFICULT
        $sqlUpdate = "UPDATE SBO_SUPPORT_SAPHANA.DIFFICULT_LEVEL SET NM_DIFFICULT = '{$request->nama_DIFFICULT}' WHERE ID_DIFFICULT = {$id}";
        $koneksi->exec($sqlUpdate);

        return redirect()->route('admin.pengaturan.difficult')->with('success', 'Difficult level updated successfully.');
    }


    public function status()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        $koneksi = HanaConnection::getConnection();
        $statusSettings = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.STATUS_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);

        
        return view('admin.pengaturan.status', compact('statusSettings'));
    }

    public function statusDestroy($id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $koneksi = HanaConnection::getConnection();
        $id = (int) $id;

        $sqlDelete = "DELETE FROM SBO_SUPPORT_SAPHANA.STATUS_LEVEL WHERE ID_STATUS = $id";
        $koneksi->exec($sqlDelete);

        return redirect()->route('admin.pengaturan.status')->with('success', 'Status setting deleted successfully.');
    }

    public function statusTambah()
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        return view('admin.pengaturan.actionstatus.tambah');
    }
    
    public function statusStore(Request $request)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $request->validate([
            'nama_status' => 'required|max:255|min:3', // Validasi nama status
            
        ]);

        $koneksi = HanaConnection::getConnection();

        // Ambil ID_STATUS terakhir dan tambah 1
        $sqlMaxId = "SELECT MAX(ID_STATUS) AS MAX_ID FROM SBO_SUPPORT_SAPHANA.STATUS_LEVEL";
        $resultId = $koneksi->query($sqlMaxId)->fetch(\PDO::FETCH_ASSOC);
        $nextId = (int) $resultId['MAX_ID'] + 1;

        // Simpan status baru
        $sqlInsert = "INSERT INTO SBO_SUPPORT_SAPHANA.STATUS_LEVEL (ID_STATUS, NM_STATUS) VALUES ($nextId, '{$request->nama_status}')";
        $koneksi->exec($sqlInsert);

        return redirect()->route('admin.pengaturan.status')->with('success', 'Status level added successfully.');
    }
    
    public function statusEdit($id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $koneksi = HanaConnection::getConnection();

        // Validasi ID untuk memastikan integer
        if (!is_numeric($id)) {
            abort(400, 'Invalid ID');
        }

        // Query langsung tanpa prepare-execute
        $status = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.STATUS_LEVEL WHERE ID_STATUS = {$id}")->fetch(\PDO::FETCH_ASSOC);

        if (!$status) {
            abort(404); // Data tidak ditemukan
        }

        return view('admin.pengaturan.actionstatus.ubah', compact('status'));
    }

    public function statusUpdate(Request $request, $id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $request->validate([
            'nama_status' => 'required|max:255|min:3', // Validasi nama status
            

        ]);

        $koneksi = HanaConnection::getConnection();

        // Update status
        $sqlUpdate = "UPDATE SBO_SUPPORT_SAPHANA.STATUS_LEVEL SET NM_STATUS = '{$request->nama_status}' WHERE ID_STATUS = {$id}";
        $koneksi->exec($sqlUpdate);

        return redirect()->route('admin.pengaturan.status')->with('success', 'Status level updated successfully.');
    }









    public function company()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        $koneksi = HanaConnection::getConnection();
        $COMPANYSettings = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);
        
        
       
        return view('admin.pengaturan.company', compact('COMPANYSettings'));
    }

    public function companyDestroy($id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $koneksi = HanaConnection::getConnection();
        $id = (int) $id;

        $sqlDelete = "DELETE FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP WHERE ID_COMPANY = $id";
        $koneksi->exec($sqlDelete);

        return redirect()->route('admin.pengaturan.company')->with('success', 'COMPANY setting deleted successfully.');
    }

    public function companyTambah()
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        return view('admin.pengaturan.actioncompany.tambah');
    }

    public function companyStore(Request $request)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $request->validate([
            'NM_COMPANY' => 'required|max:255|min:3', // Validasi nama COMPANY
            
        ]);

        $koneksi = HanaConnection::getConnection();

        // Ambil ID_COMPANY terakhir dan tambah 1
        $sqlMaxId = "SELECT MAX(ID_COMPANY) AS MAX_ID FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP";
        $resultId = $koneksi->query($sqlMaxId)->fetch(\PDO::FETCH_ASSOC);
        $nextId = (int) $resultId['MAX_ID'] + 1;

        // Simpan COMPANY baru
        $sqlInsert = "INSERT INTO SBO_SUPPORT_SAPHANA.COMPANY_SAP (ID_COMPANY, NM_COMPANY) VALUES ($nextId, '{$request->NM_COMPANY}')";
        $koneksi->exec($sqlInsert);

        return redirect()->route('admin.pengaturan.company')->with('success', 'COMPANY level added successfully.');
    }

    public function companyEdit($id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $koneksi = HanaConnection::getConnection();

        // Validasi ID untuk memastikan integer
        if (!is_numeric($id)) {
            abort(400, 'Invalid ID');
        }

        // Query langsung tanpa prepare-execute
        $COMPANY = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP WHERE ID_COMPANY = {$id}")->fetch(\PDO::FETCH_ASSOC);

        if (!$COMPANY) {
            abort(404); // Data tidak ditemukan
        }

        return view('admin.pengaturan.actionCOMPANY.ubah', compact('COMPANY'));
    }

    public function companyUpdate(Request $request, $id)
    {
        if (!session('admin_sap')) {
            abort(404); // Unauthorized access
        }

        $request->validate([
            'NM_COMPANY' => 'required|max:255|min:3', // Validasi nama COMPANY
            // Tambahkan validasi lain sesuai kebutuhan

        ]);

        $koneksi = HanaConnection::getConnection();

        // Update COMPANY
        $sqlUpdate = "UPDATE SBO_SUPPORT_SAPHANA.COMPANY_SAP SET NM_COMPANY = '{$request->NM_COMPANY}' WHERE ID_COMPANY = {$id}";
        $koneksi->exec($sqlUpdate);

        return redirect()->route('admin.pengaturan.company')->with('success', 'COMPANY level updated successfully.');
    }









}