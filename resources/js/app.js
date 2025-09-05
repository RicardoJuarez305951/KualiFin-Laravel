import './bootstrap';
import Alpine from 'alpinejs';
import authenticated from './authenticated';
import './stores/calc';
import './stores/details';
import './stores/multiPay';

window.Alpine = Alpine;
window.authenticated = authenticated;

Alpine.start();
