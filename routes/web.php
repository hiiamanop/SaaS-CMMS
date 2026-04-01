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
});

require __DIR__.'/auth.php';
