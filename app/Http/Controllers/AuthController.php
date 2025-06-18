<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\HanaConnection;

class AuthController extends Controller
{
    // Menampilkan halaman login (form input user & password)
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Fungsi utilitas untuk membersihkan input dari karakter tidak aman
    private function cleanInput($input)
    {
        // Hanya izinkan huruf, angka, @, titik, dash, dan underscore
        return preg_replace('/[^a-zA-Z0-9@.\-_]/', '', $input);
    }

    // Proses login (admin & user)
    public function login(Request $request)
    {
        $type = $request->input('type'); // admin atau user

        // Validasi input umum (berlaku untuk semua tipe login)
        $request->validate([
            'username' => 'required|alpha_num|max:50', // Hanya huruf dan angka
            'password' => 'required|string|min:6|max:100|regex:/^[\w@#$%^&*+=!?-]+$/', // Hanya karakter yang diizinkan
        ]);

        // Validasi tambahan jika tipe login adalah "user"
        if ($type === 'user') {
            $request->validate([
                'email'   => 'required|email|max:100', // Format email valid
                'company' => 'required|string|max:100|regex:/^[\w\s\-.]+$/', // Nama perusahaan aman
            ]);
        }

        // Bersihkan input untuk mencegah SQL Injection
        $username = $this->cleanInput($request->username);
        $password = $this->cleanInput($request->password);
        $email    = $type === 'user' ? $this->cleanInput($request->email) : null;
        $company  = $type === 'user' ? $this->cleanInput($request->company) : null;

        try {
            // Koneksi ke database SAP HANA
            $koneksi = HanaConnection::getConnection();

            // Buat query SQL berdasarkan tipe login
            $sql = $type === 'admin'
                ? "SELECT * FROM SBO_SUPPORT_SAPHANA.ADMIN_SAP WHERE USERNAME = '$username' AND PASSWORD = '$password' LIMIT 1"
                : "SELECT * FROM SBO_SUPPORT_SAPHANA.USER_SAP WHERE USERNAME = '$username' AND PASSWORD = '$password' AND EMAIL = '$email' AND NM_COMPANY = '$company' LIMIT 1";

            // Jalankan query
            $stmt = $koneksi->query($sql);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC); // Ambil hasil sebagai array

            // Jika user ditemukan
            if ($user) {
                if ($type === 'admin') {
                    // Simpan session untuk admin
                    session([
                        'admin_sap' => $user['USERNAME'],
                        'login_type' => 'admin'
                    ]);
                    // Arahkan ke halaman loading khusus admin
                    return view('admin.loadingAdmin');
                } else {
                    // Simpan session untuk user biasa
                    session([
                        'user_sap' => $user['USERNAME'],
                        'email'    => $user['EMAIL'],
                        'company'  => $user['NM_COMPANY'],
                        'login_type' => 'user'
                    ]);
                    // Arahkan ke halaman loading khusus user
                    return view('user.loadingUser');
                }
            } else {
                // Jika login gagal (tidak ditemukan di database)
                return back()->withErrors(['invalid' => 'Username, password, atau data lainnya salah.']);
            }
        } catch (\Exception $e) {
            // Jika ada error saat koneksi/query database
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // Fungsi logout untuk menghapus session
    public function logout()
    {
        session()->flush(); // Hapus semua data session
        return redirect()->route('login'); // Kembali ke halaman login
    }
}
