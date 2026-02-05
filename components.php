<?php
// components.php - Gemeinsame UI-Elemente für den Player
// Diese Datei wird in der index.php inkludiert und läuft im Babel-Context von React.
?>

    // --- SOCIAL LINKS ---
    // Zeigt die Social-Media-Icons basierend auf den globalen Einstellungen an
    const SocialNavLinks = ({ settings, trackSocialClick }) => (
        <div className="flex items-center gap-4">
            {settings.instagram_url && (
                <a href={settings.instagram_url} target="_blank" onClick={() => trackSocialClick('instagram')} className="social-link text-white/50 hover:text-white transition-colors">
                    <SocialIcons.Instagram />
                </a>
            )}
            {settings.facebook_url && (
                <a href={settings.facebook_url} target="_blank" onClick={() => trackSocialClick('facebook')} className="social-link text-white/50 hover:text-white transition-colors">
                    <SocialIcons.Facebook />
                </a>
            )}
            {settings.tiktok_url && (
                <a href={settings.tiktok_url} target="_blank" onClick={() => trackSocialClick('tiktok')} className="social-link text-white/50 hover:text-white transition-colors">
                    <SocialIcons.TikTok />
                </a>
            )}
            {settings.youtube_url && (
                <a href={settings.youtube_url} target="_blank" onClick={() => trackSocialClick('youtube')} className="social-link text-white/50 hover:text-white transition-colors">
                    <SocialIcons.Youtube />
                </a>
            )}
        </div>
    );

    // --- SHARE BUTTONS ---
    // Erweitertes Share-Menü mit WhatsApp, Facebook und Link-Kopie
    const ShareButtons = ({ song, artist }) => {
        const [isOpen, setIsOpen] = useState(false);
        const [copied, setCopied] = useState(false);
        const menuRef = React.useRef(null);

        const shareUrl = window.location.origin + window.location.pathname + '#detail/' + song.id;
        const shareText = encodeURIComponent(`Hör dir "${song.title}" von ${artist} an!`);
        const encodedUrl = encodeURIComponent(shareUrl);

        React.useEffect(() => {
            const handleClickOutside = (event) => {
                if (menuRef.current && !menuRef.current.contains(event.target)) setIsOpen(false);
            };
            document.addEventListener("mousedown", handleClickOutside);
            return () => document.removeEventListener("mousedown", handleClickOutside);
        }, []);

        const copyToClipboard = () => {
            const el = document.createElement('textarea');
            el.value = shareUrl;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            setCopied(true);
            setTimeout(() => { setCopied(false); setIsOpen(false); }, 1500);
        };

        return (
            <div className="absolute top-6 right-6 z-40" ref={menuRef}>
                <button 
                    onClick={() => setIsOpen(!isOpen)} 
                    className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 shadow-2xl ${isOpen ? 'bg-accent text-white rotate-90' : 'bg-black/40 backdrop-blur-md border border-white/10 text-white hover:scale-110'}`}
                >
                    <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                </button>
                {isOpen && (
                    <div className="absolute top-14 right-0 w-48 bg-[#1a2030] rounded-3xl p-3 border border-white/10 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                        <div className="space-y-1">
                            <a href={`https://api.whatsapp.com/send?text=${shareText}%20${encodedUrl}`} target="_blank" className="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-[#25D366]/10 text-slate-300 hover:text-[#25D366] transition-all"><span className="text-[10px] font-black uppercase tracking-widest">WhatsApp</span></a>
                            <a href={`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`} target="_blank" className="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-[#1877F2]/10 text-slate-300 hover:text-[#1877F2] transition-all"><span className="text-[10px] font-black uppercase tracking-widest">Facebook</span></a>
                            <button onClick={copyToClipboard} className={`w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all ${copied ? 'bg-accent/20 text-accent' : 'hover:bg-white/5 text-slate-300'}`}><span className="text-[10px] font-black uppercase tracking-widest">{copied ? 'Kopiert!' : 'Link kopieren'}</span></button>
                        </div>
                    </div>
                )}
            </div>
        );
    };

    // --- SONG TILE (KACHEL) ---
    // Die zentrale Komponente für die Discographie-Anzeige
    const SongTile = ({ item, artistName, onClick }) => {
        
        const renderReleaseDate = () => {
            if (!item.release_date) return null;
            const dateObj = new Date(item.release_date);
            const dateStr = dateObj.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit' }) + '.';
            return (
                <span className="text-[10px] font-black uppercase tracking-widest text-white opacity-90">
                    {dateStr}
                </span>
            );
        };

        // Fall 1: Album-Kachel (Sammlung von Tracks mit Fächer-Effekt)
        if (item.type === 'album') {
            const uniqueCovers = item.songs 
                ? [...new Set(item.songs.map(s => s.cover_url))].filter(Boolean)
                : [item.cover_url];
            
            const frontCover = uniqueCovers[0] || item.cover_url;
            const middleCover = uniqueCovers[1] || frontCover;
            const backCover = uniqueCovers[2] || (uniqueCovers.length > 1 ? uniqueCovers[1] : frontCover);

            return (
                <div onClick={onClick} className="group bg-[#1a2030] p-6 rounded-[2.5rem] cursor-pointer hover:-translate-y-2 transition-all duration-500 border border-white/5 hover:border-accent relative flex flex-col h-full">
                    
                    {/* Fächer-Effekt: Zwei dekorative Ebenen direkt hinter dem Hauptcover */}
                    <div className="relative aspect-square mb-5">
                        {/* Hinterste Ebene */}
                        <div className="absolute inset-2 bg-slate-700 rounded-2xl rotate-6 border border-white/10 opacity-90 group-hover:rotate-12 transition-transform duration-500 shadow-xl overflow-hidden">
                             <img src={backCover} className="w-full h-full object-cover opacity-40" alt="" />
                        </div>
                        
                        {/* Mittlere Ebene */}
                        <div className="absolute inset-1 bg-slate-600 rounded-2xl -rotate-3 border border-white/10 opacity-95 group-hover:-rotate-6 transition-transform duration-500 shadow-2xl overflow-hidden">
                             <img src={middleCover} className="w-full h-full object-cover opacity-60" alt="" />
                        </div>
                        
                        {/* Haupt-Cover Ebene */}
                        <div className="relative z-10 w-full h-full rounded-2xl overflow-hidden shadow-2xl bg-slate-800 border border-white/10">
                            <div className="absolute top-3 right-3 bg-accent text-white px-3 py-1 text-[9px] font-black uppercase tracking-widest z-20 rounded-lg shadow-lg">Album</div>
                            
                            {/* Coming Soon Badge (Blinkend) */}
                            {item.deezer_presave_url && (
                                <div className="absolute inset-0 flex items-center justify-center z-30 pointer-events-none">
                                    <div className="bg-black/60 backdrop-blur-md text-accent border border-accent/50 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] animate-pulse shadow-lg shadow-accent/20">
                                        Coming Soon
                                    </div>
                                </div>
                            )}

                            <img src={frontCover} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt={item.title} />
                            <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-30">
                                 <span className="text-[10px] font-black uppercase tracking-[0.3em] text-white">Ansehen</span>
                            </div>
                        </div>
                    </div>

                    <h3 className="font-heading text-lg truncate mb-1 leading-tight text-white">{item.title}</h3>
                    <div className="flex justify-between items-end mt-auto pt-2">
                        <p className="text-slate-500 text-[10px] font-black uppercase tracking-widest italic">
                            Album {" \u2022 "} {item.trackCount || '?'} {item.trackCount === 1 ? 'Track' : 'Tracks'}
                        </p>
                        <div className="flex items-center gap-3">
                            {renderReleaseDate()}
                            <div className="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        // Fall 2: Einzelner Song (Single)
        return (
            <div onClick={onClick} className="group bg-[#1a2030] p-6 rounded-[2.5rem] cursor-pointer hover:-translate-y-2 transition-all duration-500 border border-white/5 hover:border-accent relative flex flex-col h-full">
                <div className="aspect-square rounded-2xl overflow-hidden mb-5 shadow-xl bg-slate-800 relative">
                    {parseInt(item.is_out_now) === 1 && (
                        <div className="absolute top-3 right-3 bg-white text-black px-3 py-1 text-[9px] font-black uppercase tracking-widest z-10 rounded-lg shadow-lg">Out Now</div>
                    )}
                    
                    {/* Coming Soon Badge (Blinkend) */}
                    {item.deezer_presave_url && (
                        <div className="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                            <div className="bg-black/60 backdrop-blur-md text-accent border border-accent/50 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] animate-pulse shadow-lg shadow-accent/20">
                                Coming Soon
                            </div>
                        </div>
                    )}

                    <img src={item.cover_url} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt={item.title} />
                </div>
                <h3 className="font-heading text-lg truncate mb-1 leading-tight text-white">{item.title}</h3>
                <div className="flex items-center gap-1 mb-2">
                    <StarRatingGroup rating={parseFloat(item.rating || 0)} size={14} />
                    <span className="text-[10px] text-slate-400 font-bold ml-1">{parseFloat(item.rating || 0).toFixed(1)}</span>
                </div>
                <div className="flex justify-between items-end mt-auto pt-2">
                    <p className="text-slate-500 text-[10px] font-black uppercase tracking-widest italic truncate max-w-[120px]">{item.artist || artistName}</p>
                    <div className="flex items-center gap-3">
                        {renderReleaseDate()}
                        <div className="w-8 h-8 rounded-lg border border-white/10 flex items-center justify-center text-white/30 group-hover:border-accent group-hover:text-accent transition-all">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </div>
                    </div>
                </div>
            </div>
        );
    };