// Timer state: tracks active countdown intervals and remaining seconds per switch
var timers = {};

// Per-switch duration settings (in minutes)
var durations = {};

var POLL_INTERVAL = 5000;
var pollIntervalId = null;

/**
 * Send a GET request and handle the response.
 */
function sendRequest(url, onSuccess, onError) {
    var request = new XMLHttpRequest();
    request.open("GET", url, true);
    request.send(null);

    request.onreadystatechange = function () {
        if (request.readyState !== 4) return;

        if (request.status === 200) {
            onSuccess(request.responseText);
        } else {
            var msg = "Request to " + url + " failed (HTTP " + request.status + ")";
            if (onError) {
                onError(msg);
            } else {
                alert(msg);
            }
        }
    };
}

/**
 * Send a POST request with JSON body.
 */
function sendPostRequest(url, data, onSuccess, onError) {
    var request = new XMLHttpRequest();
    request.open("POST", url, true);
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify(data));

    request.onreadystatechange = function () {
        if (request.readyState !== 4) return;

        if (request.status === 200) {
            if (onSuccess) onSuccess(request.responseText);
        } else {
            var msg = "POST to " + url + " failed (HTTP " + request.status + ")";
            if (onError) onError(msg);
        }
    };
}

/**
 * Toggle a sprinkler on/off and manage the auto-off timer.
 */
function toggleSwitch(switchNumber, broadcomNumber) {
    sendRequest("/gpio/toggle/" + broadcomNumber, function (data) {
        if (data === "1") {
            // Sprinkler turned OFF — pause the timer if running
            pauseTimer(switchNumber);
        } else if (data === "0") {
            // Sprinkler turned ON — resume paused timer or start new one if enabled
            resumeOrStartTimer(switchNumber, broadcomNumber);
        } else if (data === "fail") {
            alert("Toggle failed: the server returned an error.");
        } else {
            alert("Toggle failed: unexpected response from server.");
        }
    });
}

/**
 * Read the current state of a sprinkler and set the checkbox accordingly.
 */
function setSwitch(switchNumber, broadcomNumber) {
    sendRequest("/gpio/read/" + broadcomNumber, function (data) {
        var elem = document.getElementById("switch_" + switchNumber);
        if (data === "0") {
            elem.checked = true;
        } else if (data === "1") {
            elem.checked = false;
        } else {
            alert("Failed to read sprinkler state.");
        }
    });
}

// ──────────────────────────────────────────────
// Timer management
// ──────────────────────────────────────────────

function getConfig() {
    return document.getElementById("sprinkla-config");
}

function getSelectedDuration(switchNumber) {
    var cfg = getConfig();
    var defaultMin = cfg ? parseInt(cfg.dataset.timerDefault, 10) : 15;
    if (durations[switchNumber] === undefined) {
        durations[switchNumber] = defaultMin;
    }
    return durations[switchNumber] * 60;
}

function adjustDuration(switchNumber, broadcomNumber, direction) {
    var cfg = getConfig();
    var step = cfg ? parseInt(cfg.dataset.timerStep, 10) : 5;
    var min = cfg ? parseInt(cfg.dataset.timerMin, 10) : 5;
    var defaultMin = cfg ? parseInt(cfg.dataset.timerDefault, 10) : 15;

    if (durations[switchNumber] === undefined) {
        durations[switchNumber] = defaultMin;
    }

    durations[switchNumber] += direction * step;
    if (durations[switchNumber] < min) {
        durations[switchNumber] = min;
    }

    var display = document.getElementById("timer_duration_display_" + switchNumber);
    if (display) {
        display.textContent = durations[switchNumber] + "m";
    }

    // If a timer is actively running, restart it with the new duration
    if (timers[switchNumber] && !timers[switchNumber].paused) {
        startTimerIfEnabled(switchNumber, broadcomNumber);
    }
}

function isTimerEnabled(switchNumber) {
    var toggle = document.getElementById("timer_toggle_" + switchNumber);
    return toggle && toggle.checked;
}

function startTimerIfEnabled(switchNumber, broadcomNumber) {
    if (!isTimerEnabled(switchNumber)) return;

    clearTimerLocal(switchNumber);

    var remaining = getSelectedDuration(switchNumber);
    startCountdown(switchNumber, broadcomNumber, remaining);
    saveTimerToServer(switchNumber);
}

/**
 * Resume a paused timer, or start a new one if none is paused.
 */
function resumeOrStartTimer(switchNumber, broadcomNumber) {
    if (timers[switchNumber] && timers[switchNumber].paused) {
        startCountdown(switchNumber, broadcomNumber, timers[switchNumber].remaining);
        saveTimerToServer(switchNumber);
    } else {
        startTimerIfEnabled(switchNumber, broadcomNumber);
    }
}

/**
 * Start (or resume) the interval countdown from a given number of seconds.
 */
function startCountdown(switchNumber, broadcomNumber, remaining) {
    if (timers[switchNumber] && timers[switchNumber].intervalId) {
        clearInterval(timers[switchNumber].intervalId);
    }

    var now = Math.floor(Date.now() / 1000);
    var endTime = now + remaining;

    updateTimerDisplay(switchNumber, remaining);
    updateClearButton(switchNumber, true);

    timers[switchNumber] = {
        broadcomNumber: broadcomNumber,
        name: sprinklerNames[switchNumber] || 'Unknown',
        remaining: remaining,
        paused: false,
        endTime: endTime,
        durationMinutes: durations[switchNumber] || Math.ceil(remaining / 60),
        intervalId: setInterval(function () {
            remaining--;
            timers[switchNumber].remaining = remaining;
            updateTimerDisplay(switchNumber, remaining);

            if (remaining <= 0) {
                clearTimerLocal(switchNumber);
                saveTimerToServer(switchNumber);
                // Turn off sprinkler (idempotent — safe if cron already did it)
                sendRequest("/gpio/off/" + broadcomNumber, function () {
                    var elem = document.getElementById("switch_" + switchNumber);
                    if (elem) elem.checked = false;
                });
            }
        }, 1000)
    };
}

/**
 * Pause the countdown timer (stop ticking but keep remaining time).
 */
function pauseTimer(switchNumber) {
    if (!timers[switchNumber]) return;

    if (timers[switchNumber].intervalId) {
        clearInterval(timers[switchNumber].intervalId);
    }
    timers[switchNumber].intervalId = null;
    timers[switchNumber].paused = true;
    timers[switchNumber].endTime = null;
    updateTimerDisplay(switchNumber, timers[switchNumber].remaining);
    saveTimerToServer(switchNumber);
}

/**
 * Clear the countdown timer locally (no server save).
 */
function clearTimerLocal(switchNumber) {
    if (timers[switchNumber]) {
        if (timers[switchNumber].intervalId) {
            clearInterval(timers[switchNumber].intervalId);
        }
        delete timers[switchNumber];
    }
    updateTimerDisplay(switchNumber, 0);
    updateClearButton(switchNumber, false);
}

/**
 * Clear the countdown timer and save to server.
 */
function clearTimer(switchNumber) {
    clearTimerLocal(switchNumber);
    saveTimerToServer(switchNumber);
}

/**
 * Update the visible countdown display. Shows "PAUSED" styling when paused.
 */
function updateTimerDisplay(switchNumber, totalSeconds) {
    var display = document.getElementById("timer_display_" + switchNumber);
    if (!display) return;

    if (totalSeconds <= 0) {
        display.textContent = "";
        display.style.display = "none";
    } else {
        var mins = Math.floor(totalSeconds / 60);
        var secs = totalSeconds % 60;
        var timeStr = mins.toString().padStart(2, "0") + ":" + secs.toString().padStart(2, "0");
        var isPaused = timers[switchNumber] && timers[switchNumber].paused;
        display.textContent = isPaused ? timeStr + " ⏸" : timeStr;
        display.style.display = "inline";
        display.className = isPaused ? "timer-countdown timer-paused" : "timer-countdown";
    }
}

/**
 * Show or hide the clear button for a timer.
 */
function updateClearButton(switchNumber, visible) {
    var btn = document.getElementById("timer_clear_" + switchNumber);
    if (!btn) return;
    btn.style.display = visible ? "inline" : "none";
}

/**
 * Called when the clear button is clicked — fully resets the timer.
 */
function onTimerClear(switchNumber) {
    clearTimer(switchNumber);
}

/**
 * Called when a timer toggle is changed while a sprinkler is already on.
 */
function onTimerToggleChange(switchNumber, broadcomNumber) {
    var switchElem = document.getElementById("switch_" + switchNumber);
    if (switchElem && switchElem.checked) {
        if (isTimerEnabled(switchNumber)) {
            startTimerIfEnabled(switchNumber, broadcomNumber);
        } else {
            clearTimer(switchNumber);
        }
    }
}

// ──────────────────────────────────────────────
// Server sync
// ──────────────────────────────────────────────

/**
 * Save the current timer state for a switch to the server.
 */
function saveTimerToServer(switchNumber) {
    var state = null;
    if (timers[switchNumber]) {
        state = {
            broadcomNumber: timers[switchNumber].broadcomNumber,
            name: timers[switchNumber].name,
            endTime: timers[switchNumber].endTime,
            paused: timers[switchNumber].paused,
            remaining: timers[switchNumber].remaining,
            durationMinutes: timers[switchNumber].durationMinutes
        };
    }
    sendPostRequest("/timer/save", {
        switchNumber: String(switchNumber),
        state: state
    });
}

/**
 * Load all timer states from the server and apply them locally.
 */
function loadTimerStates(callback) {
    sendRequest("/timer/load", function (data) {
        var serverState;
        try {
            serverState = JSON.parse(data);
        } catch (e) {
            if (callback) callback();
            return;
        }
        applyServerState(serverState);
        if (callback) callback();
    }, function () {
        // Silently ignore load errors (server may not have data yet)
        if (callback) callback();
    });
}

/**
 * Apply server timer state to local UI and countdowns.
 */
function applyServerState(serverState) {
    var now = Math.floor(Date.now() / 1000);

    Object.keys(serverState).forEach(function (sw) {
        var s = serverState[sw];
        var switchNumber = parseInt(sw, 10);
        var localTimer = timers[switchNumber];

        // Enable the timer toggle checkbox
        var toggle = document.getElementById("timer_toggle_" + switchNumber);
        if (toggle) toggle.checked = true;

        // Update duration display
        if (s.durationMinutes) {
            durations[switchNumber] = s.durationMinutes;
            var display = document.getElementById("timer_duration_display_" + switchNumber);
            if (display) display.textContent = s.durationMinutes + "m";
        }

        if (s.paused) {
            // Server says paused — update local if different
            if (!localTimer || !localTimer.paused || Math.abs(localTimer.remaining - s.remaining) > 2) {
                if (localTimer && localTimer.intervalId) {
                    clearInterval(localTimer.intervalId);
                }
                timers[switchNumber] = {
                    broadcomNumber: s.broadcomNumber,
                    name: s.name || sprinklerNames[switchNumber] || 'Unknown',
                    remaining: s.remaining,
                    paused: true,
                    endTime: null,
                    durationMinutes: s.durationMinutes,
                    intervalId: null
                };
                updateTimerDisplay(switchNumber, s.remaining);
                updateClearButton(switchNumber, true);
            }
        } else if (s.endTime) {
            // Server says running
            var serverRemaining = s.endTime - now;

            if (serverRemaining <= 0) {
                // Timer expired — clear and turn off sprinkler
                clearTimerLocal(switchNumber);
                saveTimerToServer(switchNumber);
                sendRequest("/gpio/off/" + s.broadcomNumber, function () {
                    var elem = document.getElementById("switch_" + switchNumber);
                    if (elem) elem.checked = false;
                });
                return;
            }

            // Skip update if local countdown is already in sync
            if (localTimer && !localTimer.paused && localTimer.intervalId) {
                if (Math.abs(localTimer.remaining - serverRemaining) <= 3) {
                    return;
                }
            }

            if (localTimer && localTimer.intervalId) {
                clearInterval(localTimer.intervalId);
            }
            startCountdown(switchNumber, s.broadcomNumber, Math.round(serverRemaining));
        }
    });

    // Clear local timers that no longer exist on server (another user cleared them)
    Object.keys(timers).forEach(function (sw) {
        if (!serverState.hasOwnProperty(sw)) {
            clearTimerLocal(parseInt(sw, 10));
            var toggle = document.getElementById("timer_toggle_" + sw);
            if (toggle) toggle.checked = false;
        }
    });
}

function startPolling() {
    if (pollIntervalId) clearInterval(pollIntervalId);
    pollIntervalId = setInterval(function () {
        loadTimerStates();
    }, POLL_INTERVAL);
}

// Load timer state from server on page load
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
        loadTimerStates();
        startPolling();
    });
} else {
    loadTimerStates();
    startPolling();
}
