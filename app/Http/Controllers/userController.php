<?php

namespace App\Http\Controllers;

use App\Helpers\HanaConnection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Termwind\Components\Dd;




class userController extends Controller
{
// This method is used to display the dashboard page
    public function index(Request $request)
{   
    
    if (!Session::has('user_sap') || !Session::has('company')) {
    abort(404); 
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
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY
        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        $where 
        GROUP BY COMPANY_SAP.NM_COMPANY
    ";
    $dataChart = $koneksi->query($chartQuery)->fetchAll();

    // Data tabel
    $tabelQuery = "
        SELECT * 
        FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
        $where
    ";
    $aktivitas = $koneksi->query($tabelQuery)->fetchAll();

    return view('user.dashboardUser', compact('dataChart', 'aktivitas'));
}
// This method is used to display the activity report page
    public function activityReport()
    {
         if (!Session::has('user_sap') || !Session::has('company')) {
    abort(404); 
}
        $koneksi = HanaConnection::getConnection();
        
        $username= session::get( 'user_sap' ) ;

      $sql = " SELECT ACTIVITY.*, COMPANY_SAP.NM_COMPANY FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY 
        WHERE ACTIVITY.NM_USER = '$username' "; 
        $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        // Ambil foto pertama untuk setiap aktivitas
        foreach ($activities as &$activity) {
            $id = $activity['ID_ACTIVITY'];
            $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
            $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
            $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
        }

        return view('user.activity.report', compact('activities'));
    }


        // This method is used to display the form for adding a new activity report
                public function activitytambah()
            {
                if (!Session::has('user_sap') || !Session::has('company')) {
                    abort(404); }
                
                $koneksi = HanaConnection::getConnection();
                $companyName = Session::get('company');
                $email = Session::get('email');
                $username = Session::get('user_sap');
                $query = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP WHERE NM_COMPANY = '$companyName'");
                $queryy = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.EMAIL_SAP WHERE NM_EMAIL = '$email'");
                $companies = $query->fetchAll(\PDO::FETCH_ASSOC);

            return view('user.activity.actionreport.tambah', compact('companies', 'companyName', 'email', 'username'));
            
            }
// This method is used to store a new activity report
            public function activityStore(Request $request)
{
    if (!Session::has('user_sap') || !Session::has('company')) {
        abort(404);
    }

    $koneksi = HanaConnection::getConnection();
    date_default_timezone_set("Asia/Jakarta");

    $request->validate([
        'company' => 'required',
        'email' => 'required',
        'username' => 'required',
        'Subject' => 'required',
        'deskripsi' => 'required',
        'foto.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Ambil ID_ACTIVITY terakhir
    $stmt = $koneksi->query("SELECT MAX(ID_ACTIVITY) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY");
    $last = $stmt->fetch(\PDO::FETCH_ASSOC);
    $nextIdInt = $last['ID'] + 1;
    $TGL_ACTIVITY = date("Y-m-d H:i:s");

    // Ambil nama company dari ID
    $companyName = '';
    $query = $koneksi->query("SELECT NM_COMPANY FROM SBO_SUPPORT_SAPHANA.COMPANY_SAP WHERE ID_COMPANY = '{$request->company}'");
    if ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
        $companyName = strtolower(trim($row['NM_COMPANY']));
    } else {
        return back()->with('error', 'Nama perusahaan tidak ditemukan.');
    }

    // Tentukan kode perusahaan
    $kodePerusahaan = match ($companyName) {
        'cmnp' => '01',
        'cpi' => '02',
        'cw' => '03',
        'cms' => '04',
        'cmnproper' => '05',
        'cmlj' => '06',
        'ckjt' => '07',
        default => 'UNK',
    };

    // Format nomor urut 4 digit
    $nomorUrut = str_pad($nextIdInt, 4, '0', STR_PAD_LEFT);

    // Format tahun dan bulan
    $yearCode = date('y');
    $monthCode = date('m');

    // Buat TIKET
    $tiket = "TIKET-{$kodePerusahaan}{$yearCode}{$monthCode}{$nomorUrut}";

    // Proses upload foto
    $mainPhotoName = null;
    $uploadedPhotos = [];

    if ($request->hasFile('foto')) {
        foreach ($request->file('foto') as $file) {
            $uniqueName = uniqid('foto_', true) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads', $uniqueName, 'public');
            $uploadedPhotos[] = $uniqueName;
        }
        $mainPhotoName = $uploadedPhotos[0] ?? null;
    }

    // Simpan ke tabel aktivitas
    $stmt = $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY 
        (ID_ACTIVITY, ID_COMPANY, MAIL_COMPANY, NM_USER, SUBJECT, DESKRIPSI, ID_ACTIVITY_FOTO, TGL_ACTIVITY, TIKET)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $nextIdInt,
        $request->company,
        $request->email,
        $request->username,
        $request->Subject,
        $request->deskripsi,
        $mainPhotoName,
        $TGL_ACTIVITY,
        $tiket
    ]);

    // Simpan semua foto ke tabel ACTIVITY_FOTO
    foreach ($uploadedPhotos as $photoName) {
        $stmtFoto = $koneksi->query("SELECT MAX(ID_ACTIVITY_FOTO) AS ID FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO");
        $lastFoto = $stmtFoto->fetch(\PDO::FETCH_ASSOC);
        $nextFotoId = $lastFoto['ID'] + 1;

        $koneksi->prepare("INSERT INTO SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO (ID_ACTIVITY_FOTO, ID_ACTIVITY, NM_ACTIVITY_FOTO)
            VALUES (?, ?, ?)")
            ->execute([
                $nextFotoId,
                $nextIdInt,
                $photoName
            ]);
    }

    return redirect()->route('user.activity.report')->with('success', 'Data berhasil disimpan.');
}


                public function activitydetail($id)
                {
                    if (!Session::has('user_sap') || !Session::has('company')) {
                    abort(404); }


                    $koneksi = HanaConnection::getConnection(); // gunakan koneksi ke SAP HANA
                  $username = Session::get('user_sap');
                    // Ambil detail aktivitas (query langsung)
                    $query = "
                        SELECT 
                            ACTIVITY.*,
                            COMPANY_SAP.NM_COMPANY 
                        FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                        WHERE ACTIVITY.ID_ACTIVITY = '$id'
                        AND ACTIVITY.NM_USER = '$username'
                        
                    ";

                    $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);
                     if (!$activity) {
                    abort(404);
                    }
                        
                    // Ambil foto aktivitas
                    $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
                                    ->fetchAll(\PDO::FETCH_ASSOC);

                    return view('user.activity.actionreport.detail', compact('activity', 'fotos', 'username'));
                }


                // This method is used to display the activity status page
                public function activityStatus()
                {
                    if (!Session::has('user_sap') || !Session::has('company')) {
                    abort(404); }
                    $koneksi = HanaConnection::getConnection();;
                    
                    $username= session::get( 'user_sap' ) ;

                    $sql = "
                        SELECT 
                            ACTIVITY.*, 
                            STATUS_LEVEL.NM_STATUS, 
                            COMPANY_SAP.NM_COMPANY 
                        FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                        LEFT JOIN SBO_SUPPORT_SAPHANA.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                        LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                        WHERE ACTIVITY.NM_USER = '$username' ";

                    $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                    // Ambil foto pertama untuk setiap aktivitas
                    foreach ($activities as &$activity) {
                        $id = $activity['ID_ACTIVITY'];
                        $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
                        $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
                        $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
                    }

                    return view('user.activity.status', compact('activities'));
                }

                public function activityDetailStatus($id)
            {
                if (!Session::has('user_sap') || !Session::has('company')) {
                    abort(404); }
                $koneksi = HanaConnection::getConnection(); // gunakan koneksi ke SAP HANA
                $username= session::get( 'user_sap' ) ;
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
                    AND ACTIVITY.NM_USER = '$username'
                ";

                $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);
                if (!$activity) {
                    abort(404);
                    }

                // Ambil foto aktivitas
                $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
                                ->fetchAll(\PDO::FETCH_ASSOC);

                return view('user.activity.actionstatus.detail', compact('activity', 'fotos','username'));
            }




        // This method is used to display the activity solved page
            public function activitySolved()
            {
               if (!Session::has('user_sap') || !Session::has('company')) {
                    abort(404); }
                $koneksi = HanaConnection::getConnection();;
                            $username= session::get( 'user_sap' ) ;

                            $sql = "
                                SELECT 
                                    ACTIVITY.*, 
                                    STATUS_LEVEL.NM_STATUS, 
                                    COMPANY_SAP.NM_COMPANY 
                                FROM SBO_SUPPORT_SAPHANA.ACTIVITY 
                                LEFT JOIN SBO_SUPPORT_SAPHANA.STATUS_LEVEL ON ACTIVITY.ID_STATUS = STATUS_LEVEL.ID_STATUS
                                LEFT JOIN SBO_SUPPORT_SAPHANA.COMPANY_SAP ON ACTIVITY.ID_COMPANY = COMPANY_SAP.ID_COMPANY
                            WHERE ACTIVITY.NM_USER = '$username' ";
                            
                            $activities = $koneksi->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                            // Ambil foto pertama untuk setiap aktivitas
                            foreach ($activities as &$activity) {
                                $id = $activity['ID_ACTIVITY'];
                                $fotoQuery = $koneksi->query("SELECT NM_ACTIVITY_FOTO FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id' LIMIT 1");
                                $foto = $fotoQuery->fetch(\PDO::FETCH_ASSOC);
                                $activity['FOTO'] = $foto['NM_ACTIVITY_FOTO'] ?? null;
                            }

                            return view('user.activity.solved', compact('activities'));
                        }


                public function activitydetailsolved($id)
            {
                if (!Session::has('user_sap') || !Session::has('company')) {
                    abort(404); }
                $koneksi = HanaConnection::getConnection(); // gunakan koneksi ke SAP HANA
                    $username= session::get( 'user_sap' ) ;
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
                    AND ACTIVITY.NM_USER = '$username'

                ";

                $activity = $koneksi->query($query)->fetch(\PDO::FETCH_ASSOC);
                if (!$activity) {
                    abort(404);
                    }


                // Ambil foto aktivitas
                $fotos = $koneksi->query("SELECT * FROM SBO_SUPPORT_SAPHANA.ACTIVITY_FOTO WHERE ID_ACTIVITY = '$id'")
                                ->fetchAll(\PDO::FETCH_ASSOC);

                return view('user.activity.actionsolved.detail', compact('activity', 'fotos', 'username'));
            }



}
