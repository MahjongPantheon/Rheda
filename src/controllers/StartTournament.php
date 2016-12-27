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

class StartTournament extends Controller {
    protected $_mainTemplate = 'StartTournament';
    protected $_lastEx = null;

    protected function _beforeRun()
    {
        if (!empty($this->_path['action']) && $this->_path['action'] == 'start') {
            if (empty($_COOKIE['secret']) || $_COOKIE['secret'] != ADMIN_COOKIE) {
                return true; // to show error in _run
            }

            try {
                $this->_api->execute('startGamesWithSeating', [TOURNAMENT_ID, 1, mt_rand(100000, 999999)]);
                $this->_api->execute('startTimer', [TOURNAMENT_ID]);
            } catch (Exception $e) {
                $this->_lastEx = $e;
                return true;
            }
            header('Location: /tourn/');
            return false;
        }

        return true;
    }
    
    protected function _run()
    {
        if (empty($_COOKIE['secret']) || $_COOKIE['secret'] != ADMIN_COOKIE) {
            return [
                'allOk' => false,
                'reason' => "Секретное слово неправильное"
            ];
        }

        if (!empty($this->_lastEx)) {
            return [
                'allOk' => false,
                'reason' => $this->_lastEx->getMessage()
            ];
        }
        
        $allOk = true;
        $reason = '';
        
        $players = $this->_api->execute('getAllPlayers', [TOURNAMENT_ID]);
        if (count($players) % 4 !== 0) {
            $allOk = false;
            $reason = 'Столы не укомплектованы! Число игроков не делится нацело на 4, нужно добавить или убрать людей!';
        } else {
            $timerState = $this->_api->execute('getTimerState', [TOURNAMENT_ID]);
            if ($timerState['started']) { // Check once after click on START
                $allOk = false;
                $reason = 'Игры уже начаты';
            }
        }

        $tables = $this->_api->execute('getTablesState', [TOURNAMENT_ID]);

        return [
            'allOk' => $allOk,
            'reason' => $reason,
            'tables' => array_map(function($t) {
                $t['finished'] = $t['status'] == 'finished';
                return $t;
            }, $tables)
        ];
    }
}