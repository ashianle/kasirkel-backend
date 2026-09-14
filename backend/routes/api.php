<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TbBarangController;
use App\Http\Controllers\Api\TbKategoriController;
use App\Http\Controllers\Api\TbSupplierController;
use App\Http\Controllers\Api\TbSekolahController;
use App\Http\Controllers\Api\TbUserController;
use App\Http\Controllers\Api\TbKelompokKategoriController;
use App\Http\Controllers\Api\TbKelompokPelangganController;
use App\Http\Controllers\Api\TbPenjualanController;
use App\Http\Controllers\Api\TbPembelianController;
use App\Http\Controllers\Api\TbPelangganController;


//auth

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//protected api

Route::middleware('auth:sanctum')->group(function () {

    //auth
    Route::post('/logout', [AuthController::class, 'logout']);


    //barang

    Route::get('/barang', [TbBarangController::class, 'index']);
    Route::post('/barang', [TbBarangController::class, 'store']);
    Route::get('/barang/{id}', [TbBarangController::class, 'show']);
    Route::put('/barang/{id}', [TbBarangController::class, 'update']);
    Route::delete('/barang/{id}', [TbBarangController::class, 'destroy']);


    //penjualan

    Route::get('/penjualan', [TbPenjualanController::class, 'index']);
    Route::post('/penjualan', [TbPenjualanController::class, 'store']);
    Route::get('/penjualan/{id}', [TbPenjualanController::class, 'show']);
    Route::delete('/penjualan/{id}', [TbPenjualanController::class, 'destroy']);


    //pembelian

    Route::get('/pembelian', [TbPembelianController::class, 'index']);
    Route::post('/pembelian', [TbPembelianController::class, 'store']);
    Route::get('/pembelian/{id}', [TbPembelianController::class, 'show']);
    Route::delete('/pembelian/{id}', [TbPembelianController::class, 'destroy']);


    //pelanggan

    Route::get('/pelanggan', [TbPelangganController::class, 'index']);
    Route::post('/pelanggan', [TbPelangganController::class, 'store']);
    Route::get('/pelanggan/{id}', [TbPelangganController::class, 'show']);
    Route::put('/pelanggan/{id}', [TbPelangganController::class, 'update']);
    Route::delete('/pelanggan/{id}', [TbPelangganController::class, 'destroy']);


    //kelompok pelanggan

    Route::get('/kelompok-pelanggan', [TbKelompokPelangganController::class, 'index']);
    Route::post('/kelompok-pelanggan', [TbKelompokPelangganController::class, 'store']);
    Route::get('/kelompok-pelanggan/{id}', [TbKelompokPelangganController::class, 'show']);
    Route::put('/kelompok-pelanggan/{id}', [TbKelompokPelangganController::class, 'update']);
    Route::delete('/kelompok-pelanggan/{id}', [TbKelompokPelangganController::class, 'destroy']);


    //supplier

    Route::get('/supplier', [TbSupplierController::class, 'index']);
    Route::post('/supplier', [TbSupplierController::class, 'store']);
    Route::get('/supplier/{id}', [TbSupplierController::class, 'show']);
    Route::put('/supplier/{id}', [TbSupplierController::class, 'update']);
    Route::delete('/supplier/{id}', [TbSupplierController::class, 'destroy']);


    //kelompok kategori

    Route::get('/kelompok-kategori', [TbKelompokKategoriController::class, 'index']);
    Route::post('/kelompok-kategori', [TbKelompokKategoriController::class, 'store']);
    Route::get('/kelompok-kategori/{id}', [TbKelompokKategoriController::class, 'show']);
    Route::put('/kelompok-kategori/{id}', [TbKelompokKategoriController::class, 'update']);
    Route::delete('/kelompok-kategori/{id}', [TbKelompokKategoriController::class, 'destroy']);


    //kategori

    Route::get('/kategori', [TbKategoriController::class, 'index']);
    Route::post('/kategori', [TbKategoriController::class, 'store']);
    Route::get('/kategori/{id}', [TbKategoriController::class, 'show']);
    Route::put('/kategori/{id}', [TbKategoriController::class, 'update']);
    Route::delete('/kategori/{id}', [TbKategoriController::class, 'destroy']);


    //users

    Route::get('/users', [TbUserController::class, 'index']);
    Route::post('/users', [TbUserController::class, 'store']);
    Route::get('/users/{id}', [TbUserController::class, 'show']);
    Route::put('/users/{id}', [TbUserController::class, 'update']);
    Route::delete('/users/{id}', [TbUserController::class, 'destroy']);


    //sekolah

    Route::get('/sekolah', [TbSekolahController::class, 'index']);
    Route::post('/sekolah', [TbSekolahController::class, 'store']);
    Route::get('/sekolah/{id}', [TbSekolahController::class, 'show']);
    Route::put('/sekolah/{id}', [TbSekolahController::class, 'update']);
    Route::delete('/sekolah/{id}', [TbSekolahController::class, 'destroy']);

});