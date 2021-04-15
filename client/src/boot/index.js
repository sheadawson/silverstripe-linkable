/* global window */
import Injector from 'lib/Injector';
import LinkField from 'components/linkfield';

window.document.addEventListener('DOMContentLoaded', () => {
    Injector.component.registerMany({
        LinkField
    });
});