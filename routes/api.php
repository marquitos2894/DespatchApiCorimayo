<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DespatchController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\RegisterController;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

//api
Route::post('register',[RegisterController::class,'store']);

Route::post('login', [AuthController::class,'login']);
Route::post('logout', [AuthController::class,'logout']);
Route::post('refresh', [AuthController::class,'refresh']);
Route::post('me', [AuthController::class,'me']);

Route::apiResource('companies',CompanyController::class)->middleware('auth:api');

//invoices
Route::post('invoices/send',[InvoiceController::class,'send'])->middleware('auth:api');
Route::post('invoices/xml',[InvoiceController::class,'xml'])->middleware('auth:api');
Route::post('invoices/pdf',[InvoiceController::class,'pdf'])->middleware('auth:api');

//Notes
Route::post('note/send',[NoteController::class , 'send'])->middleware('auth:api');
Route::post('note/xml',[NoteController::class,'xml'])->middleware('auth:api');
Route::post('note/pdf',[NoteController::class,'pdf'])->middleware('auth:api');

//Despatches
Route::post('despatches/send',[DespatchController::class , 'send'])->middleware('auth:api');
Route::post('despatches/xml',[DespatchController::class,'xml'])->middleware('auth:api');
Route::post('despatches/pdf',[DespatchController::class,'pdf'])->middleware('auth:api');
Route::post('despatches/store',[DespatchController::class,'store'])->middleware('auth:api');
Route::post('despatches/viewlist',[DespatchController::class,'index'])->middleware('auth:api');
Route::post('despatches/viewguia',[DespatchController::class,'view'])->middleware('auth:api');
Route::post('despatches/search',[DespatchController::class,'search'])->middleware('auth:api');
Route::post('despatches/detailsdespatch',[DespatchController::class,'detailsdespatch'])->middleware('auth:api');
//detailsdespatch
//Machine
Route::post('machine/viewlist',[MachineController::class,'index'])->middleware('auth:api');
