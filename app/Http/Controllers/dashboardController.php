<?php

namespace App\Http\Controllers;

use App\Helpers\HanaConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;




class dashboardController extends Controller
{
// This method is used to display the dashboard page
    public function index(Request $request)
{
    if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
    $koneksi = HanaConnection::getConnection();

    // Filter WHERE
    $where = "WHERE 1=1";
    if ($request->bulan) {
        $where .= " AND MONTH(TGL_ACTIVITY) = " . (int) $request->bulan;
    }
    if ($request->tahun) {
        $where .= " AND YEAR(TGL_ACTIVITY) = " . (int) $request->tahun;
    }

    // Data untuk chart
    $chartQuery = "
        SELECT COMPANY_SAP.NM_COMPANY, COUNT(*) AS JUMLAH 
        FROM SBO_CMNP_KK.ACTIVITY
        LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        $where 
        GROUP BY COMPANY_SAP.NM_COMPANY
    ";
    $dataChart = $koneksi->query($chartQuery)->fetchAll();

    // Data tabel
    $tabelQuery = "
        SELECT * 
        FROM SBO_CMNP_KK.ACTIVITY 
        LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        $where
    ";
    $aktivitas = $koneksi->query($tabelQuery)->fetchAll();

    return view('admin.dashboardAdmin', compact('dataChart', 'aktivitas'));
}
// This method is used to display the activity report page
    public function activityReport()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        $koneksi = HanaConnection::getConnection();;
        

        $sql = "
            SELECT 
                ACTIVITY.*, 
                KATEGORI.NAMA_KATEGORI, 
                COMPANY_SAP.NM_COMPANY 
            FROM SBO_CMNP_KK.ACTIVITY 
            LEFT JOIN SBO_CMNP_KK.KATEGORI ON ACTIVITY.ID_KATEGORI = KATEGORI.ID_KATEGORI
            LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        ";

        $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        // Ambil foto pertama untuk setiap aktivitas
        foreach ($activities as &$activity) {
            $id = $activity['ID_ACTIVITY'];
            $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
            $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
            $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
        }

        return view('admin.activity.report', compact('activities'));
    }

            public function activitydestroy($id)
            {
               if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
                {
            
            $koneksi = HanaConnection::getConnection();
            

            // Ambil nama file foto utama
            $ambil_activity = $koneksi->query("SELECT ID_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY WHERE ID_ACTIVITY='$id'");
            $pecah_activity = $ambil_activity->fetch(\PDO::FETCH_ASSOC);
            $fotoUtama = $pecah_activity['ID_ACTIVITY_FOTO'] ?? null;

            // Ambil semua foto tambahan
            $ambil_foto_tambahan = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'");
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
            $koneksi->query("DELETE FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'");
            $hapus = $koneksi->query("DELETE FROM SBO_CMNP_KK.ACTIVITY WHERE ID_ACTIVITY='$id'");

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
            $datakategori = $koneksi->query("SELECT * FROM SBO_CMNP_KK.KATEGORI")->fetchAll(\PDO::FETCH_ASSOC);
            $datacompany = $koneksi->query("SELECT * FROM SBO_CMNP_KK.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

            return view('admin.activity.actionreport.tambah', compact('datakategori', 'datacompany'));
            }
// This method is used to store a new activity report
            public function activityStore(Request $request)
            {
                if (!session('admin_sap')) {
           abort(404); // Activity not found
        }

                $request->validate([
                    'company' => 'required',
                    'email' => 'required|email',
                    'username' => 'required',
                    'Subject' => 'required|max:255',
                    'deskripsi' => 'required',
                    'ID_KATEGORI' => 'required|numeric',
                    'foto.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                $koneksi = HanaConnection::getConnection();
                date_default_timezone_set("Asia/Jakarta");
                $TGL_ACTIVITY = date("Y-m-d H:i:s");

                // Ambil ID terakhir
                $stmt = $koneksi->query("SELECT MAX(ID_ACTIVITY) AS ID FROM SBO_CMNP_KK.ACTIVITY");
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
                $stmt = $koneksi->prepare("INSERT INTO SBO_CMNP_KK.ACTIVITY 
                    (ID_ACTIVITY, ID_COMPANY, MAIL_COMPANY, NM_USER, SUBJECT, DESKRIPSI, ID_ACTIVITY_FOTO, ID_KATEGORI, TGL_ACTIVITY)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $nextId,
                    $request->company,
                    $request->email,
                    $request->username,
                    $request->Subject,
                    $request->deskripsi,
                    $mainFotoName,
                    $request->ID_KATEGORI,
                    $TGL_ACTIVITY
                ]);

                // Simpan semua foto ke tabel ACTIVITY_FOTO
                foreach ($fotoNames as $fotoName) {
                    $stmt = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_CMNP_KK.ACTIVITY_FOTO");
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $nextFotoId = $row['ID'] + 1;

                    $koneksi->prepare("INSERT INTO SBO_CMNP_KK.ACTIVITY_FOTO(ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO)
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
                    $activity = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY WHERE ID_ACTIVITY='$id'")->fetch(\PDO::FETCH_ASSOC);

                    if (!$activity) {
                        abort(404); // Activity not found
                    }

                    // Fetch related photos
                    $daftar_foto = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'")->fetchAll(\PDO::FETCH_ASSOC);

                    // Fetch categories and companies for dropdowns
                    $datakategori = $koneksi->query("SELECT * FROM SBO_CMNP_KK.KATEGORI")->fetchAll(\PDO::FETCH_ASSOC);
                    $datacompany = $koneksi->query("SELECT * FROM SBO_CMNP_KK.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

                    return view('admin.activity.actionreport.ubah', compact('activity', 'daftar_foto', 'datakategori', 'datacompany'));
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
                $ambil = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = '$id_foto_to_delete'");
                $foto = $ambil->fetch(\PDO::FETCH_ASSOC);

                if ($foto) {
                    $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $stmt_delete = $koneksi->prepare("DELETE FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = ?");
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
                        'ID_KATEGORI' => 'required|numeric',
                        'foto_baru.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // For new photos
                    ]);

                    $TGL_KOMENTAR = date("Y-m-d H:i:s");

                    // Update main activity data
                    $stmt = $koneksi->prepare("
                        UPDATE SBO_CMNP_KK.ACTIVITY SET
                            ID_COMPANY = ?,
                            MAIL_COMPANY = ?,
                            NM_USER = ?,
                            SUBJECT = ?,
                            DESKRIPSI = ?,
                            KOMENTAR = ?,
                            ID_KATEGORI = ?,
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
                        $request->ID_KATEGORI,
                        $TGL_KOMENTAR,
                        $id
                    ]);

                    // Upload new photos if any (still using Storage facade for upload as it's more convenient)
                    if ($request->hasFile('foto_baru')) {
                        foreach ($request->file('foto_baru') as $file) {
                            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads', $uniqueName, 'public'); // Using Storage facade for storing

                            $stmt_max_id = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_CMNP_KK.ACTIVITY_FOTO");
                            $row_max = $stmt_max_id->fetch(\PDO::FETCH_ASSOC);
                            $next_id = $row_max['ID'] + 1;

                            $stmt_insert = $koneksi->prepare("INSERT INTO SBO_CMNP_KK.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO) VALUES (?, ?, ?)");
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
                            KATEGORI.NAMA_KATEGORI, 
                            COMPANY_SAP.NM_COMPANY 
                        FROM SBO_CMNP_KK.ACTIVITY 
                        LEFT JOIN SBO_CMNP_KK.KATEGORI ON ACTIVITY.ID_KATEGORI = KATEGORI.ID_KATEGORI
                        LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                        WHERE ACTIVITY.ID_ACTIVITY = '$id'
                    ";

                    $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);

                    // Ambil foto aktivitas
                    $fotos = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
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
                        FROM SBO_CMNP_KK.ACTIVITY 
                        LEFT JOIN SBO_CMNP_KK.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                        LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                    ";

                    $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                    // Ambil foto pertama untuk setiap aktivitas
                    foreach ($activities as &$activity) {
                        $id = $activity['ID_ACTIVITY'];
                        $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
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
                    FROM SBO_CMNP_KK.ACTIVITY 
                    LEFT JOIN SBO_CMNP_KK.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                    LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                    WHERE ACTIVITY.ID_ACTIVITY = '$id'
                ";

                $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);

                // Ambil foto aktivitas
                $fotos = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
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
                    $activity = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY WHERE ID_ACTIVITY='$id'")->fetch(\PDO::FETCH_ASSOC);

                    if (!$activity) {
                        abort(404); // Activity not found
                    }

                    // Fetch related photos
                    $daftar_foto = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'")->fetchAll(\PDO::FETCH_ASSOC);

                    // Fetch categories and companies for dropdowns
                    $datastatus = $koneksi->query("SELECT * FROM SBO_CMNP_KK.STATUS_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
                    $datacompany = $koneksi->query("SELECT * FROM SBO_CMNP_KK.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

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
                $ambil = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = '$id_foto_to_delete'");
                $foto = $ambil->fetch(\PDO::FETCH_ASSOC);

                if ($foto) {
                    $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $stmt_delete = $koneksi->prepare("DELETE FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = ?");
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
                        'komentar' => 'nullable', // Assuming komentar can be empty
                        'ID_STATUS' => 'required|numeric',
                        'foto_baru.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // For new photos
                    ]);

                    $TGL_STATUS = date("Y-m-d H:i:s");

                    // Update main activity data
                    $stmt = $koneksi->prepare("
                        UPDATE SBO_CMNP_KK.ACTIVITY SET
                            ID_COMPANY = ?,
                            MAIL_COMPANY = ?,
                            NM_USER = ?,
                            SUBJECT = ?,
                            DESKRIPSI = ?,
                            KOMENTAR = ?,
                            ID_STATUS = ?,
                            TGL_STATUS = ?
                        WHERE ID_ACTIVITY = ?
                    ");

                    $stmt->execute([
                        $request->company,
                        $request->email,
                        $request->username,
                        $request->subject,
                        $request->deskripsi,
                        $request->komentar,
                        $request->ID_STATUS,
                        $TGL_STATUS,
                        $id
                    ]);

                    // Upload new photos if any (still using Storage facade for upload as it's more convenient)
                    if ($request->hasFile('foto_baru')) {
                        foreach ($request->file('foto_baru') as $file) {
                            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads', $uniqueName, 'public'); // Using Storage facade for storing

                            $stmt_max_id = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_CMNP_KK.ACTIVITY_FOTO");
                            $row_max = $stmt_max_id->fetch(\PDO::FETCH_ASSOC);
                            $next_id = $row_max['ID'] + 1;

                            $stmt_insert = $koneksi->prepare("INSERT INTO SBO_CMNP_KK.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO) VALUES (?, ?, ?)");
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
                                FROM SBO_CMNP_KK.ACTIVITY 
                                LEFT JOIN SBO_CMNP_KK.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                                LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                            ";

                            $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                            // Ambil foto pertama untuk setiap aktivitas
                            foreach ($activities as &$activity) {
                                $id = $activity['ID_ACTIVITY'];
                                $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
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
                    FROM SBO_CMNP_KK.ACTIVITY 
                    LEFT JOIN SBO_CMNP_KK.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                    LEFT JOIN SBO_CMNP_KK.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                    WHERE ACTIVITY.ID_ACTIVITY = '$id'
                ";

                $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);

                // Ambil foto aktivitas
                $fotos = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
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
                    $activity = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY WHERE ID_ACTIVITY='$id'")->fetch(\PDO::FETCH_ASSOC);

                    if (!$activity) {
                        abort(404); // Activity not found
                    }

                    // Fetch related photos
                    $daftar_foto = $koneksi->query("SELECT * FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY='$id'")->fetchAll(\PDO::FETCH_ASSOC);

                    // Fetch categories and companies for dropdowns
                    $datastatus = $koneksi->query("SELECT * FROM SBO_CMNP_KK.STATUS_LEVEL")->fetchAll(\PDO::FETCH_ASSOC);
                    $datacompany = $koneksi->query("SELECT * FROM SBO_CMNP_KK.COMPANY_SAP")->fetchAll(\PDO::FETCH_ASSOC);

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
                $ambil = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = '$id_foto_to_delete'");
                $foto = $ambil->fetch(\PDO::FETCH_ASSOC);

                if ($foto) {
                    $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $stmt_delete = $koneksi->prepare("DELETE FROM SBO_CMNP_KK.ACTIVITY_FOTO WHERE ID_ACTIVITY_FOTO = ?");
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
                        'komentar' => 'nullable', // Assuming komentar can be empty
                        'ID_STATUS' => 'required|numeric',
                        'foto_baru.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // For new photos
                    ]);

                    $TGL_SOLVED = date("Y-m-d H:i:s");

                    // Update main activity data
                    $stmt = $koneksi->prepare("
                        UPDATE SBO_CMNP_KK.ACTIVITY SET
                            ID_COMPANY = ?,
                            MAIL_COMPANY = ?,
                            NM_USER = ?,
                            SUBJECT = ?,
                            DESKRIPSI_SOLVED = ?,
                            KOMENTAR = ?,
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
                        $request->komentar,
                        $request->ID_STATUS,
                        $TGL_SOLVED,
                        $id
                    ]);

                    // Upload new photos if any (still using Storage facade for upload as it's more convenient)
                    if ($request->hasFile('foto_baru')) {
                        foreach ($request->file('foto_baru') as $file) {
                            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads', $uniqueName, 'public'); // Using Storage facade for storing

                            $stmt_max_id = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_CMNP_KK.ACTIVITY_FOTO");
                            $row_max = $stmt_max_id->fetch(\PDO::FETCH_ASSOC);
                            $next_id = $row_max['ID'] + 1;

                            $stmt_insert = $koneksi->prepare("INSERT INTO SBO_CMNP_KK.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO) VALUES (?, ?, ?)");
                            $stmt_insert->execute([$next_id, $id, $uniqueName]);
                        }
                    }

                    return redirect()->route('admin.activity.solved')->with('success', 'Data berhasil diubah!');
                }




    public function email()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        return view('admin.pengaturan.email');
    }

    public function difficult()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        return view('admin.pengaturan.difficult');
    }

    public function status()
    {
        if (!session('admin_sap')) {
           abort(404); // Activity not found
        }
        return view('admin.pengaturan.status');
    }
}
