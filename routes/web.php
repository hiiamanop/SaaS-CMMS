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
use App\Http\Controllers\ChecksheetTemplateController;
use App\Http\Controllers\ScheduleReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assets
    Route::resource('assets', AssetController::class);

    // Spare Parts
    Route::resource('spare-parts', SparePartController::class);
    Route::post('spare-parts/{sparePart}/adjust-stock', [SparePartController::class, 'adjustStock'])->name('spare-parts.adjust-stock');

    // Maintenance Schedules
    Route::resource('maintenance-schedules', MaintenanceScheduleController::class);

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

    // Checksheet Templates (per Maintenance Schedule)
    Route::get('checksheet/templates', [ChecksheetTemplateController::class, 'index'])->name('checksheet.templates.index');
    Route::post('checksheet/templates/{schedule}/items', [ChecksheetTemplateController::class, 'storeItem'])->name('checksheet.templates.store-item');
    Route::put('checksheet/templates/items/{item}', [ChecksheetTemplateController::class, 'updateItem'])->name('checksheet.templates.update-item');
    Route::delete('checksheet/templates/items/{item}', [ChecksheetTemplateController::class, 'destroyItem'])->name('checksheet.templates.destroy-item');

    // Checksheet Sessions
    Route::get('checksheet', [ChecksheetController::class, 'index'])->name('checksheet.index');
    Route::get('checksheet/create', fn() => redirect()->route('checksheet.templates.index'))->name('checksheet.create');
    Route::post('checksheet', [ChecksheetController::class, 'store'])->name('checksheet.store');
    Route::get('checksheet/{session}/fill', [ChecksheetController::class, 'fill'])->name('checksheet.fill');
    Route::post('checksheet/{session}/autosave', [ChecksheetController::class, 'autosave'])->name('checksheet.autosave');
    Route::post('checksheet/{session}/upload-photo/{templateId}', [ChecksheetController::class, 'uploadPhoto'])->name('checksheet.upload-photo');
    Route::post('checksheet/{session}/submit', [ChecksheetController::class, 'submit'])->name('checksheet.submit');
    Route::get('checksheet/{session}', [ChecksheetController::class, 'show'])->name('checksheet.show');
    Route::get('checksheet/{session}/pdf', [ChecksheetController::class, 'exportPdf'])->name('checksheet.pdf');

    // Schedule Report
    Route::get('schedule-report', [ScheduleReportController::class, 'index'])->name('schedule-report.index');
    Route::get('schedule-report/pdf/{tab}', [ScheduleReportController::class, 'exportPdf'])->name('schedule-report.pdf');

    // Settings (admin only)
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/users/create', [SettingsController::class, 'createUser'])->name('settings.users.create');
    Route::post('settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::get('settings/users/{user}/edit', [SettingsController::class, 'editUser'])->name('settings.users.edit');
    Route::put('settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    Route::delete('settings/users/{user}', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');
    Route::post('settings/roles', [SettingsController::class, 'storeRole'])->name('settings.roles.store');
    Route::put('settings/roles/{role}', [SettingsController::class, 'updateRole'])->name('settings.roles.update');
    Route::delete('settings/roles/{role}', [SettingsController::class, 'destroyRole'])->name('settings.roles.destroy');
});

require __DIR__.'/auth.php';
