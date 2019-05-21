<?php

/******************************************************************************
 * Catlair PHP
 *
 * Базовая библиотека Catlair PHP
 * вызываемая точка входа ContentBuild(). С нее начинается посроение контента.
 *
 *
 * still@itserv.ru
 */

define("XML_HEADER",   '<?xml version="1.0" encoding="UTF-8"?>'); // заголовок xml для всех операций

define("LANG_DEFAULT", 'language_ru'); // идентификатор умолчального языка
define("LANG_UNKNOWN", 'language_unknown'); // идентификатор умолчального языка

define("TYPE_UNKNOWN", 'Unknown'); // неопределенный идентификатор дескрипта
define("TYPE_DEFAULT", 'Descript'); // умолчальный идентификатор дескрипта
define("TYPE_SITE",    'Site'); // умолчальный идентификатор дескрипта
define("TYPE_DOMAIN",  'Domain'); // умолчальный идентификатор дескрипта

define("SITE_DEFAULT", 'site_default'); // идентификатор умолчального сайта
define("SITE_UNKNOWN", 'site_unknown'); // идентификатор неизвестного сайта

define("BIND_DEFAULT", 'bind_default'); // идентификатор умолчальной связи
define("BIND_RIGHT",   'bind_right'); // идентификатор cсвязи прав

define("ACCOUNT_ROOT", 'root'); // идентификатор cсвязи прав
define("FILE_RIGHT",   0770); // файловые права на вновь создаваемые папки в fs

define("FOLDER_HOME",   'home');
define("FOLDER_TRASH",  'trash');
define("FOLDER_IMPORT", 'import');
define("DOMAIN_UNKNOWN",'domain_unknown');

define("JPEG_QUALITY", 50);

/*****************************************************************************
 * конфигурация cl
 */
$clConfig = array
(
    'CatlairVersion' => '0.3', // версия
    'RecursDepth' => 50,  // глубина рекурсии при построении
    'Title' => 'Default CatlairPHP Page',
    'ServerPath' => $_SERVER['DOCUMENT_ROOT'],
    'ContentType' => 'text/html; charset=UTF-8'
);



/* код ответа html */
$HTMLResponceCode = 0;



/**
 * установка кода ответа html
 */
function clSetHTMLResult($ACode)
{
    global $HTMLResponceCode;
    $HTMLResponceCode = $ACode;
}



/**
 * установка кода ответа html
 */
function clGetHTMLResult()
{
    global $HTMLResponceCode;
    return $HTMLResponceCode;
}

/******************************************************************************
 * Определение файловых путей для Catliair
 */


/**
 * получение корневого пути Catlair
 */
function clRootPath()
{
    if (php_sapi_name()=='cli')
    {
        // Path for CLI interface
        $Result = '/var/www/html/catlair';
    }
    else
    {
        // Path for CGI interface
        $Result = '/var/www/html/catlair';
    }
    return $Result;
}



/**
 * получение пути до сайта
 */
function clSitePath($ASite)
{
    $Result = clRootPath() . '/site/' . $ASite;
    if (!file_exists($Result)) mkdir($Result, FILE_RIGHT, true);
    return $Result;
}



/*
 * возвращает путь для кэша
 */
function clCachePath($ASite)
{
    $Result = '/tmp/catlair/'.$ASite.'/cache';
    return $Result;
}



/*
 * Загрузка контента из файла для текущей сессии
 * учитывает сессионные переменные
 */



/*
 * Замещает макроподстановки в контенте
 */

function ContentReplace (&$AContent)
{
    global $clConfig;
    $Search = array();
    $Replace = array ();
    // параметры из GET
    foreach ($_GET as $Key => $Value)
    {
        array_push($Search, '%' . $Key . '%');
        array_push($Replace, $Value);
    }
    // параметры из POST
    foreach ($_POST as $Key => $Value)
    {
        array_push($Search, '%' . $Key . '%');
        array_push($Replace, $Value);
    }
    // параметры из $clConfig
    foreach ($clConfig as $Key => $Value)
    {
        array_push($Search, '%'.$Key.'%');
        array_push($Replace, $Value);
    }
    $AContent = str_replace($Search, $Replace, $AContent);
}



/**********************************************************************
 * Функция переключения сайта в процессе испольнения скрипта
 */

function clSiteChange($ASite)
{
    global $clConfig;
    global $clSession;
    if ($clSession->GetSite() != $ASite)
    {
        $clSession->SetSite($ASite);
        $PHPFile = $clConfig['ContentPath'].'/'.$ASite.'/php/Main.php';
        // Подгрузка файла специфических библиотек
        if (file_exists($PHPFile)) include $PHPFile;
    }
}


//-----------------------------------------------------------
// Исполнение функционала clexec
//-----------------------------------------------------------


function ContentExec(&$Bf, $Command, $AValue, $Params, $Source)
{
    clBeg('');
    $r = array('NoError'=>false, 'Message'=>'', 'Error'=>'');
    switch (strtolower($Command))
    {

        default:
        if ($AValue)
            {
                $Bf['Content'] = str_replace ('%'.$Command.'%', $AValue, $Bf['Content']);
            }
            else
            {
                // Неизвестный ключ и при этом не строка для подмены в CL
                $r['Error'] = 'UnknownKey';
                $r['Message'] = 'Unknown key <b>'.$Command.'</b> (cl; set; add; file; replace; convert; exec; header)';
            }
            break;

        // Пустой тэг ничего не делаешь
        case 'cl':
            break;

        // Принудительный парсинг
        case 'pars':
            if ($AValue) $Value = $AValue;
            else $Value = (string) $Params['value'];
            if ($Value == 'true') $Bf['Content'] = ContentPars($Bf['Content'], 0);
            break;

        // Установка нового контента из Value
        case 'set':
            if ($AValue) $Bf['Content'] = $AValue; else
            {
                if ($Params['value']) $Bf['Content'] = $Params['value']; else
                {
                    $r['Error'] = 'ParamNotFound';
                    $r['Message'] = 'Parameter <b>value</b> not found';
                }
            }
            break;

        // Добавлениеа контента из Value
        case 'add':
            if ($AValue) $Bf['Content'] = $AValue; else
            {
                if ($Params['value']) $Bf['Content'] = $Params['value']; else
                {
                    $r['Error'] = 'ParamNotFound';
                    $r['Message'] = 'Parameter <b>value</b> not found';
                }
            }
            break;

        // Получение контента и внесение его в буффера
        case 'file':
        case 'content':
            if ($AValue) $ID = (string) $AValue;
            else $ID = (string) $Params['id'];
            if ($ID!='none')
            {
                // Добыча сессионных параметров
                global $clSession;
                $IDSite = $clSession->GetSite();
                $IDLang = clGetLang(null);
                // добыча контента
                $Bf['Content'] .= clDescriptContentByID($ID, $IDSite, $IDLang);
            }
            break;



        // Построение ссылки на основании шаблона <link template="Link" id="H" target="_blank"/>
        case 'link':
            if ($AValue) $IDTemplate = (string) $AValue;
            else $IDTemplate = $Params['template'];
            // Добыча сессионных параметров
            global $clSession;
            $IDSite = $clSession->GetSite();
            $IDLang = clGetLang(null);

            $Link = new TDescript();
            $Result = $Link -> Read($IDTemplate, $IDSite);
            if ($Result==rcOk)
            {
                $Content = (string) $Link->ContentRead($IDLang); /* контент ссылки  */
                /* направление вывода target */
                $Target = trim((string) $Params['target']);
                if ($Target=='') $Target='_self';
                /* подмены */

                $ID = trim((string) $Params['id']); /* идентификаор выводимый */
                $Descript = new TDescript();
                $Result = $Descript->Read($ID, $IDSite);
                if ($Result==rcOk) $Caption = $Descript->GetLang($IDLang, 'Caption', $ID);
                else $Caption=$ID;
                unset($Descript);

                $Content = str_replace('%Target%', $Target, $Content);
                $Content = str_replace('%ID%', $ID, $Content);
                $Content = str_replace('%Caption%', $Caption, $Content);
                /* возвращение результата */
                $Res['Content'] = $Content;
                $Bf['Content'] .= $Res['Content'];
            }
            else
            {
                $r['Error'] = $Res['Error'];
                $r['Message'] = $Res['Message'];
            }
            unset($Link);
            break;



        case 'url':
            $Search = array();
            $Replace = array ();
            // параметры из
            foreach ($clURL as $Key => $Value)
            {
                array_push($Search, '%'.$Key.'%');
                array_push($Replace, $Value);
            }
            $Bf['Content'] = str_replace($Search, $Replace, $Bf['Content']);
            break;

        // Подмена параметров
        case 'replace':
            $Bf['Content'] = str_replace($Params['from'], $Params['to'], $Bf['Content']);
            break;

        // Изменяет конфигурационные параметры которые используются при отсуствии сессионных
        case 'config':
            global $clConfig;
            foreach ($Params->attributes() as $Key => $Value)
            {
                switch ($Key)
                {
                    case 'language': $clConfig['Language'] = (string) $Value; break;
                    case 'title': $clConfig['Title'] = (string) $Value; break;
                    case 'start': $clConfig['Start'] = (string) $Value; break;
                    case 'url_default':
                    {
                        $clConfig['URLDefault'] = urldecode( $Value );
                        // Обновление массива clURL если нет родного URL
                        if (count ($_GET) == 0)
                        {
                            parse_str($clConfig['URLDefault'], $URL);
                            foreach ($URL as $Key => $Value) $_GET[$Key] = (string) $Value;
                        }
                    }
                }
            }
            break;

        // Изменяет текущий параметр сессиии
//        case 'session':
//            global $clSession;
//            foreach ($Params->attributes() as $Key => $Value)
//            {
//                switch ($Key)
//                {
//                case 'site': clSiteChange((string)$Value); break;
//                case 'language': $clSession->SetLanguage((string) $Value); break;
//                }
//            }
//            break;

        // Массовая подмена
        case 'masreplace':
            $Search = array();
            $Replace = array ();
            foreach ($Params->attributes() as $Key => $Value)
            {
                array_push($Search, '%'.$Key.'%');
                array_push($Replace, $Value);
            }
            $r['Content'] = str_replace($Search, $Replace, $Content);
            break;

        case 'optimize':
            case 'pure': $Bf['Content'] = preg_replace('/  +/','', preg_replace('/[\r\n]/',' ',$Bf['Content'])); break;
            break;

        // Конвертация контента в один из форматов clear; html; uri; md5
        case 'convert':
            if ($AValue) $To = $AValue;
            else $To = $Params['to'];
            $To=strtolower($To);
            switch ($To)
            {
                case 'clear': $Bf['Content'] = ''; break;
                case 'html': $Bf['Content'] = htmlspecialchars ($Bf['Content']); break;
                case 'pure': $Bf['Content'] = preg_replace('/  +/','', preg_replace('/[\r\n]/',' ',$Bf['Content'])); break;
                case 'uri': $Bf['Content'] = encodeURIComponent ($Bf['Content']); break;
                case 'md5': $Bf['Content'] = md5 ($Bf['Content']); break;
                case 'default':; break;
                default:
                    $r['Error']='UnknownConvert';
                    $r['Message']='Unknown convert mode <b>' . $To . '</b> (clear; html; uri; md5)';
                    break;
            }
            break;

        // Подключение блиблиотеки
        case 'include':
            if ($AValue) $PrefixLib = $AValue;
            else $PrefixLib = $Params['name'];
            global $clSession;
            $IDSite = $clSession->GetSite();

            if ($PrefixLib != '%library%')
            {
                $FileName = clLibraryFileAny($PrefixLib . '_public',  $IDSite);
                if ($FileName)
                {
                    try
                    {
                        include ($FileName);
                    }
                    catch (Exception $e)
                    {
                        $r['Error'] = 'UnknownLibrary;';
                        $r['Message'] = 'PHP library not loaded ' . $e->getMessage();
                    }
                }
            }
            break;

        // Выполнение PHP функции
        case 'exec':
            clDeb('exec ');
            if ($AValue) $NameFunc = $AValue;
            else $NameFunc = $Params['name'];
            $NameFunc .= 'Public';
            if (function_exists($NameFunc))
            {
                $Result = new TResult();
                $Result -> SetContent ($Bf['Content']);
                call_user_func_array($NameFunc,  array($Params, $Result));
                $Bf['Content'] = $Result->End();
            }
            else
            {
                $r['Error'] = 'UnknownFunction;';
                $r['Message'] = 'Function <b>' . $NameFunc . '</b> not found.';
            }
            break;

        // запись заголовка HTTP
        case 'header':
            if ($AValue) $St = $AValue;
            else $St = $Params['value'];
            header($St);
            break;

        case 'redirect':
            if ($AValue) $URL = $AValue;
            else $URL = $Params['url'];
            if ($URL) header('Location: '.$URL);else
            {
                $r['Error']='ParamererNoFound';
                $r['Message']='Parameter <b>url</b> not found';
            }
            break;

        // Подавление ошибки
        case 'error':
            if ($AValue) $Value = $AValue;
            else $Value = $Params['value'];
            if (strtolower($Value) == 'false')
            {
                $r['Error'] = '';
                $r['Message'] = '';
            }
            break;
    }


    if ($r['Error'])
    {
        $Params = $Source->getName();;
        foreach ($Source->attributes() as $Key => $Value) $Params.= ' ' . $Key . '="' . $Value . '"';
        $Bf['Content'] .= Error($r['Error'], $r['Message'], '<' . $Params . '/>');
    }

    clEnd('');
}



function Error($ACode, $AMessage, $ASource)
{
    return '<div style="color:red; padding:1em;"><div><b>'.$ACode.
           '</b></div><div>'.$AMessage.
           '</div><div style="font-weight:bolder;">Source</div><code style="display:block; white-space:pre-wrap;">'.
           htmlspecialchars($ASource).'</code></div>';
}



/*
 * Рекурсивная обработка строки ключа
 */
function BuildElement (&$Buffer, &$AElement, $ARecursDepth)
{
    // Обработка строки как ключа с параметрами
    ContentExec ($Buffer, $AElement->getName(), null, $AElement, $AElement);
    if ($AElement->getName() =='cl')
    {
        // Обработка директив в паре ключ-параметр
        foreach ($AElement->attributes() as $Key => $Value) ContentExec($Buffer, $Key, $Value, $AElement, $AElement);
    }
    // Обработки для возможных вложенных CL
    $Buffer['Content'] = ContentPars($Buffer['Content'], $ARecursDepth);
    // Обработка дочерних строк с ключами
    foreach ($AElement -> children() as $Line => $Param) { BuildElement ($Buffer, $Param, $ARecursDepth); }
}


//-----------------------------------------------------------
// Рекурсивный парсинг контента
//-----------------------------------------------------------
function ContentPars($AContent, $ADepth)
{
    global $clConfig;
    $ADepth = $ADepth + 1;
    if ( $ADepth < $clConfig['RecursDepth'] )
    {
        do
        {
            // Получение списка тэговРегулярное выражение выгребающее тэг cl
            preg_match('/\<cl(?:(\<)|".+?"|.|\n)*?(?(1)\/cl|\/)\>/', $AContent, $m, PREG_OFFSET_CAPTURE);
            if (count($m) > 0)
            {
                $b = $m[0][1];
                $l = strlen ($m[0][0]);
                $Source = $m[0][0];
                if ( $l > 0 )
                {
                    ContentReplace ($Source);
                    $XMLSource = '<?xml version="1.0"?>' . $Source;
                    $XML = simplexml_load_string ($XMLSource);
                    if ( $XML )
                    {
                        $Buffer = array ('Content'=>'', 'Method'=>'replace');
                        BuildElement($Buffer, $XML, $ADepth);
                        $Result=$Buffer['Content'];
                    }
                    else
                    {
                        $Result = Error('XMLError', 'Error XML pars', $XMLSource);
                    }
                    // Проверка ошибки рекурсии
                    if ( $ADepth+1 == $clConfig['RecursDepth'] ) $Result .= Error('Recurs', 'Recursion depth limit '.$ADepth, $XMLSource);
                    $AContent = trim(substr_replace ($AContent, $Result, $b, $l));
                }
            }
        } while ( count($m)>0 );
    } else $AContent='';  // if recursion
    return $AContent;
}



/*
 * Начало построителя контента
 */

function ContentBuild()
{
    global $clSession;
    global $clConfig;
    global $clLoger;

    /* chouse domain name and site */
    if (array_key_exists ('domain', $_GET))
    {
        /* выбор домена из URL */
        $DomainName = trim($_GET['domain']);
        unset($_GET['domain']);
    }
    else
    {
        /* выбор доменного имени из сессии */
        $DomainName = $clSession->Get('Domain', DOMAIN_UNKNOWN);
        if ($DomainName == DOMAIN_UNKNOWN)
        {
            /* Выбор доменного имени через имя хоста */
            $DomainName = trim($_SERVER['HTTP_HOST']);
        }
    }

    /* чтение параметров по доменному имени и запись в сессию */
    $Domain = new TDomain();
    $IDSite = $Domain->ReadByName($DomainName);
    if ($IDSite!=SITE_UNKNOWN)
    {
        /* Сайт определен по домену */
        $DefaultURL = $Domain->GetArrayValue('Post','DefaultURL','&body=Body.html&page=Page.html');
        $Start = $Domain->GetArrayValue('Post','IDContentStart','Main.html');
    }
    else
    {
        /* Сайт не удалось определить по домену */
        $IDSite = SITE_DEFAULT;
        // http_response_code(404);
        $DefaultURL = $Domain->GetArrayValue('Post','DefaultURL','&body=404.html');
        $Start = $Domain->GetArrayValue('Post','IDContentStart','Error.html');
    }

    /* chouse language */
    if (array_key_exists ('idlang', $_GET))
    {
        /* выбор домена из URL */
        $IDLang = $_GET['idlang'];
        unset($_GET['idlang']);
    }
    else
    {
        $IDLang = clGetLang(null);
        if ($IDLang == LANG_UNKNOWN) $IDLang = $Domain->GetArrayValue('Post', 'IDLangDefault', LANG_DEFAULT);
    }

    unset($Domain);


    /* Чтение данных из сайта */
    $Site = new TDescript();
    if ($Site->Read($IDSite, $IDSite) == rcOk)
    {
        /* Установка параметров сайта */
        $clConfig['Favicon'] = $Site->GetIDImage('DEFAULT_FAVICON');
        $clConfig['Title'] = $Site->GetLang($IDLang, 'Caption', 'Catlair');
        /* Установка параметров логирования */
        $clSession->Set('LogEnabled', $Site->GetArrayValue('Post','LogEnabled','off') == 'on');
        $clSession->Set('LogInfo',  $Site->GetArrayValue('Post','LogInfo','off') == 'on');
        $clSession->Set('LogDebug',  $Site->GetArrayValue('Post','LogDebug','off') == 'on');
        $clSession->Set('LogWarning', $Site->GetArrayValue('Post','LogWarning','off') == 'on');
        $clSession->Set('LogError', $Site->GetArrayValue('Post','LogError','off') == 'on');
        $clSession->Set('LogJob', $Site->GetArrayValue('Post','LogJob','off') == 'on');
    }
    unset($Site);


    /* Формирование URL из имеющегося GET и умолчальных настроек в случае отсутсвия GET */
    if (count ($_GET) == 0)
    {
        parse_str($DefaultURL, $clURL);
        foreach ($clURL as $Key => $Value) $_GET[$Key] = $Value;
    }

    clBeg('Begin build content');
    clDeb('Domain name ['.$DomainName.']');
    clDeb('ID site ['.$IDSite.']');
    clDeb('ID lang ['.$IDLang.']');

    /* сохранение сессионных переменных  */
    $clSession->SetSite($IDSite);
    $clSession->SetLanguage($IDLang);
    $clSession->SetDomain($DomainName);

    /* Начало сборки контента */
    $Content = '';
    /* Обработка входящих параметров file image template */
    if (array_key_exists ('file', $_GET))
    {
        $f = new TFile();
        if ($f->Read($_GET['file'], $IDSite) == rcOk ) $f->Send();
        unset($f);
    }
    else
    {
        if (array_key_exists ('image', $_GET))
        {
            $f = new TFile();
            if ($f->Read($_GET['image'], $IDSite) == rcOk ) $f->SendImage();
            unset($f);
        }
        else
        {
            /* выбор стартового контента */
            if (array_key_exists ('template', $_GET)) $Start = $_GET['template']; // чтение из параметра template
            $Content =  ContentPars('<cl content="' . $Start . '"/>', 0);
            /* Финальная подмена (надо подумать как ее не делать) */
            ContentReplace($Content);
        }
    }

    clEnd('End build content');

    return $Content;
}
