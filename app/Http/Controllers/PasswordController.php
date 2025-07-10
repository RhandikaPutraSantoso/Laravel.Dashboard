<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Helpers\HanaConnection;

class PasswordController extends Controller
{
    // Form ganti password
    public function edit()
    {
        return view('auth.change-password');
    }

    // Proses update password
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $loginType = session('login_type');
        $userId    = $loginType === 'admin' ? session('admin_id') : session('user_id');

        if (!$loginType || !$userId) {
            return redirect('/login')->withErrors(['session' => 'Session tidak valid atau sudah berakhir.']);
        }

        $table   = $loginType === 'admin' ? 'SBO_SUPPORT_SAPHANA.ADMIN_SAP' : 'SBO_SUPPORT_SAPHANA.USER_SAP';
        $idField = $loginType === 'admin' ? 'ID_ADMIN' : 'EMP_ID';

        try {
            $koneksi = HanaConnection::getConnection();

            // Ambil password dari database
            $sql = "SELECT PASSWORD FROM $table WHERE $idField = '$userId'";
            $stmt = $koneksi->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return back()->withErrors(['current_password' => 'User tidak ditemukan.']);
            }

            $dbPassword = $result['PASSWORD'];

            // Cek password lama (hash)
            if (!Hash::check($request->current_password, $dbPassword)) {
                return back()->withErrors(['current_password' => 'Password lama salah.']);
            }

            // Hash password baru
            $hashedPassword = Hash::make($request->password);

            // Update password ke DB
            $updateSql = "UPDATE $table SET PASSWORD = :password WHERE $idField = :id";
            $stmt = $koneksi->prepare($updateSql);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            return back()->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
