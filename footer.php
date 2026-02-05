<?php
// footer.php - Enthält nur schließende Tags und globale Komponenten wie den CookieBanner
// WICHTIG: Kein root.render() hier, das muss in der jeweiligen Hauptdatei (index.php/admin.php) passieren!
?>
        // --- GLOBAL FOOTER COMPONENT (Cookie Banner) ---
        const CookieBanner = ({ onAccept, onEssential, onNavigate }) => {
            return (
                <div className="fixed bottom-0 left-0 w-full z-50 p-4 md:p-6 animate-in slide-in-from-bottom-full duration-500">
                    <div className="max-w-4xl mx-auto glass p-6 md:p-8 rounded-[2rem] border border-white/10 shadow-2xl bg-[#020617]/90 backdrop-blur-xl flex flex-col md:flex-row items-center gap-6 md:gap-10">
                        <div className="flex-1 text-center md:text-left">
                            <h4 className="text-sm font-black uppercase text-accent tracking-widest mb-2">Datenschutzeinstellungen</h4>
                            <p className="text-xs text-slate-300 leading-relaxed">
                                Wir verwenden Cookies und ähnliche Technologien, um die Nutzererfahrung zu verbessern und statistische Daten zur Nutzung unserer Website zu erheben. Sie können entscheiden, ob Sie nur essenzielle Cookies akzeptieren oder uns erlauben, anonyme Nutzungsdaten zu sammeln.
                            </p>
                            <div className="flex gap-4 mt-2 justify-center md:justify-start">
                                <span onClick={() => onNavigate('impressum')} className="text-[9px] text-slate-500 hover:text-white cursor-pointer uppercase font-bold tracking-wider">Impressum</span>
                                <span onClick={() => onNavigate('privacy')} className="text-[9px] text-slate-500 hover:text-white cursor-pointer uppercase font-bold tracking-wider">Datenschutz</span>
                            </div>
                        </div>
                        <div className="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <button onClick={onEssential} className="px-6 py-3 rounded-xl border border-white/10 hover:bg-white/5 text-[10px] font-black uppercase tracking-widest text-slate-400 transition-colors whitespace-nowrap">
                                Nur Essenzielle
                            </button>
                            <button onClick={onAccept} className="px-6 py-3 rounded-xl bg-accent text-white hover:bg-accent/90 shadow-lg shadow-accent/20 text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                                Alle Akzeptieren
                            </button>
                        </div>
                    </div>
                </div>
            );
        };
    </script>
</body>
</html>