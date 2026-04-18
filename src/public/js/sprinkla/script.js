// Timer state: tracks active countdown intervals and remaining seconds per switch
const timers = {};

/**
 * Send a GET request and handle the response.
 * @param {string} url
 * @param {function(string): void} onSuccess - called with response text on 200
 * @param {function(string): void} [onError] - called with error message
 */
function sendRequest(url, onSuccess, onError) {
    const request = new XMLHttpRequest();
    request.open("GET", url, true);
    request.send(null);

    request.onreadystatechange = function () {
        if (request.readyState !== 4) return;

        if (request.status === 200) {
            onSuccess(request.responseText);
        } else {
            const msg = "Request to " + url + " failed (HTTP " + request.status + ")";
            if (onError) {
                onError(msg);
            } else {
                alert(msg);
            }
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
        const elem = document.getElementById("switch_" + switchNumber);
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

/**
 * Get the selected timer duration (in seconds) for a switch.
 */
function getSelectedDuration(switchNumber) {
    const radios = document.querySelectorAll('input[name="timer_duration_' + switchNumber + '"]');
    for (let i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            return parseInt(radios[i].value, 10) * 60;
        }
    }
    // Fallback to global default (set in sprinkla.php as data attribute)
    const container = document.getElementById("sprinkla-config");
    const defaultMin = container ? parseInt(container.dataset.timerDefault, 10) : 20;
    return defaultMin * 60;
}

/**
 * Check if the timer toggle is enabled for a switch.
 */
function isTimerEnabled(switchNumber) {
    const toggle = document.getElementById("timer_toggle_" + switchNumber);
    return toggle && toggle.checked;
}

/**
 * Start the auto-off countdown if the timer toggle is enabled.
 */
function startTimerIfEnabled(switchNumber, broadcomNumber) {
    if (!isTimerEnabled(switchNumber)) return;

    clearTimer(switchNumber);

    let remaining = getSelectedDuration(switchNumber);
    startCountdown(switchNumber, broadcomNumber, remaining);
}

/**
 * Resume a paused timer, or start a new one if none is paused.
 */
function resumeOrStartTimer(switchNumber, broadcomNumber) {
    if (timers[switchNumber] && timers[switchNumber].paused) {
        // Resume from where it left off
        startCountdown(switchNumber, broadcomNumber, timers[switchNumber].remaining);
    } else {
        startTimerIfEnabled(switchNumber, broadcomNumber);
    }
}

/**
 * Start (or resume) the interval countdown from a given number of seconds.
 */
function startCountdown(switchNumber, broadcomNumber, remaining) {
    // Clear any running interval but keep the timer entry
    if (timers[switchNumber] && timers[switchNumber].intervalId) {
        clearInterval(timers[switchNumber].intervalId);
    }

    updateTimerDisplay(switchNumber, remaining);
    updateClearButton(switchNumber, true);

    timers[switchNumber] = {
        broadcomNumber: broadcomNumber,
        remaining: remaining,
        paused: false,
        intervalId: setInterval(function () {
            remaining--;
            timers[switchNumber].remaining = remaining;
            updateTimerDisplay(switchNumber, remaining);

            if (remaining <= 0) {
                toggleSwitch(switchNumber, broadcomNumber);
            }
        }, 1000)
    };
}

/**
 * Pause the countdown timer (stop ticking but keep remaining time).
 */
function pauseTimer(switchNumber) {
    if (!timers[switchNumber]) return;

    clearInterval(timers[switchNumber].intervalId);
    timers[switchNumber].intervalId = null;
    timers[switchNumber].paused = true;
    updateTimerDisplay(switchNumber, timers[switchNumber].remaining);
}

/**
 * Clear the countdown timer completely (reset to zero).
 */
function clearTimer(switchNumber) {
    if (timers[switchNumber]) {
        clearInterval(timers[switchNumber].intervalId);
        delete timers[switchNumber];
    }
    updateTimerDisplay(switchNumber, 0);
    updateClearButton(switchNumber, false);
}

/**
 * Update the visible countdown display. Shows "PAUSED" styling when paused.
 */
function updateTimerDisplay(switchNumber, totalSeconds) {
    const display = document.getElementById("timer_display_" + switchNumber);
    if (!display) return;

    if (totalSeconds <= 0) {
        display.textContent = "";
        display.style.display = "none";
    } else {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        const timeStr = mins.toString().padStart(2, "0") + ":" + secs.toString().padStart(2, "0");
        const isPaused = timers[switchNumber] && timers[switchNumber].paused;
        display.textContent = isPaused ? timeStr + " ⏸" : timeStr;
        display.style.display = "inline";
        display.className = isPaused ? "timer-countdown timer-paused" : "timer-countdown";
    }
}

/**
 * Show or hide the clear button for a timer.
 */
function updateClearButton(switchNumber, visible) {
    const btn = document.getElementById("timer_clear_" + switchNumber);
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
    const switchElem = document.getElementById("switch_" + switchNumber);
    if (switchElem && switchElem.checked) {
        if (isTimerEnabled(switchNumber)) {
            startTimerIfEnabled(switchNumber, broadcomNumber);
        } else {
            clearTimer(switchNumber);
        }
    }
}

/**
 * Called when the duration radio is changed while a timer is running.
 */
function onDurationChange(switchNumber, broadcomNumber) {
    if (timers[switchNumber]) {
        // Restart the timer with the new duration
        startTimerIfEnabled(switchNumber, broadcomNumber);
    }
}
