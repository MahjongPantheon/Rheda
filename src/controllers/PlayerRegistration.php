<?php
/*  Riichi mahjong stat GUI
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

class PlayerRegistration extends Controller
{
    protected $_mainTemplate = 'PlayerRegistration';

    protected function _run()
    {
        $errorMsg = '';
        $ident = '';
        $displayName = '';

        if (!empty($_POST['ident'])) {
            $ident = $_POST['ident'];
            $displayName = $_POST['display_name'];

            if ($_COOKIE['secret'] != ADMIN_COOKIE) {
                $errorMsg = "Секретное слово неправильное";
            } else if (preg_match('#[^a-z0-9]+#is', $_POST['ident'])) {
                $errorMsg = "В системном имени должны быть только латинские буквы и цифры, никаких пробелов";
            } else {
                try {
                    $playerId = $this->_api->execute('addPlayer', [
                        $_POST['ident'], $_POST['ident'], $_POST['display_name'], null
                    ]);
                    $this->_api->execute('registerPlayer', [TOURNAMENT_ID, $playerId]);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                };
            }
        }

        return [
            'error' => $errorMsg,
            'ident' => $ident,
            'display_name' => $displayName
        ];
    }
}
