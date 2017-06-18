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
include_once __DIR__ . "/../helpers/GameFormatter.php";

class Game extends Controller
{
    protected $_mainTemplate = 'Game';

    protected function _pageTitle()
    {
        return 'Информация об игре';
    }

    protected function _run()
    {
        try {
            $formatter = new GameFormatter();
            $sessionId = $this->_getSessionId();
            $gamesData = $this->_api->execute('getGame', [$this->_eventId, $sessionId]);
            return [
                'games' => $formatter->formatGamesData($gamesData, $this->_rules),
            ];
        } catch (Exception $e) {
            return [
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @return int
     */
    protected function _getSessionId() {
        $parts = explode('/', parse_url($this->_path[0], PHP_URL_PATH));
        $id = $parts[3];
        return $id;
    }
}
