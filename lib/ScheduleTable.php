<?php

namespace Vit\DoctorSchedule;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

/**
 * Таблица расписания рабочей недели.
 * Одна строка = один день недели для одного врача (DOCTOR_ID=0 — расписание по умолчанию).
 */
class ScheduleTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_vit_doctor_schedule';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new IntegerField('DOCTOR_ID', ['required' => true, 'default' => 0]),
            new StringField('WEEKDAY', ['required' => true, 'size' => 3]),
            new StringField('IS_WORKING', ['required' => true, 'size' => 1, 'default' => 'N']),
            new StringField('TIME_FROM', ['required' => true, 'size' => 5, 'default' => '09:00']),
            new StringField('TIME_TO', ['required' => true, 'size' => 5, 'default' => '18:00']),
        ];
    }
}
