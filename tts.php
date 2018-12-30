<?php
/**
 * PHP Version 5.4 and above
 *
 * Text-to-speech output using VoiceRSS API
 *
 * Register: http://www.voicerss.org/registration.aspx
 * Manual  : http://www.voicerss.org/api/documentation.aspx
 *
 * @category  PHP_Audio
 * @package   PHP_Easy_TTS
 * @author    P H Claus <phhpro@gmail.com>
 * @copyright 2016 - 2018 P H Claus
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @version   GIT: Latest
 * @link      https://github.com/phhpro/easy-tts
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */


/**
 ***********************************************************************
 *                                                   BEGIN USER CONFIG *
 ***********************************************************************
 */


//** API key
$tts_key = "ab9a40c825f9485d9cd74c3e1fc534bb";

//** Filesize and requests
$tts_fsx = 100000;
$tts_rex = 350;

//** Create new after interval -- 0 to keep forever
$tts_new = 1;

//** Interval before creating new -- requires $tts_new = 1;
$tts_int = strtotime("7 days", 0);

//** Audio codec, language, and bitrate
$tts_auc = "wav";
$tts_aul = "en-gb";
$tts_aub = "48khz_16bit_stereo";


/**
 ***********************************************************************
 *                                                     END USER CONFIG *
 ***********************************************************************
 */


//** Headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//** Referrer and script version
$tts_ref = $_SERVER['HTTP_REFERER'];
$tts_ver = 20181230;

//** Check referrer
if (!isset($tts_ref) || $tts_ref === "") {
    echo "Missing source!";
    exit;
} else {
    $tts_src = $tts_ref;
    $tts_get = file_get_contents($tts_src);
    $tts_reg = "#<!--tts-->(.*?)<!--/tts-->#s";
    preg_match_all($tts_reg, $tts_get, $tts_dat);
    $tts_raw = strip_tags($tts_dat[1][0]);
    $tts_str = str_replace(array("://", "/"), "_", $tts_src);
    $tts_aun = $tts_str . "_tts." . $tts_auc;

    //** Create new
    if ($tts_new === 1) {

        if (file_exists($tts_aun)
            && (time()-filemtime($tts_aun) >= $tts_int)
        ) {
            unlink($tts_aun);
        }
    }

    echo "<!DOCTYPE html>\n" .
         "<html lang=\"en-GB\">\n" .
         "    <head>\n" .
         "        <meta charset=\"UTF-8\"/>\n" .
         "        <meta name=language content=\"en-GB\"/>\n" .
         "        <meta name=viewport content=\"width=device-width, " .
         "height=device-height, initial-scale=1\"/>\n" .
         "        <meta name=description " .
         "content=\"PHP Easy TTS adds high quality text to speech " .
         "output to web pages\"/>\n" .
         "        <meta name=keywords " .
         "content=\"PHP Easy TTS,Text to Speech\"/>\n" .
         "        <meta name=robots " .
         "content=\"noindex, nofollow\"/>\n" .
         "        <title>PHP Easy TTS Speaking: $tts_src</title>\n" .
         "        <style>\n" .
         "        * {\n" .
         "            background-color: #ccc;\n" .
         "            color: #333;\n" .
         "            font-family: sans-serif;\n" .
         "            font-size: 105%;\n" .
         "            font-weight: normal;\n" .
         "            text-decoration: none;\n" .
         "        }\n\n" .
         "        #tts {\n" .
         "            background-color: #999;\n" .
         "            color: #333;\n" .
         "            font-size: 115%;\n" .
         "            text-align: justify;\n" .
         "            padding: 8px;\n" .
         "            border: 1px solid #666;\n" .
         "        }\n\n" .
         "        a {\n" .
         "            background-color: inherit;\n" .
         "            color: #33f;\n" .
         "        }\n\n" .
         "        a:hover, a:active, a:focus {\n" .
         "            background-color: inherit;\n" .
         "            color: #900;\n" .
         "        }\n" .
         "        </style>\n" .
         "    </head>\n" .
         "    <body>\n" .
         "        <h1 id=top>Source: <a href=\"$tts_src\" " .
         "title=\"Click here to view the original page\">" .
         "$tts_src</a></h1>\n" .
         "        <p>\n" .
         "            <audio src=\"$tts_aun\" autoplay controls " .
         "title=\"Click PLAY button to listen\"></audio></p>\n" .
         "        <div id=tts>";

    //** Check size and requests
    if (!file_exists($tts_aun)) {
        $tts_hdr = array_change_key_case(get_headers($tts_src, 1));
        $tts_len = $tts_hdr['content-length'];
        
        if ($tts_len <= $tts_fsx) {
            $tts_red = "requests_" . date('Y-m-d') . ".txt";

            if (is_file($tts_red)) {
                $tts_rec = (file_get_contents($tts_red)+1);
            } else {
                $tts_rec = 1;
            }

            if ($tts_rec >$tts_rex) {
                $tts_err = "Maximum daily requests!";
            } else {
                file_put_contents($tts_aun, $tts_raw);
                file_put_contents($tts_red, $tts_rec);

                include "./voicerss.php";

                $tts_api = new VoiceRSS;
                $tts_out = $tts_api->speech([
                    'key'  => $tts_key,
                    'hl'   => $tts_aul,
                    'src'  => $tts_raw,
                    'r'    => '0',
                    'c'    => $tts_auc,
                    'f'    => $tts_aub,
                    'ssml' => 'false',
                    'b64'  => 'false'
                    ]);

                file_put_contents($tts_aun, $tts_out['response']);
            }
        } else {
            $tts_err = "Source exceeds maximum filesize!";
        }
    }

    if (isset($tts_err)) {
        echo "        <p>$tts_err</p>\n";
    } else {
        echo $tts_raw;
    }

    echo "</div>\n" .
         "        <p><a href=\"$tts_aun\" " .
         "title=\"Right-click here and select Save-As to " .
         "download audio file\">Download</a> <a href=\"#top\" " .
         "title=\"Click here to jump to the top of the page\">" .
         "Top</a></p>\n";
         "        <p>Powered by " .
         "<a href=\"https://github.com/phhpro/easy-tts\" " .
         "title=\"Click here to get a free copy of this script\">" .
         "PHP Easy TTS v$tts_ver</a></p>\n" .
         "    </body>\n" .
         "</html>\n";
}
