<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\HanaConnection;

class AuthController extends Controller
{
    // Menampilkan form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Fungsi sanitasi input
    private function cleanInput($input)
    {
        return preg_replace('/[^a-zA-Z0-9@.\-_]/', '', $input);
    }

    // Proses login otomatis (admin & user)
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:100',
        ]);

        $username = $this->cleanInput($request->username);
        $password = $this->cleanInput($request->password);

        try {
            $koneksi = HanaConnection::getConnection();

            // Cek ADMIN_SAP
            $sqlAdmin = "SELECT * FROM SBO_SUPPORT_SAPHANA.ADMIN_SAP WHERE USERNAME = '$username' AND PASSWORD = '$password' LIMIT 1";
            $stmtAdmin = $koneksi->query($sqlAdmin);
            $admin = $stmtAdmin->fetch(\PDO::FETCH_ASSOC);

            if ($admin) {
                session([
                    'admin_sap'   => $admin['USERNAME'],
                    'admin_id'    => $admin['ID_ADMIN'],
                    'email'      => null,
                    'company'    => 'ADMIN',
                    'login_type' => 'admin',
                ]);
                return view('admin.loadingAdmin');
            }

            // Cek USER_SAP
            $sqlUser = "SELECT * FROM SBO_SUPPORT_SAPHANA.USER_SAP WHERE USERNAME = '$username' AND PASSWORD = '$password' LIMIT 1";
            $stmtUser = $koneksi->query($sqlUser);
            $user = $stmtUser->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                session([
                    'user_sap'   => $user['USERNAME'],
                    'user_id'    => $user['EMP_ID'],
                    'email'      => $user['EMAIL'],
                    'company'    => $user['NM_COMPANY'],
                    'login_type' => 'user',
                ]);
                return view('user.loadingUser');
            }

            // Gagal login
            return back()->withErrors(['invalid' => 'Username atau password salah.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // Logout
    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}
