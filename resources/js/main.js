import { createApp } from 'vue';
import vuetify from './plugins/vuetify';
import CRUDLayout from './layout/CRUDLayout.vue';
import AuthLayout from './layout/AuthLayout.vue';
import TableComponent from './components/common/CRUDTableComponent.vue';
import AppHeader from './components/partials/AppHeader.vue';
import AppFooter from './components/partials/AppFooter.vue';
import LeftBar from './components/partials/LeftBar.vue';
import BooleanInput from './components/inputs/BooleanInput.vue';
import SelectInput from './components/inputs/SelectInput.vue';
import StringInput from './components/inputs/StringInput.vue';
import SubmitBtnComponent from './components/common/SubmitBtnComponent.vue';
import TextInput from './components/inputs/TextInput.vue';
import FileInput from './components/inputs/FileInput.vue';
import TranslatableInput from './components/inputs/TranslatableInput.vue';
import EditorInput from './components/inputs/EditorInput.vue';
import TranslatableEditorInput from './components/inputs/TranslatableEditorInput.vue';
import ActionBtnsComponent from "./components/common/ActionBtnsComponent.vue";

const app = createApp({});

app.use(vuetify);

app.component('crud-layout', CRUDLayout);
app.component('auth-layout', AuthLayout);
app.component('crud-items', TableComponent);
app.component('app-header', AppHeader);
app.component('app-footer', AppFooter);
app.component('left-bar', LeftBar);
app.component('submit-btn-component', SubmitBtnComponent);
app.component('action-btns-component', ActionBtnsComponent);

app.component('boolean-input', BooleanInput);
app.component('select-input', SelectInput);
app.component('string-input', StringInput);
app.component('text-input', TextInput);
app.component('file-input', FileInput);
app.component('translatable-input', TranslatableInput);
app.component('editor-input', EditorInput);
app.component('translatable-editor-input', TranslatableEditorInput);

app.mount('#app');
