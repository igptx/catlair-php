<?php

/******************************************************************************
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * 30.03.2019
 *
 * still@itserv.ru
 */

/**
 * Поиск десприптов
 * Возвращает в $AResult перечень дескриптов в соответсвии с условиями
 */
function DescriptLoadPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;

    /*List of conditions*/
    $IDLang = clGetLang(clGetIncome('IDLang', $AParams, null));
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $ID = clGetIncome('ID', $AParams, null);
    $IDType = clGetIncome('IDType', $AParams, null);
    $RecordCurrent = clGetIncome('RecordCurrent', $AParams, 0);
    $RecordCount = clGetIncome('RecordCount', $AParams, 5);
    $IDParent = clGetIncome('IDParent', $AParams, null);
    $IDBind = clGetIncome('IDBind', $AParams, BIND_DEFAULT);

    /* Create find string */
    $Find = rawurldecode(clGetIncome('Find', $AParams, ''));
    if ($ID!=null) $Find .= ' ' . clIndexIDString($ID);
    if ($IDType!=null) $Find .= ' ' . clIndexTypeString($IDType);
    $Find = trim($Find);

    /* Построение списка дескриптов и возврат в виде записей */
    $Descripts = new TDescripts();
    $Descripts->LoadFromIndex($IDSite, $IDLang, $Find, $IDParent, $IDBind);
    $Descripts->SelectDescript($IDSite, $IDLang, 'ID', $IDBind); /* получение данных дескрипта */
    $Descripts->SelectChildsCount($IDSite, 'ID', $IDBind); /* получение данных дескрипта */
    $Descripts->Sort('Caption', 1); /* Сортировка по Caption */

    /*Общее количество записей*/
    $AResult->SetGroup('Records', 'RecordTotal', $Descripts->RecordCount());

    $Descripts->Cut($RecordCurrent, $RecordCount); /* отсекли только необходимые для пагинации */
    $Descripts->SelectCaptionType($IDSite, $IDLang, 'ID', 'CaptionType'); /* получение caption */
    $Descripts->Set(['IDParent'=>$IDParent]); /* Прописали всем текущего запаршиваемого парента */
    $Descripts->BuildResult($AResult, 'Records');

    // Сохранение параметров
    $AResult->SetGroup('Records', 'RecordCurrent', $RecordCurrent+$Descripts->RecordCount());

    unset($Descript);

    /* Завершение */
    $AResult->SetCode(rcOk);
    clEnd('');
    return true;
}
