import { Moon, Sun } from 'lucide-react';
import { SidebarMenuButton } from '@/components/ui/sidebar';
import { useAppearance } from '@/hooks/use-appearance';

/**
 * Toggle claro/oscuro para el footer del sidebar (como el design). Alterna entre
 * light y dark usando el appearance actual resuelto.
 */
export function ThemeToggle() {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';
    const label = isDark ? 'Modo claro' : 'Modo oscuro';

    return (
        <SidebarMenuButton
            onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
            tooltip={{ children: label }}
        >
            {isDark ? <Sun /> : <Moon />}
            <span>{label}</span>
        </SidebarMenuButton>
    );
}
