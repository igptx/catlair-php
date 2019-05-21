<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 * Получение информации о дескрипе
 *
 * still@itserv.ru
 */



/**
 * Строит форму для редактирования дескрипта
 */

function DescriptPreparePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // получение параметров
    $Lang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $Suffix = clGetIncome('FormSuffix', $AParams, null);
    $ID=clGetIncome('ID', $AParams, null);
    $ContentSize = clGetIncome('ContentSize', $AParams, CONTENT_NONE);

    // Если дескрипт передан то обрабатываем его
    if ($ID==null) $AResult->SetCode('EmptyIDDescript');
    else
    {
        // обработка дескрипта
        $d = new TDescript(); // Дескрипт
        // грузим дескрипт
        if ($d->Read($ID, $Site)!=rcOk) $AResult->SetCode('UnknownDescript');
        else
        {
            // для дефаултного просто вызываем функцию
            $d->ContentBuild($Lang, $Site, $AResult);

            // получаем контент сли он запрошен
            if ($ContentSize != CONTENT_NONE)
            {
                $Content = $d->ContentRead($Lang);
                if ($ContentSize != CONTENT_ALL) $Content = $substr($Content, 0, $AContentSize);
                $AResult->Set('ContentBody', $Content);
            }

            // Закидываем в результат значения массива Post
            $d->ArrayToResult('Post', $AResult);

            // Подгрузка формы специифического контента
            if ($Suffix!=null)
            {
                $IDForm = $d->Type . $Suffix;
                if (!clDescriptExistsAny($Site, $IDForm)) $IDForm = TYPE_DEFAULT . $Suffix;
                $FormContent = clDescriptContentByID($IDForm, $Site, $Lang);
                $FormContent = ContentPars($FormContent,0);
                $AResult->Set('FormContent', $FormContent);
            }
            $AResult->SetCode(rcOk);
        }
        unset($d);
    }

    clEnd('');
    return true;
}
