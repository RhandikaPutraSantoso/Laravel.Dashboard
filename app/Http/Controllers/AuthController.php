<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\HanaConnection;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    private function cleanInput($input)
    {
        return trim(preg_replace('/[^a-zA-Z0-9@.\-_\s]/', '', $input));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:100',
        ]);

        $username = strtoupper($this->cleanInput($request->username));
        $inputPassword = $request->password;

        try {
            $koneksi = HanaConnection::getConnection();

            // 🔹 Cek ADMIN_SAP
            $sqlAdmin = "SELECT * FROM SBO_SUPPORT_SAPHANA.ADMIN_SAP WHERE UPPER(USERNAME) = '$username' LIMIT 1";
            $admin = $koneksi->query($sqlAdmin)->fetch(\PDO::FETCH_ASSOC);

            if ($admin && Hash::check($inputPassword, $admin['PASSWORD'])) {
                session([
                    'admin_sap'   => $admin['USERNAME'],
                    'admin_id'    => $admin['ID_ADMIN'],
                    'email'       => null,
                    'company'     => 'ADMIN',
                    'login_type'  => 'admin',
                ]);
                session()->regenerate();
                return view('admin.loadingAdmin');
            }

            // 🔹 Cek USER_SAP
            $sqlUser = "SELECT * FROM SBO_SUPPORT_SAPHANA.USER_SAP WHERE UPPER(USERNAME) = '$username' LIMIT 1";
            $user = $koneksi->query($sqlUser)->fetch(\PDO::FETCH_ASSOC);

            if ($user && Hash::check($inputPassword, $user['PASSWORD'])) {
                session([
                    'user_sap'    => $user['USERNAME'],
                    'user_id'     => $user['EMP_ID'],
                    'email'       => $user['EMAIL'],
                    'company'     => $user['NM_COMPANY'],
                    'login_type'  => 'user',
                ]);
                session()->regenerate();
                return view('user.loadingUser');
            }

            // ❌ Jika tidak ditemukan atau password salah
            return back()->withErrors(['invalid' => 'Username atau password salah.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}
