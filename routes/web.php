<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\MaintenanceRecordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\ScheduleReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\ItemImportController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PvMapController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // AI Chatbot
    Route::get('/chat/messages', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('/chat/clear', [ChatController::class, 'clearHistory'])->name('chat.clear');
    // Import Items
    Route::post('items/import', [ItemImportController::class, 'import'])->name('items.import');
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assets
    Route::get('assets/{asset}/work-orders', [AssetController::class, 'workOrders'])->name('assets.work-orders');
    Route::post('assets/swap-position', [AssetController::class, 'swapPosition'])->name('assets.swap-position');
    Route::post('assets/update-position', [AssetController::class, 'updatePosition'])->name('assets.update-position');
    Route::get('assets/by-location', [AssetController::class, 'byLocation'])->name('assets.by-location');
    Route::post('assets/quick-save-pv', [AssetController::class, 'quickSavePv'])->name('assets.quick-save-pv');
    Route::delete('assets/{asset}/pv', [AssetController::class, 'destroyPv'])->name('assets.destroy-pv');
    Route::resource('assets', AssetController::class);

    // Label & QR Code Printing (Thermal Rolls & A4 Sheets)
    Route::get('labels/print', [\App\Http\Controllers\LabelPrintController::class, 'print'])->name('labels.print');

    Route::get('spare-parts/export', [SparePartController::class, 'exportCsv'])->name('spare-parts.export');
    Route::resource('spare-parts', SparePartController::class);
    Route::post('spare-parts/{sparePart}/adjust-stock', [SparePartController::class, 'adjustStock'])->name('spare-parts.adjust-stock');

    Route::get('tools/export', [ToolController::class, 'exportCsv'])->name('tools.export');
    Route::resource('tools', ToolController::class);

    Route::get('consumables/export', [ConsumableController::class, 'exportCsv'])->name('consumables.export');
    Route::resource('consumables', ConsumableController::class);

    // Maintenance Schedules
    Route::get('maintenance-schedules/transformers', [MaintenanceScheduleController::class, 'getTransformers'])->name('maintenance-schedules.transformers');
    Route::resource('maintenance-schedules', MaintenanceScheduleController::class);

    // Findings
    Route::resource('findings', \App\Http\Controllers\FindingController::class);

    // Work Orders
    Route::resource('work-orders', WorkOrderController::class);
    Route::post('work-orders/{workOrder}/update-status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
    Route::post('work-orders/{workOrder}/toggle-checklist/{item}', [WorkOrderController::class, 'toggleChecklist'])->name('work-orders.toggle-checklist');
    Route::get('my-jobs', [WorkOrderController::class, 'myJobs'])->name('work-orders.my-jobs');

    // Maintenance Records
    Route::resource('maintenance-records', MaintenanceRecordController::class);

    // Timeline
    Route::get('timeline', [TimelineController::class, 'index'])->name('timeline.index');

    // KPI
    Route::get('kpi', [KpiController::class, 'index'])->name('kpi.index');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');



    // Checksheet Sessions
    Route::get('checksheet', [ChecksheetController::class, 'index'])->name('checksheet.index');
    Route::get('checksheet/create', fn() => redirect()->route('checksheet.index'))->name('checksheet.create');
    Route::post('checksheet', [ChecksheetController::class, 'store'])->name('checksheet.store');
    Route::get('checksheet/{session}/fill', [ChecksheetController::class, 'fill'])->name('checksheet.fill');
    Route::post('checksheet/{session}/autosave', [ChecksheetController::class, 'autosave'])->name('checksheet.autosave');
    Route::post('checksheet/{session}/upload-photo/{templateId}', [ChecksheetController::class, 'uploadPhoto'])->name('checksheet.upload-photo');
    Route::post('checksheet/{session}/submit', [ChecksheetController::class, 'submit'])->name('checksheet.submit');
    Route::get('checksheet/{session}', [ChecksheetController::class, 'show'])->name('checksheet.show');
    Route::get('checksheet/{session}/pdf', [ChecksheetController::class, 'exportPdf'])->name('checksheet.pdf');

    // Schedule Report
    Route::get('schedule-report', [ScheduleReportController::class, 'index'])->name('schedule-report.index');
    Route::post('schedule-report/override', [ScheduleReportController::class, 'overrideOnTime'])->name('schedule-report.override');
    Route::get('schedule-report/pdf/{tab}', [ScheduleReportController::class, 'exportPdf'])->name('schedule-report.pdf');

    // Reports (monthly activity & item usage)
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('reports/{monthlyReport}/download', [ReportController::class, 'download'])->name('reports.download');

    // Settings (admin only)
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings/fields', [SettingsController::class, 'updateFieldSettings'])->name('settings.fields.update');
    Route::get('settings/users/create', [SettingsController::class, 'createUser'])->name('settings.users.create');
    Route::post('settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::get('settings/users/{user}/edit', [SettingsController::class, 'editUser'])->name('settings.users.edit');
    Route::put('settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    Route::delete('settings/users/{user}', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');
    Route::post('settings/roles', [SettingsController::class, 'storeRole'])->name('settings.roles.store');
    Route::put('settings/roles/{role}', [SettingsController::class, 'updateRole'])->name('settings.roles.update');
    Route::put('settings/roles/{role}/permissions', [SettingsController::class, 'updateRolePermissions'])->name('settings.roles.permissions');
    Route::delete('settings/roles/{role}', [SettingsController::class, 'destroyRole'])->name('settings.roles.destroy');
    Route::post('settings/locations', [SettingsController::class, 'storeLocation'])->name('settings.locations.store');
    Route::put('settings/locations/{location}', [SettingsController::class, 'updateLocation'])->name('settings.locations.update');
    Route::delete('settings/locations/{location}', [SettingsController::class, 'destroyLocation'])->name('settings.locations.destroy');

    // PV Maps
    Route::get('settings/locations/{location}/pv-maps', [PvMapController::class, 'show'])->name('pv-maps.show');
    Route::post('settings/locations/{location}/pv-maps/upload', [PvMapController::class, 'uploadCsv'])->name('pv-maps.upload');
    Route::post('settings/locations/{location}/pv-maps/save', [PvMapController::class, 'save'])->name('pv-maps.save');
    Route::get('settings/locations/{location}/pv-maps/{transformerBlock}', [PvMapController::class, 'getMap'])->name('pv-maps.get');

    // Daily Reports (Personal Notes)
    Route::resource('daily-reports', DailyReportController::class)->only(['index', 'store', 'destroy']);
    Route::post('daily-reports/{dailyReport}/upload-photo', [DailyReportController::class, 'uploadPhoto'])->name('daily-reports.upload-photo');
    Route::delete('daily-reports/{dailyReport}/photos/{photo}', [DailyReportController::class, 'deletePhoto'])->name('daily-reports.delete-photo');
});

require __DIR__.'/auth.php';
