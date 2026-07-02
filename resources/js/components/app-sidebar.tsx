import { Link } from '@inertiajs/react';
import {
    ChartColumnIncreasing,
    House,
    Package,
    Settings,
    ShoppingCart,
    Tag,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

// NOTE: hrefs point to dashboard() as placeholders until each module gets its
// own route in Phases 1–6 (see docs/superpowers/specs/2026-07-01-plan-tecnico-mvp.md).
const generalNav: NavItem[] = [
    { title: 'Inicio', href: dashboard(), icon: House },
    { title: 'Vender', href: dashboard(), icon: ShoppingCart },
];

const catalogoNav: NavItem[] = [
    { title: 'Productos', href: dashboard(), icon: Tag },
    { title: 'Stock', href: dashboard(), icon: Package },
];

const relacionesNav: NavItem[] = [
    { title: 'Clientes', href: dashboard(), icon: Users },
    { title: 'Reportes', href: dashboard(), icon: ChartColumnIncreasing },
];

const sistemaNav: NavItem[] = [
    { title: 'Configuración', href: dashboard(), icon: Settings },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={generalNav} label="General" />
                <NavMain items={catalogoNav} label="Catálogo" />
                <NavMain items={relacionesNav} label="Relaciones" />
                <NavMain items={sistemaNav} label="Sistema" />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
