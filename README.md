Riichi GUI
==========
[![Build Status](https://travis-ci.org/Furiten/riichi-gui.svg?branch=master)](https://travis-ci.org/Furiten/riichi-gui)

Simple GUI for [riichi API](https://github.com/Furiten/riichi-api). Derived from good old [riichi statboard](https://github.com/Furiten/riichi-statboard) with no backward compatibility.

Requirements
------------

- Live instance of Riichi API
- PHP 5.5+
- Apache w/ mod_rewrite or Nginx

End user is expected to be familiar with php-based websited installation mechanisms and should be able to deploy all required infrastructure items.

Installation
------------

- Set up your web server to use www folder as document root. Also it should invoke www/index.php as default entry point for every requested path that is not a file or directory.
- To install all composer dependencies, run `make deps`.
- Edit `config/const.php` and fill in your API server url.

