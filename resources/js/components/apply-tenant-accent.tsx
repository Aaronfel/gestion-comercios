import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import type { Tenant } from '@/types';

/** Vendi default accent — matches app.css. When a tenant keeps it, we DON'T
 * override so the hand-tuned light/dark `--primary` from the stylesheet applies
 * (dark uses a luminous violet). Only a custom accent takes over both themes. */
const DEFAULT_ACCENT = '#6c4df2';

/**
 * White-label: applies the current tenant's accent color to the CSS theme
 * variables at runtime, so each comercio can re-brand the app (Configuración →
 * color de acento). Renders nothing.
 */
export function ApplyTenantAccent() {
    const { tenant } = usePage().props as unknown as { tenant?: Tenant | null };
    const accent = tenant?.accent_color;

    useEffect(() => {
        const root = document.documentElement;
        const vars = ['--primary', '--sidebar-primary', '--ring'];

        // Default accent → let the stylesheet drive both themes (keeps dark tuning).
        if (!accent || accent.toLowerCase() === DEFAULT_ACCENT) {
            vars.forEach((v) => root.style.removeProperty(v));
            return;
        }

        vars.forEach((v) => root.style.setProperty(v, accent));
    }, [accent]);

    return null;
}
