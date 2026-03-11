<?php

namespace Vit\DoctorSchedule;

use Bitrix\Main\Config\Option;

/**
 * Расписание рабочей недели врача.
 * Хранение: одна опция модуля (JSON). Легко расширить до нескольких врачей (массив по doctor_id).
 */
class Schedule
{
    public const MODULE_ID = 'vit.doctor.schedule';
    public const OPTION_SCHEDULE = 'doctor_week_schedule';

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
     * @return array<string, array{is_working: string, from?: string, to?: string}>
     */
    public static function getWeekSchedule(): array
    {
        $json = Option::get(self::MODULE_ID, self::OPTION_SCHEDULE, '{}');
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $data = [];
        }

        $result = [];
        foreach (self::DAYS as $day) {
            $result[$day] = [
                'is_working' => $data[$day]['is_working'] ?? 'N',
                'from'       => $data[$day]['from'] ?? '09:00',
                'to'         => $data[$day]['to'] ?? '18:00',
            ];
        }
        return $result;
    }

    /**
     * Сохранить расписание на неделю.
     * @param array<string, array{is_working: string, from?: string, to?: string}> $data
     */
    public static function saveWeekSchedule(array $data): void
    {
        $normalized = [];
        foreach (self::DAYS as $day) {
            $row = $data[$day] ?? [];
            $isWorking = (!empty($row['is_working']) && (string)$row['is_working'] === 'Y') ? 'Y' : 'N';
            $normalized[$day] = [
                'is_working' => $isWorking,
                'from'       => self::normalizeTime($row['from'] ?? '09:00'),
                'to'         => self::normalizeTime($row['to'] ?? '18:00'),
            ];
        }
        Option::set(self::MODULE_ID, self::OPTION_SCHEDULE, json_encode($normalized, JSON_UNESCAPED_UNICODE));
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
