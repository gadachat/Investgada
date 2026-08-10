<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminProfitShareController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSecurityController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\CryptoChartController;
use App\Http\Controllers\User\InvestmentPackageController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\Web3WalletController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\WithdrawalController;
use App\Http\Controllers\User\ReferralController;
use App\Http\Controllers\User\BinaryController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\ProfitShareController;
use App\Http\Controllers\User\AutoTradeController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\User\HistoryController;
use Illuminate\Support\Facades\Route;

// History & Exports
Route::middleware(['auth', 'verified'])->prefix('dashboard/history')->name('dashboard.history.')->group(function () {
    Route::get('/deposits', [HistoryController::class, 'depositHistory'])->name('deposits');
    Route::get('/withdrawals', [HistoryController::class, 'withdrawalHistory'])->name('withdrawals');
    Route::get('/commissions', [HistoryController::class, 'commissionHistory'])->name('commissions');
    Route::get('/deposits/export', [HistoryController::class, 'exportDeposits'])->name('deposits.export');
    Route::get('/withdrawals/export', [HistoryController::class, 'exportWithdrawals'])->name('withdrawals.export');
    Route::get('/commissions/export', [HistoryController::class, 'exportCommissions'])->name('commissions.export');
});

// Auth routes
require __DIR__ . '/auth.php';

// Home (Landing Page)
Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('home');

// Dashboard shortcut (used by login/register redirects)
Route::middleware(['auth'])->get('/dashboard', fn() => redirect()->route('dashboard.index'))->name('dashboard');

// ============================================
// USER DASHBOARD ROUTES
// ============================================

// ── 2FA Verification Routes ──
Route::get('/2fa/verify', [\App\Http\Controllers\Auth\LoginController::class, 'show2faVerify'])->name('2fa.verify');
Route::post('/2fa/verify', [\App\Http\Controllers\Auth\LoginController::class, 'verify2fa'])->name('2fa.verify.post');
Route::post('/2fa/recovery', [\App\Http\Controllers\Auth\LoginController::class, 'verify2faRecovery'])->name('2fa.verify.recovery');

Route::middleware(['auth', 'account.status'])->prefix('dashboard')->name('dashboard.')->group(function () {

    // Dashboard overview
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/live-prices', [DashboardController::class, 'livePrices'])->name('live-prices');
    Route::get('/market-overview', [DashboardController::class, 'marketOverview'])->name('market-overview');

    // Crypto chart
    Route::get('/crypto-feed', [CryptoChartController::class, 'feed'])->name('crypto-feed');
    Route::get('/crypto-tick', [CryptoChartController::class, 'tick'])->name('crypto-tick');

    // Investment packages
    Route::get('/packages', [InvestmentPackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{slug}', [InvestmentPackageController::class, 'show'])->name('packages.show');
    Route::post('/packages/{id}/invest', [InvestmentPackageController::class, 'invest'])->name('packages.invest');

    // My investments
    Route::get('/investments', [InvestmentPackageController::class, 'myInvestments'])->name('investments.index');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');
    Route::get('/wallet/history', [WalletController::class, 'history'])->name('wallet.history');

    // Web3 Wallet Connection
    Route::get('/web3/config', [Web3WalletController::class, 'config'])->name('web3.config');
    Route::post('/web3/connect', [Web3WalletController::class, 'connect'])->name('web3.connect');
    Route::post('/web3/{wallet}/disconnect', [Web3WalletController::class, 'disconnect'])->name('web3.disconnect');
    Route::post('/web3/{wallet}/set-primary', [Web3WalletController::class, 'setPrimary'])->name('web3.set-primary');

    // Deposits
    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit.create');
    Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');

    // Withdrawals
    Route::get('/withdrawal', [WithdrawalController::class, 'create'])->name('withdrawal.create');
    Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');

    // Referral system
    Route::get('/referral', [ReferralController::class, 'index'])->name('referral.index');

    // Rank Advancement
    Route::get('/rank', [\App\Http\Controllers\User\RankController::class, 'index'])->name('rank.index');
    Route::post('/referral/generate-link', [ReferralController::class, 'generateLink'])->name('referral.generate-link');

    // Binary MLM
    Route::get('/binary', [BinaryController::class, 'index'])->name('binary.index');
    Route::get('/binary/node/{userId}', [BinaryController::class, 'getNode'])->name('binary.node');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');

    // Profit sharing
    Route::get('/profit-share', [ProfitShareController::class, 'index'])->name('profit-share.index');
    Route::get('/leadership-bonus', [\App\Http\Controllers\User\LeadershipBonusController::class, 'index'])->name('leadership.index');
    Route::get('/profit-share/{id}', [ProfitShareController::class, 'show'])->name('profit-share.show');

    // Auto-Trading
    Route::get('/autotrade', [AutoTradeController::class, 'index'])->name('autotrade.index');
    Route::post('/autotrade/start', [AutoTradeController::class, 'start'])->name('autotrade.start');
    Route::post('/autotrade/{session}/stop', [AutoTradeController::class, 'stop'])->name('autotrade.stop');
    Route::get('/autotrade/history', [AutoTradeController::class, 'history'])->name('autotrade.history');
    Route::get('/autotrade/live', [AutoTradeController::class, 'liveFeed'])->name('autotrade.live');

    // Manual Trading
    Route::get('/trade', [\App\Http\Controllers\User\TradeController::class, 'index'])->name('trade.index');
    Route::post('/trade/subscribe', [\App\Http\Controllers\User\TradeController::class, 'subscribe'])->name('trade.subscribe');
    Route::post('/trade/open', [\App\Http\Controllers\User\TradeController::class, 'open'])->name('trade.open');
    Route::post('/trade/{position}/close', [\App\Http\Controllers\User\TradeController::class, 'close'])->name('trade.close');
    Route::get('/trade/price', [\App\Http\Controllers\User\TradeController::class, 'getPrice'])->name('trade.price');
    Route::post('/trade/update-positions', [\App\Http\Controllers\User\TradeController::class, 'updatePositions'])->name('trade.update');
    Route::get('/trade/scanner', [\App\Http\Controllers\User\TradeController::class, 'scanner'])->name('trade.scanner');
    Route::post('/trade/withdraw', [\App\Http\Controllers\User\TradeController::class, 'withdrawTrading'])->name('trade.withdraw');
    Route::get('/trade/history', [\App\Http\Controllers\User\TradeController::class, 'history'])->name('trade.history');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');


        // 2FA Management
        Route::get('/security/2fa/setup', [\App\Http\Controllers\User\TwoFactorController::class, 'setup'])->name('2fa.setup');
        Route::post('/security/2fa/enable', [\App\Http\Controllers\User\TwoFactorController::class, 'enable'])->name('2fa.enable');
        Route::get('/security/2fa/recovery', [\App\Http\Controllers\User\TwoFactorController::class, 'recovery'])->name('2fa.recovery');
        Route::get('/security/2fa/manage', [\App\Http\Controllers\User\TwoFactorController::class, 'manage'])->name('2fa.manage');
        Route::post('/security/2fa/disable', [\App\Http\Controllers\User\TwoFactorController::class, 'disable'])->name('2fa.disable');
        Route::post('/security/2fa/regenerate', [\App\Http\Controllers\User\TwoFactorController::class, 'regenerate'])->name('2fa.regenerate');

        // Trading Signals
        Route::get('/signals', [\App\Http\Controllers\User\SignalController::class, 'index'])->name('signals.index');

        // Copy Trading
        Route::get('/copy-trade', [\App\Http\Controllers\User\CopyTradeController::class, 'index'])->name('copy-trade.index');
        Route::post('/copy-trade/subscribe/{masterTrader}', [\App\Http\Controllers\User\CopyTradeController::class, 'subscribe'])->name('copy-trade.subscribe');
        Route::post('/copy-trade/unsubscribe/{subscription}', [\App\Http\Controllers\User\CopyTradeController::class, 'unsubscribe'])->name('copy-trade.unsubscribe');
        Route::get('/copy-trade/performance', [\App\Http\Controllers\User\CopyTradeController::class, 'performance'])->name('copy-trade.performance');

        // Activity Log
        Route::get('/activity-log', [\App\Http\Controllers\User\ActivityLogController::class, 'index'])->name('activity-log');

        // Announcements
        Route::get('/announcements/active', [\App\Http\Controllers\User\AnnouncementController::class, 'active'])->name('announcements.active');
        Route::post('/announcements/dismiss', [\App\Http\Controllers\User\AnnouncementController::class, 'dismiss'])->name('announcements.dismiss');

        // Referral Marketing
        Route::get('/referral/download-marketing', [ReferralController::class, 'downloadMarketing'])->name('referral.download-marketing');

        // Invoices & Statements
        Route::get('/invoice/deposit/{deposit}', [\App\Http\Controllers\User\InvoiceController::class, 'depositReceipt'])->name('invoice.deposit');
        Route::get('/invoice/withdrawal/{withdrawal}', [\App\Http\Controllers\User\InvoiceController::class, 'withdrawalReceipt'])->name('invoice.withdrawal');
        Route::get('/invoice/investment/{investment}', [\App\Http\Controllers\User\InvoiceController::class, 'investmentReceipt'])->name('invoice.investment');
        Route::get('/statement', [\App\Http\Controllers\User\InvoiceController::class, 'accountStatement'])->name('statement');

    // Fund Applications
    Route::get('/funds', [\App\Http\Controllers\User\FundApplicationController::class, 'index'])->name('funds.index');
    Route::get('/funds/create', [\App\Http\Controllers\User\FundApplicationController::class, 'create'])->name('funds.create');
    Route::post('/funds', [\App\Http\Controllers\User\FundApplicationController::class, 'store'])->name('funds.store');
        // Profile
        Route::get('/profile', [\App\Http\Controllers\User\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\App\Http\Controllers\User\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [\App\Http\Controllers\User\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/funds/{fund}', [\App\Http\Controllers\User\FundApplicationController::class, 'show'])->name('funds.show');
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::middleware(['auth', 'role:admin,super_admin', 'security.gate'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Admin dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Deposits management
        Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
        Route::post('/deposits/{id}/approve', [AdminDepositController::class, 'approve'])->name('deposits.approve');
        Route::post('/deposits/{id}/reject', [AdminDepositController::class, 'reject'])->name('deposits.reject');

        // Withdrawals management
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{id}/complete', [AdminWithdrawalController::class, 'complete'])->name('withdrawals.complete');
        Route::post('/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

        // Package management
        Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/create', [AdminPackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [AdminPackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{id}/edit', [AdminPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{id}', [AdminPackageController::class, 'update'])->name('packages.update');
        Route::post('/packages/{id}/toggle', [AdminPackageController::class, 'toggle'])->name('packages.toggle');
        Route::delete('/packages/{id}', [AdminPackageController::class, 'destroy'])->name('packages.destroy');

        // Settings
        Route::get('/settings/features', [AdminSettingsController::class, 'features'])->name('settings.features');
        Route::post('/settings/features/toggle', [AdminSettingsController::class, 'toggleFeature'])->name('settings.toggle-feature');
        Route::get('/settings/features/{feature}/config', [AdminSettingsController::class, 'getFeatureConfig'])->name('settings.get-feature-config');
        Route::post('/settings/features/{feature}/config', [AdminSettingsController::class, 'updateFeatureConfig'])->name('settings.update-feature-config');
        Route::get('/settings/platform', [AdminSettingsController::class, 'platform'])->name('settings.platform');
        Route::post('/settings/platform', [AdminSettingsController::class, 'updatePlatform'])->name('settings.platform.update');
        Route::get('/settings/ranks', [AdminSettingsController::class, 'ranks'])->name('settings.ranks');
        Route::post('/settings/ranks', [AdminSettingsController::class, 'storeRank'])->name('settings.ranks.store');
        Route::put('/settings/ranks/{rank}', [AdminSettingsController::class, 'updateRank'])->name('settings.ranks.update');
        Route::get('/settings/addresses', [AdminSettingsController::class, 'addresses'])->name('settings.addresses');
        Route::post('/settings/addresses', [AdminSettingsController::class, 'storeAddress'])->name('settings.addresses.store');
        Route::patch('/settings/addresses/{address}/toggle', [AdminSettingsController::class, 'toggleAddress'])->name('settings.addresses.toggle');
        Route::delete('/settings/addresses/{address}', [AdminSettingsController::class, 'destroyAddress'])->name('settings.addresses.destroy');

        // Site Settings (Branding, Logo, SEO)
        Route::get('/settings/site', [App\Http\Controllers\Admin\AdminSiteSettingsController::class, 'index'])->name('settings.site');
        Route::post('/settings/site', [App\Http\Controllers\Admin\AdminSiteSettingsController::class, 'update'])->name('site-settings.update');
        Route::get('/cron', fn() => view('admin.settings.cron'))->name('cron');

        // Auto-Trading Management
        Route::get('/autotrade', [App\Http\Controllers\Admin\AdminAutoTradeController::class, 'index'])->name('autotrade.index');
        Route::post('/autotrade', [App\Http\Controllers\Admin\AdminAutoTradeController::class, 'update'])->name('autotrade.update');
        Route::get('/autotrade/sessions', [App\Http\Controllers\Admin\AdminAutoTradeController::class, 'sessions'])->name('autotrade.sessions');
        Route::get('/autotrade/trades', [App\Http\Controllers\Admin\AdminAutoTradeController::class, 'trades'])->name('autotrade.trades');
        Route::post('/autotrade/{session}/force-stop', [App\Http\Controllers\Admin\AdminAutoTradeController::class, 'forceStop'])->name('autotrade.force-stop');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/broadcast', [AdminNotificationController::class, 'broadcast'])->name('notifications.broadcast');
        Route::post('/notifications/template', [AdminNotificationController::class, 'storeTemplate'])->name('notifications.store-template');
        Route::delete('/notifications/template/{id}', [AdminNotificationController::class, 'deleteTemplate'])->name('notifications.delete-template');
        Route::delete('/notifications/{id}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

        // Profit sharing
        Route::get('/profit-share', [AdminProfitShareController::class, 'index'])->name('profit-share.index');
        Route::post('/profit-share/run', [AdminProfitShareController::class, 'runCycle'])->name('profit-share.run');
        Route::post('/profit-share/settings', [AdminProfitShareController::class, 'updateSettings'])->name('profit-share.settings');
        Route::get('/leadership-bonus', [\App\Http\Controllers\Admin\AdminLeadershipBonusController::class, 'index'])->name('leadership.index');
        Route::post('/leadership-bonus/run', [\App\Http\Controllers\Admin\AdminLeadershipBonusController::class, 'runCycle'])->name('leadership.run');

        // Reports & Analytics
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/transactions', [AdminReportController::class, 'transactions'])->name('reports.transactions');
        Route::get('/reports/users', [AdminReportController::class, 'users'])->name('reports.users');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');

        // User Management
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/send-funds', [\App\Http\Controllers\Admin\AdminUserController::class, 'sendFunds'])->name('users.send-funds');
        Route::post('/users/{user}/deduct-funds', [\App\Http\Controllers\Admin\AdminUserController::class, 'deductFunds'])->name('users.deduct-funds');
        Route::post('/users/{user}/status', [\App\Http\Controllers\Admin\AdminUserController::class, 'updateStatus'])->name('users.update-status');
        Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('/signals', [\App\Http\Controllers\Admin\AdminSignalController::class, 'index'])->name('signals.index');
        Route::get('/signals/create', [\App\Http\Controllers\Admin\AdminSignalController::class, 'create'])->name('signals.create');
        Route::post('/signals', [\App\Http\Controllers\Admin\AdminSignalController::class, 'store'])->name('signals.store');
        Route::post('/signals/{signal}/close', [\App\Http\Controllers\Admin\AdminSignalController::class, 'close'])->name('signals.close');
        Route::delete('/signals/{signal}', [\App\Http\Controllers\Admin\AdminSignalController::class, 'destroy'])->name('signals.destroy');

        Route::post('/users/{user}/applicant-type', [\App\Http\Controllers\Admin\AdminUserController::class, 'updateApplicantType'])->name('users.applicant-type');

        // Security System
        Route::get('/security', [AdminSecurityController::class, 'index'])->name('security.index');
        Route::get('/security/audit-trail', [AdminSecurityController::class, 'auditTrail'])->name('security.audit-trail');
        Route::get('/security/ip-management', [AdminSecurityController::class, 'ipManagement'])->name('security.ip-management');
        Route::post('/security/block-ip', [AdminSecurityController::class, 'blockIp'])->name('security.block-ip');
        Route::post('/security/whitelist-ip', [AdminSecurityController::class, 'whitelistIp'])->name('security.whitelist-ip');
        Route::delete('/security/ip/{ip}', [AdminSecurityController::class, 'removeIp'])->name('security.remove-ip');
        Route::get('/security/sessions', [AdminSecurityController::class, 'sessions'])->name('security.sessions');
        Route::delete('/security/sessions/{session}', [AdminSecurityController::class, 'terminateSession'])->name('security.terminate-session');
        Route::get('/security/settings', [AdminSecurityController::class, 'settings'])->name('security.settings');
        Route::post('/security/settings', [AdminSecurityController::class, 'updateSettings'])->name('security.update-settings');
        Route::post('/security/clear-logs', [AdminSecurityController::class, 'clearLogs'])->name('security.clear-logs');
        // Trading Management
        Route::get('/trading', [\App\Http\Controllers\Admin\AdminTradeController::class, 'index'])->name('trading.index');
        Route::get('/trading/{position}', [\App\Http\Controllers\Admin\AdminTradeController::class, 'show'])->name('trading.show');
        Route::post('/trading/{position}/force-close', [\App\Http\Controllers\Admin\AdminTradeController::class, 'forceClose'])->name('trading.force-close');
        Route::get('/trading/settings', [\App\Http\Controllers\Admin\AdminTradeController::class, 'settings'])->name('trading.settings');
        Route::post('/trading/settings', [\App\Http\Controllers\Admin\AdminTradeController::class, 'updateSettings'])->name('trading.settings.update');
        Route::post('/trading/packages', [\App\Http\Controllers\Admin\AdminTradeController::class, 'storePackage'])->name('trading.packages.store');
        Route::post('/trading/packages/{package}', [\App\Http\Controllers\Admin\AdminTradeController::class, 'updatePackage'])->name('trading.packages.update');
        Route::post('/trading/packages/{package}/toggle', [\App\Http\Controllers\Admin\AdminTradeController::class, 'togglePackage'])->name('trading.packages.toggle');

        // Support Tickets
        Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
        Route::get('/support/{ticket}', [AdminSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('support.reply');
        Route::post('/support/{ticket}/assign', [AdminSupportController::class, 'assign'])->name('support.assign');
        Route::post('/support/{ticket}/assign-me', [AdminSupportController::class, 'assignToMe'])->name('support.assign-me');
        Route::post('/support/{ticket}/close', [AdminSupportController::class, 'close'])->name('support.close');
        Route::post('/support/{ticket}/reopen', [AdminSupportController::class, 'reopen'])->name('support.reopen');
        Route::post('/support/{ticket}/priority', [AdminSupportController::class, 'updatePriority'])->name('support.priority');

        // Fund Applications Management
        Route::get('/funds', [\App\Http\Controllers\Admin\AdminFundController::class, 'index'])->name('funds.index');
        Route::get('/funds/{fund}', [\App\Http\Controllers\Admin\AdminFundController::class, 'show'])->name('funds.show');
        Route::post('/funds/{fund}/approve', [\App\Http\Controllers\Admin\AdminFundController::class, 'approve'])->name('funds.approve');
        Route::post('/funds/{fund}/reject', [\App\Http\Controllers\Admin\AdminFundController::class, 'reject'])->name('funds.reject');
        Route::post('/funds/{fund}/revoke', [\App\Http\Controllers\Admin\AdminFundController::class, 'revoke'])->name('funds.revoke');
        Route::post('/funds/{fund}/production', [\App\Http\Controllers\Admin\AdminFundController::class, 'updateProduction'])->name('funds.production');
        Route::get('/funds/settings', [\App\Http\Controllers\Admin\AdminFundController::class, 'settings'])->name('funds.settings');
        Route::post('/funds/settings', [\App\Http\Controllers\Admin\AdminFundController::class, 'updateSettings'])->name('funds.settings.update');

        // Audit Logs
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{log}', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'show'])->name('audit-logs.show');
        Route::get('/audit-logs-export', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'export'])->name('audit-logs.export');
        Route::post('/audit-logs/clear', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'clear'])->name('audit-logs.clear');

        // Announcements
        Route::get('/announcements', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::patch('/announcements/{announcement}', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::patch('/announcements/{announcement}/toggle', [\App\Http\Controllers\Admin\AdminAnnouncementController::class, 'toggle'])->name('announcements.toggle');

        // Master Traders (Copy Trading)
        Route::get('/master-traders', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'index'])->name('master-traders.index');
        Route::get('/master-traders/create', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'create'])->name('master-traders.create');
        Route::post('/master-traders', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'store'])->name('master-traders.store');
        Route::get('/master-traders/{masterTrader}/edit', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'edit'])->name('master-traders.edit');
        Route::put('/master-traders/{masterTrader}', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'update'])->name('master-traders.update');
        Route::delete('/master-traders/{masterTrader}', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'destroy'])->name('master-traders.destroy');
        Route::patch('/master-traders/{masterTrader}/toggle', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'toggle'])->name('master-traders.toggle');
        Route::post('/master-traders/{masterTrader}/update-stats', [\App\Http\Controllers\Admin\AdminMasterTraderController::class, 'updateStats'])->name('master-traders.update-stats');
    });


// KYC status redirect (used by EnsureKycVerified middleware)
Route::middleware('auth')->get('/kyc/status', function () {
    return redirect()->route('dashboard.kyc.index');
})->name('kyc.status');

// ===== KYC MODULE =====
Route::middleware('auth')->group(function () {
    // User KYC

    // Support Tickets
    Route::prefix('dashboard/support')->name('dashboard.support.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\SupportTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [\App\Http\Controllers\User\SupportTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\User\SupportTicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [\App\Http\Controllers\User\SupportTicketController::class, 'close'])->name('close');
        Route::post('/{ticket}/rate', [\App\Http\Controllers\User\SupportTicketController::class, 'rate'])->name('rate');
    });

        Route::prefix('dashboard/kyc')->name('dashboard.kyc.')->group(function () {
        Route::get('/', [App\Http\Controllers\User\KycController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\User\KycController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\User\KycController::class, 'store'])->name('store');
        Route::get('/{id}/download/{type}', [App\Http\Controllers\User\KycController::class, 'downloadDocument'])->name('download');
    });
});

// ===== ADMIN KYC =====
Route::middleware(['auth', 'role:admin,super_admin', 'security.gate'])->prefix('admin/kyc')->name('admin.kyc.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminKycController::class, 'index'])->name('index');
    Route::get('/{id}', [App\Http\Controllers\Admin\AdminKycController::class, 'show'])->name('show');
    Route::post('/{id}/approve', [App\Http\Controllers\Admin\AdminKycController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [App\Http\Controllers\Admin\AdminKycController::class, 'reject'])->name('reject');
    Route::post('/toggle', [App\Http\Controllers\Admin\AdminKycController::class, 'toggle'])->name('toggle');
    Route::get('/{id}/download/{type}', [App\Http\Controllers\Admin\AdminKycController::class, 'downloadDocument'])->name('download');
});

// ===== ADMIN CHAT WIDGET (Tawk.to) =====
Route::middleware(['auth', 'role:admin,super_admin', 'security.gate'])->prefix('admin/chat-widget')->name('admin.chat-widget.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminChatWidgetController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\Admin\AdminChatWidgetController::class, 'update'])->name('update');
    Route::post('/toggle', [App\Http\Controllers\Admin\AdminChatWidgetController::class, 'toggle'])->name('toggle');
});

// ===== LANDING PAGE =====
Route::get('/api/market-tickers', [App\Http\Controllers\LandingController::class, 'marketTickers'])->name('market-tickers');
Route::get('/api/recent-activity', [App\Http\Controllers\LandingController::class, 'recentActivity'])->name('recent-activity');

// ===== ADMIN LANDING PAGE EDITOR =====
Route::middleware(['auth', 'role:admin,super_admin', 'security.gate'])->prefix('admin/landing')->name('admin.landing.')->group(function () {
    Route::get('/edit', [App\Http\Controllers\Admin\AdminLandingPageController::class, 'edit'])->name('edit');
    Route::post('/update', [App\Http\Controllers\Admin\AdminLandingPageController::class, 'update'])->name('update');
});

// ===== INSTALLER (only works before installation) =====
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [App\Http\Controllers\InstallerController::class, 'index'])->name('index');
    Route::get('/database', [App\Http\Controllers\InstallerController::class, 'database'])->name('database');
    Route::post('/test-db', [App\Http\Controllers\InstallerController::class, 'testDatabase'])->name('test-db');
    Route::get('/admin', [App\Http\Controllers\InstallerController::class, 'admin'])->name('admin');
    Route::post('/run', [App\Http\Controllers\InstallerController::class, 'run'])->name('run');
});
