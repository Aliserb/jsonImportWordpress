<?
use JsonMachine\JsonMachine;

echo '<h2>Форма для импорта вторички</h2>';

// Выводим форму для загрузки JSON файла
echo '<form method="post">';
echo '<input type="submit" name="submit_secondary" value="Импортировать вторички">';
echo '</form>';

// Обработка отправки формы
if (isset($_POST['submit_secondary'])) {
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
    $json_data = JsonMachine::fromFile(PLUGIN_PATH . 'import_files/test_secondary.json');

    foreach ($json_data as $item) {  
        $itemSlug = cyr2lat("{$item['total_square']}кв.м {$item['total_room_count']} комн. {$item['geo_cache_district_name']} {$item['geo_cache_street_name']} Дом {$item['geo_cache_building_name']}");
        
        if(isset($item['update_datetime'])) {
            $update_datetime = $item['update_datetime']; // Дата публикации
        }

        // поле адрес
        $addressAr = array(
            $item['geo_cache_country_name'],
            $item['geo_cache_state_name'],
            $item['geo_cache_highway_name_1'],
            $item['geo_cache_region_name'],
            $item['geo_cache_street_name'],
            $item['geo_cache_district_name'],
            $item['geo_cache_building_name'],
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
        
        if($item['geo_cache_subway_station_name_1'] or $item['geo_cache_subway_station_name_2'] or $item['geo_cache_subway_station_name_3'] or $item['geo_cache_subway_station_name_4']) {
            $subwayArr = array(
                isset($item['geo_cache_subway_station_name_1']) ? $item['geo_cache_subway_station_name_1'] : '-',
                isset($item['geo_cache_subway_station_name_2']) ? $item['geo_cache_subway_station_name_2'] : '-',
                isset($item['geo_cache_subway_station_name_3']) ? $item['geo_cache_subway_station_name_3'] : '-',
                isset($item['geo_cache_subway_station_name_4']) ? $item['geo_cache_subway_station_name_4'] : '-',
            );

            $subwayString = implode(', ', $subwayArr); // метро
        }

        if($item['walking_access_1'] or $item['walking_access_2'] or $item['walking_access_3'] or $item['walking_access_4']) {
            $walking_access_subway_ar = array(
                isset($item['walking_access_1']) ? $item['walking_access_1'] : '-',
                isset($item['walking_access_2']) ? $item['walking_access_2'] : '-',
                isset($item['walking_access_3']) ? $item['walking_access_3'] : '-',
                isset($item['walking_access_4']) ? $item['walking_access_4'] : '-',
            );
        }

        if($item['transport_access_1'] or $item['transport_access_2'] or $item['transport_access_3'] or $item['transport_access_4']) {
            $transport_access_subway_ar = array(
                isset($item['transport_access_1']) ? $item['transport_access_1'] : '-',
                isset($item['transport_access_2']) ? $item['transport_access_2'] : '-',
                isset($item['transport_access_3']) ? $item['transport_access_3'] : '-',
                isset($item['transport_access_4']) ? $item['transport_access_4'] : '-',
            );
        }
        
        if (isset($item['storey'])) {
            $storey = $item['storey']; // этаж
        }
        
        if (isset($item['storeys_count'])) {
            $storeys_count = $item['storeys_count']; // этажность всего
        }
        
        if (isset($item['walls_material_type_name'])) {
            $walls_material_type_name = $item['walls_material_type_name']; // материал дома
        }                

        if (isset($item['storeys_count'])) {
            $storeys_count = $item['storeys_count']; // этажность всего
        }
    
        if(isset($item['kitchen_square'])) {
            $kitchen_square = "{$item['kitchen_square']} кв.м";
        }
    
        if(isset($item['life_square'])) {
            $life_square = "{$item['life_square']} кв.м";
        }

        if(isset($item['media_name'])) {
            $media_name = $item['media_name'];
        }

        $specials = array();
    
        // галерея
        $photoList = $item['photo_list'];
        $photoArray = explode(',', $photoList);

        // заголовок и контент
        $itemTitleArr = array(
            "{$totalSquare}кв.м",
            "{$totalRoomCount} комн.",
            "{$item['geo_cache_district_name']}",
            "{$item['geo_cache_street_name']}",
            "{$item['geo_cache_building_name']}",
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
    
        // Проверяем, существует ли запись с таким slug
        $post_id = getPostIdBySlug($itemSlug);
    
        if ($post_id) {
            // Запись уже существует, обновляем ее
            $post_data = [
                'ID'            => $post_id,
                'post_title'    => 'WINNER ' . $itemTitle,
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
            updateField('kitchen_square', $kitchen_square, $post_id);
            updateField('life_square', $life_square, $post_id);
            updateField('is_studio', 1, $post_id);
            updateField('is_apartment', 1, $post_id);
            updateField('is_free_planning', 1, $post_id);

            // станции метро
            if($subwayArr) {
                foreach($subwayArr as $subwayItem) {
                    if($subwayItem != '') {
                        $row = array(
                            'station' => $subwayItem,
                        );
                        
                        update_row('metro_station', 0, $row, $post_id);  
                    }
                }
            }

            // время до метро пешком
            if($walking_access_subway_ar) {
                foreach($walking_access_subway_ar as $walkingAccessItem) {
                    if($walkingAccessItem != '') {
                        $row = array(
                            'time' => $walkingAccessItem,
                        );
                        
                        update_row('access_to_subway_walking', 0, $row, $post_id);  
                    }
                }
            }

            // время до метро на транспорте
            if($transport_access_subway_ar) {
                foreach($transport_access_subway_ar as $transportAccessItem) {
                    if($transportAccessItem != '') {
                        $row = array(
                            'time' => $transportAccessItem,
                        );
                        
                        update_row('access_to_subway_transport', 0, $row, $post_id);  
                    }
                }
            }

            if($specials) {
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
                'post_title'    => 'WINNER ' . $itemTitle,
                'post_content'  => $item['note'],
                'post_name'     => $itemSlug,
                'post_status'   => 'publish',
                'post_type'     => 'objects',
                'post_author'   => get_current_user_id(),
                'post_date'     => $update_datetime,
            ];

            $post_id = wp_insert_post(wp_slash($post_data));

            wp_set_post_terms($post_id, 'Вторичное', 'obj_categories', true); // добавляю категорию
            wp_set_post_terms($post_id, 'Квартира', 'obj_tags', true); // добавляю теги

            updateField('address', $address, $post_id);
            updateField('price', $price, $post_id);
            updateField('rooms', $totalRoomCount, $post_id);
            updateField('square', $totalSquare, $post_id);
            updateField('current_floor', $storey, $post_id);
            updateField('all_floor', $storeys_count, $post_id);
            updateField('media_name', $media_name, $post_id);
            updateField('kitchen_square', $kitchen_square, $post_id);
            updateField('life_square', $life_square, $post_id);
            updateField('is_studio', 1, $post_id);
            updateField('is_apartment', 1, $post_id);
            updateField('is_free_planning', 1, $post_id);

            // станции метро
            if($subwayArr) {
                foreach($subwayArr as $subwayItem) {
                    if($subwayItem != '') {
                        $row = array(
                            'station' => $subwayItem,
                        );
                        
                        update_row('metro_station', 0, $row, $post_id);  
                    }
                }
            }

            // время до метро пешком
            if($walking_access_subway_ar) {
                foreach($walking_access_subway_ar as $walkingAccessItem) {
                    if($walkingAccessItem != '') {
                        $row = array(
                            'time' => $walkingAccessItem,
                        );
                        
                        update_row('access_to_subway_walking', 0, $row, $post_id);  
                    }
                }
            }

            // время до метро на транспорте
            if($transport_access_subway_ar) {
                foreach($transport_access_subway_ar as $transportAccessItem) {
                    if($transportAccessItem != '') {
                        $row = array(
                            'time' => $transportAccessItem,
                        );
                        
                        update_row('access_to_subway_transport', 0, $row, $post_id);  
                    }
                }
            }

            if($specials) {
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
}