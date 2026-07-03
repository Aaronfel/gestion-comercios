import { Head } from '@inertiajs/react';
import { Construction } from 'lucide-react';

/**
 * Placeholder navegable para los ítems del sidebar cuyo módulo todavía no se
 * construyó (Fases 1–6). Se reemplaza por la pantalla real en cada fase.
 * Recibe `title` y `phase` como props desde la ruta (Route::inertia).
 */
export default function Placeholder({
    title,
    phase,
}: {
    title: string;
    phase: number;
}) {
    return (
        <>
            <Head title={title} />
            <div className="flex h-full flex-1 flex-col items-center justify-center gap-5 p-6 text-center">
                <div className="vendi-hero shadow-warm-lg flex size-16 items-center justify-center rounded-2xl text-white">
                    <Construction className="size-8" />
                </div>
                <div className="space-y-2">
                    <span className="bg-secondary text-muted-foreground inline-block rounded-full px-3 py-1 text-xs font-medium tracking-wider uppercase">
                        Fase {phase} · en construcción
                    </span>
                    <h1 className="font-display text-3xl font-semibold">{title}</h1>
                    <p className="text-muted-foreground mx-auto max-w-sm text-sm">
                        Este módulo todavía no está construido — es parte de la Fase {phase} del
                        plan. Por ahora es un placeholder navegable del boilerplate.
                    </p>
                </div>
            </div>
        </>
    );
}
