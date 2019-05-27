<?php

/******************************************************************************
 * Catlair PHP Copyright (C) 2019  a@itserv.ru
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 ******************************************************************************
 *
 * Public library for speech.
 * Based on festival & lame
 *
 ******************************************************************************
 */

function SpeechPublic()
{
    clBeg('Speech begin');

    global $clSession;
    // Получение параметров
    $IDLang = clGetLang(clGetIncome('IDLang', null, null));
    $IDSite = clGetIncome('IDSite', null,  $clSession->GetSite());
    $Text = clGetIncome('text', null, null);
    $Voice = clGetIncome('voice', null, null);

    switch ($Voice)
    {
        case '1': $VoiceName = 'voice_cmu_us_slt_cg'; break;
        case '2': $VoiceName = 'voice_cmu_us_aup_cg'; break;
        case '3': $VoiceName = 'voice_cmu_us_fem_cg'; break;
        case '4': $VoiceName = 'voice_cmu_us_awb_cg'; break;
        case '5': $VoiceName = 'voice_cmu_us_ksp_cg'; break;
        case '6': $VoiceName = 'voice_kal_diphone'; break;
        default: $VoiceName = 'voice_cmu_us_fem_cg'; break;
    }

    if ($Text != null && $VoiceName != null)
    {
        $FilePath = clSitePath($IDSite) . '/speech';
        if (!file_exists($FilePath)) mkdir($FilePath, FILE_RIGHT, true);
        $FileName = $FilePath . '/' . md5($Text.$VoiceName);
        if (!file_exists($FileName.'mp4'))
        {
            /* interface for festival */
            $Result = [];
            $Command = 'echo "'.$Text.'" | text2wave -eval "('.$VoiceName.')" -o ' . $FileName . '.wav';
            exec($Command, $Result);
            clDump('Result', $Result);
            /* convert to mp3 */
            $Command = 'lame ' . $FileName . '.wav';
            exec($Command, $Result);
            clDump('Result', $Result);
            unlink($FileName.'.wav');
        }
        clSendFile($Text,$FileName.'.mp3');
    }

    clEnd('Speech end');
    return true;
}
