<?php
// views.php - Views für die öffentliche Webplayer-Oberfläche
?>

    // --- VIEW: HOME (DISCOGRAPHIE) ---
    const HomeView = ({ items, settings, navigate, navigateAlbum, trackSocialClick }) => {
        const globalTikTok = settings.global_tiktok_embed;
        // Wir prüfen, ob ein Instagram-Post statt TikTok eingebettet wurde
        const isInstagramEmbed = globalTikTok && globalTikTok.includes('instagram.com');

        React.useEffect(() => {
            if (globalTikTok) {
                if (window.twttr && window.twttr.widgets) {
                    window.twttr.widgets.load();
                }
                const scriptId = isInstagramEmbed ? 'instagram-embed-script' : 'tiktok-embed-script';
                if (!document.getElementById(scriptId)) {
                    const script = document.createElement('script');
                    script.id = scriptId;
                    script.src = isInstagramEmbed ? 'https://www.instagram.com/embed.js' : 'https://www.tiktok.com/embed.js';
                    script.async = true;
                    document.body.appendChild(script);
                } else if (isInstagramEmbed && window.instgrm) {
                    window.instgrm.Embeds.process();
                }
            }
        }, [globalTikTok, isInstagramEmbed]);

        return (
            <div className="animate-in fade-in duration-700">
                <h2 className="font-heading text-2xl sm:text-4xl mb-12 sm:mb-16 text-center uppercase tracking-[0.2em] sm:tracking-[0.4em] font-black opacity-20 italic">
                    Discographie
                </h2>
                
                <div className="flex flex-col lg:flex-row gap-10 lg:gap-16 items-start">
                    {/* SOCIAL SIDEBAR */}
                    {globalTikTok && (
                        <div className="w-full lg:w-80 xl:w-96 shrink-0 space-y-8 animate-in slide-in-from-left-4 duration-500">
                            <h3 className="font-heading text-xs uppercase tracking-[0.3em] text-accent mb-6 opacity-60 text-center lg:text-left">
                                Featured Content
                            </h3>
                            <div className="glass p-4 rounded-[2rem] border border-white/5 shadow-2xl bg-slate-900/40">
                                <div className="mb-4 flex items-center justify-between px-2">
                                    <div className="flex items-center gap-3 min-w-0">
                                        <div className="w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center border border-accent/30 text-accent text-sm">
                                            {isInstagramEmbed ? <SocialIcons.Instagram /> : <SocialIcons.TikTok />}
                                        </div>
                                        <div className="min-w-0">
                                            <p className="text-[10px] font-black uppercase text-white truncate">{settings.artist_name || 'TheStealth'}</p>
                                            <p className="text-[8px] font-bold uppercase text-accent tracking-widest">Official Channel</p>
                                        </div>
                                    </div>
                                    {/* Link zum Profil direkt in der Sidebar */}
                                    <a 
                                        href={isInstagramEmbed ? settings.instagram_url : settings.tiktok_url} 
                                        target="_blank" 
                                        className="text-[9px] font-black uppercase text-slate-500 hover:text-accent transition-colors"
                                        onClick={() => trackSocialClick(isInstagramEmbed ? 'instagram' : 'tiktok')}
                                    >
                                        Profil ?
                                    </a>
                                </div>
                                <div className="rounded-2xl overflow-hidden bg-black/20 flex justify-center min-h-[400px]">
                                    <div 
                                        className="w-full"
                                        dangerouslySetInnerHTML={{ __html: globalTikTok }} 
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* SONG GRID */}
                    <div className="flex-1 w-full">
                        <div className={`grid grid-cols-1 ${globalTikTok ? 'md:grid-cols-2' : 'md:grid-cols-2 lg:grid-cols-3'} gap-6 sm:gap-10`}>
                            {items.map(item => (
                                <SongTile 
                                    key={item.id} 
                                    item={item} 
                                    artistName={settings.artist_name}
                                    onClick={() => item.type === 'album' ? navigateAlbum(item.title) : navigate('detail', item)} 
                                />
                            ))}
                        </div>
                        {items.length === 0 && (
                            <div className="text-center py-20 opacity-30">
                                <p className="font-heading uppercase tracking-widest">Keine Tracks gefunden</p>
                            </div>
                        )}
                    </div>
                </div>

                <NewsletterBox />
            </div>
        );
    };

    // --- VIEW: ALBUM ---
    const AlbumView = ({ albumName, songs, settings, navigate }) => (
        <div className="animate-in fade-in slide-in-from-bottom-4">
            <button onClick={() => navigate('home')} className="mb-12 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-accent transition-colors">
                {"\u2190 Zur\u00fcck zur \u00dcbersicht"}
            </button>
            <div className="text-center mb-16">
                <h2 className="font-heading text-4xl sm:text-6xl font-black italic tracking-tighter mb-4">{albumName}</h2>
                <p className="text-accent text-xl font-light uppercase tracking-widest">Album Collection</p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-10">
                {songs.map(s => (
                    <SongTile 
                        key={s.id} 
                        item={{...s, type: 'single'}} 
                        artistName={settings.artist_name}
                        onClick={() => navigate('detail', s)} 
                    />
                ))}
            </div>
        </div>
    );

    // --- VIEW: DETAIL ---
    const DetailView = ({ song, settings, navigate, navigateAlbum, handleRate }) => (
        <div className="animate-in fade-in slide-in-from-bottom-4">
            <button onClick={() => navigate('home')} className="mb-12 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-accent transition-colors">
                {"\u2190 Zur\u00fcck"}
            </button>
            <div className="flex flex-col lg:flex-row gap-16">
                <div className="lg:w-1/2">
                    <div className="relative rounded-[3.5rem] overflow-hidden shadow-2xl mb-10 border border-white/5 aspect-square bg-slate-900">
                        {parseInt(song.is_out_now) === 1 && (
                            <div className="absolute top-6 left-6 bg-white text-black px-4 py-1.5 text-[10px] font-black uppercase tracking-widest z-20 rounded-xl shadow-2xl shadow-white/10">Out Now</div>
                        )}
                        
                        {/* Coming Soon Badge (Blinkend) */}
                        {song.deezer_presave_url && (
                            <div className="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                                <div className="bg-black/60 backdrop-blur-md text-accent border border-accent/50 px-6 py-3 rounded-2xl text-[12px] font-black uppercase tracking-[0.3em] animate-pulse shadow-lg shadow-accent/20">
                                    Coming Soon
                                </div>
                            </div>
                        )}

                        <ShareButtons song={song} artist={song.artist || settings.artist_name} />
                        <img src={song.cover_url} className="w-full h-full object-cover relative z-10" alt="" />
                    </div>
                    <div className="glass p-10 rounded-[2.5rem] space-y-6">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h4 className="text-[10px] font-black uppercase text-accent tracking-widest mb-1">Rating & Story</h4>
                                <div className="flex items-center gap-2">
                                    <span className="text-lg font-black text-white">{parseFloat(song.rating || 0).toFixed(1)}</span>
                                    <span className="text-[10px] text-slate-500 uppercase font-black tracking-tighter mt-1">/ 5.0 ({song.rating_count || 0})</span>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <StarRatingGroup rating={parseFloat(song.rating || 0)} size={24} interactive={true} onRate={(stars) => handleRate(song.id, stars)} />
                            </div>
                        </div>
                        <p className="text-slate-300 italic text-lg leading-relaxed font-serif tracking-tight">"{song.description}"</p>
                        <audio controls className="w-full rounded-full mt-6" src={song.mp3_url}></audio>
                    </div>
                </div>
                <div className="lg:w-1/2 flex flex-col justify-center">
                    {song.album && (
                        <div onClick={() => navigateAlbum(song.album)} className="inline-block bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full mb-6 cursor-pointer transition-all w-fit">
                            <span className="text-[10px] font-black uppercase tracking-widest text-slate-300">Album: <span className="text-white">{song.album}</span></span>
                        </div>
                    )}
                    <h2 className="font-heading text-4xl sm:text-6xl mb-2 font-black tracking-tighter italic">{song.title}</h2>
                    <p className="text-accent text-2xl sm:text-3xl mb-12 font-light italic">{song.artist || settings.artist_name}</p>
                    <div className="space-y-4">
                        {[
                            { k: 'spotify_url', n: 'Spotify', icon: 'https://cdn-icons-png.flaticon.com/512/174/174872.png' },
                            { k: 'apple_music_url', n: 'Apple Music', icon: 'https://upload.wikimedia.org/wikipedia/commons/5/5f/Apple_Music_icon.svg' },
                            { k: 'soundcloud_url', n: 'SoundCloud', icon: 'https://cdn-icons-png.flaticon.com/512/145/145809.png' },
                            { k: 'deezer_url', n: 'Deezer', icon: 'https://newsroom-deezer.com/wp-content/uploads/2023/11/deezer_logo_picto.png' }
                        ].map(p => song[p.k] && (
                            <a key={p.k} href={song[p.k]} target="_blank" className="flex items-center gap-6 p-6 rounded-3xl bg-white/[0.03] border border-white/10 hover:border-accent transition-all font-bold uppercase tracking-widest group">
                                <img src={p.icon} className="w-8 h-8 object-contain opacity-50 group-hover:opacity-100 transition-opacity" alt="" />
                                <span className="text-xl">{p.n}</span>
                            </a>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );

    // --- VIEW: HÖRPROBEN ---
    const AudioView = ({ songs, navigate }) => (
        <div className="max-w-3xl mx-auto space-y-12">
            <h2 className="font-heading text-4xl mb-16 text-center text-accent uppercase tracking-widest font-black opacity-30 italic">{"H\u00f6rproben"}</h2>
            {songs.map(s => (
                <div key={s.id} className="glass p-10 rounded-[3rem] border border-white/5 transition-all hover:border-accent">
                    <div className="flex flex-col md:flex-row items-center gap-10">
                        <div className="relative w-32 h-32 rounded-3xl overflow-hidden shadow-2xl border border-white/10 cursor-pointer group hover:scale-105 transition-all duration-300 shrink-0 bg-slate-800" onClick={() => navigate('detail', s)}>
                            <img src={s.cover_url} className="w-full h-full object-cover group-hover:opacity-40 transition-opacity" alt="" />
                            <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span className="text-[10px] font-black uppercase text-white tracking-widest italic">Details</span>
                            </div>
                        </div>
                        <div className="flex-1 w-full text-center md:text-left">
                            <h3 className="font-heading text-2xl mb-2 uppercase font-black tracking-tighter cursor-pointer hover:text-accent transition-colors italic inline-block" onClick={() => navigate('detail', s)}>
                                {s.title}
                            </h3>
                            <audio controls className="w-full mt-4" src={s.mp3_url}></audio>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );

    // --- VIEW: MERCH ---
    const MerchView = ({ merch, merchLoading }) => (
        <div className="animate-in fade-in">
            <h2 className="font-heading text-4xl mb-12 text-center text-accent uppercase tracking-widest font-black opacity-30 italic">Merch Shop</h2>
            {merchLoading ? <div className="flex justify-center py-20"><div className="spinner"></div></div> : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    {merch.map(p => (
                        <a key={p.id} href={p.url} target="_blank" className="glass p-6 rounded-[2rem] merch-card block group hover:border-accent transition-all">
                            <div className="aspect-square rounded-2xl overflow-hidden bg-white mb-4"><img src={p.image.url} className="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110" alt="" /></div>
                            <h3 className="font-bold text-sm truncate">{p.name}</h3>
                            <p className="text-accent font-black mt-1">{p.price.amount} {p.price.currency}</p>
                        </a>
                    ))}
                    {merch.length === 0 && <p className="col-span-full text-center text-slate-500 py-20">Momentan keine Artikel geladen.</p>}
                </div>
            )}
        </div>
    );

    // --- VIEW: ABOUT ---
    const AboutView = ({ settings, trackSocialClick }) => (
        <div className="animate-in fade-in slide-in-from-bottom-4 max-w-4xl mx-auto">
            <h2 className="font-heading text-4xl mb-12 text-center text-accent uppercase tracking-widest font-black opacity-30 italic">About {settings.artist_name || 'TheStealth'}</h2>
            <div className="flex flex-col md:flex-row gap-12 items-start">
                {settings.about_photo_url && (
                    <div className="w-full md:w-1/3">
                        <div className="relative rounded-[3rem] overflow-hidden shadow-2xl border border-white/10 md:rotate-3 hover:rotate-0 transition-all duration-500 group">
                            <div className="absolute inset-0 bg-accent/20 mix-blend-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <img src={settings.about_photo_url} className="w-full h-auto object-cover" alt="Artist Profile" onError={(e) => {e.target.style.display='none'}} />
                        </div>
                    </div>
                )}
                <div className={`w-full ${settings.about_photo_url ? 'md:w-2/3' : 'md:w-full'}`}>
                    <div className="glass p-8 md:p-12 rounded-[3rem] border border-white/5 relative overflow-hidden">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-accent/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                        <div className="relative z-10 leading-relaxed text-lg text-slate-300 whitespace-pre-line font-medium">
                            {settings.about_text ? settings.about_text : <span className="italic text-slate-600">Noch keine Biografie hinterlegt.</span>}
                        </div>
                        <div className="mt-8 pt-8 border-t border-white/5 flex gap-6 opacity-50 hover:opacity-100 transition-opacity">
                            <SocialNavLinks settings={settings} trackSocialClick={trackSocialClick} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );

    // --- VIEW: IMPRESSUM & PRIVACY ---
    const LegalView = ({ title, content, settings, type }) => (
        <div className="max-w-2xl mx-auto glass p-16 rounded-[4rem]">
            <h2 className="font-heading text-4xl mb-12 text-accent uppercase font-black tracking-widest italic">{title}</h2>
            {type === 'impressum' ? (
                <div className="space-y-4">
                    <p className="text-white text-2xl font-black uppercase italic tracking-tighter mb-4">{settings.legal_name || settings.artist_name}</p>
                    <p className="text-slate-400 leading-relaxed font-medium whitespace-pre-line text-lg">{settings.legal_address}</p>
                    <div className="h-px bg-white/5 my-8"></div>
                    <p className="text-slate-500 text-sm font-bold uppercase tracking-widest">Email: <a href={`mailto:${settings.legal_email}`} className="hover:text-accent transition-colors underline decoration-accent/30">{settings.legal_email}</a></p>
                </div>
            ) : (
                <div className="text-slate-300 leading-relaxed">
                    {content}
                </div>
            )}
        </div>
    );

    // --- HELPER COMPONENT: NEWSLETTER BOX ---
    const NewsletterBox = () => {
        const [email, setEmail] = React.useState('');
        const [status, setStatus] = React.useState('idle');
        const [pref, setPref] = React.useState('all'); // 'all' oder 'info'

        const handleSubscribe = async () => {
            if (!email) return;
            setStatus('loading');
            try {
                const res = await fetch('api.php?action=subscribe', {
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ email: email, preference: pref })
                });
                const d = await res.json();
                if (d.success) { 
                    setEmail(''); 
                    setStatus('success');
                } else {
                    setStatus('error');
                }
            } catch(e) { 
                console.error(e); 
                setStatus('error'); 
            }
        };

        return (
            <div className="mt-24 p-10 glass rounded-[3rem] text-center max-w-2xl mx-auto border border-white/5 relative overflow-hidden group">
                <div className="absolute -top-10 -right-10 w-40 h-40 bg-accent/5 rounded-full blur-3xl group-hover:bg-accent/10 transition-all"></div>
                <h3 className="font-heading text-xl mb-4 uppercase italic tracking-[0.2em] font-black">Newsletter</h3>
                <p className="text-slate-400 text-xs mb-8 uppercase tracking-widest font-bold">Keine Releases & Updates mehr verpassen.</p>
                
                {/* PREFERENCE TOGGLE */}
                <div className="flex justify-center gap-2 mb-8">
                    <button 
                        onClick={() => setPref('all')}
                        className={`px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all border ${pref === 'all' ? 'bg-accent text-white border-accent shadow-lg shadow-accent/20' : 'bg-white/5 text-slate-500 border-white/5 hover:bg-white/10'}`}
                    >
                        Alles
                    </button>
                    <button 
                        onClick={() => setPref('info')}
                        className={`px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all border ${pref === 'info' ? 'bg-accent text-white border-accent shadow-lg shadow-accent/20' : 'bg-white/5 text-slate-500 border-white/5 hover:bg-white/10'}`}
                    >
                        Nur Song Releases
                    </button>
                </div>

                <div className="flex flex-col sm:flex-row gap-3">
                    <input 
                        className="flex-1 bg-slate-950/50 p-5 rounded-2xl border border-white/10 text-sm outline-none focus:border-accent transition-all" 
                        placeholder="Deine Email Adresse" 
                        value={email} 
                        onChange={e => setEmail(e.target.value)} 
                    />
                    <button 
                        onClick={handleSubscribe} 
                        disabled={status === 'loading'}
                        className="bg-accent text-white px-8 py-5 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg shadow-accent/20 hover:scale-105 active:scale-95 transition-all disabled:opacity-50"
                    >
                        {status === 'loading' ? 'Lade...' : status === 'success' ? 'Abonniert!' : 'Abonnieren'}
                    </button>
                </div>
                {status === 'error' && <p className="text-red-500 text-[10px] font-black uppercase mt-4 animate-bounce">Fehler beim Abonnieren.</p>}
            </div>
        );
    }