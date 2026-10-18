<?php

namespace App\Controllers\Admin;

use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Driver;

class DriverAdminController
{
    public function index(): void
    {
        $drivers = Driver::all();
        View::render('admin.drivers.index', [
            'drivers' => $drivers,
            'pageTitle' => 'Kelola Armada Driver - Admin EFOOD'
        ]);
    }

    public function store(): void
    {
        $name = Security::sanitizeString($_POST['name'] ?? '');
        $phone = Security::sanitizePhone($_POST['phone'] ?? '');
        $whatsapp = Security::sanitizePhone($_POST['whatsapp'] ?? $phone);
        $vehicle = Security::sanitizeString($_POST['vehicle_type'] ?? 'Sepeda Motor');
        $plate = strtoupper(Security::sanitizeString($_POST['vehicle_plate'] ?? ''));

        if (empty($name) || empty($phone) || empty($plate)) {
            Session::flash('danger', 'Nama driver, nomor telepon, dan plat kendaraan wajib diisi.');
            header('Location: ' . View::url('admin/driver'));
            exit;
        }

        Driver::create([
            'name' => $name,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'vehicle_type' => $vehicle,
            'vehicle_plate' => $plate,
            'status' => 'active',
            'is_available' => 1
        ]);

        Session::flash('success', "Driver {$name} berhasil didaftarkan!");
        header('Location: ' . View::url('admin/driver'));
        exit;
    }

    public function toggle(int $id): void
    {
        $driver = Driver::findById($id);
        if ($driver) {
            $newStatus = $driver['status'] === 'active' ? 'inactive' : 'active';
            Driver::update($id, [
                'name' => $driver['name'],
                'phone' => $driver['phone'],
                'whatsapp' => $driver['whatsapp'],
                'vehicle_type' => $driver['vehicle_type'],
                'vehicle_plate' => $driver['vehicle_plate'],
                'status' => $newStatus,
                'is_available' => $newStatus === 'active' ? 1 : 0
            ]);
            Session::flash('info', "Status driver {$driver['name']} diubah menjadi {$newStatus}.");
        }

        header('Location: ' . View::url('admin/driver'));
        exit;
    }
}
