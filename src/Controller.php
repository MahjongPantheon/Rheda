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
require_once __DIR__ . '/helpers/Url.php';
require_once __DIR__ . '/helpers/Config.php';
require_once __DIR__ . '/helpers/HttpClient.php';
use Handlebars\Handlebars;

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
     * @var int
     */
    protected $_eventId;

    /**
     * @var string
     */
    protected $_mainTemplate = '';

    public function __construct($url, $path)
    {
        $this->_url = $url;
        $this->_path = $path;
        $this->_api = new \JsonRPC\Client(Sysconf::API_URL, false, new HttpClient(Sysconf::API_URL));

        $eidMatches = [];
        if (empty($path['event']) || !preg_match('#eid(\d+)#is', $path['event'], $eidMatches)) {
            // TODO: убрать чтобы показать страницу со списком событий
            //throw new Exception('No event id found! Use single-event mode, or choose proper event on main page');
            exit('Please select some event!');
        }
        $this->_eventId = intval($eidMatches[1]);

        /** @var HttpClient $client */
        $client = $this->_api->getHttpClient();

        $client->withHeaders([
            'X-Debug-Token: aehbntyrey',
            'X-Auth-Token: ' . Sysconf::API_ADMIN_TOKEN,
            'X-Api-Version: ' . Sysconf::API_VERSION_MAJOR . '.' . Sysconf::API_VERSION_MINOR
        ]);
        if (Sysconf::DEBUG_MODE) {
            $client->withDebug();
        }

        $this->_rules = Config::fromRaw($this->_api->execute('getGameConfig', [$this->_eventId]));
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

            $m = new Handlebars([
                'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__ . '/templates/'),
                'partials_loader' => new \Handlebars\Loader\FilesystemLoader(
                    __DIR__ . '/templates/',
                    ['prefix' => '_']
                )
            ]);

            $m->addHelper("href", function($template, $context, $args, $source) {
                list($url, $name) = $args->getPositionalArguments();
                if (empty($name)) {
                    $name = $source; // may be used as block helper, without name, for html embed for example.
                }
                return '<a href="' . Url::make(Url::interpolate($url, $context), $this->_eventId) . '">'
                    . Url::interpolate($name, $context) . '</a>';
            });
            $m->addHelper("hrefblank", function($template, $context, $args, $source) {
                list($url, $name) = $args->getPositionalArguments();
                if (empty($name)) {
                    $name = $source; // may be used as block helper, without name, for html embed for example.
                }
                return '<a href="' . Url::make(Url::interpolate($url, $context), $this->_eventId) . '" target="_blank">'
                    . Url::interpolate($name, $context) . '</a>';
            });

            header("Content-type: text/html; charset=utf-8");

            $add = ($detector->isMobile() && !$detector->isTablet()) ? 'Mobile' : ''; // use full version for tablets

            echo $m->render($add . 'Layout', [
                'isOnline' => $this->_rules->isOnline(),
                'pageTitle' => $pageTitle,
                'content' => $m->render($add . $this->_mainTemplate, $context),
                'isLoggedIn' => $this->_adminAuthOk()
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
        $controller = Sysconf::SINGLE_MODE
            ? self::_singleEventMode($url, $routes)
            : self::_multiEventMode($url, $routes);

        if (!$controller) {
            throw new Exception('No available controller found for this URL');
        }

        return $controller;
    }

    protected static function _singleEventMode($url, $routes)
    {
        $matches = [];
        foreach ($routes as $regex => $controller) {
            $re = '#^' . preg_replace('#^!#is', '', $regex) . '/?$#';
            if (preg_match($re, $url, $matches)) {
                require_once __DIR__ . "/controllers/{$controller}.php";
                $matches['event'] = 'eid' . Sysconf::OVERRIDE_EVENT_ID;
                return new $controller($url, $matches);
            }
        }

        return null;
    }

    protected static function _multiEventMode($url, $routes)
    {
        $matches = [];
        foreach ($routes as $regex => $controller) {
            if ($regex[0] === '!') {
                $re = '#^' . mb_substr($regex, 1) . '/?$#';
            } else {
                $re = '#^/(?<event>eid\d+)' . $regex . '/?$#';
            }

            if (preg_match($re, $url, $matches)) {
                require_once __DIR__ . "/controllers/{$controller}.php";
                return new $controller($url, $matches);
            }
        }

        return null;
    }

    protected function _adminAuthOk()
    {
        if (Sysconf::SINGLE_MODE) {
            return !empty($_COOKIE['secret']) && $_COOKIE['secret'] == Sysconf::SUPER_ADMIN_COOKIE;
        } else {
            return !empty($_COOKIE['secret'])
                && !empty(Sysconf::ADMIN_AUTH()[$this->_eventId]['cookie'])
                && $_COOKIE['secret'] == Sysconf::ADMIN_AUTH()[$this->_eventId]['cookie'];
        }
    }

    protected function _getAdminCookie($password)
    {
        if (Sysconf::SINGLE_MODE) {
            if ($password == Sysconf::SUPER_ADMIN_PASS) {
                return Sysconf::SUPER_ADMIN_COOKIE;
            }
        } else {
            if (
                !empty(Sysconf::ADMIN_AUTH()[$this->_eventId]['password'])
                && $password == Sysconf::ADMIN_AUTH()[$this->_eventId]['password']
            ) {
                return Sysconf::ADMIN_AUTH()[$this->_eventId]['cookie'];
            }
        }

        return false;
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

        if (intval($major) !== Sysconf::API_VERSION_MAJOR) {
            throw new Exception('API major version mismatch. Update your app or API instance!');
        }

        if (intval($minor) > Sysconf::API_VERSION_MINOR && Sysconf::DEBUG_MODE) {
            trigger_error('API minor version mismatch. Consider updating if possible', E_USER_WARNING);
        }
    }
}
