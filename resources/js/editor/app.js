import { createApp } from 'vue';
import EditorApp from './EditorApp.vue';

const mount = document.getElementById('floorplan-editor');

if (mount) {
    createApp(EditorApp, {
        config: JSON.parse(mount.dataset.config || '{}'),
    }).mount(mount);
}
