import defaultTheme from 'tailwindcss/defaultTheme';
import { createRequire } from 'module';
import path from 'path';

const require = createRequire(import.meta.url);

const fluxPluginCandidates = [
    '@livewire/flux/tailwind-plugin',
    '@livewire/flux/tailwind',
    path.resolve('./vendor/livewire/flux/tailwind.plugin.cjs'),
    path.resolve('./vendor/livewire/flux/tailwind.config.js'),
];

let fluxPlugin = null;
for (const candidate of fluxPluginCandidates) {
    try {
        const resolved = require(candidate);
        fluxPlugin = resolved?.default ?? resolved;
        if (fluxPlugin) {
            break;
        }
    } catch (error) {
        if (error.code !== 'MODULE_NOT_FOUND') {
            console.warn(`Flux Tailwind plugin load warning from ${candidate}:`, error);
        }
    }
}

const plugins = [];
if (fluxPlugin) {
    plugins.push(typeof fluxPlugin === 'function' ? fluxPlugin() : fluxPlugin);
}

export default {
    darkMode: false,
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,jsx,ts,tsx,vue}',
        './vendor/livewire/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#EEF2FF',
                    100: '#E0E7FF',
                    200: '#C7D2FE',
                    300: '#A5B4FC',
                    400: '#818CF8',
                    500: '#6366F1',
                    600: '#4F46E5',
                    700: '#4338CA',
                    800: '#1E3A8A',
                    900: '#172554',
                    950: '#0F172A',
                },
                neutral: {
                    50: '#F8FAFC',
                    100: '#F1F5F9',
                    200: '#E2E8F0',
                    300: '#CBD5F5',
                    400: '#94A3B8',
                    500: '#64748B',
                    600: '#475569',
                    700: '#334155',
                    800: '#1E293B',
                    900: '#0F172A',
                    950: '#020617',
                },
                success: {
                    50: '#ECFDF9',
                    100: '#CFFAEF',
                    200: '#9FF5E0',
                    300: '#5EEFD0',
                    400: '#2AD8B7',
                    500: '#14B89C',
                    600: '#0F766E',
                    700: '#0A5A57',
                    800: '#064C47',
                    900: '#04332F',
                },
                warning: {
                    50: '#FFF7ED',
                    100: '#FFEAD5',
                    200: '#FDDFA8',
                    300: '#FBC46A',
                    400: '#F59E0B',
                    500: '#D97706',
                    600: '#B45309',
                    700: '#92400E',
                    800: '#78350F',
                    900: '#4C1D0D',
                },
                danger: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    300: '#FCA5A5',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                    800: '#991B1B',
                    900: '#7F1D1D',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                sm: '0.375rem',
                md: '0.5rem',
                lg: '0.75rem',
                xl: '1rem',
                '2xl': '1.5rem',
            },
        },
    },
    plugins,
};
