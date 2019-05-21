<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * still@itserv.ru
 */


/*
 * Пользовательское создание новго дескрипта из формы
 */
function FileUploadPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $Lang = clGetLang(clGetIncome('IDLang', $AParams, null));
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $IDParent = clGetIncome('IDParent', $AParams, null);
    $ID = clGetIncome('ID', $AParams, null);
    $Caption = clGetIncome('Caption', $AParams, $ID);

    clDeb('Received files ['.count($_FILES).']');

    if ($IDParent == null && $ID == null) $Result = 'UnknownParentAndDescript';
    else
    {
        clBeg('Processing');
        foreach ($_FILES as $File)
        {
            $d = new TFile();
            $Result='Unknow';
            // Пытаемся прочитайть файл с указанными идентификаторм
            // что бы потом грузить в него
            if ($ID != null) $Result = $d->Read($ID, $Site);
            // Если по ID не прочится генерим идентификатор из имени файла
            if ($Result!=rcOk)
            {
                // Создаем новое имя и читаем его еще раз
                $NewID = $File['name'];
                $Result = $d->Read($NewID, $Site);
                // если файл открылся надо проверить лежит ли он в переданной папке
                if ($Result == rcOk)
                {
                    $Result = $d->ParentRead();
                    if (!$d->ParentExist($IDParent, BIND_DEFAULT)) $Result='NeedCreate';
                }

                // если что то пошло не так с имением $NewID  но дескрипт все же есть
                if ($Result != rcOk && clDescriptExists($Site, $NewID))
                {
                    // генерим новое имя
                    $Ext = pathinfo($NewID, PATHINFO_EXTENSION);
                    $Name =  pathinfo($NewID, PATHINFO_FILENAME);
                    $NewID = clGUID() . '.' . $Ext;
                }

                if ($Result != rcOk)
                {
                    // Формируем новый дескрип
                    $Result = $d->Create($NewID, TYPE_FILE, $Site);
                    if ($Result == rcOk)
                    {
                        // Установка связей для родителя
                        $Parent=new TDescript();
                        $Parent->Read($IDParent, $Site);
                        $Result=$d->BindBegin($Parent);
                        $Result=$d->Bind($Parent, BIND_DEFAULT, false);
                        $Result=$d->Bind($Parent, BIND_RIGHT, false);
                        $Result=$d->BindEnd($Parent);
                        unset($Parent);
                    }
                }
            }

            // Если файл успешно подготовлен и ошибок нет при привязке то прописываем параметры
            if ( $Result == rcOk && $d->Prepared())
            {
                // Запись параметров дескрипта
                $d->SetLang(LANG_DEFAULT, 'Caption', $File['name']);
                $Result = $d->Import($File['tmp_name'], $Lang);
                if ($Result == rcOk)
                {
                    // Выполняем сохранение дескрипта
                    $Result = $d->Flush();
                    if ($Result == rcOk)
                    {
                        // Удаляем кэш файла если он графика
                        $d->CacheDelete($Lang);
                        // Возвращаем результа записи
                        $AResult->Set('ID', $d->ID);
                        $AResult->Set('IDParent', $IDParent);
                    }
                }
            }
            // Разрушаем дескрипт
            unset($d);
        }
        clEnd('Processing end');
    }

    // обработка резульата
    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}
