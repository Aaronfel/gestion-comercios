export default function AppLogo() {
    return (
        <>
            <div className="bg-primary text-primary-foreground flex aspect-square size-8 items-center justify-center rounded-xl">
                <span className="font-display text-lg leading-none font-semibold">V</span>
            </div>
            <div className="ml-1 grid flex-1 text-left">
                <span className="font-display truncate text-sm leading-tight font-semibold">
                    Vendi
                </span>
                <span className="text-muted-foreground truncate text-[10px] font-medium tracking-wider uppercase">
                    POS · Argentina
                </span>
            </div>
        </>
    );
}
