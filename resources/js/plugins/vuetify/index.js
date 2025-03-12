import './index.scss'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

const vuetify = createVuetify({
    components: {
        ...components,
    },
    directives,
    icons: {
        defaultSet: 'mdi',
    },
    defaults: {
        VBtn: {
          rounded: 'xl',
          textTransform: 'none',
        },
        VTextField: {
            rounded: 'xl',
            variant: 'solo-filled',
            density: 'comfortable',
            flat: true,
        },
        VFileInput: {
            rounded: 'xl',
            variant: 'solo-filled',
            density: 'comfortable',
            flat: true,
        },
        VSelect: {
            rounded: 'xl',
            variant: 'solo-filled',
            density: 'comfortable',
            flat: true,
        },
        VTextarea: {
            rounded: 'xl',
            variant: 'solo-filled',
            density: 'comfortable',
            flat: true,
        },
    },
    theme: {
        defaultTheme: 'dark',
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#30A488',
                    secondary: '#F0F4FF',
                    background: '#FAFAFA',
                    surface: '#F7F9FC',
                    text: '#212121',
                    border: '#D0D4DA',
                    success: '#2E7D61',
                    warning: '#FCB743',
                    error: '#D32F2F',
                    info: '#4469B0',
                    muted: '#6D6A75',
                }
            },
            dark: {
                dark: true,
                colors: {
                    primary: '#2E7D61',
                    secondary: '#1C2833',
                    background: '#0B141D',
                    surface: '#16212B',
                    text: '#DDE4E9',
                    border: '#1A222A',
                    success: '#4CAF50',
                    warning: '#E39A3D',
                    error: '#E57373',
                    info: '#5C84D1',
                    muted: '#8A8D94',
                }
            },

        },
    }
})

export default vuetify;
