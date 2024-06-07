<?php
header("Content-Type: application/json; charset=utf-8");
include "standalone.php";

class TreeExporter
{
    private $exportData;

    public function __construct()
    {
        $this->exportData = [];
    }

    public function getTreeExport($id)
    {
        $this->processChildElements($id);
        return $this->exportData;
    }

    private function processChildElements($parentId)
    {
        $children = $this->getChildElements($parentId);
        foreach ($children as $child) {
            $element = new umiHierarchyElement($child['child_id']);
            if ($element && $this->isValidElement($element)) {
                $this->exportData[] = $this->prepareElementData($element);
                $this->processChildElements($child['child_id']);
            }
        }
    }

    private function getChildElements($parentId)
    {
        $sql = "SELECT * FROM cms3_hierarchy WHERE rel = $parentId ORDER BY obj_id ASC";
        $result = l_mysql_query($sql);
        return mysql_fetch_all($result);
    }

    private function isValidElement($element)
    {
        return $element->getIsActive() && !$element->getIsDeleted();
    }

    private function prepareElementData($element)
    {
        $dataModule = cmsController::getInstance()->getModule('data');
        return [
            "CODE" => $element->getAltName(),
            "ID" => $element->getId(),
            "PARENT" => $element->getParentId(),
            "ORDER" => $element->getOrd(),
            // Другие поля элемента
        ];
    }
}

$treeExporter = new TreeExporter();
$data = $treeExporter->getTreeExport(50);
echo json_encode($data);
