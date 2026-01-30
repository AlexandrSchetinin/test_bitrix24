<?php
use Bitrix\Crm\DealTable;
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler(
    'crm',
    'OnBeforeCrmDealUpdate',
    'onBeforeDealUpdate'
);

function onBeforeDealUpdate(&$arFields)
{
    $dealId = $arFields['ID'];

    $dealResult = DealTable::getList([
        'select' => ["ID", 'SOURCE_ID', 'ASSIGNED_BY_ID'],
        'filter' => ['ID' => $dealId],
    ]);
    $deal = $dealResult->fetch();

    if ($deal['SOURCE_ID'] === 'TRADE_SHOW' && (int)$arFields["MODIFY_BY_ID"] === (int)$deal['ASSIGNED_BY_ID']) {
        $allowedFields = [
            'STAGE_ID',
            "ID",
            'ASSIGNED_BY_ID',
            '~DATE_MODIFY',
            'MODIFY_BY_ID',
            'IS_MANUAL_OPPORTUNITY',
            "STAGE_SEMANTIC_ID",
            "IS_NEW",
            "MOVED_BY_ID",
            "MOVED_TIME",
        ];

        foreach ($arFields as $field => $value) {
            if (! in_array($field, $allowedFields)) {
                $arFields['RESULT_MESSAGE'] = 'Редактирование полей запрещено для сделок с источником "Выставка".';
                $GLOBALS['APPLICATION']->ThrowException('Редактирование полей запрещено для сделок с источником "Выставка".');

                return false;
            }
        }
    }
    return true;
}
