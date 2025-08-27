import './bootstrap';
import Alpine from 'alpinejs';
import authenticated from './authenticated';
import './stores/calc';

window.Alpine = Alpine;
window.authenticated = authenticated;

Alpine.start();
