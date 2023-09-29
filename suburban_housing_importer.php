<?
use JsonMachine\JsonMachine;

$walking_access_subway_ar = array();

// Определение функции updateField
function updateField($field, $item, $post_id) {
    // Обновляем пользовательские поля
    if(isset($item)) {
        update_field($field, $item, $post_id);
    }
}

function getPostIdBySlug($itemSlug) {
    // Используйте WP_Query для проверки существования записи с заданным slug'ом
    $query = new WP_Query(array(
        'post_type' => 'objects',
        'name' => $itemSlug,
        'posts_per_page' => 1,
    ));

    if ($query->have_posts()) {
        return $query->posts[0]->ID;
    }

    return false;
}

// Получаем содержимое JSON файла
$json_data = JsonMachine::fromFile(PLUGIN_PATH . 'import_files/test_suburban.json');

foreach ($json_data as $item) {  
    $itemSlug = cyr2lat("{$item['total_square']}кв.м {$item['total_room_count']} комн. {$item['geo_cache_district_name']} {$item['geo_cache_street_name']} Дом {$item['geo_cache_building_name']}");
    
    if(isset($item['deal_type_name'])) {
        $deal_type_name = $item['deal_type_name']; // тип объявления
    }

    if(isset($item['realty_type_name'])) {
        $realty_type_name = $item['realty_type_name']; // тип недвижимости
    }

    if(isset($item['update_datetime'])) {
        $update_datetime = $item['update_datetime']; // Дата публикации
    }

    // поле адрес
    $addressAr = array(
        $item['geo_cache_country_name'],
        $item['geo_cache_state_name'],
        $item['geo_cache_highway_name_1'],
        $item['geo_cache_region_name'],
        $item['geo_cache_settlement_name'],
        $item['geo_cache_street_name'],
    );

    $address = '';
    foreach($addressAr as $addressItem) {
        if($addressItem != '' and end($addressAr) != $addressItem) {
            $address .= $addressItem . ', ';
        } else {
            $address .= $addressItem;
        }
    }
    
    if(isset($item['price_rub'])) {
        $price = $item['price_rub']; // цена в рублях
    }
    
    if(isset($item['total_room_count'])) {
        $totalRoomCount = $item['total_room_count']; // количество комнат
    }
    
    if(isset($item['total_square'])) {
        $totalSquare = $item['total_square']; // кв.м
    }
    
    if (isset($item['storey'])) {
        $storey = $item['storey']; // этаж
    }
    
    if (isset($item['storeys_count'])) {
        $storeys_count = $item['storeys_count']; // этажность всего
    }           

    if($item['geo_cache_highway_name_1']) {
        $geo_cache_highway_name_1 = $item['geo_cache_highway_name_1'];
    } else {
        $geo_cache_highway_name_1 = '';
    }

    if($item['geo_town_transport_access']) {
        $geo_town_transport_access = $item['geo_town_transport_access'] . "км от мкад";
    } else {
        $geo_town_transport_access = '';
    }

    if($item['land_square']) {
        $land_square = $item['land_square'];
    } else {
        $land_square = '';
    }

    if($item['house_square']) {
        $house_square = $item['house_square'] . "кв.м";
    } else {
        $house_square = '';
    }

    if($item['land_category_name']) {
        $land_category_name = $item['land_category_name'];
    } else {
        $land_category_name = '';
    }

    if($item['gas_type_name'] != '') {
        $gas_type_name = $item['gas_type_name'];
    } else {
        $gas_type_name = '';
    }

    if($item['plumbing_type_name'] != '') {
        $plumbing_type_name = $item['plumbing_type_name'];
    } else {
        $plumbing_type_name = '';
    }

    if($item['electricity_type_name'] != '') {
        $electricity_type_name = $item['electricity_type_name'];
    } else {
        $electricity_type_name = '';
    }

    if($item['sewerage_type_name'] != '') {
        $sewerage_type_name = $item['sewerage_type_name'];
    } else {
        $sewerage_type_name = '';
    }

    $itemTitleArr = array(
        "{$land_square} соток",
        "{$item['geo_cache_state_name']}",
        "{$item['geo_cache_district_name']}",
        "{$item['geo_cache_street_name']}",
        "{$item['realty_type_name']}",
    );
    $itemTitle = '';
    foreach($itemTitleArr as $itemTitleElem) {
        if($itemTitleElem != '' and end($itemTitleArr) != $itemTitleElem) {
            $itemTitle .= $itemTitleElem . ', ';
        } else {
            $itemTitle .= $itemTitleElem;
        }
    }

    $content = $item['note']; // текст

    if($item['media_name'] != '') {
        $media_name = $item['media_name'];
    } else {
        $media_name = '';
    }



    // особенности
    if($item['is_studio']) {
        $is_studio = 'студия';
    } else {
        $is_studio = '';
    }

    if($item['is_apartment']) {
        $is_apartment = 'апартаменты';
    } else {
        $is_apartment = '';
    }

    if($item['is_free_planning']) {
        $is_free_planning = 'Свободная планировка';
    } else {
        $is_free_planning = '';
    }

    if($item['kitchen_square']) {
        $kitchen_square = "Площадь кухни {$item['kitchen_square']} кв.м";
    } else {
        $kitchen_square = '';
    }

    if($item['life_square']) {
        $life_square = "Жилая площадь {$item['life_square']} кв.м";
    } else {
        $life_square = '';
    }

    $specials = array(
        $is_studio, 
        $is_apartment, 
        $is_free_planning, 
        $kitchen_square, 
        $life_square,
    );

    // галерея
    $photoList = $item['photo_list'];
    $photoArray = explode(',', $photoList);

    // Проверяем, существует ли запись с таким slug
    $post_id = getPostIdBySlug($itemSlug);

    if ($post_id) {
        // Запись уже существует, обновляем ее
        $post_data = [
            'ID'            => $post_id,
            'post_title'    => 'suburban new import ' . $itemTitle,
            'post_content'  => $item['note'],
            'post_status'   => 'publish',
            'post_date'     => $update_datetime,
        ];

        wp_update_post(wp_slash($post_data));

        updateField('address', $address, $post_id);
        updateField('price', $price, $post_id);
        updateField('rooms', $totalRoomCount, $post_id);
        updateField('square', $totalSquare, $post_id);
        updateField('current_floor', $storey, $post_id);
        updateField('all_floor', $storeys_count, $post_id);
        updateField('media_name', $media_name, $post_id);
        updateField('highway', $geo_cache_highway_name_1, $post_id);
        updateField('km_form_road', $geo_town_transport_access, $post_id);
        updateField('land_square', $land_square, $post_id);
        updateField('house_square', $house_square, $post_id);
        updateField('land_category_name', $land_category_name, $post_id);
        updateField('gas_type_name', $gas_type_name, $post_id);
        updateField('plumbing_type_name', $plumbing_type_name, $post_id);
        updateField('electricity_type_name', $electricity_type_name, $post_id);
        updateField('sewerage_type_name', $sewerage_type_name, $post_id);
        updateField('is_studio', 0, $post_id);
        updateField('is_apartment', 0, $post_id);
        updateField('is_free_planning', 0, $post_id);

        if($specials) {
            // Очищаем повторитель 'specials' для данной записи
            delete_field('specials', $post_id);

            foreach($specials as $specialItem) {
                if($specialItem != '') {
                    $row = array(
                        'special' => $specialItem,
                    );
                    
                    update_row('specials', 0, $row, $post_id);  
                }
            }
        }

        if($photoArray) {
            // Очищаем повторитель 'gallery' для данной записи
            delete_field('gallery', $post_id);

            foreach($photoArray as $photoItem) {
                $row = array(
                    'url' => 'https://images.baza-winner.ru/' . $photoItem . '_1024x768',
                );
                
                update_row('gallery', 0, $row, $post_id);  
            }                  
        }
    } else {
        // Запись не существует, создаем новую
        $post_data = [
            'post_title'    => 'suburban ' . $itemTitle,
            'post_content'  => $item['note'],
            'post_name'     => $itemSlug,
            'post_status'   => 'publish',
            'post_type'     => 'objects',
            'post_author'   => get_current_user_id(),
            'post_date'     => $update_datetime,
        ];

        $post_id = wp_insert_post(wp_slash($post_data));

        wp_set_post_terms($post_id, $deal_type_name, 'obj_categories', true); // добавляю категорию
        wp_set_post_terms($post_id, $realty_type_name, 'obj_tags', true); // добавляю теги

        updateField('address', $address, $post_id);
        updateField('price', $price, $post_id);
        updateField('rooms', $totalRoomCount, $post_id);
        updateField('square', $totalSquare, $post_id);
        updateField('current_floor', $storey, $post_id);
        updateField('all_floor', $storeys_count, $post_id);
        updateField('media_name', $media_name, $post_id);
        updateField('highway', $geo_cache_highway_name_1, $post_id);
        updateField('km_form_road', $geo_town_transport_access, $post_id);
        updateField('land_square', $land_square, $post_id);
        updateField('house_square', $house_square, $post_id);
        updateField('land_category_name', $land_category_name, $post_id);
        updateField('gas_type_name', $gas_type_name, $post_id);
        updateField('plumbing_type_name', $plumbing_type_name, $post_id);
        updateField('electricity_type_name', $electricity_type_name, $post_id);
        updateField('sewerage_type_name', $sewerage_type_name, $post_id);

        if($specials) {
             // Очищаем повторитель 'specials' для данной записи
             delete_field('specials', $post_id);

            foreach($specials as $specialItem) {
                if($specialItem != '') {
                    $row = array(
                        'special' => $specialItem,
                    );
                    
                    update_row('specials', 0, $row, $post_id);  
                }
            }
        }

        if($photoArray) {
            // Очищаем повторитель 'gallery' для данной записи
            delete_field('gallery', $post_id);

            foreach($photoArray as $photoItem) {
                $row = array(
                    'url' => 'https://images.baza-winner.ru/' . $photoItem . '_1024x768',
                );
                
                update_row('gallery', 0, $row, $post_id);  
            }                  
        }
    }
}      

// Выводим сообщение об успешном импорте
echo '<p>Данные успешно импортированы.</p>';

die(); // заканчиваем работу плагина