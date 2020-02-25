<?php

/*
 * This file is part of Hi-Media Quizzes.
 *
 * Hi-Media Quizzes is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Hi-Media Quizzes is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Hi-Media Quizzes. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Himedia\QCM;

/**
 * Petite classe outils.
 *
 * Copyright (c) 2013 Hi-Media
 * Copyright (c) 2013 Geoffroy Aubry <gaubry@hi-media.com>
 * Licensed under the GNU General Public License v3 (LGPL version 3).
 *
 * @copyright 2013 Hi-Media
 * @copyright 2013 Geoffroy Aubry <gaubry@hi-media.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Tools
{
    public static function getRemainingTimeMsg ($iRemainingSeconds)
    {
        $iRemainingSeconds = max(0, $iRemainingSeconds);
        $sTimelimitMsg = '';
        if ($iRemainingSeconds >= 60) {
            $iRemainingMinutes = floor($iRemainingSeconds / 60);
            $sTimelimitMsg = $iRemainingMinutes . ' minute' . ($iRemainingMinutes > 1 ? 's' : '');
        }
        if ($iRemainingSeconds < 60 || $iRemainingSeconds % 60 != 0) {
            $sTimelimitMsg .= ($iRemainingSeconds >= 60 ? ' et ' : '') . ($iRemainingSeconds % 60) . ' s';
        }
        return $sTimelimitMsg;
    }

    /**
     * Retourne l'adresse IP de l'utilisateur.
     *
     * @return string l'adresse IP de l'utilisateur.
     */
    public static function getIP () {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $sIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $sIp = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $sIp = $_SERVER['REMOTE_ADDR'];
        }
        return $sIp;
    }

    // escape '<?php' (PHP), '<!' et '<=' (PCRE)
    public static function escapeQuestionText ($sRawText)
    {
        $aSearch = array(
            '/<\?php/',
            '/<!(?!--)/',
            '/<=/'
        );
        $aReplace = array(
            '&lt;?php',
            '&lt;!',
            '&lt;='
        );
        $sEscapedText = preg_replace($aSearch, $aReplace, $sRawText);
        return $sEscapedText;
    }

    public static function formatQuestionChoices (array $aChoices)
    {
        foreach ($aChoices as $iIdx => $sRawText) {
            $aChoices[$iIdx] = self::formatText($sRawText);
        }
        return $aChoices;
    }

    public static function formatText ($sRawQuestion)
    {
        $sQuestionSubject = self::escapeQuestionText($sRawQuestion);

        $sClass = 'brush: %s; auto-links: false; toolbar: false; gutter: false';
        $sQuestionSubject = preg_replace(
            '/<pre\s*>/sim',
            '<pre class="plain">',
            $sQuestionSubject
        );
        $sQuestionSubject = preg_replace(
            '/<pre class="(bash|css|java|js|php|plain|shell|sql)">(.*?<\/pre>)/sim',
            '<div class="qcm-sh"><pre class="brush: $1; auto-links: false; toolbar: false; gutter: false">$2</div>',
            $sQuestionSubject
        );
        return $sQuestionSubject;
    }

    public static function simpleEncrypt ($text, $salt) {
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($text, $cipher, $salt, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $salt, $as_binary=true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
        return trim($ciphertext);
    }

    public static function simpleDecrypt ($text, $salt) {
        $c = base64_decode($text);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $salt, $options=OPENSSL_RAW_DATA, $iv);
        return trim($original_plaintext);
    }
}
