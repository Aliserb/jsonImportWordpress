<?php
/*
Plugin Name: Winner JSON importer
Description: Import objects from a JSON file into WordPress.
Version: 1.0
Author: Tursun Alisher
*/

require ABSPATH . 'vendor/autoload.php';
define("IMPORT_PLUGIN_PATH", plugin_dir_path( __FILE__ ));

// Регистрируем хук, который будет выполняться при активации плагина
register_activation_hook(__FILE__, 'json_importer_activate');
define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

function json_importer_activate() {
    // Здесь вы можете добавить дополнительные действия при активации плагина, если это необходимо
}

// Добавляем страницу настроек плагина в админ-панели WordPress
add_action('admin_menu', 'json_importer_menu');

function json_importer_menu() {
    add_menu_page(
        'JSON Importer',
        'JSON Importer',
        'manage_options',
        'json-importer',
        'json_importer_page'
    );
}

function cyr2lat($text) {
    $cyrillic = array(
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м',
        'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ',
        'ы', 'ь', 'э', 'ю', 'я',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М',
        'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ',
        'Ы', 'Ь', 'Э', 'Ю', 'Я'
    );

    $latin = array(
        'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'kh', 'ts', 'ch', 'sh', 'shch', '',
        'y', '', 'e', 'yu', 'ya',
        'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'Zh', 'Z', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Kh', 'Ts', 'Ch', 'Sh', 'Shch', '',
        'Y', '', 'E', 'Yu', 'Ya'
    );

    return str_replace($cyrillic, $latin, $text);
}

// хук для запуска импорта загородки
add_action( 'start_import_suburban', 'import_suburban' );
function import_suburban() {
    include PLUGIN_PATH . '/suburban_housing_importer.php'; // обработка загородки
}

// Функция для отображения содержимого страницы настроек плагина
function json_importer_page() {
    echo '<div class="wrap">';
    echo '<h2>JSON Importer</h2>';
    
    include PLUGIN_PATH . '/secondary_housing_importer.php'; // обработка вторички

    // загородка
    echo '<h2>Форма для импорта загородки</h2>';

    // Выводим форму для загрузки JSON файла
    echo '<form method="post">';
    echo '<input type="submit" name="submit_suburban" value="Импортировать загородку">';
    echo '</form>';

    // Обработка отправки формы
    if (isset($_POST['submit_suburban']) or $_GET['suburban_import'] == 'start') {
        do_action( 'start_import_suburban' );
    }

    echo '</div>';
}

