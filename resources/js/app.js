import './bootstrap';
import Alpine from 'alpinejs';
import authenticated from './authenticated';
import './stores/calc';
import './stores/details';

window.Alpine = Alpine;
window.authenticated = authenticated;

Alpine.start();
