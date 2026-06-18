<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('index');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
});

Route::get('image/{width}/{height}/{any}', [HomeController::class, 'getImage'])->where('any', '.*');

Route::get('test', function () {
    // $time_start = microtime(true);

    // $data=Member::query()
    // ->with([
    //         'donations',
    //         'member_gallery',
    //         'Zone:name',
    //         'Kuldevi:name',
    //         'nativePlace:native',
    //         'spouseRecursive',
    //         'parentRecursive'
    //     ])
    //     // ->where('id', 'ae17964f-a9c9-404b-8bf0-ebfcaa7dcfbc')
    //     ->whereNull('head_of_the_family_id')
    //     ->get();

    // $time_end = microtime(true);
    // // echo '<b>Total Get Execution Time:</b> ' . (($time_end - $time_start)) . 'Milliseconds </br>';
    // return response(['time'=> ($time_end - $time_start),'data'=> $data]);

    // LazyCollection::make(function () {
    //     $handle = Member::get();

    //     while (($line = count($handle)) !== false) {
    //         yield $line;
    //     }
    // });

    // dd(Member::cursor());
    // $time_start = microtime(true);
    // Member::get();
    // $time_end = microtime(true);
    // echo '<b>Total Get Execution Time:</b> ' . (($time_end - $time_start)) . 'Milliseconds </br>';

    // $time_start = microtime(true);
    // Member::cursor();
    // $time_end = microtime(true);
    // echo '<b>Total Cursor Execution Time:</b> ' . (($time_end - $time_start)) . 'Milliseconds </br>';
});
