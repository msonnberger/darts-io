![logo](https://users.multimediatechnology.at/~fhs45907/mmp1/client/img/logo.svg)

# darts.io — Remote Darts Scoreboard

_This project has been done as part of the MultiMediaTechnology bachelor's program at Salzburg University of Applied Sciences._
_Contact: [msonnberger.mmt-b2020@fh-salzburg.ac.at](mailto:msonnberger.mmt-b2020@fh-salzburg.ac.at)_

## Demo

Here is a working live demo: https://users.multimediatechnology.at/~fhs45907/mmp1/

## Installation

- Make sure **PHP** is installed. Has been tested with version `8.0.6`.
- Run `php server/bin/composer install` to install dependencies.
- In your web server config, create a reverse proxy to the **WebSocket** server. See [documentation](https://httpd.apache.org/docs/2.4/mod/mod_proxy_wstunnel.html) for Apache 2.4
- Run `php server/bin/start-server.php` to get the WebSocket server running. Default port is 9000.
- In `/client/app.js` on line 7, set URL for WebSocket server or leave it on default for localhost
- Ready! Test connection by opening up `/client/index.html`. In your terminal where the PHP script is running, it should say "New Connection! (_ID_)"

## Usage

Users can either create a new room or join an existing one with a Room ID. When creating a new room, you can choose the point-mode (301, 501, 701 or 1001), as well as in-mode and out-mode (single, double, triple, master). These settings can be changed during a game and will automatically be applied once a new game starts.

Currently, only two players at a time are supported. The player who created the room will go first by default.

Player 1 throws three darts, clicks the respective fields or buttons on the app and confirms with `OK`. After that, throws will be sent to the backend where score calculation gets done. Both players immediately see their updated scoreboards and stats.

After your game is finished (when someone hits exactly 0 points) your can either start a new game or go back to the home screen.
