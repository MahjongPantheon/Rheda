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

require_once __DIR__ . '/../helpers/Url.php';

class AdminLogin extends Controller
{
    protected $_mainTemplate = 'AdminLogin';

    protected function _pageTitle()
    {
        return 'Вход администратора';
    }

    protected function _run()
    {
        $isLoggedIn = (isset($_COOKIE['secret']) && $_COOKIE['secret'] == ADMIN_COOKIE);
        $error = null;

        if (!empty($_POST['secret'])) {
            if ($_POST['secret'] != ADMIN_PASSWORD) {
                $error = "Wrong password!";
            } else {
                setcookie('secret', ADMIN_COOKIE, time() + ADMIN_COOKIE_LIFE, '/');
                header('Location: ' . Url::make($this->_url, $this->_eventId));
            }
        }

        return [
            'error' => $error,
            'isLoggedIn' => $isLoggedIn
        ];
    }
}
