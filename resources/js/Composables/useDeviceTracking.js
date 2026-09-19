import { firebaseAuth, firebaseDatabase } from '@/firebase';
import { signInWithCustomToken } from 'firebase/auth';
import { onValue, ref as databaseRef } from 'firebase/database';
import { onBeforeUnmount, ref } from 'vue';

/**
 * Subscribes to a device's live position on the Firebase Realtime
 * Database. Authenticates the browser against Firebase using a
 * short-lived custom token minted by Laravel (see
 * App\Services\Tracking\FirebaseTokenService), so Realtime Database
 * security rules can authorize the read per-user instead of the
 * database being world-readable.
 *
 * @param {string} deviceId
 * @param {{ token: string, refreshToken: () => Promise<string>, initialPosition?: object|null }} options
 */
export function useDeviceTracking(deviceId, { token, refreshToken, initialPosition = null }) {
    const position = ref(initialPosition);
    const status = ref('connecting'); // 'connecting' | 'live' | 'error'
    const error = ref(null);

    let stopListening = null;
    let refreshTimer = null;

    const listen = () => {
        const locationRef = databaseRef(firebaseDatabase, `devices/${deviceId}/location`);

        stopListening = onValue(
            locationRef,
            (snapshot) => {
                position.value = snapshot.val();
                status.value = 'live';
            },
            (err) => {
                error.value = err;
                status.value = 'error';
            },
        );
    };

    const scheduleTokenRefresh = () => {
        // Custom tokens expire after 1h; refresh a few minutes early.
        refreshTimer = setTimeout(async () => {
            try {
                const freshToken = await refreshToken();
                await signInWithCustomToken(firebaseAuth, freshToken);
                scheduleTokenRefresh();
            } catch (err) {
                error.value = err;
                status.value = 'error';
            }
        }, 50 * 60 * 1000);
    };

    const start = async () => {
        try {
            await signInWithCustomToken(firebaseAuth, token);
            listen();
            scheduleTokenRefresh();
        } catch (err) {
            error.value = err;
            status.value = 'error';
        }
    };

    const stop = () => {
        stopListening?.();
        stopListening = null;

        if (refreshTimer) {
            clearTimeout(refreshTimer);
            refreshTimer = null;
        }
    };

    onBeforeUnmount(stop);

    return { position, status, error, start, stop };
}
