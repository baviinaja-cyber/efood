<?php

namespace App\Controllers\Admin;

use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Setting;

class SettingAdminController
{
    public function index(): void
    {
        $settings = Setting::all();
        $map = [];
        foreach ($settings as $s) {
            $map[$s['key_name']] = $s['key_value'];
        }

        View::render('admin.settings.index', [
            'settings' => $map,
            'pageTitle' => 'Pengaturan Aplikasi & Tarif - Admin EFOOD'
        ]);
    }

    public function update(): void
    {
        $appName = Security::sanitizeString($_POST['app_name'] ?? 'EFOOD');
        $tagline = Security::sanitizeString($_POST['app_tagline'] ?? '');
        $baseFee = (float) ($_POST['base_delivery_fee'] ?? 9000);
        $perKmFee = (float) ($_POST['per_km_fee'] ?? 2500);
        $adminWa = Security::sanitizePhone($_POST['admin_whatsapp'] ?? '6281234567890');
        $enableCod = isset($_POST['enable_cod']) ? '1' : '0';
        $enableTransfer = isset($_POST['enable_transfer']) ? '1' : '0';
        $bankInfo = Security::sanitizeString($_POST['bank_transfer_info'] ?? '');

        Setting::set('app_name', $appName);
        Setting::set('app_tagline', $tagline);
        Setting::set('base_delivery_fee', (string) $baseFee);
        Setting::set('per_km_fee', (string) $perKmFee);
        Setting::set('admin_whatsapp', $adminWa);
        Setting::set('enable_cod', $enableCod);
        Setting::set('enable_transfer', $enableTransfer);
        Setting::set('bank_transfer_info', $bankInfo);

        Session::flash('success', 'Pengaturan aplikasi dan tarif pengiriman berhasil disimpan.');
        header('Location: ' . View::url('admin/pengaturan'));
        exit;
    }
}
