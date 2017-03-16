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

require_once __DIR__ . '/../helpers/Array.php';

class Penalties extends Controller
{
    protected $_errors = [];
    protected $_mainTemplate = 'Penalties';

    protected function _beforeRun()
    {
        $this->_errors = [];

        if ($this->_path['action'] == 'apply') {
            $userId = intval($_POST['player']);
            $amount = intval($_POST['amount']);
            $reason = $_POST['reason'];
            try {
                $this->_api->execute('addPenalty', [TOURNAMENT_ID, $userId, $amount, $reason]);
            } catch (Exception $e) {
                $this->_errors []= $e->getMessage();
                return true;
            }

            header('Location: /penalties/');
            return false;
        }

        return true;
    }

    protected function _run()
    {
        $amounts = [];
        try {
            $players = $this->_api->execute('getAllPlayers', [TOURNAMENT_ID]);
            $settings = $this->_api->execute('getGameConfig', [TOURNAMENT_ID]);
            for ($i = $settings['minPenalty']; $i <= $settings['maxPenalty']; $i += $settings['penaltyStep']) {
                $amounts []= [
                    'view' => $i / (float)$settings['ratingDivider'],
                    'value' => $i
                ];
            }
        } catch (Exception $e) {
            $players = [];
            $this->_errors []= $e->getMessage();
        }

        return [
            'players' => $players,
            'penaltyAmounts' => $amounts,
            'error' => implode(', ', $this->_errors)
        ];
    }
}
