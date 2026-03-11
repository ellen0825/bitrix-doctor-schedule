<?php
/**
 * Установка модуля vit.doctor.schedule
 */
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

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
        $this->InstallDefaultSchedule();
        $this->CopyAdminFiles();
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
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
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

    /**
     * Дефолтное расписание: Пн–Пт 09:00–18:00, Сб–Вс выходные
     */
    private function InstallDefaultSchedule(): void
    {
        $default = [
            'MON' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'TUE' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'WED' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'THU' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'FRI' => ['is_working' => 'Y', 'from' => '09:00', 'to' => '18:00'],
            'SAT' => ['is_working' => 'N'],
            'SUN' => ['is_working' => 'N'],
        ];
        Option::set($this->MODULE_ID, 'doctor_week_schedule', json_encode($default, JSON_UNESCAPED_UNICODE));
    }
}
