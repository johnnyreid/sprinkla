# Sprinkla

A PHP web interface for controlling garden and lawn sprinklers via a Raspberry Pi. The web UI sends commands over the network to a [pigpio](https://abyz.me.uk/rpi/pigpio/) daemon running on the Pi, which drives relay-controlled solenoid valves through GPIO pins.

## Architecture

```
┌──────────────┐         HTTP          ┌───────────────┐       pigpio TCP       ┌─────────────┐
│  Browser     │  ───────────────────► │  PHP Web App  │  ────────────────────► │ Raspberry Pi │
│  (JS + HTML) │  GET /gpio/toggle/17  │  (this repo)  │  socket 8888           │ pigpio daemon│
│              │ ◄─────────────────── │               │ ◄──────────────────── │ GPIO → Relay │
│              │    "0" or "1"         │               │    pin state           │ → Sprinkler  │
└──────────────┘                       └───────────────┘                       └─────────────┘
                                              │
                                     data/timer_state.json
                                              │
                                       ┌──────┴──────┐
                                       │  Cron Job   │  ← runs every minute
                                       │  (cron.php) │  ← turns off expired timers
                                       └─────────────┘
```

The web app and the Raspberry Pi are **separate hosts**. This repo contains only the web interface — the Pi runs the pigpio daemon independently. Communication uses the [php-pigpio](https://github.com/johnnyreid/php-pigpio) library over TCP.

### GPIO Logic

- `0` = relay ON (sprinkler running)
- `1` = relay OFF (sprinkler stopped)

## Project Structure

```
sprinkla/
├── config/
│   ├── bootstrap.php          # Autoloader, encoding, timezone setup
│   └── config.php             # Sprinkler GPIO mappings, Pi host/port, timer settings
│
├── data/
│   ├── .gitkeep               # Keeps directory in git
│   ├── timer_state.json       # Server-side timer state (auto-created, git-ignored)
│   └── sprinkler_on_since.json # Records when each sprinkler was turned on (auto-created, git-ignored)
│
├── src/
│   ├── router.php             # Lightweight PHP router (maps URL patterns to scripts)
│   ├── routes.php             # Route definitions
│   │
│   ├── public/                # Web root (Apache/nginx document root)
│   │   ├── index.php          # Front controller — validates requests, loads routes
│   │   ├── .htaccess          # URL rewriting to index.php
│   │   ├── sprinkla.php       # Main UI — sprinkler toggles, timer controls, countdown
│   │   ├── header.html        # Shared page header / navigation
│   │   ├── footer.html        # Shared page footer
│   │   ├── generic.html       # Yard map / GPIO pin reference page
│   │   ├── elements.html      # HTML element showcase (template reference)
│   │   │
│   │   ├── js/
│   │   │   ├── sprinkla/
│   │   │   │   └── script.js  # Core app JS — AJAX toggle/read, timer sync, countdown
│   │   │   ├── jquery.min.js  # jQuery (used by skel template framework)
│   │   │   ├── init.js        # Skel responsive framework init
│   │   │   ├── skel.min.js    # Skel framework
│   │   │   └── skel-layers.min.js
│   │   │
│   │   ├── css/               # Stylesheets (responsive breakpoints via skel)
│   │   │   ├── style.css      # Main styles
│   │   │   ├── style-*.css    # Breakpoint-specific overrides
│   │   │   └── ...
│   │   │
│   │   ├── fonts/             # FontAwesome icons
│   │   └── images/            # UI images, GPIO pin diagrams
│   │
│   ├── scripts/
│   │   ├── gpio/
│   │   │   ├── read.php       # GET /gpio/read/$pin — reads current GPIO state
│   │   │   ├── toggle.php     # GET /gpio/toggle/$pin — toggles GPIO and returns new state
│   │   │   └── off.php        # GET /gpio/off/$pin — turns off a sprinkler (idempotent)
│   │   ├── timer/
│   │   │   ├── load.php       # GET /timer/load — returns all timer states as JSON
│   │   │   ├── save.php       # POST /timer/save — saves timer state for a switch
│   │   │   └── cron.php       # CLI — checks for expired timers and turns off sprinklers
│   │   └── gpioToggle.php     # Legacy standalone toggle script (CLI + HTTP)
│   │
│   ├── helpers/
│   │   └── log.php            # Logging functions (access.log and operations.log)
│   │
│   └── routes.php             # Route definitions
│
├── composer.json              # PHP dependencies and project metadata
├── composer.lock              # Locked dependency versions (git-ignored)
├── composer.phar              # Composer binary
├── vendor/                    # Composer dependencies (git-ignored)
├── install-cron.sh            # Installs the timer cron job
├── install.sh                 # System setup: user, group, permissions, logrotate
├── web.config                 # IIS config (placeholder)
└── xdebug.ini                 # Xdebug debug config
```

## How It Works

### Request Flow

1. **Browser** loads `sprinkla.php` — PHP renders a toggle switch and timer controls for each sprinkler defined in `config.php`
2. On page load, `script.js` calls `setSwitch()` for each sprinkler, which sends `GET /gpio/read/{pin}` to check current state and sets the checkbox accordingly
3. On page load, `loadTimerStates()` fetches `GET /timer/load` to restore any active or paused timers from the server
4. When the user clicks a toggle, `toggleSwitch()` sends `GET /gpio/toggle/{pin}`
5. The route (`routes.php`) maps this to `scripts/gpio/toggle.php`
6. `toggle.php` validates the pin against the config, reads the current GPIO state via the pigpio TCP client, flips it, writes the new value, and returns `"0"` (on) or `"1"` (off)
7. The JS callback updates the UI and starts/pauses the auto-off timer if enabled, saving state to the server via `POST /timer/save`

### Router

A custom lightweight router in `router.php` provides `get()`, `post()`, `put()`, `patch()`, `delete()` functions. Routes are defined in `routes.php`:

```php
get('/', 'public/sprinkla.php');
get('/gpio/read/$broadcomNumber', 'scripts/gpio/read');
get('/gpio/toggle/$broadcomNumber', 'scripts/gpio/toggle');
get('/gpio/off/$broadcomNumber', 'scripts/gpio/off');
get('/timer/load', 'scripts/timer/load');
post('/timer/save', 'scripts/timer/save');
```

URL parameters prefixed with `$` (like `$broadcomNumber`) are extracted and injected as PHP variables into the included script.

### Auto-Off Timer

Each sprinkler row in the UI includes:

- **⏱ Timer toggle** — enables/disables the auto-off timer for that sprinkler
- **−5 / +5 buttons** — adjust the duration in 5-minute steps (default 15 minutes, minimum 5 minutes)
- **Countdown display** — shows `MM:SS` remaining in red when active, orange when paused
- **✕ Clear button** — fully resets the timer

#### Server-Side Persistence

Timer state is stored on the server in `data/timer_state.json` using absolute Unix timestamps. This means:

- **Timers survive page refresh** — reopening the page restores active countdowns
- **Multiple users see the same state** — the browser polls the server every 5 seconds for updates
- **Timers work without a browser** — a cron job checks for expired timers every minute and sends the GPIO turn-off command directly

When the browser is open and a timer expires, the JS immediately calls the idempotent `/gpio/off/{pin}` endpoint for instant response. The cron job acts as a safety net for when no browser is open (with up to 60 seconds delay).

#### Timer Behaviour

- Turning **off** a sprinkler **pauses** the timer (preserving remaining time)
- Turning **on** a sprinkler **resumes** a paused timer
- Adjusting the duration while a timer is running restarts the countdown with the new duration
- The **✕ clear button** fully resets the timer regardless of state

#### Safety Timeout

As a safeguard against forgotten sprinklers, every sprinkler turn-on is recorded in `data/sprinkler_on_since.json` with the broadcom pin number and a start timestamp:

```json
{
    "17": {
        "broadcomNumber": 17,
        "name": "Lawn under stairs",
        "startTime": 1713420000
    }
}
```

The cron job checks for any sprinkler that has been running longer than `max_run_minutes` (default 60 minutes) and turns it off automatically. This is independent of the per-sprinkler timer — even if no timer is set, the safety timeout will still activate.

### Logging

All activity is logged to `/var/log/sprinkla/` (configurable via `log_dir` in config):

- **`access.log`** — every HTTP request: timestamp, client IP, method, URI, status code, referer, user-agent
- **`operations.log`** — sprinkler operations: toggles, auto-off events, timer changes, cron actions

Logs are rotated daily by logrotate. The current day's log is always `access.log` / `operations.log`. Previous days are renamed to `access.log.20260418`, `operations.log.20260418`, etc.

Example `operations.log`:
```
[2026-04-18 12:58:18] Toggled Lawn Corners (pin 23): → ON (from 192.168.1.100)
[2026-04-18 13:13:18] CRON: Timer expired, turned off Lawn Corners (pin 23)
[2026-04-18 14:00:01] CRON: Safety timeout, turned off Lawn under stairs (pin 17) after 60 minutes
```

## Configuration

All settings are in `config/config.php`:

```php
// Raspberry Pi pigpio daemon connection
$config["pigpio_host"] = '192.168.20.9';
$config["pigpio_port"] = 8888;

// Auto-off timer settings
$config["timer_default"] = 15;   // Default duration in minutes
$config["timer_step"]    = 5;    // +/- adjustment step in minutes
$config["timer_min"]     = 5;    // Minimum allowed duration in minutes

// Server-side timer state directory
$config["data_dir"] = __DIR__ . '/../data';

// Safety timeout — max minutes a sprinkler can run before auto-off (0 to disable)
$config["max_run_minutes"] = 60;

// Log directory
$config["log_dir"] = '/var/log/sprinkla';

// Sprinkler definitions — indexes 0-4 are Lawns, 5+ are Garden Beds
$config["gpio"][0]["broadcom_number"] = 17;
$config["gpio"][0]["name"]            = "Lawn under stairs";
// ... etc
```

To add or remove sprinklers, edit the `$config["gpio"]` array. The UI splits at index 5 (Lawns vs Garden Beds).

## Installation

### Prerequisites

- PHP 8.2+
- Apache or nginx with URL rewriting
- Composer
- A Raspberry Pi running the [pigpio daemon](https://abyz.me.uk/rpi/pigpio/) accessible over the network on port 8888

### Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/johnnyreid/sprinkla.git
   cd sprinkla
   ```

2. Install PHP dependencies:
   ```bash
   php composer.phar install
   ```

3. Configure the Pi connection in `config/config.php` — set `pigpio_host` to your Raspberry Pi's IP address.

4. Point your web server's document root to `src/public/`.

5. Ensure URL rewriting is enabled (`.htaccess` handles this for Apache; for nginx, configure `try_files` to fall back to `index.php`).

6. Run the system setup script (creates user, group, permissions, log directory, logrotate):
   ```bash
   sudo ./install.sh -p /var/www/sprinkla
   ```

   The script sets the following permissions:

   | Path | Dirs | Files | Purpose |
   |------|------|-------|---------|
   | Project (general) | `755` | `644` | Read-only for group/others |
   | `data/` | `775` | — | Web server writes timer and state data |
   | `/var/log/sprinkla/` | `775` | — | Web server writes log files |
   | `*.sh` scripts | — | `755` | Executable |

   The web server (`www-data`, added to the `sprinkla` group) can read all project files but can only write to `data/` and the log directory. Nothing under `src/public/` is writable by the web server.

7. Restart both the web server **and PHP-FPM** for the group change to take effect. When using PHP-FPM, Apache alone is not enough — PHP-FPM runs as a separate process with its own group list:
   ```bash
   sudo systemctl restart apache2
   sudo systemctl restart php8.4-fpm    # adjust version to match your install
   ```

   Verify PHP-FPM picked up the `sprinkla` group:
   ```bash
   cat /proc/$(pgrep -f 'php-fpm: pool' | head -1)/status | grep Groups
   ```
   The output should include the `sprinkla` group ID (check with `getent group sprinkla`).

8. Install the timer cron job:
   ```bash
   sudo ./install-cron.sh
   ```
   This adds a crontab entry (running as the `sprinkla` user) that checks every minute for expired timers and enforces the safety timeout. It will prompt for the project path (defaults to `/var/www/sprinkla/`).

### Starting the pigpio Daemon (on the Raspberry Pi)

```bash
sudo pigpiod
```

To allow remote connections, ensure pigpiod is started without the `-n` flag (or with `-n` specifying allowed hosts).

## Raspberry Pi Setup Notes

### Disable Edimax WiFi Adapter Power Management

If using an Edimax EW-7811Un, prevent the adapter from entering low-power mode:

```bash
sudo nano /etc/modprobe.d/8192cu.conf
```

Add:
```
options 8192cu rtw_power_mgnt=0 rtw_enusbss=0
```

### GPIO Pin Mapping

| Relay | Broadcom Pin | Sprinkler              |
|-------|-------------|------------------------|
| 1     | 17          | Lawn under stairs      |
| 2     | 23          | Lawn Corners           |
| 3     | 24          | Lawn West Wall         |
| 4     | 4           | Lawn Middle Rotor      |
| 5     | 5           | Lawn Hill              |
| 6     | 27          | Top Garden Bed         |
| 7     | 22          | Kitchen Window Garden  |
| 8     | 25          | Northern Garden Bed    |
| 9     | 18          | Northeastern Garden    |
| 10    | 6           | Spare                  |

## License

© Johnny Reid. All rights reserved.
