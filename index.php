<?php require_once 'header.php'; ?>

    /**
     * --- WEBPLAYER INDEX ---
     * Wir setzen den Babel-Block aus der header.php fort.
     */

    // --- FRONTEND ARCHITEKTUR ---
    <?php require_once 'components.php'; ?>
    <?php require_once 'views.php'; ?>

    // --- ZUSÄTZLICHE STYLES FÜR DYNAMISCHE HOVER ---
    const style = document.createElement('style');
    style.innerHTML = `
        .hover-accent-color:hover { color: var(--accent) !important; }
        .nav-link-base { transition: color 0.3s ease; }
    `;
    document.head.appendChild(style);

    // --- MAIN APP LOGIC ---
    const App = () => {
        const [view, setView] = useState('home');
        const [cookieConsent, setCookieConsent] = useState(localStorage.getItem('cookieConsent')); 
        const [songs, setSongs] = useState([]);
        const [merch, setMerch] = useState([]);
        const [merchLoading, setMerchLoading] = useState(false);
        const [settings, setSettings] = useState({ 
            artist_name: 'TheStealth', 
            accent_color: '#93d507', 
            main_color: '#020617', 
            merch_enabled: '0', 
            spreadshirt_shop_name: '', 
            site_domain: '',
            instagram_url: '', // Initialisierung
            facebook_url: '',
            tiktok_url: '',
            youtube_url: ''
        });
        const [loading, setLoading] = useState(true);
        const [isMenuOpen, setIsMenuOpen] = useState(false);
        const [secretClicks, setSecretClicks] = useState(0); 
        const [selectedSong, setSelectedSong] = useState(null);
        const [selectedAlbum, setSelectedAlbum] = useState(null);

        // Lokale Komponente für Social Links
        const LocalSocialNavLinks = ({ settings, trackSocialClick }) => {
            const insta = settings.instagram_url || settings.insta_url;
            const fb = settings.facebook_url || settings.fb_url;
            const tt = settings.tiktok_url || settings.tt_url;
            const yt = settings.youtube_url || settings.yt_url;

            return (
                <div className="flex items-center gap-4">
                    {insta && (
                        <a 
                            href={insta} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            onClick={() => trackSocialClick('instagram')} 
                            className="social-link text-white/50 hover-accent-color transition-colors"
                            title="Instagram"
                        >
                            <SocialIcons.Instagram />
                        </a>
                    )}
                    {fb && (
                        <a 
                            href={fb} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            onClick={() => trackSocialClick('facebook')} 
                            className="social-link text-white/50 hover-accent-color transition-colors"
                            title="Facebook"
                        >
                            <SocialIcons.Facebook />
                        </a>
                    )}
                    {tt && (
                        <a 
                            href={tt} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            onClick={() => trackSocialClick('tiktok')} 
                            className="social-link text-white/50 hover-accent-color transition-colors"
                            title="TikTok"
                        >
                            <SocialIcons.TikTok />
                        </a>
                    )}
                    {yt && (
                        <a 
                            href={yt} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            onClick={() => trackSocialClick('youtube')} 
                            className="social-link text-white/50 hover-accent-color transition-colors"
                            title="YouTube"
                        >
                            <SocialIcons.Youtube />
                        </a>
                    )}
                </div>
            );
        };

        const updateDynamicFavicon = useCallback((color) => {
            const canvas = document.createElement('canvas'); canvas.width = 64; canvas.height = 64;
            const ctx = canvas.getContext('2d'); ctx.fillStyle = color;
            const r = 16;
            ctx.beginPath(); ctx.moveTo(r, 0); ctx.lineTo(64 - r, 0); ctx.quadraticCurveTo(64, 0, 64, r); ctx.lineTo(64, 64 - r); ctx.quadraticCurveTo(64, 64, 64 - r, 64); ctx.lineTo(r, 64); ctx.quadraticCurveTo(0, 64, 0, 64 - r); ctx.lineTo(0, r); ctx.quadraticCurveTo(0, 0, r, 0); ctx.fill(); ctx.fillStyle = 'white';
            ctx.beginPath(); ctx.moveTo(24, 18); ctx.lineTo(46, 32); ctx.lineTo(24, 46); ctx.fill();
            const link = document.getElementById('dynamic-favicon'); if (link) link.href = canvas.toDataURL('image/png');
        }, []);

        const handleRouting = useCallback((songList = songs) => {
            const hash = window.location.hash.replace('#', '');
            if (!hash || hash === 'home') { 
                setView('home'); 
                setSelectedSong(null); 
                setSelectedAlbum(null); 
            } else if (hash.startsWith('detail/')) { 
                const id = hash.split('/')[1]; 
                setView('detail'); 
                const song = songList.find(s => String(s.id) === String(id)); 
                if (song) setSelectedSong(song); 
            } else if (hash.startsWith('album/')) { 
                const albumName = decodeURIComponent(hash.split('/')[1]); 
                setView('album'); 
                setSelectedAlbum(albumName); 
            } else {
                setView(hash);
            }
        }, [songs]);

        useEffect(() => { 
            window.addEventListener('hashchange', () => handleRouting()); 
            return () => window.removeEventListener('hashchange', () => handleRouting()); 
        }, [handleRouting]);

        useEffect(() => {
            if (secretClicks >= 10) {
                window.location.href = 'admin.php';
            }
        }, [secretClicks]);

        // --- ZENTRALES VIEW TRACKING ---
        useEffect(() => {
            if (loading) return; 
            if (cookieConsent !== 'accepted' && cookieConsent !== 'essential') return;

            const currentHash = window.location.hash.replace('#', '') || 'home';
            
            fetch('api.php?action=logVisit', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ identifier: currentHash }) 
            }).catch(() => {});
        }, [view, selectedSong, selectedAlbum, cookieConsent, loading]);

        const fetchData = async () => {
            setLoading(true);
            try {
                const [sRes, setRes] = await Promise.all([
                    fetch('api.php?action=getSongs'), 
                    fetch('api.php?action=getSettings')
                ]);
                const sData = await sRes.json(); 
                const setData = await setRes.json();
                
                const validSongs = Array.isArray(sData) ? sData : []; 
                setSongs(validSongs);
                
                if (setData && typeof setData === 'object') {
                    if (setData.insta_url && !setData.instagram_url) {
                        setData.instagram_url = setData.insta_url;
                    }
                    
                    setSettings(prev => ({ ...prev, ...setData }));
                    if (setData.accent_color) {
                        document.documentElement.style.setProperty('--accent', setData.accent_color);
                        updateDynamicFavicon(setData.accent_color);
                    }
                }
                
                handleRouting(validSongs);
            } catch (err) { 
                console.error("Ladefehler:", err); 
            } finally { 
                setLoading(false); 
            }
        };

        const fetchMerch = async () => {
            if (!settings.spreadshirt_shop_name || settings.merch_enabled !== '1') return;
            setMerchLoading(true);
            try {
                const res = await fetch(`https://shop.spreadshirt.net/${settings.spreadshirt_shop_name}/shopData/v1/products?limit=12`);
                const data = await res.json(); 
                if (data && data.products) setMerch(data.products);
            } catch (err) { 
                console.error("Merch API Fehler:", err); 
            } finally { 
                setMerchLoading(false); 
            }
        };

        useEffect(() => { fetchData(); }, []);
        useEffect(() => { 
            if (view === 'merch' && merch.length === 0) fetchMerch(); 
        }, [view, settings.spreadshirt_shop_name, settings.merch_enabled]);

        const navigate = (target, song = null) => {
            setIsMenuOpen(false);
            const hash = song ? `detail/${song.id}` : target;
            window.location.hash = hash;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const navigateAlbum = (albumName) => { 
            setIsMenuOpen(false); 
            window.location.hash = `album/${encodeURIComponent(albumName)}`; 
            window.scrollTo({ top: 0, behavior: 'smooth' }); 
        };

        const trackSocialClick = (platform) => { 
            if (cookieConsent === 'accepted') {
                fetch('api.php?action=logVisit', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ identifier: `social_${platform}` }) 
                }).catch(() => {}); 
            }
        };

        const handleRate = async (id, stars) => { 
            try { 
                const res = await fetch('api.php?action=rate', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ id, stars }) 
                }); 
                const data = await res.json();
                if (data.success) {
                    fetchData(); 
                }
            } catch (err) { 
                console.error("Rating fehlgeschlagen"); 
            } 
        };

        const saveCookieConsent = (type) => { 
            localStorage.setItem('cookieConsent', type); 
            setCookieConsent(type); 
        };

        const getHomeItems = () => {
            const items = []; 
            const processedAlbums = new Set();
            songs.forEach(song => {
                if (song.album && song.album.trim() !== '') {
                    if (!processedAlbums.has(song.album)) {
                        processedAlbums.add(song.album);
                        const albumSongs = songs.filter(s => s.album === song.album);
                        items.push({ 
                            type: 'album', 
                            id: 'album_' + song.album, 
                            title: song.album, 
                            artist: song.artist, 
                            cover_url: song.cover_url, 
                            songs: albumSongs, 
                            trackCount: albumSongs.length,
                            is_out_now: albumSongs.some(s => parseInt(s.is_out_now) === 1),
                            deezer_presave_url: albumSongs.find(s => s.deezer_presave_url)?.deezer_presave_url,
                            release_date: albumSongs[0]?.release_date
                        });
                    }
                } else { 
                    items.push({ type: 'single', ...song }); 
                }
            });
            return items;
        };

        return (
            <div className="min-h-screen pb-20 selection:bg-accent selection:text-white">
                <nav className="fixed w-full top-0 z-50 glass border-b border-white/5 h-20" style={{ backgroundColor: settings.header_footer_color || undefined }}>
                    <div className="max-w-6xl mx-auto px-4 md:px-6 h-full flex justify-between items-center">
                        <h1 
                            className="font-heading text-xl md:text-2xl font-black text-accent tracking-tight cursor-pointer uppercase italic truncate max-w-[70%] px-1" 
                            onClick={() => {
                                setSecretClicks(prev => prev + 1);
                                navigate('home');
                            }}
                        >
                            {settings.artist_name}
                        </h1>
                        
                        <div className="relative flex items-center gap-2 md:gap-6">
                            {loading && <div className="spinner"></div>}
                            
                            <div className="hidden md:flex gap-6 items-center">
                                <button onClick={() => navigate('home')} className="font-heading text-[10px] tracking-widest uppercase font-black hover-accent-color transition-colors nav-link-base">Tracks</button>
                                {settings.merch_enabled === '1' && (
                                    <button onClick={() => navigate('merch')} className="font-heading text-[10px] tracking-widest uppercase font-black hover-accent-color transition-colors nav-link-base text-accent">Merch</button>
                                )}
                                <button onClick={() => navigate('audio')} className="font-heading text-[10px] tracking-widest uppercase font-black hover-accent-color transition-colors nav-link-base">Hörproben</button>
                                <button onClick={() => navigate('about')} className="font-heading text-[10px] tracking-widest uppercase font-black hover-accent-color transition-colors nav-link-base">About</button>
                                <div className="h-4 w-px bg-white/10 mx-2"></div>
                                <LocalSocialNavLinks settings={settings} trackSocialClick={trackSocialClick} />
                            </div>

                            <button onClick={() => setIsMenuOpen(!isMenuOpen)} className="md:hidden font-heading text-[10px] tracking-widest uppercase font-black hover-accent-color p-2">
                                {isMenuOpen ? '?' : 'Menü'}
                            </button>

                            {isMenuOpen && (
                                <div className="absolute right-0 top-full mt-4 w-64 glass rounded-[2rem] py-4 shadow-2xl border border-white/10 animate-in fade-in slide-in-from-top-2 origin-top-right">
                                    <button onClick={() => navigate('home')} className="w-full text-left px-8 py-4 hover:bg-white/5 font-bold uppercase text-[10px] block hover-accent-color">Tracks Katalog</button>
                                    {settings.merch_enabled === '1' && (
                                        <button onClick={() => navigate('merch')} className="w-full text-left px-8 py-4 hover:bg-white/5 font-bold uppercase text-[10px] block text-accent italic hover-accent-color">Merch Shop</button>
                                    )}
                                    <button onClick={() => navigate('audio')} className="w-full text-left px-8 py-4 hover:bg-white/5 font-bold uppercase text-[10px] block hover-accent-color">Hörproben</button>
                                    <button onClick={() => navigate('about')} className="w-full text-left px-8 py-4 hover:bg-white/5 font-bold uppercase text-[10px] block hover-accent-color">Biografie</button>
                                    <button onClick={() => navigate('impressum')} className="w-full text-left px-8 py-4 hover:bg-white/5 text-slate-500 uppercase text-[10px] block hover-accent-color">Rechtliches</button>
                                    <div className="border-t border-white/5 mt-2 pt-4 px-8 pb-2 flex justify-center">
                                        <LocalSocialNavLinks settings={settings} trackSocialClick={trackSocialClick} />
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </nav>

                <main className="pt-32 px-4 md:px-6 max-w-6xl mx-auto min-h-[60vh]">
                    {view === 'home' && <HomeView items={getHomeItems()} settings={settings} navigate={navigate} navigateAlbum={navigateAlbum} trackSocialClick={trackSocialClick} />}
                    {view === 'album' && selectedAlbum && <AlbumView albumName={selectedAlbum} songs={songs.filter(s => s.album === selectedAlbum)} settings={settings} navigate={navigate} />}
                    {view === 'detail' && selectedSong && <DetailView song={selectedSong} settings={settings} navigate={navigate} navigateAlbum={navigateAlbum} handleRate={handleRate} />}
                    {view === 'merch' && <MerchView merch={merch} merchLoading={merchLoading} />}
                    {view === 'audio' && <AudioView songs={songs} navigate={navigate} />}
                    {view === 'about' && <AboutView settings={settings} trackSocialClick={trackSocialClick} />}
                    {view === 'impressum' && <LegalView type="impressum" title="Impressum" settings={settings} />}
                    {view === 'privacy' && <LegalView type="privacy" title="Datenschutz" settings={settings} content={
                        <div className="space-y-6">
                            <h3 className="text-white text-xl font-bold uppercase italic tracking-tight">Cookies & Tracking</h3>
                            <p className="text-slate-400 leading-relaxed">Diese Website nutzt Cookies zur Verbesserung der Nutzererfahrung und zur Erfassung anonymer Nutzungsstatistiken. Wir tracken Besuche auf Songs und sozialen Plattformen nur, wenn du dem zustimmst.</p>
                            <div className="p-8 rounded-[2rem] bg-white/[0.02] border border-white/10">
                                <p className="text-[10px] font-black uppercase text-slate-500 mb-2 tracking-widest">Status deiner Einwilligung:</p>
                                <p className="text-accent font-bold uppercase italic">{cookieConsent === 'accepted' ? 'Alle Cookies akzeptiert' : (cookieConsent === 'essential' ? 'Nur essenzielle Cookies' : 'Noch keine Entscheidung getroffen')}</p>
                                <button onClick={() => saveCookieConsent(null)} className="mt-6 text-[9px] font-black uppercase text-slate-500 border border-white/10 px-6 py-2 rounded-xl hover:bg-white/5 transition-colors">Einstellungen zurücksetzen</button>
                            </div>
                        </div>
                    } />}
                </main>

                <footer className="py-24 text-center border-t border-white/5 bg-slate-950/40 mt-32" style={{ backgroundColor: settings.header_footer_color || undefined }}>
                    <div className="max-w-xl mx-auto px-10">
                        <p 
                            className="font-heading text-[10px] tracking-[1.5em] text-slate-800 uppercase font-black cursor-pointer hover-accent-color transition-colors"
                            onClick={() => setSecretClicks(prev => prev + 1)}
                            title="Admin Login"
                        >
                            &copy; 2026 {settings.artist_name} MUSIC
                        </p>
                        <div className="flex justify-center gap-6 mt-12">
                            <button onClick={() => navigate('about')} className="text-[9px] font-black uppercase tracking-widest text-slate-600 hover-accent-color transition-colors nav-link-base">Biografie</button>
                            <button onClick={() => navigate('impressum')} className="text-[9px] font-black uppercase tracking-widest text-slate-600 hover-accent-color transition-colors nav-link-base">Impressum</button>
                            <button onClick={() => navigate('privacy')} className="text-[9px] font-black uppercase tracking-widest text-slate-600 hover-accent-color transition-colors nav-link-base">Datenschutz</button>
                        </div>
                    </div>
                </footer>

                {!cookieConsent && <CookieBanner onAccept={() => saveCookieConsent('accepted')} onEssential={() => saveCookieConsent('essential')} onNavigate={(v) => navigate(v)} />}
            </div>
        );
    };

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);

<?php require_once 'footer.php'; ?>