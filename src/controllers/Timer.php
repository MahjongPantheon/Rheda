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

class Timer extends Controller
{
    protected $_mainTemplate = 'Timer';
    
    protected function _run()
    {
        $timerState = $this->_api->execute('getTimerState', [TOURNAMENT_ID]);
        if ($timerState['started'] && $timerState['time_remaining']) {
            $formattedTime = (int)($timerState['time_remaining'] / 60) . ':'
                           . (floor(($timerState['time_remaining'] % 60) / 10) * 10);
            return [
                'redZoneLength' => 10,
                'initialTime' => $formattedTime
            ];
        }
    }
}
