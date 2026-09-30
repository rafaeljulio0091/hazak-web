import { firebaseAuth, firebaseDatabase } from '@/firebase';
import { signInWithCustomToken } from 'firebase/auth';
import { onValue, ref as databaseRef } from 'firebase/database';
import { computed, onBeforeUnmount, ref } from 'vue';

/**
 * Subscribes to the live position of one or more devices on the Firebase
 * Realtime Database. Authenticates the browser against Firebase using a
 * short-lived custom token minted by Laravel (see
 * App\Services\Tracking\FirebaseTokenService), so Realtime Database
 * security rules authorize each read per-user (admins via the `dashboard`
 * claim, regular users via users/{uid}/sharing/{deviceId}) instead of the
 * database being world-readable.
 *
 * A device whose read is denied only flags its own entry in `errors`; the
 * other listeners keep running.
 *
 * @param {string[]} deviceIds
 * @param {{ token: string, refreshToken: () => Promise<string>, initialPositions?: Record<string, object|null> }} options
 */
export function useDevicesTracking(deviceIds, { token, refreshToken, initialPositions = {} }) {
    const positions = ref({ ...initialPositions });
    const errors = ref({});
    const status = ref('connecting'); // 'connecting' | 'live' | 'error'
    const error = ref(null);

    let unsubscribers = [];
    let refreshTimer = null;

    const listen = () => {
        if (!deviceIds.length) {
            status.value = 'live';
            return;
        }

        unsubscribers = deviceIds.map((deviceId) =>
            onValue(
                databaseRef(firebaseDatabase, `devices/${deviceId}/location`),
                (snapshot) => {
                    positions.value = { ...positions.value, [deviceId]: snapshot.val() };
                    status.value = 'live';
                },
                (err) => {
                    errors.value = { ...errors.value, [deviceId]: err };

                    if (Object.keys(errors.value).length === deviceIds.length) {
                        error.value = err;
                        status.value = 'error';
                    }
                },
            ),
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
        unsubscribers.forEach((unsubscribe) => unsubscribe());
        unsubscribers = [];

        if (refreshTimer) {
            clearTimeout(refreshTimer);
            refreshTimer = null;
        }
    };

    onBeforeUnmount(stop);

    return { positions, errors, status, error, start, stop };
}

/**
 * Single-device convenience wrapper around useDevicesTracking.
 *
 * @param {string} deviceId
 * @param {{ token: string, refreshToken: () => Promise<string>, initialPosition?: object|null }} options
 */
export function useDeviceTracking(deviceId, { token, refreshToken, initialPosition = null }) {
    const tracking = useDevicesTracking([deviceId], {
        token,
        refreshToken,
        initialPositions: { [deviceId]: initialPosition },
    });

    const position = computed(() => tracking.positions.value[deviceId] ?? null);

    return { position, status: tracking.status, error: tracking.error, start: tracking.start, stop: tracking.stop };
}
