<?php

use Spatie\Backtrace\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APController;
use App\Http\Controllers\APTaggingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RequestorController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\OrganizationalDepartmentController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UrgencyTypesController;

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
        Route::post('department/import', [DepartmentController::class, 'import']);

        //Location
        Route::resource('location', LocationController::class);
        Route::patch('location/change-status/{id}', [LocationController::class, 'change_status']);

        //Reference
        Route::resource('reference', ReferenceController::class);
        Route::patch('reference/change-status/{id}', [ReferenceController::class, 'change_status']);

        //Urgency Type
        Route::resource('urgency-type', UrgencyTypesController::class);
        Route::patch('urgency-type/change-status/{id}', [UrgencyTypesController::class, 'change_status']);

        //Supplier
        Route::resource('supplier', SupplierController::class);
        Route::patch('supplier/change-status/{id}', [SupplierController::class, 'change_status']);
    });

    //AP
    Route::post('receive/{id}', [APTaggingController::class, 'received']);
    Route::post('return/{id}', [APTaggingController::class, 'returned']);
    Route::put('update-transaction/{id}', [APTaggingController::class, 'updateTransaction']);

    //Transaction
    Route::resource('transaction', TransactionController::class);
    Route::patch('transaction-void/{id}', [TransactionController::class, 'void']);

    //Finance
    Route::put('finance-approval', [FinanceController::class, 'voucher_approve']);

    Route::post('logout', [AuthController::class, 'logout']);
});
