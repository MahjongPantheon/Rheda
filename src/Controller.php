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

require_once __DIR__ . '/helpers/MobileDetect.php';
require_once __DIR__ . '/helpers/Config.php';
require_once __DIR__ . '/helpers/HttpClient.php';

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
     * @var Config
     */
    protected $_rules;

    /**
     * @var string
     */
    protected $_mainTemplate = '';

    public function __construct($url, $path)
    {
        $this->_url = $url;
        $this->_path = $path;
        $this->_api = new \JsonRPC\Client(API_URL, false, new HttpClient(API_URL));

        /** @var HttpClient $client */
        $client = $this->_api->getHttpClient();

        $client->withHeaders([
            'X-Auth-Token: ' . API_ADMIN_TOKEN,
            'X-Api-Version: ' . API_VERSION_MAJOR . '.' . API_VERSION_MINOR
        ]);
        if (DEBUG_MODE) {
            $client->withDebug();
        }

        $this->_rules = Config::fromRaw($this->_api->execute('getGameConfig', [TOURNAMENT_ID]));
        $this->_checkCompatibility($client->getLastHeaders());
    }

    public function run()
    {
        if (empty($this->_rules->rulesetTitle())) {
            echo '<h2>Oops.</h2>Failed to get event configuration!';
            return;
        }

        if ($this->_beforeRun()) {
            $context = $this->_run();
            $pageTitle = $this->_pageTitle(); // должно быть после run! чтобы могло использовать полученные данные
            $detector = new \MobileDetect();

            $m = new Mustache_Engine(array(
                'loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/templates/'),
            ));

            header("Content-type: text/html; charset=utf-8");

            $isLoggedIn = (isset($_COOKIE['secret']) && $_COOKIE['secret'] == ADMIN_COOKIE);
            $add = ($detector->isMobile() && !$detector->isTablet()) ? 'Mobile' : ''; // use full version for tablets

            echo $m->render($add . 'Layout', [
                'isOnline' => $this->_rules->isOnline(),
                'pageTitle' => $pageTitle,
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

    /**
     * @return string current page title
     */
    abstract protected function _pageTitle();

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

    protected function _checkCompatibility($headersArray)
    {
        $header = '';
        foreach ($headersArray as $h) {
            if (strpos($h, 'X-Api-Version') === 0) {
                $header = $h;
                break;
            }
        }

        if (empty($header)) {
            return;
        }

        list ($major, $minor) = explode('.', trim(str_replace('X-Api-Version: ', '', $header)));

        if (intval($major) !== API_VERSION_MAJOR) {
            throw new Exception('API major version mismatch. Update your app or API instance!');
        }

        if (intval($minor) > API_VERSION_MINOR && DEBUG_MODE) {
            trigger_error('API minor version mismatch. Consider updating if possible', E_USER_WARNING);
        }
    }
}
