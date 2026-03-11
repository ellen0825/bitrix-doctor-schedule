<?php
/**
 * Установка модуля vit.doctor.schedule
 * Хранение расписания: таблица b_vit_doctor_schedule (см. README — архитектура).
 */
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Vit\DoctorSchedule\Schedule;

Loc::loadMessages(__FILE__);

class vit_doctor_schedule extends CModule
{
    public $MODULE_ID = 'vit.doctor.schedule';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME        = 'Расписание врача (vit.doctor)';
        $this->MODULE_DESCRIPTION = 'Настройка рабочей недели врача: рабочие дни, время начала и окончания работы.';
        $this->PARTNER_NAME       = 'VIT';
        $this->PARTNER_URI        = '';
    }

    public function DoInstall(): bool
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallDB();
        $this->CopyAdminFiles();
        $this->InstallDefaultSchedule();
        return true;
    }

    public function DoUninstall(): bool
    {
        $adminDir = Application::getDocumentRoot() . '/bitrix/admin';
        foreach (['vit_doctor_schedule.php', 'vit_doctor_schedule.menu.php'] as $file) {
            $path = $adminDir . '/' . $file;
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $this->UninstallDB();
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

    /** Создание таблицы b_vit_doctor_schedule */
    public function InstallDB(): bool
    {
        $connection = Application::getConnection();
        $sql = (string) file_get_contents(__DIR__ . '/db/mysql/install.sql');
        if ($sql !== '') {
            $connection->query(trim($sql));
        }
        return true;
    }

    /** Удаление таблицы при снятии модуля */
    public function UninstallDB(): bool
    {
        $connection = Application::getConnection();
        $sql = (string) file_get_contents(__DIR__ . '/db/mysql/uninstall.sql');
        if ($sql !== '') {
            $connection->query(trim($sql));
        }
        return true;
    }

    private function CopyAdminFiles(): void
    {
        $moduleRoot = dirname(__DIR__);
        $adminDir = Application::getDocumentRoot() . '/bitrix/admin';
        foreach (['vit_doctor_schedule.php', 'vit_doctor_schedule.menu.php'] as $file) {
            $src = $moduleRoot . '/admin/' . $file;
            $dst = $adminDir . '/' . $file;
            if (is_file($src)) {
                copy($src, $dst);
            }
        }
    }

    /** Дефолтное расписание: Пн–Пт 09:00–18:00, Сб–Вс выходные (DOCTOR_ID=0) */
    private function InstallDefaultSchedule(): void
    {
        Loader::includeModule($this->MODULE_ID);
        $default = [
            'MON' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'TUE' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'WED' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'THU' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'FRI' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'SAT' => ['is_working' => 'N', 'from' => '09:00', 'to' => '18:00'],
            'SUN' => ['is_working' => 'N', 'from' => '09:00', 'to' => '18:00'],
        ];
        Schedule::saveWeekSchedule($default, 0);
    }
}
