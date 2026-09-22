<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Livewire\Admin;
use App\Livewire\Public;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik (PWA) -- CLAUDE.md §6
|--------------------------------------------------------------------------
*/
Route::view('/', 'public.splash')->name('splash');
Route::get('/mulai', Public\Onboarding::class)->name('onboarding');

Route::middleware('guest')->group(function () {
    Route::get('/daftar', function () {
        return view('public.auth.register', ['regencies' => \App\Models\Regency::orderBy('name')->get()]);
    })->name('register');
    Route::view('/masuk', 'public.auth.login')->name('login');
    Route::view('/lupa-kata-sandi', 'public.auth.forgot-password')->name('password.request');
    Route::get('/reset-kata-sandi/{token}', function (string $token) {
        return view('public.auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::get('/menu', Public\Menu::class)->name('menu');
Route::get('/peta', Public\PetaRisiko::class)->name('peta');
Route::get('/wilayah/{district:slug}', Public\DetailWilayah::class)->name('wilayah.show');
Route::get('/aksi', Public\AksiPencegahan::class)->name('aksi');
Route::view('/aksi/panduan', 'public.aksi-panduan')->name('aksi.panduan');
Route::get('/peringatan', Public\Peringatan::class)->name('peringatan');
Route::get('/news', Public\News\Index::class)->name('news.index');
Route::get('/news/{post:slug}', Public\News\Show::class)->name('news.show');
Route::get('/program', Public\Program\Index::class)->name('program.index');
Route::get('/program/{program:slug}', Public\Program\Show::class)->name('program.show');

Route::get('/tentang', function () {
    return view('public.tentang', ['model' => \App\Models\RiskModel::active()]);
})->name('tentang');
Route::view('/privasi', 'public.privasi')->name('privasi');
Route::view('/syarat', 'public.syarat')->name('syarat');
Route::view('/offline', 'public.offline')->name('offline');

Route::middleware('auth')->group(function () {
    Route::get('/lapor', Public\Lapor::class)->name('lapor');
    Route::get('/lapor/saya', Public\LaporSaya::class)->name('lapor.saya');
    Route::get('/notifikasi', Public\Notifikasi::class)->name('notifikasi');
    Route::get('/profil', Public\Profil::class)->name('profil');

    Route::post('/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel admin -- CLAUDE.md §7
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    Route::middleware('can:verify-reports')->group(function () {
        Route::get('/laporan', Admin\Reports\Index::class)->name('reports.index');
        Route::get('/laporan/{report}', Admin\Reports\Show::class)->name('reports.show');
    });

    Route::middleware('can:access-admin')->group(function () {
        Route::get('/', Admin\Dashboard::class)->name('dashboard');
        Route::get('/hotspot', Admin\Hotspots\Index::class)->name('hotspots.index');
        Route::get('/wilayah', Admin\Regions\Index::class)->name('regions.index');
        Route::get('/model-risiko', Admin\RiskModel\Index::class)->name('risk-model.index');
        Route::get('/validasi-historis', Admin\Validation\Index::class)->name('validation.index');
        Route::get('/peringatan', Admin\Alerts\Index::class)->name('alerts.index');
        Route::get('/rekomendasi', Admin\Recommendations\Index::class)->name('recommendations.index');
        Route::get('/news', Admin\Posts\Index::class)->name('posts.index');
        Route::get('/program', Admin\Programs\Index::class)->name('programs.index');
        Route::get('/pengguna', Admin\Users\Index::class)->name('users.index');
        Route::get('/kontak-instansi', Admin\AuthorityContacts\Index::class)->name('authority-contacts.index');
        Route::get('/log-aktivitas', Admin\ActivityLogs\Index::class)->name('activity-logs.index');
    });

    Route::middleware('can:access-superadmin')->group(function () {
        Route::get('/pengaturan', Admin\Settings\Index::class)->name('settings.index');
    });
});
