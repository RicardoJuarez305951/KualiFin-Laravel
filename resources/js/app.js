import './bootstrap';
import Alpine from 'alpinejs';
import authenticated from './authenticated';

window.Alpine = Alpine;
window.authenticated = authenticated;

Alpine.start();
