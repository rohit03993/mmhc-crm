import type { CapacitorConfig } from '@capacitor/cli';

/**
 * Live-server mode: the Android app loads production CRM in a WebView.
 * Deploy Laravel with git → users get UI updates without rebuilding the APK.
 *
 * Local WebView testing: temporarily set server.url to your machine IP, e.g.
 *   url: 'http://192.168.1.10:8000'
 * and use cleartext: true only on a debug build (not for Play Store).
 */
const config: CapacitorConfig = {
    appId: 'com.themmhc.crm',
    appName: 'MeD Miracle Health Care',
    webDir: 'www',
    server: {
        // Live site homepage; CRM pages load after splash.
        url: 'https://themmhc.com',
        cleartext: false,
        androidScheme: 'https',
    },
    android: {
        allowMixedContent: false,
    },
};

export default config;
