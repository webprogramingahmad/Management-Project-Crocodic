<?php

use App\Http\Controllers\Admin\Activity\IndexActivityController;
use App\Http\Controllers\Admin\Admin\CreateAdminController;
use App\Http\Controllers\Admin\Admin\DestroyAdminController;
use App\Http\Controllers\Admin\Admin\DestroypictAdminController;
use App\Http\Controllers\Admin\Admin\EditAdminController;
use App\Http\Controllers\Admin\Admin\IndexAdminController;
use App\Http\Controllers\Admin\Admin\ShowAdminController;
use App\Http\Controllers\Admin\Admin\StoreAdminController;
use App\Http\Controllers\Admin\Admin\UpdateAdminController;
use App\Http\Controllers\Admin\Admin\UploadpictAdminController;
use App\Http\Controllers\Admin\Administration\CreateAdministrationController;
use App\Http\Controllers\Admin\Administration\DestroyAdministrationController;
use App\Http\Controllers\Admin\Administration\IndexAdministrationController;
use App\Http\Controllers\Admin\Administration\ShowAdministrationController;
use App\Http\Controllers\Admin\Administration\StoreAdministrationController;
use App\Http\Controllers\Admin\Administration\UpdateAdministrationController;
use App\Http\Controllers\Admin\Dashboard\IndexDashboardController;
use App\Http\Controllers\Admin\Profile\IndexProfileController;
use App\Http\Controllers\Admin\Profile\UpdateProfileController;
use App\Http\Controllers\Admin\Profile\EditProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\Project\CreateProjectController;
use App\Http\Controllers\Admin\Project\DestroyProjectController;
use App\Http\Controllers\Admin\Project\EditProjectController;
use App\Http\Controllers\Admin\Project\IndexProjectController;
use App\Http\Controllers\Admin\Project\StoreProjectController;
use App\Http\Controllers\Admin\Project\UpdateProjectController;
use App\Http\Controllers\Admin\Tasks\CreateProjectTaskController;
use App\Http\Controllers\Admin\Tasks\IndexProjectTaskController;
use App\Http\Controllers\Admin\Tasks\IndexTaskController;
use App\Http\Controllers\Admin\Tasks\StoreProjectTaskController;
use App\Http\Controllers\Admin\Tasks\StoreTaskController;
use App\Http\Controllers\Admin\Tasks\TransferProjectTaskController;
use App\Http\Controllers\Admin\Tasks\TransferTaskController;
use App\Http\Controllers\Admin\Tasks\UpdateProjectTaskController;
use App\Http\Controllers\UpdateStatusTaskController;
use App\Http\Controllers\Admin\Tasks\UpdateTaskController;
use App\Http\Controllers\Director\Administration\CreateAdministrationController as DirectorCreateAdministrationController;
use App\Http\Controllers\Director\Administration\IndexAdministrationController as DirectorIndexAdministrationController;
use App\Http\Controllers\Director\Administration\ShowAdministrationController as DirectorShowAdministrationController;
use App\Http\Controllers\Director\Administration\StoreAdministrationController as DirectorStoreAdministrationController;
use App\Http\Controllers\Director\Dashboard\IndexDashboardController as DirectorIndexDashboardController;
use App\Http\Controllers\Director\Profile\EditProfileController as DirectorEditProfileController;
use App\Http\Controllers\Director\Profile\IndexProfileController as DirectorIndexProfileController;
use App\Http\Controllers\Director\Profile\UpdateProfileController as DirectorUpdateProfileController;
use App\Http\Controllers\Director\Projects\CreateProjectController as DirectorCreateProjectController;
use App\Http\Controllers\Director\Projects\DestroyProjectController as DirectorDestroyProjectController;
use App\Http\Controllers\Director\Projects\EditProjectController as DirectorEditProjectController;
use App\Http\Controllers\Director\Projects\IndexProjectController as DirectorIndexProjectController;
use App\Http\Controllers\Director\Projects\StoreProjectController as DirectorStoreProjectController;
use App\Http\Controllers\Director\Projects\UpdateProjectController as DirectorUpdateProjectController;
use App\Http\Controllers\Director\Tasks\IndexProjectTaskController as DirectorIndexProjectTaskController;
use App\Http\Controllers\Director\Tasks\IndexTaskController as DirectorIndexTaskController;
use App\Http\Controllers\Director\Tasks\StoreProjectTaskController as DirectorStoreProjectTaskController;
use App\Http\Controllers\Director\Tasks\StoreTaskController as DirectorStoreTaskController;
use App\Http\Controllers\Director\Tasks\TransferProjectTaskController as DirectorTransferProjectTaskController;
use App\Http\Controllers\Director\Tasks\TransferTaskController as DirectorTransferTaskController;
use App\Http\Controllers\Director\Tasks\UpdateProjectTaskController as DirectorUpdateProjectTaskController;
use App\Http\Controllers\Director\Tasks\UpdateTaskController as DirectorUpdateTaskController;
use App\Http\Controllers\Director\Tasks\ReviewTaskDecisionController as DirectorReviewTaskDecisionController;
use App\Http\Controllers\UploadpictProfileController;
use App\Http\Controllers\UploadProfileController;
use App\Http\Controllers\User\Administration\CreateAdministrationController as UserCreateAdministrationController;
use App\Http\Controllers\User\Administration\IndexAdministrationController as UserIndexAdministrationController;
use App\Http\Controllers\User\Administration\ShowAdministrationController as UserShowAdministrationController;
use App\Http\Controllers\User\Administration\StoreAdministrationController as UserStoreAdministrationController;
use App\Http\Controllers\User\Dashboard\IndexDashboardController as UserIndexDashboardController;
use App\Http\Controllers\User\Profile\EditProfileController as UserEditProfileController;
use App\Http\Controllers\User\Profile\IndexProfileController as UserIndexProfileController;
use App\Http\Controllers\User\Profile\UpdateProfileController as UserUpdateProfileController;
use App\Http\Controllers\User\Projects\IndexProjectController as UserIndexProjectController;
use App\Http\Controllers\User\Tasks\IndexProjectTaskController as UserIndexProjectTaskController;
use App\Http\Controllers\User\Tasks\IndexTaskController as UserIndexTaskController;
use App\Http\Controllers\User\Tasks\StoreProjectTaskController as UserStoreProjectTaskController;
use App\Http\Controllers\User\Tasks\StoreTaskController as UserStoreTaskController;
use App\Http\Controllers\User\Tasks\UpdateProjectTaskController as UserUpdateProjectTaskController;
use App\Http\Controllers\User\Tasks\UpdateTaskController as UserUpdateTaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\OpenDashboardNotificationController;

Route::get('/search', [SearchController::class, 'index'])->name('search.route');
Route::get('/send-email', [VerificationController::class, 'showEmailForm'])->name('email.form');
Route::post('/send-email', [VerificationController::class, 'sendEmail'])->name('email.send');
Route::get('/email-verification', [VerificationController::class, 'showVerificationPage'])->name('email.verification');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])
    ->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::view('/reset-password-success', 'auth.reset-password-succes')
    ->name('password.reset.success');
Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])
    ->name('password.update');

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('loginsystem');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
 * Legacy URLs (poin 2): redirect ke prefix baru agar bookmark / tautan lama tidak putus.
 */
Route::middleware(['auth', 'checkRole:executive', 'nocache'])->group(function () {
    Route::permanentRedirect('/admin/dashboard', '/executive/dashboard');
    Route::permanentRedirect('/admin/project', '/executive/project');
    Route::permanentRedirect('/admin/tasks', '/executive/tasks');
    Route::permanentRedirect('/admin/activity', '/executive/activity');
    Route::permanentRedirect('/admin/administration', '/executive/administration');
    Route::permanentRedirect('/admin/profile', '/executive/profile');
    Route::permanentRedirect('/admin/admin', '/executive/accounts');
    Route::permanentRedirect('/admin/admin/admin-create', '/executive/accounts/admin-create');
});

Route::middleware(['auth', 'checkRole:staff', 'nocache'])->group(function () {
    Route::permanentRedirect('/dashboard', '/staff/dashboard');
    Route::permanentRedirect('/project', '/staff/project');
    Route::permanentRedirect('/tasks', '/staff/tasks');
    Route::permanentRedirect('/administration', '/staff/administration');
    Route::permanentRedirect('/profile', '/staff/profile');
});

Route::middleware(['auth', 'checkRole:executive', 'nocache'])->group(function () {
    Route::prefix('/executive')->group(function () {
        Route::get('/dashboard', IndexDashboardController::class)->name('executive.dashboard.index');

        Route::prefix('/project')->group(function () {
            Route::get('', IndexProjectController::class)->name('executive.projects.index');
            Route::get('/project-create', CreateProjectController::class)->name('executive.projects.create');
            Route::post('/project-store', StoreProjectController::class)->name('executive.project.store');
            Route::get('/project-edit/{id}', EditProjectController::class)->name('executive.project.edit');
            Route::delete('/project-destroy/{id}', DestroyProjectController::class)->name('executive.project.destroy');
            Route::put('/project-update/{id}', UpdateProjectController::class)->name('executive.project.update');
            Route::get('/{id}/tasks', IndexProjectTaskController::class)->name('executive.project.tasks.index');
            Route::get('/{id}/task/task-create', CreateProjectTaskController::class)->name('executive.project.task.create');
            Route::post('/{id}/task/task-store', StoreProjectTaskController::class)->name('executive.project.task.store');
            Route::post('/{id}/task/task-transfer', TransferProjectTaskController::class)->name('executive.project.task.transfer');
            Route::post('/{id}/task/task-update', UpdateProjectTaskController::class)->name('executive.project.task.update');
        });

        Route::prefix('/tasks')->group(function () {
            Route::get('', IndexTaskController::class)->name('executive.tasks.index');
            Route::post('/task-store', StoreTaskController::class)->name('executive.task.store');
            Route::post('/task-update', UpdateTaskController::class)->name('executive.task.update');
            Route::post('/task-update-status/{id}', UpdateStatusTaskController::class)->name('executive.task.updateStatus');
            Route::put('/task-update', UpdateTaskController::class)->name('executive.tasks.update');
            Route::post('/task/task-transfer', TransferTaskController::class)->name('executive.task.transfer');
        });

        Route::get('/activity', IndexActivityController::class)->name('executive.activity.index');

        Route::prefix('/administration')->group(function () {
            Route::get('/', IndexAdministrationController::class)->name('executive.administration.index');
            Route::post('/{id}/status', UpdateAdministrationController::class)
                ->name('executive.administrations.updateStatus');
            Route::get('/administrations-create', CreateAdministrationController::class)->name('executive.administration.create');
            Route::post('/administration-store', StoreAdministrationController::class)->name('executive.administration.store');
            Route::delete('/administration-destroy/{id}', DestroyAdministrationController::class)->name('executive.administration.destroy');
            Route::get('/{id}', ShowAdministrationController::class)->name('executive.administration.show');
        });

        Route::prefix('/profile')->group(function () {
            Route::get('/', IndexProfileController::class)->name('executive.profile.index');
            Route::get('/profile-edit/{id}', EditProfileController::class)->name('executive.profile.edit');
            Route::put('/profile-update/{id}', UpdateProfileController::class)->name('executive.profile.update');
        });

        Route::prefix('/accounts')->group(function () {
            Route::get('', IndexAdminController::class)->name('executive.accounts.index');
            Route::get('/admin-create', CreateAdminController::class)->name('executive.accounts.create');
            Route::post('/admin-store', StoreAdminController::class)->name('executive.accounts.store');
            Route::get('/admin-show/{id}', ShowAdminController::class)->name('executive.accounts.show');
            Route::get('/admin-edit/{id}', EditAdminController::class)->name('executive.accounts.edit');
            Route::put('/admin-update/{id}', UpdateAdminController::class)->name('executive.accounts.update');
            Route::delete('/admin-destroy/{id}', DestroyAdminController::class)->name('executive.accounts.destroy');
            Route::delete('/admin-destroy-avatar/{id}', DestroypictAdminController::class)->name('executive.accounts.destroy.avatar');
            Route::post('/admin-uploadpict/{id}', UploadpictAdminController::class)->name('executive.accounts.upload.avatar');
        });
    });
});

Route::middleware(['auth', 'checkRole:staff', 'nocache'])->group(function () {
    Route::prefix('/staff')->group(function () {
        Route::get('/dashboard', UserIndexDashboardController::class)->name('staff.dashboard.index');

        Route::prefix('/project')->group(function () {
            Route::get('', UserIndexProjectController::class)->name('staff.projects.index');
            Route::get('/{id}/tasks', UserIndexProjectTaskController::class)->name('staff.project.tasks.index');
            Route::post('/{id}/task/task-store', UserStoreProjectTaskController::class)->name('staff.project.task.store');
            Route::post('/{id}/task/task-update', UserUpdateProjectTaskController::class)->name('staff.project.task.update');
        });

        Route::prefix('/tasks')->group(function () {
            Route::get('', UserIndexTaskController::class)->name('staff.tasks.index');
            Route::post('/task-store', UserStoreTaskController::class)->name('staff.task.store');
            Route::post('/task-update-status/{id}', UpdateStatusTaskController::class)->name('staff.task.updateStatus');
            Route::post('/task-update', UserUpdateTaskController::class)->name('staff.task.update');
        });

        Route::prefix('/administration')->group(function () {
            Route::get('/', UserIndexAdministrationController::class)->name('staff.administration.index');
            Route::get('/administration-create', UserCreateAdministrationController::class)->name('staff.administration.create');
            Route::post('/administration-store', UserStoreAdministrationController::class)->name('staff.administration.store');
            Route::get('/{id}', UserShowAdministrationController::class)->name('staff.administration.show');
        });

        Route::prefix('/profile')->group(function () {
            Route::get('/', UserIndexProfileController::class)->name('staff.profile.index');
            Route::get('/profile-edit/{id}', UserEditProfileController::class)->name('staff.profile.edit');
            Route::put('/profile-update/{id}', UserUpdateProfileController::class)->name('staff.profile.update');
        });
    });
});

Route::middleware(['auth', 'checkRole:director', 'nocache'])->group(function () {
    Route::prefix('/director')->group(function () {
        Route::get('/dashboard', DirectorIndexDashboardController::class)->name('director.dashboard.index');

        Route::prefix('/project')->group(function () {
            Route::get('', DirectorIndexProjectController::class)->name('director.projects.index');
            Route::get('/project-create', DirectorCreateProjectController::class)->name('director.projects.create');
            Route::post('/project-store', DirectorStoreProjectController::class)->name('director.project.store');
            Route::get('/project-edit/{id}', DirectorEditProjectController::class)->name('director.project.edit');
            Route::put('/project-update/{id}', DirectorUpdateProjectController::class)->name('director.project.update');
            Route::delete('/project-destroy/{id}', DirectorDestroyProjectController::class)->name('director.project.destroy');
            Route::get('/{id}/tasks', DirectorIndexProjectTaskController::class)->name('director.project.tasks.index');
            Route::post('/{id}/task/task-store', DirectorStoreProjectTaskController::class)->name('director.project.task.store');
            Route::post('/{id}/task/task-transfer', DirectorTransferProjectTaskController::class)->name('director.project.task.transfer');
            Route::post('/{id}/task/task-update', DirectorUpdateProjectTaskController::class)->name('director.project.task.update');
        });

        Route::prefix('/tasks')->group(function () {
            Route::get('', DirectorIndexTaskController::class)->name('director.tasks.index');
            Route::post('/task-store', DirectorStoreTaskController::class)->name('director.task.store');
            Route::post('/task-update', DirectorUpdateTaskController::class)->name('director.task.update');
            Route::post('/task-review-decision/{id}', DirectorReviewTaskDecisionController::class)->name('director.task.reviewDecision');
            Route::post('/task-update-status/{id}', UpdateStatusTaskController::class)->name('director.task.updateStatus');
            Route::post('/task/task-transfer', DirectorTransferTaskController::class)->name('director.task.transfer');
        });

        Route::prefix('/administration')->group(function () {
            Route::get('/', DirectorIndexAdministrationController::class)->name('director.administration.index');
            Route::get('/administration-create', DirectorCreateAdministrationController::class)->name('director.administration.create');
            Route::post('/administration-store', DirectorStoreAdministrationController::class)->name('director.administration.store');
            Route::get('/{id}', DirectorShowAdministrationController::class)->name('director.administration.show');
        });

        Route::prefix('/profile')->group(function () {
            Route::get('/', DirectorIndexProfileController::class)->name('director.profile.index');
            Route::get('/profile-edit/{id}', DirectorEditProfileController::class)->name('director.profile.edit');
            Route::put('/profile-update/{id}', DirectorUpdateProfileController::class)->name('director.profile.update');
        });
    });
});
Route::view('/tumbal', 'tumbal')->name('tumbal');

Route::middleware('auth')->group(function () {
    Route::post('/profile-uploadpict/{id}', UploadpictProfileController::class)->name('upload.avatar');
    Route::get('/dashboard/notifications/open', OpenDashboardNotificationController::class)->name('dashboard.notifications.open');
});
