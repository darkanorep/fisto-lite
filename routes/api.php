<?php

use App\Models\Form;
use App\Models\Document;
use Spatie\Backtrace\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RequestorController;
use App\Http\Controllers\OrganizationalDepartmentController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    //Admin
    Route::group(['prefix' => 'admin', ['middleware' => 'admin']], function () {

        //User
        Route::post('register', [AuthController::class, 'register']);
        Route::get('users', [AuthController::class, 'index']);

        //Form
        Route::resource('form', FormController::class);
        Route::patch('form/change-status/{id}', [FormController::class, 'change_status']);
        Route::post('form/import', [FormController::class, 'import']);

        //Organizational Department
        Route::resource('organizational-department', OrganizationalDepartmentController::class);
        Route::post('organizational-department/import', [OrganizationalDepartmentController::class, 'import']);

        //Document
        Route::resource('document', DocumentController::class);
        Route::patch('document/change-status/{id}', [DocumentController::class, 'change_status']);

        //Category
        Route::resource('category', CategoryController::class);
        Route::patch('category/change-status/{id}', [CategoryController::class, 'change_status']);

        //Company
        Route::resource('company', CompanyController::class);
        Route::patch('company/change-status/{id}', [CompanyController::class, 'change_status']);

        //Department
        Route::resource('department', DepartmentController::class);
        Route::patch('department/change-status/{id}', [DepartmentController::class, 'change_status']);
    });

    //Requestor
    Route::resource('make-request', RequestorController::class);

    //AP
    Route::put('ap-received', [APController::class, 'received']);
    Route::put('tag-issuance/{id}', [APController::class, 'issuing_tag_no']);
    Route::post('voucher-creation', [APController::class, 'voucher_creation']);

    //Finance
    Route::put('finance-approval', [FinanceController::class, 'voucher_approve']);

    Route::post('logout', [AuthController::class, 'logout']);
});
