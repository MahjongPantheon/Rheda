<?php

/*  Rheda: visualizer and control panel
 *  Copyright (C) 2016  o.klimenko aka ctizen
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Sysconf
{
    // Single event mode settings; enable SINGLE_MODE and fill others with any non-empty value
    const SINGLE_MODE = false; // next items won't work until this is false
    const OVERRIDE_EVENT_ID = 1;
    const SUPER_ADMIN_PASS = 'hjpjdstckjybrb';
    const SUPER_ADMIN_COOKIE = 'kldfmewmd9vbeiogbjsdvjepklsdmnvmn';

    // Multi-event mode auth settings. Will not work when single mode is active
    public static function ADMIN_AUTH() {
        return [ // event id -> auth
            100500 => ['cookie' => 'verysecretcookie', 'password' => 'verysecretpassword'],
            100501 => ['cookie' => 'verysecretcookie', 'password' => 'verysecretpassword'],
        ];
    }

    // Common settings
    const API_URL = 'https://api.mjtop.net/'; // Config tip: change this to your entry point
    const API_VERSION_MAJOR = 1;
    const API_VERSION_MINOR = 0;
    const DEBUG_MODE = true; // TODO -> to false in prod!
    const API_ADMIN_TOKEN = 'nehybh,scnhsqc,hjc'; // TODO -> change it on prod!
    const ADMIN_COOKIE_LIFE = 3600; // in seconds, also counts for super cookies
}