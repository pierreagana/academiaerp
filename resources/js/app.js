//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

/**
 * Bundled here instead of loaded from unpkg.com/cdn.jsdelivr.net in every
 * dashboard's <head> — every Blade view below still reaches these the same
 * way it always did (Alpine's x-data/@click directives, and the bare `L`
 * and `TomSelect` globals `transport/*.blade.php` and
 * `components/searchable-select.blade.php` already call), just self-hosted
 * so none of it silently breaks the moment a browser can't reach those CDNs.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

import L from 'leaflet';
window.L = L;

import TomSelect from 'tom-select';
window.TomSelect = TomSelect;
