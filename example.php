<?php
// т.к. отдаем в формате json что бы нормально прринимать на другой стороне
header("Content-Type:application/json; charset=utf-8");
// подключаем umi
include "standalone.php";

$out = array();

/**
 * Основная фнукция получения элементов
 * @param int $ID ID раздела относительно которого будет строиться дерево на экспорт
 * @return array|bool
 */
function getTreeExport($ID = 0)
{
    if (intval($ID) == 0) {
        return false;
    }

    /**
     * Получаем детей элемента
     */
    $sql = "SELECT * FROM  cms3_hierarchy WHERE  rel = $ID ORDER BY  cms3_hierarchy.obj_id ASC";
    $result = l_mysql_query($sql);

    while (list($child_id, $rel_id) = mysql_fetch_row($result)) {
        if ($rel_id == $ID) {
            // получаем объект элемента
            $elem = $element = new umiHierarchyElement($child_id);
            if ($elem
                && $elem->getIsActive()  // если элемент активен
                && !$elem->getIsDeleted() // если элемент не удален
                //&& $elem->getObjectTypeId() == 9 // если элемент нужно типа
            ) {
                $dataModule = cmsController::getInstance()->getModule('data');
                $out[] = array(
                    "CODE" => $elem->getAltName(), // код элемента которые используется для адреса
                    "ID" => $child_id, // ID Элемента
                    "PARENT" => $elem->getParentId(), // ID родительского элемента
                    "ORDER" => $elem->getOrd(), // порядок элемента относительн соседей
                    "NAME" => $elem->getName(), // Название
                    "TYPE" => $elem->getHierarchyType()->getTitle(), // Название типа
                    "TYPE_ID" => $elem->getObjectTypeId(), // ID типа данных
                    "META_TITLE" => $elem->getValue('title'), // Заголовок браузера
                    "META_KEYWORDS" => $elem->getValue('meta_keywords'), // Ключевые слова
                    "META_H1" => $elem->getValue('h1'), // Заголовок страницы
                    "META_DESCRIPTION" => $elem->getValue('meta_descriptions'), // Описание страницы
                    //"SOME_PROPERTY" => $elem->getValue('code_property'), // выбока любого свойства по его коду
                    "IMAGE" => ($elem->getValue('image') instanceof umiFile) ? $elem->getValue('image')->getFilePath(true) : '', // выбор произвольного свойства типа файл
                    "COLLECTION" => array(
                        "NAME" => $dataModule->getProperty($child_id, 'collection', '.default'),
                        "ID" => $elem->getValue('collection')
                    ), // выборка произвольного свойства как справочника (будет выбран ID и само значение )
                    "CHILDS" => getTreeExport($child_id) // получаем детей в рекурсии пока они есть
                );
            }
        }
    }
    return $out;
}

/**
 * Получаем данные
 */
$out = getTreeExport(50);

/**
 * Отдаем даныне в виде JSON
 */
echo json_encode($out);
