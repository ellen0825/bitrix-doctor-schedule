<?php

namespace Vit\DoctorSchedule;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Сервис расписания рабочей недели врача.
 * Хранение: таблица b_vit_doctor_schedule (одна строка на день на врача).
 * DOCTOR_ID = 0 — расписание по умолчанию (один врач или шаблон).
 */
class Schedule
{
    /** Коды дней недели (Пн–Вс) */
    public const DAYS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];

    /** Названия дней для админки */
    public static function getDayNames(): array
    {
        return [
            'MON' => 'Понедельник',
            'TUE' => 'Вторник',
            'WED' => 'Среда',
            'THU' => 'Четверг',
            'FRI' => 'Пятница',
            'SAT' => 'Суббота',
            'SUN' => 'Воскресенье',
        ];
    }

    /**
     * Получить расписание на неделю.
     * @param int $doctorId 0 = расписание по умолчанию
     * @return array<string, array{is_working: string, from: string, to: string}>
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getWeekSchedule(int $doctorId = 0): array
    {
        $rows = ScheduleTable::getList([
            'filter' => ['=DOCTOR_ID' => $doctorId],
            'select' => ['WEEKDAY', 'IS_WORKING', 'TIME_FROM', 'TIME_TO'],
        ])->fetchAll();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row['WEEKDAY']] = [
                'is_working' => $row['IS_WORKING'],
                'from'       => $row['TIME_FROM'],
                'to'         => $row['TIME_TO'],
            ];
        }

        $result = [];
        foreach (self::DAYS as $day) {
            $result[$day] = $byDay[$day] ?? [
                'is_working' => 'N',
                'from'       => '09:00',
                'to'         => '18:00',
            ];
        }
        return $result;
    }

    /**
     * Сохранить расписание на неделю.
     * @param array<string, array{is_working?: string, from?: string, to?: string}> $data
     * @param int $doctorId 0 = расписание по умолчанию
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function saveWeekSchedule(array $data, int $doctorId = 0): void
    {
        foreach (self::DAYS as $day) {
            $row = $data[$day] ?? [];
            $isWorking = (!empty($row['is_working']) && (string)$row['is_working'] === 'Y') ? 'Y' : 'N';
            $from = self::normalizeTime($row['from'] ?? '09:00');
            $to   = self::normalizeTime($row['to'] ?? '18:00');

            $existing = ScheduleTable::getList([
                'filter' => ['=DOCTOR_ID' => $doctorId, '=WEEKDAY' => $day],
                'select' => ['ID'],
                'limit'  => 1,
            ])->fetch();

            $fields = [
                'DOCTOR_ID'  => $doctorId,
                'WEEKDAY'    => $day,
                'IS_WORKING' => $isWorking,
                'TIME_FROM'  => $from,
                'TIME_TO'    => $to,
            ];

            if ($existing) {
                ScheduleTable::update($existing['ID'], $fields);
            } else {
                ScheduleTable::add($fields);
            }
        }
    }

    private static function normalizeTime(string $time): string
    {
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            $parts = explode(':', $time);
            return sprintf('%02d:%02d', (int)$parts[0], (int)$parts[1]);
        }
        return '09:00';
    }
}
