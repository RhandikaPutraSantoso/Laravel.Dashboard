<?php
// routes/web.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\userController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Helpers\HanaConnection;

// Route::get('/reset-password-plaintext', function () {
//     $koneksi = HanaConnection::getConnection();

//     // ✅ Reset USER_SAP dengan USERNAME = 'dika'
//     $newUserPass = 'user123';
//     $sqlUser = "UPDATE SBO_SUPPORT_SAPHANA.USER_SAP SET PASSWORD = '$newUserPass' WHERE USERNAME = 'dika'";
//     $koneksi->exec($sqlUser);

//     // ✅ Reset ADMIN_SAP dengan USERNAME = 'admin'
//     $newAdminPass = 'admin123';
//     $sqlAdmin = "UPDATE SBO_SUPPORT_SAPHANA.ADMIN_SAP SET PASSWORD = '$newAdminPass' WHERE USERNAME = 'admin'";
//     $koneksi->exec($sqlAdmin);

//     return "✅ Password user 'dika' direset ke 'user123'<br>✅ Password admin 'admin' direset ke 'admin123'";
// });


// Route::get('/migrasi-password', function () {
//     $koneksi = HanaConnection::getConnection();

//     $log = [];

//     // 🔹 Migrasi ADMIN_SAP
//     $stmtAdmin = $koneksi->query("SELECT ID_ADMIN, PASSWORD FROM SBO_SUPPORT_SAPHANA.ADMIN_SAP");
//     foreach ($stmtAdmin as $row) {
//         $id = $row['ID_ADMIN'];
//         $password = $row['PASSWORD'];

//         // Jika belum di-hash
//         if (Hash::needsRehash($password)) {
//             $hashed = Hash::make($password);
//             $update = $koneksi->prepare("UPDATE SBO_SUPPORT_SAPHANA.ADMIN_SAP SET PASSWORD = ? WHERE ID_ADMIN = ?");
//             $update->execute([$hashed, $id]);

//             $log[] = "✔ ADMIN $id berhasil dihash.";
//         } else {
//             $log[] = "⏭ ADMIN $id sudah ter-hash.";
//         }
//     }

//     // 🔹 Migrasi USER_SAP
//     $stmtUser = $koneksi->query("SELECT EMP_ID, PASSWORD FROM SBO_SUPPORT_SAPHANA.USER_SAP");
//     foreach ($stmtUser as $row) {
//         $id = $row['EMP_ID'];
//         $password = $row['PASSWORD'];

//         if (Hash::needsRehash($password)) {
//             $hashed = Hash::make($password);
//             $update = $koneksi->prepare("UPDATE SBO_SUPPORT_SAPHANA.USER_SAP SET PASSWORD = ? WHERE EMP_ID = ?");
//             $update->execute([$hashed, $id]);

//             $log[] = "✔ USER $id berhasil dihash.";
//         } else {
//             $log[] = "⏭ USER $id sudah ter-hash.";
//         }
//     }

//     return implode("<br>", $log); // Output ke browser
// });




Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('forgot-password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'checkUser']);
// Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');


Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Loading → langsung redirect ke dashboard
Route::get('/admin/dashboardAdmin', [DashboardController::class, 'index'])->middleware('checklogin')->name('admin.dashboardAdmin');

// Grouped protected routes
Route::middleware('checklogin')->group(callback: function () {
    //log-viewers
    Route::get('log-viewers', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
    Route::get('/get-notifications', [App\Http\Controllers\dashboardController::class, 'fetch']);
    Route::post('/admin/activity/send-email/{id}', [dashboardController::class, 'sendEmail'])->name('admin.activity.sendEmail');
    Route::get('/admin/activity/fetch-log', [DashboardController::class, 'fetchActivityLog']);

Route::get('/change-password', [PasswordController::class, 'edit'])->name('password.edit');
Route::post('/change-password', [PasswordController::class, 'update'])->name('password.update');


    

    Route::get('/admin/activity/report', [DashboardController::class, 'activityReport'])->name('admin.activity.report');
    Route::get('/admin/activity/berita-acara/{id}', [DashboardController::class, 'cetakBeritaAcara'])->name('admin.activity.berita_acara_pdf');

    Route::get('/admin/activity/actionreport/tambah', [DashboardController::class, 'activitytambah'])->name('admin.activity.actionreport.tambah');
    Route::post('/admin/activity/report/store', [DashboardController::class, 'activityStore'])->name('admin.activity.report.store');
    Route::delete('/admin/activity/report/delete/{id}', [DashboardController::class, 'activitydestroy'])->name('admin.activity.report.destroy');
    Route::get('/admin/activity/actionreport/detail/{id}', [DashboardController::class, 'activitydetail'])->name('admin.activity.actionreport.detail');
    Route::get('/admin/activity/report/ubah/{id}', [DashboardController::class, 'activityEdit'])->name('admin.activity.actionreport.ubah');
    Route::post('/admin/activity/report/ubah/{id}', [DashboardController::class, 'activityUpdate'])->name('admin.activity.actionreport.update');

    Route::get('/admin/activity/status', [DashboardController::class, 'activityStatus'])->name('admin.activity.status');
    Route::get('/admin/activity/actionstatus/detail/{id}', [DashboardController::class, 'activityDetailStatus'])->name('admin.activity.actionstatus.detail');
    Route::get('/admin/activity/status/ubah/{id}', [DashboardController::class, 'activityEditStatus'])->name('admin.activity.actionstatus.ubah');
    Route::post('/admin/activity/status/ubah/{id}', [DashboardController::class, 'activityUpdateStatus'])->name('admin.activity.actionstatus.update');

    Route::get('/admin/activity/solved', [DashboardController::class, 'activitySolved'])->name('admin.activity.solved');
    Route::get('/admin/activity/actionsolved/detail/{id}', [DashboardController::class, 'activityDetailSolved'])->name('admin.activity.actionsolved.detail');
    Route::get('/admin/activity/solved/ubah/{id}', [DashboardController::class, 'activityEditSolved'])->name('admin.activity.actionsolved.ubah');
    Route::post('/admin/activity/solved/ubah/{id}', [DashboardController::class, 'activityUpdateSolved'])->name('admin.activity.actionsolved.update');

    Route::get('/admin/pengaturan/email', [DashboardController::class, 'email'])->name('admin.pengaturan.email');
    Route::delete('/admin/pengaturan/email/delete/{id}', [DashboardController::class, 'emailDestroy'])->name('admin.pengaturan.email.destroy');
    Route::get('admin/pengaturan/email/tambah', [DashboardController::class, 'emailTambah'])->name('admin.pengaturan.actionemail.tambah');
    Route::post('/admin/pengaturan/email/store', [DashboardController::class, 'emailStore'])->name('admin.pengaturan.email.store');
    Route::get('/admin/pengaturan/email/ubah/{id}', [DashboardController::class, 'emailEdit'])->name('admin.pengaturan.actionemail.ubah');
    Route::put('/admin/pengaturan/email/ubah/{id}', [DashboardController::class, 'emailUpdate'])->name('admin.pengaturan.actionemail.update');
    




    Route::get('/admin/pengaturan/difficult', [DashboardController::class, 'difficult'])->name('admin.pengaturan.difficult');
    Route::delete('/admin/pengaturan/difficult/delete/{id}', [DashboardController::class, 'difficultDestroy'])->name('admin.pengaturan.difficult.destroy');
    Route::get('admin/pengaturan/difficult/tambah', [DashboardController::class, 'difficultTambah'])->name('admin.pengaturan.actiondifficult.tambah');
    Route::post('/admin/pengaturan/difficult/store', [DashboardController::class, 'difficultStore'])->name('admin.pengaturan.difficult.store');
    Route::get('/admin/pengaturan/difficult/ubah/{id}', [DashboardController::class, 'difficultEdit'])->name('admin.pengaturan.actiondifficult.ubah');
    Route::put('/admin/pengaturan/difficult/ubah/{id}', [DashboardController::class, 'difficultUpdate'])->name('admin.pengaturan.actiondifficult.update');



    Route::get('/admin/pengaturan/status', [DashboardController::class, 'status'])->name('admin.pengaturan.status');
    Route::delete('/admin/pengaturan/status/delete/{id}', [DashboardController::class, 'statusDestroy'])->name('admin.pengaturan.status.destroy');
    Route::get('admin/pengaturan/status/tambah', [DashboardController::class, 'statusTambah'])->name('admin.pengaturan.actionstatus.tambah');
    Route::post('/admin/pengaturan/status/store', [DashboardController::class, 'statusStore'])->name('admin.pengaturan.status.store');
    Route::get('/admin/pengaturan/status/ubah/{id}', [DashboardController::class, 'statusEdit'])->name('admin.pengaturan.actionstatus.ubah');
    Route::put('/admin/pengaturan/status/ubah/{id}', [DashboardController::class, 'statusUpdate'])->name('admin.pengaturan.actionstatus.update');

    Route::get('/admin/pengaturan/company', [DashboardController::class, 'company'])->name('admin.pengaturan.company');
    Route::delete('/admin/pengaturan/company/delete/{id}', [DashboardController::class, 'companyDestroy'])->name('admin.pengaturan.company.destroy');
    Route::get('admin/pengaturan/company/tambah', [DashboardController::class, 'companyTambah'])->name('admin.pengaturan.actioncompany.tambah');
    Route::post('/admin/pengaturan/company/store', [DashboardController::class, 'companyStore'])->name('admin.pengaturan.company.store');
    Route::get('/admin/pengaturan/company/ubah/{id}', [DashboardController::class, 'companyEdit'])->name('admin.pengaturan.actioncompany.ubah');
    Route::put('/admin/pengaturan/company/ubah/{id}', [DashboardController::class, 'companyUpdate'])->name('admin.pengaturan.actioncompany.update');

});

// Loading → langsung redirect ke Dashboard User
Route::get('user/dashboardUser', [ userController::class, 'index'])->middleware('checklogin')->name('user.dashboardUser');

// Grouped protected routes
Route::middleware('checklogin')->group(function () {

    Route::get('/admin/activity/timeline', [userController::class, 'getRecentActivityTimeline']);

    Route::get('/change-password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::post('/change-password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/user/profile/edit', [userController::class, 'profileEdit'])->name('user.profile.edit');
    Route::post('/user/profile/update', [userController::class, 'profileUpdate'])->name('user.profile.update');


    Route::get('/user/activity/report', [userController::class, 'activityReport'])->name('user.activity.report');
    Route::get('/user/activity/actionreport/tambah', [userController::class, 'activitytambah'])->name('user.activity.actionreport.tambah');
    Route::post('/user/activity/report/store', [userController::class, 'activityStore'])->name('user.activity.report.store');
    Route::delete('/user/activity/report/delete/{id}', [userController::class, 'activitydestroy'])->name('user.activity.report.destroy');
    Route::get('/user/activity/actionreport/detail/{id}', [userController::class, 'activitydetail'])->name('user.activity.actionreport.detail');
    Route::get('/user/activity/report/ubah/{id}', [userController::class, 'activityEdit'])->name('user.activity.actionreport.ubah');
    Route::post('/user/activity/report/ubah/{id}', [userController::class, 'activityUpdate'])->name('user.activity.actionreport.update');



    Route::get('/user/activity/status', [userController::class, 'activityStatus'])->name('user.activity.status');
    Route::get('/user/activity/actionstatus/detail/{id}', [userController::class, 'activityDetailStatus'])->name('user.activity.actionstatus.detail');
    Route::get('/user/activity/status/ubah/{id}', [userController::class, 'activityEditStatus'])->name('user.activity.actionstatus.ubah');
    Route::post('/user/activity/status/ubah/{id}', [userController::class, 'activityUpdateStatus'])->name('user.activity.actionstatus.update');

    Route::get('/user/activity/solved', [userController::class, 'activitySolved'])->name('user.activity.solved');
    Route::get('/user/activity/actionsolved/detail/{id}', [userController::class, 'activityDetailSolved'])->name('user.activity.actionsolved.detail');
    Route::get('/user/activity/solved/ubah/{id}', [userController::class, 'activityEditSolved'])->name('user.activity.actionsolved.ubah');
    Route::post('/user/activity/solved/ubah/{id}', [userController::class, 'activityUpdateSolved'])->name('user.activity.actionsolved.update');
    
    
});