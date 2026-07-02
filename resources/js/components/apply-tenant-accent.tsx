import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import type { Tenant } from '@/types';

/**
 * White-label: applies the current tenant's accent color to the CSS theme
 * variables at runtime, so each comercio can re-brand the app (Configuración →
 * color de acento). Renders nothing. Defaults keep Vendi violet when unset.
 */
export function ApplyTenantAccent() {
    const { tenant } = usePage().props as unknown as { tenant?: Tenant | null };
    const accent = tenant?.accent_color;

    useEffect(() => {
        if (!accent) {
            return;
        }

        const root = document.documentElement;
        root.style.setProperty('--primary', accent);
        root.style.setProperty('--sidebar-primary', accent);
        root.style.setProperty('--ring', accent);
    }, [accent]);

    return null;
}
