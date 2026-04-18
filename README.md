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
├── src/
│   ├── router.php             # Lightweight PHP router (maps URL patterns to scripts)
│   ├── routes.php             # Route definitions (/, /gpio/read/$pin, /gpio/toggle/$pin)
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
│   │   │   │   └── script.js  # Core app JS — AJAX toggle/read, timer countdown logic
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
│   │   │   └── toggle.php     # GET /gpio/toggle/$pin — toggles GPIO and returns new state
│   │   └── gpioToggle.php     # Legacy standalone toggle script (CLI + HTTP)
│   │
│   └── routes.php             # Route definitions (/, /gpio/read/$pin, /gpio/toggle/$pin)
│
├── composer.json              # PHP dependencies and project metadata
├── composer.lock              # Locked dependency versions (git-ignored)
├── composer.phar              # Composer binary
├── vendor/                    # Composer dependencies (git-ignored)
├── web.config                 # IIS config (placeholder)
├── xdebug.ini                 # Xdebug debug config
└── models/                    # (empty — reserved for future use)
```

## How It Works

### Request Flow

1. **Browser** loads `sprinkla.php` — PHP renders a toggle switch and timer controls for each sprinkler defined in `config.php`
2. On page load, `script.js` calls `setSwitch()` for each sprinkler, which sends `GET /gpio/read/{pin}` to check current state and sets the checkbox accordingly
3. When the user clicks a toggle, `toggleSwitch()` sends `GET /gpio/toggle/{pin}`
4. The route (`routes.php`) maps this to `scripts/gpio/toggle.php`
5. `toggle.php` validates the pin against the config, reads the current GPIO state via the pigpio TCP client, flips it, writes the new value, and returns `"0"` (on) or `"1"` (off)
6. The JS callback updates the UI and starts/clears the auto-off timer if enabled

### Router

A custom lightweight router in `router.php` provides `get()`, `post()`, `put()`, `patch()`, `delete()` functions. Routes are defined in `routes.php`:

```php
get('/', 'public/sprinkla.php');
get('/gpio/read/$broadcomNumber', 'scripts/gpio/read');
get('/gpio/toggle/$broadcomNumber', 'scripts/gpio/toggle');
```

URL parameters prefixed with `$` (like `$broadcomNumber`) are extracted and injected as PHP variables into the included script.

### Auto-Off Timer

Each sprinkler row in the UI includes:

- **⏱ Timer toggle** — enables/disables the auto-off timer for that sprinkler
- **Duration radio buttons** — choose 15, 20, or 25 minutes (configurable in `config.php`)
- **Countdown display** — shows `MM:SS` remaining in red when active

The timer is client-side JavaScript (`setInterval`). When the countdown reaches zero, it calls `toggleSwitch()` to turn the sprinkler off. Manually turning off a sprinkler clears the timer.

## Configuration

All settings are in `config/config.php`:

```php
// Raspberry Pi pigpio daemon connection
$config["pigpio_host"] = '192.168.20.9';
$config["pigpio_port"] = 8888;

// Auto-off timer options (minutes)
$config["timer_options"] = [15, 20, 25];
$config["timer_default"] = 20;

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

