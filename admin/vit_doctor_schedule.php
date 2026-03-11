<?php
/**
 * Админ-страница: настройка рабочей недели врача.
 * Разместить в bitrix/admin/vit_doctor_schedule.php или открывать через меню.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

use Vit\DoctorSchedule\Schedule;

$MODULE_ID = 'vit.doctor.schedule';
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm('Доступ запрещён.');
}

\Bitrix\Main\Loader::includeModule($MODULE_ID);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $raw = $_POST['schedule'] ?? [];
    Schedule::saveWeekSchedule($raw);
    $message = 'Расписание сохранено.';
}

$schedule = Schedule::getWeekSchedule();
$dayNames = Schedule::getDayNames();

$APPLICATION->SetTitle('Расписание врача');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>

<?php if ($message): ?>
    <div class="adm-info-message-wrap">
        <div class="adm-info-message"><?= htmlspecialchars($message) ?></div>
    </div>
<?php endif; ?>

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
            <?php foreach (Schedule::DAYS as $code): ?>
                <?php
                $row = $schedule[$code];
                $name = $dayNames[$code] ?? $code;
                $checked = ($row['is_working'] === 'Y') ? ' checked' : '';
                ?>
                <tr>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td>
                        <input type="hidden" name="schedule[<?= $code ?>][is_working]" value="N">
                        <input type="checkbox" name="schedule[<?= $code ?>][is_working]" value="Y"<?= $checked ?> id="ch_<?= $code ?>">
                    </td>
                    <td>
                        <input type="time" name="schedule[<?= $code ?>][from]" value="<?= htmlspecialchars($row['from']) ?>" class="adm-input">
                    </td>
                    <td>
                        <input type="time" name="schedule[<?= $code ?>][to]" value="<?= htmlspecialchars($row['to']) ?>" class="adm-input">
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
(function(){
    var form = document.getElementById('schedule-form');
    if (!form) return;
    function toggleTime(tr) {
        var ch = tr.querySelector('input[type="checkbox"]');
        var inputs = tr.querySelectorAll('input[type="time"]');
        var on = ch && ch.checked;
        inputs.forEach(function(inp) { inp.disabled = !on; });
    }
    form.querySelectorAll('tbody tr').forEach(function(tr) {
        var ch = tr.querySelector('input[type="checkbox"]');
        if (ch) {
            ch.addEventListener('change', function() { toggleTime(tr); });
            toggleTime(tr);
        }
    });
})();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
