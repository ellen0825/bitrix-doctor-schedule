<?php
/**
 * Модуль «Расписание врача» для решения vit.doctor
 * Управление рабочей неделей: рабочие дни, время начала и окончания.
 */

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'vit.doctor.schedule',
    [
        'Vit\\DoctorSchedule\\Schedule'       => 'lib/Schedule.php',
        'Vit\\DoctorSchedule\\ScheduleTable'  => 'lib/ScheduleTable.php',
    ]
);
