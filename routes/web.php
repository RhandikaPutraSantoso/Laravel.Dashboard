<?php
// routes/web.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\userController;


Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Loading → langsung redirect ke dashboard
Route::get('/admin/dashboardAdmin', [DashboardController::class, 'index'])->middleware('checklogin')->name('admin.dashboardAdmin');

// Grouped protected routes
Route::middleware('checklogin')->group(callback: function () {
    Route::get('/admin/activity/report', [DashboardController::class, 'activityReport'])->name('admin.activity.report');
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
    Route::get('/admin/pengaturan/difficult', [DashboardController::class, 'difficult'])->name('admin.pengaturan.difficult');
    Route::get('/admin/pengaturan/status', [DashboardController::class, 'status'])->name('admin.pengaturan.status');

});

// Loading → langsung redirect ke Dashboard User
Route::get('user/dashboardUser', [ userController::class, 'index'])->middleware('checklogin')->name('user.dashboardUser');

// Grouped protected routes
Route::middleware('checklogin')->group(function () {
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