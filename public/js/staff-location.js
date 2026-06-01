/**
 * Patient staff search: uses device GPS (current location), not pincode.
 */
(function () {
    const btn = document.getElementById('btnUseMyLocation');
    if (!btn) {
        return;
    }

    const panel = document.querySelector('.staff-location-panel');
    const resolveUrl = btn.dataset.resolveUrl;
    const statusEl = document.getElementById('staffLocationStatus');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function setStatus(message, type) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message;
        statusEl.className = 'staff-location-status staff-location-status--' + (type || 'info');
        statusEl.hidden = !message;
    }

    function setLoading(loading) {
        btn.disabled = loading;
        btn.classList.toggle('is-loading', loading);
        const label = btn.querySelector('.btn-label');
        if (label) {
            label.textContent = loading ? 'Getting location…' : (btn.dataset.defaultLabel || 'Use current location');
        }
    }

  btn.dataset.defaultLabel = btn.querySelector('.btn-label')?.textContent || 'Use current location';

    function requestLocation() {
        if (!navigator.geolocation) {
            setStatus('Location is not supported in this browser.', 'error');
            return;
        }

        if (!window.isSecureContext) {
            setStatus('Location requires HTTPS. Open the app using a secure (https://) link.', 'error');
            return;
        }

        setLoading(true);
        setStatus('Requesting your current location…', 'info');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                setStatus('Finding nearest staff…', 'info');

                fetch(resolveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        save_to_profile: true,
                    }),
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { ok: res.ok, data: data };
                        });
                    })
                    .then(function ({ ok, data }) {
                        setLoading(false);
                        if (ok && data.success && data.redirect_url) {
                            setStatus(data.message || 'Redirecting…', 'success');
                            window.location.href = data.redirect_url;
                            return;
                        }
                        setStatus(data.message || 'Could not use your location.', 'error');
                    })
                    .catch(function () {
                        setLoading(false);
                        setStatus('Network error. Check connection and try again.', 'error');
                    });
            },
            function (error) {
                setLoading(false);
                let message = 'Could not get your current location.';
                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Location blocked. Allow location for this site in browser or phone settings, then tap the button again.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    message = 'GPS unavailable. Move outdoors or try again.';
                } else if (error.code === error.TIMEOUT) {
                    message = 'Location timed out. Try again.';
                }
                setStatus(message, 'error');
            },
            {
                enableHighAccuracy: true,
                timeout: 25000,
                maximumAge: 60000,
            }
        );
    }

    btn.addEventListener('click', requestLocation);

    if (panel && panel.dataset.autoLocate === '1') {
        if (!sessionStorage.getItem('mmhc_staff_gps_prompted')) {
            sessionStorage.setItem('mmhc_staff_gps_prompted', '1');
            setTimeout(requestLocation, 800);
        }
    }
})();
