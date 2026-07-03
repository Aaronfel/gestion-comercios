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
import { ThemeToggle } from '@/components/theme-toggle';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    clientes,
    configuracion,
    dashboard,
    productos,
    reportes,
    stock,
    vender,
} from '@/routes';
import type { NavItem } from '@/types';

// Inicio = dashboard. El resto son placeholders navegables (páginas "en
// construcción") hasta que cada módulo tenga su pantalla real en las Fases 1–6.
const generalNav: NavItem[] = [
    { title: 'Inicio', href: dashboard(), icon: House },
    { title: 'Vender', href: vender(), icon: ShoppingCart },
];

const catalogoNav: NavItem[] = [
    { title: 'Productos', href: productos(), icon: Tag },
    { title: 'Stock', href: stock(), icon: Package },
];

const relacionesNav: NavItem[] = [
    { title: 'Clientes', href: clientes(), icon: Users },
    { title: 'Reportes', href: reportes(), icon: ChartColumnIncreasing },
];

const sistemaNav: NavItem[] = [
    { title: 'Configuración', href: configuracion(), icon: Settings },
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
                <SidebarMenu>
                    <SidebarMenuItem>
                        <ThemeToggle />
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
