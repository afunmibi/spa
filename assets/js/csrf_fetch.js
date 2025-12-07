// Small helper that sets X-CSRF-Token header for mutating fetch calls
(function(){
    function getToken(){
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : null;
    }

    if (!window._csrfFetchInstalled) {
        const _orig = window.fetch;
        window.fetch = function(input, init){
            init = init || {};
            const method = (init.method || 'GET').toUpperCase();
            if (['POST','PUT','PATCH','DELETE'].includes(method)) {
                init.headers = init.headers || {};
                // If headers is a Headers instance support set
                try {
                    if (typeof init.headers.get === 'function') {
                        // Headers object - append only if not present
                        if (!init.headers.get('X-CSRF-Token')) {
                            const t = getToken(); if (t) init.headers.set('X-CSRF-Token', t);
                        }
                    } else {
                        // plain object
                        if (!init.headers['X-CSRF-Token'] && !init.headers['x-csrf-token']) {
                            const t = getToken(); if (t) init.headers['X-CSRF-Token'] = t;
                        }
                    }
                } catch (e) {
                    const t = getToken(); if (t) init.headers['X-CSRF-Token'] = t;
                }
            }
            return _orig.call(this, input, init);
        };
        window._csrfFetchInstalled = true;
    }
})();
