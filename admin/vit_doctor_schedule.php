<?php
/**
 * Управление расписанием врача.
 * Настройка рабочей недели: рабочие дни, время начала и окончания работы.
 * Пример: Понедельник 09:00–18:00, Вторник 09:00–18:00, Среда — выходной.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

$MODULE_ID = 'vit.doctor.schedule';
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm('Доступ запрещён.');
}
Loader::includeModule($MODULE_ID);

// Дни недели (Пн–Вс)
$DAYS = ['MON' => 'Понедельник', 'TUE' => 'Вторник', 'WED' => 'Среда', 'THU' => 'Четверг', 'FRI' => 'Пятница', 'SAT' => 'Суббота', 'SUN' => 'Воскресенье'];
$OPTION_NAME = 'doctor_week_schedule';

function normalizeTime($time) {
    if (preg_match('/^\d{1,2}:\d{2}$/', (string)$time)) {
        $parts = explode(':', $time);
        return sprintf('%02d:%02d', (int)$parts[0], (int)$parts[1]);
    }
    return '09:00';
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $raw = $_POST['schedule'] ?? [];
    $saved = [];
    foreach (array_keys($DAYS) as $code) {
        $row = $raw[$code] ?? [];
        $isWorking = (!empty($row['is_working']) && (string)$row['is_working'] === 'Y') ? 'Y' : 'N';
        $saved[$code] = [
            'is_working' => $isWorking,
            'from' => normalizeTime($row['from'] ?? '09:00'),
            'to'   => normalizeTime($row['to'] ?? '18:00'),
        ];
    }
    Option::set($MODULE_ID, $OPTION_NAME, json_encode($saved, JSON_UNESCAPED_UNICODE));
    $message = 'Расписание сохранено.';
}

$json = Option::get($MODULE_ID, $OPTION_NAME, '{}');
$data = json_decode($json, true);
if (!is_array($data)) {
    $data = [];
}
$schedule = [];
foreach (array_keys($DAYS) as $code) {
    $schedule[$code] = [
        'is_working' => $data[$code]['is_working'] ?? 'N',
        'from'       => $data[$code]['from'] ?? '09:00',
        'to'         => $data[$code]['to'] ?? '18:00',
    ];
}

$APPLICATION->SetTitle('Расписание врача');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>

<?php if ($message): ?>
    <div class="adm-info-message-wrap">
        <div class="adm-info-message"><?= htmlspecialchars($message) ?></div>
    </div>
<?php endif; ?>

<p class="adm-description">Настройте рабочую неделю врача: отметьте рабочие дни и укажите время начала и окончания работы.<br>Пример: Понедельник — 09:00–18:00, Вторник — 09:00–18:00, Среда — выходной.</p>

<form method="post" action="" id="schedule-form">
    <?= bitrix_sessid_post() ?>
    <table class="adm-detail-content-table edit-table">
        <thead>
            <tr>
                <th>День недели</th>
                <th>Рабочий день</th>
                <th>Время начала</th>
                <th>Время окончания</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($DAYS as $code => $name): ?>
                <?php
                $row = $schedule[$code];
                $checked = ($row['is_working'] === 'Y') ? ' checked' : '';
                ?>
                <tr>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td>
                        <input type="hidden" name="schedule[<?= htmlspecialchars($code) ?>][is_working]" value="N">
                        <label for="ch_<?= htmlspecialchars($code) ?>">
                            <input type="checkbox" name="schedule[<?= htmlspecialchars($code) ?>][is_working]" value="Y"<?= $checked ?> id="ch_<?= htmlspecialchars($code) ?>">
                            <span class="day-status"><?= $row['is_working'] === 'Y' ? 'Рабочий' : 'Выходной' ?></span>
                        </label>
                    </td>
                    <td>
                        <input type="time" name="schedule[<?= htmlspecialchars($code) ?>][from]" value="<?= htmlspecialchars($row['from']) ?>" class="adm-input" data-day="<?= htmlspecialchars($code) ?>">
                    </td>
                    <td>
                        <input type="time" name="schedule[<?= htmlspecialchars($code) ?>][to]" value="<?= htmlspecialchars($row['to']) ?>" class="adm-input" data-day="<?= htmlspecialchars($code) ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>
        <input type="submit" name="save" value="Сохранить" class="adm-btn adm-btn-green">
    </p>
</form>

<script>
(function() {
    var form = document.getElementById('schedule-form');
    if (!form) return;
    form.querySelectorAll('tbody tr').forEach(function(tr) {
        var ch = tr.querySelector('input[type="checkbox"]');
        var timeInputs = tr.querySelectorAll('input[type="time"]');
        var statusSpan = tr.querySelector('.day-status');
        function update() {
            var on = ch && ch.checked;
            timeInputs.forEach(function(inp) { inp.disabled = !on; });
            if (statusSpan) statusSpan.textContent = on ? 'Рабочий' : 'Выходной';
        }
        if (ch) {
            ch.addEventListener('change', update);
            update();
        }
    });
})();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
