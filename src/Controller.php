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

require_once __DIR__ . '/helpers/MobileDetect.php';

abstract class Controller
{
    /**
     * request_uri
     * @var string
     */
    protected $_url;
    /**
     * Parsed slugs list
     * @var string[]
     */
    protected $_path;

    /**
     * @var \JsonRPC\Client
     */
    protected $_api;

    /**
     * @var string
     */
    protected $_mainTemplate = '';

    public function __construct($url, $path)
    {
        $this->_url = $url;
        $this->_path = $path;
        $this->_api = new \JsonRPC\Client(API_URL);
        $this->_api->getHttpClient()->withHeaders([
            'X-Auth-Token: ' . API_ADMIN_TOKEN
        ]);
        if (DEBUG_MODE) {
            $this->_api->getHttpClient()->withDebug();
        }
    }

    public function run()
    {
        if ($this->_beforeRun()) {
            $context = $this->_run();
            $detector = new \MobileDetect();

            $m = new Mustache_Engine(array(
                'loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/templates/'),
            ));

            header("Content-type: text/html; charset=utf-8");

            $isLoggedIn = (isset($_COOKIE['secret']) && $_COOKIE['secret'] == ADMIN_COOKIE);
            $add = ($detector->isMobile() && !$detector->isTablet()) ? 'Mobile' : ''; // use full version for tablets

            echo $m->render($add . 'Layout', [
                'isOnline' => IS_ONLINE,
                'content' => $m->render($add . $this->_mainTemplate, $context),
                'isLoggedIn' => $isLoggedIn
            ]);
        }

        $this->_afterRun();
    }

    /**
     * @return string Mustache context for render
     */
    abstract protected function _run();

    protected function _beforeRun()
    {
        return true;
    }

    protected function _afterRun()
    {
    }

    /**
     * @param $url
     * @return Controller
     * @throws Exception
     */
    public static function makeInstance($url)
    {
        $routes = require_once __DIR__ . '/../config/routes.php';
        $matches = [];
        foreach ($routes as $regex => $controller) {
            if (preg_match('#^' . $regex . '$#', $url, $matches)) {
                require_once __DIR__ . "/controllers/{$controller}.php";
                return new $controller($url, $matches);
            }
        }
        throw new Exception('No available controller found for this URL');
    }
}
