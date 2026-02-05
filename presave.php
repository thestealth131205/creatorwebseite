<?php require_once 'header.php'; ?>

    /**
     * --- PRESAVE LANDING PAGE ---
     * Die Hauptkomponente MUSS 'App' heißen, damit footer.php sie rendern kann.
     */
    const App = () => {
        const [song, setSong] = React.useState(null);
        const [settings, setSettings] = React.useState({});
        const [loading, setLoading] = React.useState(true);
        const [error, setError] = React.useState(null);

        React.useEffect(() => {
            const loadData = async () => {
                try {
                    const [sRes, setRes] = await Promise.all([
                        fetch('api.php?action=getSongs'),
                        fetch('api.php?action=getSettings')
                    ]);
                    
                    // Prüfen ob Requests erfolgreich waren
                    if (!sRes.ok || !setRes.ok) throw new Error("Netzwerk Fehler");

                    const allSongs = await sRes.json();
                    const settingsData = await setRes.json();
                    
                    setSettings(settingsData);
                    
                    // Custom Colors anwenden
                    if (settingsData.accent_color) document.documentElement.style.setProperty('--accent', settingsData.accent_color);
                    if (settingsData.main_color) document.documentElement.style.setProperty('--main-bg', settingsData.main_color);

                    // Song ID aus URL holen (z.B. presave.php?id=4)
                    const params = new URLSearchParams(window.location.search);
                    const id = params.get('id');

                    let currentSong = null;

                    if (id && Array.isArray(allSongs)) {
                        // Spezifischen Song suchen (Typ-sicherer Vergleich)
                        currentSong = allSongs.find(s => String(s.id) === String(id));
                    } else if (Array.isArray(allSongs) && allSongs.length > 0) {
                        // Fallback: Neuesten Song nehmen, falls keine ID angegeben oder ID falsch
                        currentSong = allSongs[0];
                    }

                    if (currentSong) {
                        setSong(currentSong);
                        // Log Visit
                        fetch('api.php?action=logVisit', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ identifier: 'presave/' + currentSong.id })
                        });
                        // Page Title dynamisch setzen
                        document.title = `${currentSong.title} - ${currentSong.artist || settingsData.artist_name || 'Music'}`;
                    } else {
                        setError("Song konnte nicht gefunden werden.");
                    }

                } catch (err) {
                    console.error("Fehler in presave.php:", err);
                    setError("Laden fehlgeschlagen. Bitte später erneut versuchen.");
                } finally {
                    setLoading(false);
                }
            };
            loadData();
        }, []);

        // --- LADEZUSTAND ---
        if (loading) {
            return (
                <div className="min-h-screen flex items-center justify-center">
                    <div className="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                </div>
            );
        }

        // --- FEHLERZUSTAND ---
        if (error || !song) {
            return (
                <div className="min-h-screen flex items-center justify-center text-center p-6 flex-col gap-6">
                    <h1 className="font-heading text-xl md:text-2xl font-black italic tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-500">
                        {settings.artist_name ? settings.artist_name.toUpperCase() : 'ARTIST'}
                    </h1>
                    <div className="glass p-10 rounded-[2.5rem] border border-white/10 max-w-md w-full">
                        <div className="text-4xl mb-4">??</div>
                        <h2 className="text-xl font-black text-white uppercase mb-2">Ups!</h2>
                        <p className="text-slate-400 mb-6 text-sm">{error || "Dieser Song existiert nicht oder wurde gelöscht."}</p>
                        <a href="index.php" className="inline-block px-8 py-4 bg-white/10 rounded-xl font-black uppercase text-[10px] hover:bg-white/20 transition-all tracking-widest text-white">
                            Zur Startseite
                        </a>
                    </div>
                </div>
            );
        }

        const isOut = parseInt(song.is_out_now) === 1;

        // Button Komponente
        const LinkButton = ({ url, bg, label, iconPath }) => {
            if (!url) return null;
            return (
                <a href={url} target="_blank" className="group w-full flex items-center justify-between p-4 rounded-2xl transition-all hover:scale-[1.02] hover:shadow-xl relative overflow-hidden mb-3 border border-white/5" style={{ backgroundColor: bg }}>
                    <div className="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                    <span className="relative z-10 font-black uppercase italic text-white text-sm tracking-widest flex items-center gap-3">
                        {/* Fallback Icon falls kein SVG da ist */}
                        {iconPath ? (
                           <img src={iconPath} className="w-5 h-5 object-contain invert brightness-0 filter" alt="" />
                        ) : (
                           <span className="w-5 h-5 flex items-center justify-center text-[10px]">?</span>
                        )}
                        {label}
                    </span>
                    <span className="relative z-10 bg-black/20 backdrop-blur-sm px-3 py-1 rounded-lg text-[9px] font-bold text-white uppercase border border-white/10 shadow-sm">
                        {isOut ? 'Play' : 'Pre-Save'}
                    </span>
                </a>
            );
        };

        return (
            <div className="min-h-screen flex flex-col font-sans text-slate-200 py-10 px-4 md:px-6">
                
                {/* Header / Logo Area */}
                <div className="text-center mb-8 animate-in fade-in duration-700">
                    <h1 className="font-heading text-xl md:text-2xl font-black italic tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-500 cursor-pointer hover:opacity-80 transition-opacity" onClick={() => window.location.href='index.php'}>
                        {settings.artist_name ? settings.artist_name.toUpperCase() : 'ARTIST'}
                    </h1>
                </div>

                <main className="flex-1 flex items-center justify-center">
                    <div className="w-full max-w-5xl glass p-8 md:p-12 rounded-[3rem] border border-white/10 shadow-2xl relative overflow-hidden animate-in slide-in-from-bottom-8 duration-700 bg-slate-900/40">
                        
                        {/* Background Glow */}
                        <div className="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
                            <div className="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-accent/10 blur-[120px] rounded-full mix-blend-screen"></div>
                            <div className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-500/10 blur-[120px] rounded-full mix-blend-screen"></div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-20 items-start">
                            
                            {/* LEFT COLUMN: COVER */}
                            <div className="relative group mx-auto md:mx-0 w-full max-w-md">
                                <div className="aspect-square rounded-[2.5rem] overflow-hidden border border-white/10 shadow-2xl relative z-10 bg-slate-950">
                                    <img src={song.cover_url} className="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" alt="Cover" />
                                    
                                    {/* Status Badge */}
                                    <div className={`absolute top-5 right-5 backdrop-blur-md text-white text-[10px] font-black uppercase px-4 py-1.5 rounded-full border border-white/10 shadow-lg ${isOut ? 'bg-accent/90' : 'bg-black/60'}`}>
                                        {isOut ? 'Out Now' : 'Coming Soon'}
                                    </div>
                                </div>
                                {/* Reflection / Shadow under cover */}
                                <div className="absolute -bottom-8 left-8 right-8 h-16 bg-black/60 blur-2xl rounded-full z-0 opacity-60"></div>
                            </div>

                            {/* RIGHT COLUMN: INFO & LINKS */}
                            <div className="flex flex-col justify-center h-full pt-4 md:pt-0">
                                <div className="text-center md:text-left mb-10 space-y-3">
                                    <h2 className="font-heading text-4xl md:text-5xl lg:text-6xl font-black uppercase italic tracking-tighter leading-none text-white drop-shadow-xl">
                                        {song.title}
                                    </h2>
                                    <p className="text-accent text-sm md:text-base font-bold tracking-[0.3em] uppercase">
                                        {song.artist || settings.artist_name}
                                    </p>
                                    {song.album && <p className="text-slate-500 text-[10px] font-bold uppercase tracking-widest pt-2">Aus dem Album: {song.album}</p>}
                                </div>

                                <div className="space-y-3 w-full max-w-md mx-auto md:mx-0">
                                    <LinkButton url={song.spotify_url} bg="#1DB954" label="Spotify" iconPath="https://www.svgrepo.com/show/512899/spotify-168.svg" />
                                    <LinkButton url={song.apple_music_url} bg="#FA243C" label="Apple Music" iconPath="https://www.svgrepo.com/show/503337/apple-music.svg" />
                                    <LinkButton url={song.amazon_music_url} bg="#00A8E1" label="Amazon Music" iconPath="https://upload.wikimedia.org/wikipedia/commons/4/4a/Amazon_icon.svg" />
                                    <LinkButton url={song.deezer_url} bg="#A238FF" label="Deezer" iconPath="https://upload.wikimedia.org/wikipedia/commons/d/d7/Deezer_logo.svg" />
                                    <LinkButton url={song.soundcloud_url} bg="#FF5500" label="SoundCloud" iconPath="https://www.svgrepo.com/show/354362/soundcloud.svg" />
                                    <LinkButton url={song.youtube_url} bg="#FF0000" label="YouTube" iconPath="https://www.svgrepo.com/show/13671/youtube.svg" />
                                    <LinkButton url={song.tiktok_url} bg="#000000" label="TikTok Sound" iconPath="https://www.svgrepo.com/show/303260/tiktok-logo-logo.svg" />
                                </div>
                            </div>

                        </div>

                        {/* BOTTOM ROW: DESCRIPTION */}
                        {song.description && (
                            <div className="mt-16 pt-10 border-t border-white/5 animate-in fade-in delay-200">
                                <h3 className="text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] mb-4 text-center md:text-left">Details</h3>
                                <p className="text-slate-300 leading-relaxed font-medium whitespace-pre-line text-center md:text-left max-w-4xl text-sm md:text-base">
                                    {song.description}
                                </p>
                            </div>
                        )}

                        <div className="mt-12 text-center">
                            <a href="index.php" className="inline-block px-6 py-3 rounded-xl bg-white/5 text-[9px] font-black uppercase tracking-widest text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                                ? Mehr von {settings.artist_name}
                            </a>
                        </div>
                    </div>
                </main>

                <footer className="py-8 text-center opacity-40 hover:opacity-100 transition-opacity">
                    <p className="text-[9px] font-black uppercase tracking-widest text-slate-500">
                        &copy; 2026 {settings.artist_name}
                    </p>
                </footer>
            </div>
        );
    };

<?php require_once 'footer.php'; ?>