<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AiAssistantController;
use App\Http\Controllers\Admin\AiKnowledgeController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GtinLookupController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\InventoryMovementController as AdminInventoryMovementController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PurchaseController as AdminPurchaseController;
use App\Http\Controllers\Admin\NfeImportController;
use App\Http\Controllers\Admin\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Mapa inicial de rotas do site (farmacia).
| Rotas publicas (site) e rotas administrativas.
|
*/

// Home: em ambiente interno, leva ao login (ou painel se já estiver autenticado).
Route::get('/', function () {
    if (! User::query()->exists()) {
        return redirect()->route('admin.register');
    }

    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
})->name('home');

// Para compatibilidade com o middleware "auth" do Laravel.
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

// Admin (sistema interno)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'show'])->middleware('guest')->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('guest')->name('login.submit');
    Route::get('/registro', [AdminRegisterController::class, 'show'])->middleware('guest')->name('register');
    Route::post('/registro', [AdminRegisterController::class, 'store'])->middleware('guest')->name('register.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/relatorios', [AdminReportController::class, 'index'])
            ->middleware('role:admin,gerente')
            ->name('reports.index');

        Route::get('/auditoria', [AuditLogController::class, 'index'])
            ->middleware('role:admin,gerente')
            ->name('audit.index');
        Route::get('/auditoria/{log}', [AuditLogController::class, 'show'])
            ->middleware('role:admin,gerente')
            ->name('audit.show');

        Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner');

        Route::get('/api/produtos', [AdminProductController::class, 'apiSearch'])
            ->middleware('throttle:admin-api')
            ->name('api.products.search');
        Route::get('/api/gtin', [GtinLookupController::class, 'lookup'])
            ->middleware('throttle:admin-api')
            ->name('api.gtin.lookup');

        Route::get('/assistente', [AiAssistantController::class, 'index'])
            ->middleware('role:admin,gerente')
            ->name('assistant');
        Route::post('/assistente/mensagem', [AiAssistantController::class, 'send'])
            ->middleware('role:admin,gerente')
            ->middleware('throttle:admin-ai')
            ->name('assistant.send');
        Route::post('/assistente/reset', [AiAssistantController::class, 'reset'])
            ->middleware('role:admin,gerente')
            ->middleware('throttle:admin-ai')
            ->name('assistant.reset');

        Route::resource('conhecimentos', AiKnowledgeController::class)
            ->parameters(['conhecimentos' => 'entry'])
            ->names('knowledge')
            ->middleware('role:admin,gerente');

        Route::resource('produtos', AdminProductController::class)
            ->parameters(['produtos' => 'product'])
            ->names('products')
            ->middleware('role:admin,gerente,atendente');

        Route::resource('categorias', AdminCategoryController::class)
            ->parameters(['categorias' => 'category'])
            ->names('categories')
            ->middleware('role:admin,gerente');

        Route::resource('ofertas', AdminOfferController::class)
            ->parameters(['ofertas' => 'offer'])
            ->names('offers')
            ->middleware('role:admin,gerente');

        Route::resource('lojas', AdminStoreController::class)
            ->parameters(['lojas' => 'store'])
            ->names('stores')
            ->middleware('role:admin,gerente');

        Route::resource('usuarios', AdminUserController::class)
            ->parameters(['usuarios' => 'user'])
            ->names('users')
            ->except(['show'])
            ->middleware('role:admin');

        Route::resource('fornecedores', AdminSupplierController::class)
            ->parameters(['fornecedores' => 'supplier'])
            ->names('suppliers')
            ->middleware('role:admin,gerente');

        Route::get('/compras/importar-xml', [NfeImportController::class, 'show'])
            ->middleware('role:admin,gerente')
            ->name('purchases.import_xml');
        Route::post('/compras/importar-xml', [NfeImportController::class, 'store'])
            ->middleware('role:admin,gerente')
            ->name('purchases.import_xml.store');
        Route::get('/compras/{purchase}/xml', [NfeImportController::class, 'download'])
            ->middleware('role:admin,gerente')
            ->where(['purchase' => '[0-9]+'])
            ->name('purchases.download_xml');

        Route::resource('compras', AdminPurchaseController::class)
            ->parameters(['compras' => 'purchase'])
            ->names('purchases')
            ->only(['index', 'create', 'store', 'show'])
            ->where(['purchase' => '[0-9]+'])
            ->middleware('role:admin,gerente');

        Route::resource('vendas', AdminSaleController::class)
            ->parameters(['vendas' => 'sale'])
            ->names('sales')
            ->only(['index', 'create', 'store', 'show'])
            ->middleware('role:admin,gerente,atendente,caixa');

        Route::resource('transferencias', TransferController::class)
            ->parameters(['transferencias' => 'transfer'])
            ->names('transfers')
            ->only(['index', 'create', 'store', 'show'])
            ->middleware('role:admin,gerente');

        Route::get('/estoque', [AdminInventoryController::class, 'index'])
            ->middleware('role:admin,gerente,atendente')
            ->name('inventory.index');
        Route::get('/estoque/reposicao', [AdminInventoryController::class, 'replenishment'])
            ->middleware('role:admin,gerente,atendente')
            ->name('inventory.replenishment');
        Route::get('/estoque/movimentacoes', [AdminInventoryMovementController::class, 'index'])
            ->middleware('role:admin,gerente,atendente')
            ->name('inventory.movements.index');
        Route::get('/estoque/movimentar', [AdminInventoryMovementController::class, 'create'])
            ->middleware('role:admin,gerente')
            ->name('inventory.movements.create');
        Route::post('/estoque/movimentar', [AdminInventoryMovementController::class, 'store'])
            ->middleware('role:admin,gerente')
            ->name('inventory.movements.store');
    });
});
