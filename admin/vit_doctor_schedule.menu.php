<?php
/**
 * Пункт левого меню админки Bitrix: «Расписание врача»
 */
$aMenu = [
    'parent_menu' => 'global_menu_settings',
    'sort'        => 100,
    'text'        => 'Расписание врача',
    'title'       => 'Настройка рабочей недели врача: рабочие дни, время начала и окончания',
    'url'         => 'vit_doctor_schedule.php',
    'icon'        => 'sys_menu_icon',
    'page_icon'   => 'sys_page_icon',
];
return $aMenu;
