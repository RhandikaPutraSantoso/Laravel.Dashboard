<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\HanaConnection;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    // Tampilkan form lupa password
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // Cek user & email
    // public function checkUser(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required|string|max:50',
    //         'email'    => 'required|email|max:100',
    //     ]);

    //     $username = strtoupper($request->username);
    //     $email = $request->email;
    //     $koneksi = HanaConnection::getConnection();

    //     // Cek ke USER_SAP
    //     $sql = "SELECT * FROM SBO_SUPPORT_SAPHANA.USER_SAP WHERE UPPER(USERNAME) = '$username' AND EMAIL = '$email'";
    //     $user = $koneksi->query($sql)->fetch(\PDO::FETCH_ASSOC);

    //     if ($user) {
    //         return view('auth.reset-password', [
    //             'username' => $user['USERNAME'],
    //             'id'       => $user['EMP_ID'],
    //             'login_type' => 'user',
    //         ]);
    //     }

    //     // Cek ke ADMIN_SAP jika perlu
    //     $sqlAdmin = "SELECT * FROM SBO_SUPPORT_SAPHANA.ADMIN_SAP WHERE UPPER(USERNAME) = '$username'";
    //     $admin = $koneksi->query($sqlAdmin)->fetch(\PDO::FETCH_ASSOC);

    //     if ($admin && empty($admin['EMAIL'])) {
    //         // Bisa tambahkan validasi tambahan di sini
    //         return view('auth.reset-password', [
    //             'username' => $admin['USERNAME'],
    //             'id'       => $admin['ID_ADMIN'],
    //             'login_type' => 'admin',
    //         ]);
    //     }

    //     return back()->withErrors(['invalid' => 'Data tidak ditemukan.']);
    // }

    // // Reset password
    // public function resetPassword(Request $request)
    // {
    //     $request->validate([
    //         'id'         => 'required',
    //         'login_type' => 'required|in:user,admin',
    //         'password'   => 'required|string|min:6|confirmed',
    //     ]);

    //     $id         = $request->id;
    //     $login_type = $request->login_type;
    //     $newPass    = Hash::make($request->password);

    //     $table = $login_type === 'admin' ? 'SBO_SUPPORT_SAPHANA.ADMIN_SAP' : 'SBO_SUPPORT_SAPHANA.USER_SAP';
    //     $field = $login_type === 'admin' ? 'ID_ADMIN' : 'EMP_ID';

    //     $koneksi = HanaConnection::getConnection();
    //     $stmt = $koneksi->prepare("UPDATE $table SET PASSWORD = :password WHERE $field = :id");
    //     $stmt->bindParam(':password', $newPass);
    //     $stmt->bindParam(':id', $id);
    //     $stmt->execute();
        
    //     return redirect('/')->with('success', 'Password berhasil direset. Silakan login.');
    // }




public function checkUser(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:50',
        'email'    => 'required|email|max:100',
    ]);

    $username = strtoupper($request->username);
    $email = $request->email;
    $koneksi = HanaConnection::getConnection();

    // Cek USER_SAP
    $sql = "SELECT * FROM SBO_SUPPORT_SAPHANA.USER_SAP WHERE UPPER(USERNAME) = '$username' AND EMAIL = '$email'";
    $user = $koneksi->query($sql)->fetch(\PDO::FETCH_ASSOC);

    if ($user) {
        $newPasswordPlain = Str::random(8); // contoh: gHt82dKp
        $newPasswordHash  = Hash::make($newPasswordPlain);

        // Update password baru ke DB
        $stmt = $koneksi->prepare("UPDATE SBO_SUPPORT_SAPHANA.USER_SAP SET PASSWORD = ? WHERE EMP_ID = ?");
        $stmt->execute([$newPasswordHash, $user['EMP_ID']]);

        // Kirim email
        Mail::send('emails.password-reset', [
    'username' => $user['USERNAME'],
    'password' => $newPasswordPlain
], function ($message) use ($user) {
    $message->to($user['EMAIL'])
            ->subject('Reset Password SAP Support');
});

        return back()->with('success', 'Password baru telah dikirim ke email Anda.');
    }

    return back()->withErrors(['invalid' => 'Data tidak ditemukan.']);
}


}
