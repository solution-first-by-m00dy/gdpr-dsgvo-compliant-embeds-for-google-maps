(function ($) {
    var consentCookieName = 'dsgvo_gm_consent';
    var consentCookieDays = 180;

    function hasConsentCookie() {
        return document.cookie.split(';').some(function (cookie) {
            return cookie.trim().indexOf(consentCookieName + '=1') === 0;
        });
    }

    function setConsentCookie() {
        var expires = new Date();
        expires.setTime(expires.getTime() + consentCookieDays * 24 * 60 * 60 * 1000);

        var cookie = consentCookieName + '=1; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
        if (window.location.protocol === 'https:') {
            cookie += '; Secure';
        }

        document.cookie = cookie;
    }

    function loadOverlay($container) {
        if (!$container.length || $container.data('dsgvoGmLoaded')) {
            return;
        }

        // 1) Get Raw Base64 of data-iframe
        var container = $container[0];
        var b64 = $container.attr('data-iframe') || (container && container.dataset.iframe);
        if (!b64) {
            console.error('DSGVO GM: No data-iframe attribute found');
            return;
        }
        b64 = b64.replace(/\s+/g, '');

        try {
            // 2) atob → raw HTML
            var html = atob(b64);

            // 3) Parsing with DOMParser (to stop inline scripts)
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var iframe = doc.querySelector('iframe');
            if (!iframe) {
                console.error('DSGVO GM: No <iframe> found in decoded HTML');
                return;
            }

            // 4) Allow only permitted URLs of google (maps)
            var src = iframe.getAttribute('src');
            var url;
            try {
                url = new URL(src, window.location.href);
            } catch (_) {
                console.error('DSGVO GM: Invalid iframe src URL');
                return;
            }
            var allowedOrigins = ['https://www.google.com', 'https://maps.google.com'];
            if (allowedOrigins.indexOf(url.origin) === -1) {
                console.error('DSGVO GM: iframe src origin not allowed:', url.origin);
                return;
            }

            var sandboxFlags = [
                'allow-scripts',
                'allow-same-origin',
                'allow-popups',
                'allow-popups-to-escape-sandbox',        
                'allow-top-navigation-by-user-activation'
            ];

            // 5) Create a new safe iframe
            var $safeIframe = $('<iframe>', {
                src: url.href,
                width: iframe.getAttribute('width') || '600',
                height: iframe.getAttribute('height') || '450',
                frameborder: 0,
                style: iframe.getAttribute('style') || '',
                allowfullscreen: "",
                loading: 'lazy',
                referrerpolicy: 'no-referrer-when-downgrade',
                sandbox: sandboxFlags.join(' ')
            });

            // 6) Empty container and fill it with new safe iframe
            $container.data('dsgvoGmLoaded', true).empty().append($safeIframe);

        } catch (err) {
            console.error('DSGVO GM: Invalid Base64 in data-iframe:', err, b64);
        }
    }

    function getTargetOverlays($container) {
        if ($container.attr('data-load-all') === '1') {
            return $('.dsgvo-gm-overlay[data-load-all="1"]');
        }

        return $container;
    }

    $(document).on('click', '.dsgvo-gm-load-btn', function (e) {
        e.preventDefault();

        var $container = $(this).closest('.dsgvo-gm-overlay');
        if ($container.find('.dsgvo-gm-remember-checkbox').is(':checked')) {
            setConsentCookie();
        }

        getTargetOverlays($container).each(function () {
            loadOverlay($(this));
        });
    });

    $(function () {
        if (!hasConsentCookie()) {
            return;
        }

        $('.dsgvo-gm-overlay').each(function () {
            loadOverlay($(this));
        });
    });
})(jQuery);
