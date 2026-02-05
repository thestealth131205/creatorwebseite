<?php
// admin_views.php - Vollständige Views für Dashboard, Songs, Settings, Media & System
?>

// --- CORE UI COMPONENTS (Sicherheits-Definition) ---
// Wir definieren diese hier erneut, falls admin_components.php nicht korrekt geladen wurde
if (typeof AdminInput === 'undefined') {
    window.AdminInput = ({ label, value, onChange, type = "text", placeholder = "" }) => (
        <div className="space-y-2">
            <label className="text-[10px] font-black uppercase text-slate-500 tracking-widest ml-1">{label}</label>
            {type === 'textarea' ? (
                <textarea 
                    className="w-full bg-slate-950/50 p-4 rounded-2xl border border-white/10 text-sm focus:border-accent outline-none min-h-[120px] transition-all focus:bg-slate-950 font-medium text-slate-300 placeholder:text-slate-800 shadow-inner" 
                    value={value || ''} 
                    onChange={onChange} 
                    placeholder={placeholder} 
                />
            ) : (
                <input 
                    type={type} 
                    className="w-full bg-slate-950/50 p-4 rounded-2xl border border-white/10 text-sm focus:border-accent outline-none transition-all focus:bg-slate-950 font-medium text-slate-300 placeholder:text-slate-800 h-14 shadow-inner" 
                    value={value || ''} 
                    onChange={onChange} 
                    placeholder={placeholder} 
                />
            )}
        </div>
    );
}

if (typeof ToggleSwitch === 'undefined') {
    window.ToggleSwitch = ({ label, checked, onChange }) => (
        <div className="flex items-center justify-between bg-slate-950/30 p-6 rounded-2xl border border-white/5 hover:border-white/10 transition-colors shadow-inner">
            <span className="text-[11px] font-black uppercase text-slate-400 tracking-widest">{label}</span>
            <div 
                className={`w-14 h-7 rounded-full cursor-pointer transition-all relative ${checked ? 'bg-accent shadow-[0_0_15px_rgba(147,213,7,0.3)]' : 'bg-slate-800'}`} 
                onClick={() => onChange(!checked)}
            >
                <div className={`absolute top-1 w-5 h-5 rounded-full bg-white shadow-sm transition-all ${checked ? 'left-8' : 'left-1'}`}></div>
            </div>
        </div>
    );
}

// --- 1. KOMPLEXER SONG EDITOR MODAL ---
const SongEditForm = ({ song, onSave, onCancel }) => {
    const [form, setForm] = useState(song || { 
        title: '', artist: 'TheStealth', description: '', is_out_now: 0,
        release_date: '', album: '', isrc_code: '', spotify_uri: '',
        spotify_url: '', apple_music_url: '', deezer_url: '', youtube_url: '', 
        soundcloud_url: '', amazon_music_url: '', spotify_artist_url: '', deezer_presave_url: '',
        facebook_url: '', instagram_url: '', tiktok_url: '', cover_url: '', mp3_url: ''
    });
    
    const [uploading, setUploading] = useState(false);
    const [activeTab, setActiveTab] = useState('general');

    const handleChange = (k, v) => setForm(prev => ({ ...prev, [k]: v }));

    const handleFile = async (e, type) => {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 50 * 1024 * 1024) { 
            if(!window.confirm("Achtung: Die Datei ist größer als 50MB. Fortfahren?")) return;
        }

        setUploading(true);
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await fetch('api.php?action=uploadMedia', {
                method: 'POST',
                headers: { 'X-Admin-Code': SERVER_ADMIN_CODE },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                handleChange(type === 'cover' ? 'cover_url' : 'mp3_url', data.url);
            }
        } catch (err) { console.error("Upload fehlgeschlagen"); }
        setUploading(false);
    };

    const tabs = [
        { id: 'general', label: 'Stammdaten', icon: <Icons.Songs /> },
        { id: 'links', label: 'Streaming & Social', icon: <Icons.Dashboard /> },
        { id: 'media', label: 'Dateien & ISRC', icon: <Icons.System /> }
    ];

    return (
        <div className="fixed inset-0 z-[150] flex items-center justify-center p-4 md:p-10 animate-in fade-in duration-300">
            <div className="absolute inset-0 bg-slate-950/95 backdrop-blur-2xl" onClick={onCancel}></div>
            <div className="glass w-full max-w-5xl max-h-[95vh] overflow-hidden rounded-[3.5rem] border border-white/10 flex flex-col relative z-10 shadow-[0_0_120px_rgba(0,0,0,0.8)]">
                
                <header className="p-10 border-b border-white/5 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white/[0.02]">
                    <div className="flex items-center gap-6">
                        <div className="w-16 h-16 rounded-3xl bg-accent/10 flex items-center justify-center text-accent shadow-inner">
                            <Icons.Songs />
                        </div>
                        <div>
                            <h2 className="text-3xl font-black uppercase tracking-tighter leading-none">{form.id ? 'Release bearbeiten' : 'Neuen Release anlegen'}</h2>
                            <p className="text-[11px] text-slate-500 font-bold uppercase tracking-[0.4em] mt-3">{form.title || 'Untitled Track'}</p>
                        </div>
                    </div>
                    <div className="flex gap-4 w-full md:w-auto">
                        <button onClick={onCancel} className="flex-1 md:flex-none px-10 py-5 rounded-2xl border border-white/10 text-[10px] font-black uppercase tracking-widest hover:bg-white/5 transition-all">Abbrechen</button>
                        <button onClick={() => onSave(form)} className="flex-1 md:flex-none px-12 py-5 rounded-2xl bg-accent text-white text-[10px] font-black uppercase tracking-widest shadow-2xl shadow-accent/20 hover:scale-105 active:scale-95 transition-all">Sichern</button>
                    </div>
                </header>

                <nav className="flex px-10 border-b border-white/5 bg-slate-950/20">
                    {tabs.map(t => (
                        <button 
                            key={t.id}
                            onClick={() => setActiveTab(t.id)}
                            className={`flex items-center gap-4 px-10 py-8 text-[11px] font-black uppercase tracking-[0.2em] transition-all border-b-2 ${activeTab === t.id ? 'border-accent text-accent shadow-[inset_0_-10px_20px_-15px_rgba(147,213,7,0.3)]' : 'border-transparent text-slate-500 hover:text-slate-300'}`}
                        >
                            {t.icon} {t.label}
                        </button>
                    ))}
                </nav>

                <div className="flex-1 overflow-y-auto p-10 md:p-14 custom-scrollbar bg-slate-950/10">
                    {activeTab === 'general' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-10 animate-in slide-in-from-right-10 duration-500">
                            <AdminInput label="Track Titel" value={form.title} onChange={e => handleChange('title', e.target.value)} placeholder="z.B. Moonlight" />
                            <AdminInput label="Artist" value={form.artist} onChange={e => handleChange('artist', e.target.value)} placeholder="TheStealth" />
                            <div className="md:col-span-2">
                                <AdminInput label="Beschreibung" type="textarea" value={form.description} onChange={e => handleChange('description', e.target.value)} placeholder="Hintergrundinfos zum Song..." />
                            </div>
                            <AdminInput label="Release Datum" type="date" value={form.release_date} onChange={e => handleChange('release_date', e.target.value)} />
                            <AdminInput label="Album / EP" value={form.album} onChange={e => handleChange('album', e.target.value)} placeholder="Optional: Name der Kollektion" />
                            <div className="md:col-span-2 pt-6">
                                <ToggleSwitch label="Status: Veröffentlicht (Out Now)" checked={form.is_out_now == 1} onChange={v => handleChange('is_out_now', v ? 1 : 0)} />
                            </div>
                        </div>
                    )}

                    {activeTab === 'links' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-10 animate-in slide-in-from-right-10 duration-500">
                            <div className="space-y-10">
                                <h4 className="text-[11px] font-black uppercase text-accent tracking-[0.4em] flex items-center gap-3">
                                    <div className="w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></div> Streaming Plattformen
                                </h4>
                                <AdminInput label="Spotify" value={form.spotify_url} onChange={e => handleChange('spotify_url', e.target.value)} />
                                <AdminInput label="Apple Music" value={form.apple_music_url} onChange={e => handleChange('apple_music_url', e.target.value)} />
                                <AdminInput label="Deezer" value={form.deezer_url} onChange={e => handleChange('deezer_url', e.target.value)} />
                                <AdminInput label="YouTube" value={form.youtube_url} onChange={e => handleChange('youtube_url', e.target.value)} />
                            </div>
                            <div className="space-y-10">
                                <h4 className="text-[11px] font-black uppercase text-accent tracking-[0.4em] flex items-center gap-3">
                                    <div className="w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></div> Social & Promo
                                </h4>
                                <AdminInput label="Instagram Promo" value={form.instagram_url} onChange={e => handleChange('instagram_url', e.target.value)} />
                                <AdminInput label="TikTok Promo" value={form.tiktok_url} onChange={e => handleChange('tiktok_url', e.target.value)} />
                                <AdminInput label="Deezer Presave" value={form.deezer_presave_url} onChange={e => handleChange('deezer_presave_url', e.target.value)} />
                                <AdminInput label="Spotify Artist ID" value={form.spotify_artist_url} onChange={e => handleChange('spotify_artist_url', e.target.value)} />
                            </div>
                        </div>
                    )}

                    {activeTab === 'media' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 animate-in slide-in-from-right-10 duration-500">
                            <div className="space-y-8">
                                <div className="p-8 rounded-3xl bg-slate-900/50 border border-white/5 flex flex-col gap-6">
                                    <h4 className="text-[10px] font-black uppercase text-slate-500 tracking-[0.3em]">Artwork Management</h4>
                                    <div className="aspect-square w-full max-w-[200px] rounded-2xl overflow-hidden border border-white/10 mx-auto shadow-2xl relative group">
                                        <img src={form.cover_url || 'https://via.placeholder.com/400?text=No+Cover'} className="w-full h-full object-cover" />
                                        {uploading && <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center text-accent text-[9px] font-black">Lade...</div>}
                                    </div>
                                    <input type="file" onChange={e => handleFile(e, 'cover')} className="text-[10px] text-slate-500 w-full" accept="image/*" />
                                    <AdminInput label="Oder Cover URL" value={form.cover_url} onChange={e => handleChange('cover_url', e.target.value)} />
                                </div>
                            </div>
                            <div className="space-y-8">
                                <div className="p-8 rounded-3xl bg-slate-900/50 border border-white/5 flex flex-col gap-6">
                                    <h4 className="text-[10px] font-black uppercase text-slate-500 tracking-[0.3em]">Audio & Codes</h4>
                                    <AdminInput label="MP3 Audio URL" value={form.mp3_url} onChange={e => handleChange('mp3_url', e.target.value)} />
                                    <input type="file" onChange={e => handleFile(e, 'mp3')} className="text-[10px] text-slate-500 w-full" accept="audio/mpeg" />
                                    <div className="grid grid-cols-1 gap-6 pt-4 border-t border-white/5">
                                        <AdminInput label="ISRC Code" value={form.isrc_code} onChange={e => handleChange('isrc_code', e.target.value)} />
                                        <AdminInput label="Spotify Track URI" value={form.spotify_uri} onChange={e => handleChange('spotify_uri', e.target.value)} />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

// --- 2. DASHBOARD VIEW ---
const AdminDashboardView = ({ stats, loading }) => {
    if (loading || !stats) return (
        <div className="flex flex-col items-center justify-center p-32 animate-pulse">
            <div className="w-20 h-20 border-4 border-accent border-t-transparent rounded-full animate-spin mb-8 shadow-[0_0_30px_rgba(147,213,7,0.2)]"></div>
            <div className="text-slate-700 font-black uppercase tracking-[0.5em] text-xs">Accessing Neural Stats...</div>
        </div>
    );

    const cards = [
        { label: 'Besuche Gesamt', value: stats.total_visits || 0, icon: <Icons.Dashboard />, color: 'text-blue-400', bg: 'bg-blue-400/5' },
        { label: 'Unique Visitors', value: stats.unique_visitors || 0, icon: <Icons.System />, color: 'text-purple-400', bg: 'bg-purple-400/5' },
        { label: 'Tracks Online', value: stats.total_songs || 0, icon: <Icons.Songs />, color: 'text-accent', bg: 'bg-accent/5' },
        { label: 'Newsletter Abos', value: stats.subscribers || 0, icon: <Icons.Settings />, color: 'text-yellow-400', bg: 'bg-yellow-400/5' }
    ];

    return (
        <div className="space-y-12 animate-in fade-in slide-in-from-bottom-10 duration-1000">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                {cards.map((c, i) => (
                    <div key={i} className={`glass p-12 rounded-[3rem] border border-white/5 hover:border-accent/20 transition-all group relative overflow-hidden ${c.bg}`}>
                        <div className="relative z-10">
                            <div className={`mb-10 ${c.color} opacity-30 group-hover:opacity-100 transition-all transform group-hover:scale-125 duration-700`}>{c.icon}</div>
                            <div className="text-6xl font-black mb-3 tracking-tighter tabular-nums">{c.value}</div>
                            <div className="text-[12px] font-black uppercase text-slate-500 tracking-[0.3em]">{c.label}</div>
                        </div>
                        <div className="absolute -right-8 -bottom-8 opacity-[0.02] group-hover:opacity-[0.1] transition-all scale-[3] transform rotate-12 group-hover:rotate-0 duration-1000">{c.icon}</div>
                    </div>
                ))}
            </div>
            
            <div className="glass p-12 rounded-[3.5rem] border border-white/5 bg-white/[0.01] flex flex-col items-center justify-center text-center">
                <div className="flex gap-12 opacity-30">
                    <div className="flex items-center gap-3"><div className="w-2.5 h-2.5 rounded-full bg-accent animate-pulse shadow-[0_0_10px_#93d507]"></div> <span className="text-[10px] font-black uppercase tracking-widest text-slate-300 italic">Core Systems Operational</span></div>
                    <div className="flex items-center gap-3"><div className="w-2.5 h-2.5 rounded-full bg-accent animate-pulse shadow-[0_0_10px_#93d507]"></div> <span className="text-[10px] font-black uppercase tracking-widest text-slate-300 italic">API Pipeline Stable</span></div>
                </div>
            </div>
        </div>
    );
};

// --- 3. SONGS VIEW ---
const AdminSongsView = ({ songs, onEdit, onDelete, onAdd }) => {
    const [search, setSearch] = useState('');
    const filtered = songs ? songs.filter(s => s.title.toLowerCase().includes(search.toLowerCase())) : [];

    return (
        <div className="space-y-10 animate-in fade-in duration-700">
            <header className="flex flex-col md:flex-row justify-between items-stretch gap-8">
                <div className="relative flex-1 group">
                    <div className="absolute left-8 top-1/2 -translate-y-1/2 text-slate-700 group-focus-within:text-accent transition-colors">
                        <Icons.Dashboard />
                    </div>
                    <input 
                        className="w-full bg-slate-900/50 border border-white/10 rounded-3xl py-6 pl-20 pr-10 text-sm outline-none focus:border-accent transition-all shadow-inner placeholder:text-slate-800"
                        placeholder="Durchsuche deine Discographie..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                    />
                </div>
                <button onClick={onAdd} className="bg-accent text-white px-12 py-6 rounded-3xl font-black uppercase text-[11px] tracking-widest shadow-2xl shadow-accent/20 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-4">
                    <Icons.Songs /> Release hinzufügen
                </button>
            </header>

            <div className="grid grid-cols-1 gap-5">
                {filtered.map(s => (
                    <div key={s.id} className="glass p-6 rounded-[2.5rem] border border-white/5 flex flex-col sm:flex-row items-center justify-between group hover:bg-white/[0.02] transition-all gap-8">
                        <div className="flex items-center gap-8 w-full">
                            <div className="w-20 h-20 rounded-2xl overflow-hidden border border-white/10 relative flex-shrink-0 shadow-2xl group-hover:border-accent/40 transition-all">
                                <img src={s.cover_url} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                                {s.is_out_now == 1 && <div className="absolute top-2 right-2 w-3.5 h-3.5 bg-accent rounded-full border-2 border-slate-950 shadow-[0_0_15px_rgba(147,213,7,0.7)] animate-pulse"></div>}
                            </div>
                            <div className="min-w-0">
                                <h4 className="font-black text-xl uppercase tracking-tighter truncate group-hover:text-accent transition-colors">{s.title}</h4>
                                <div className="flex items-center gap-4 mt-2">
                                    <p className="text-[11px] text-slate-400 font-bold uppercase tracking-widest">{s.artist}</p>
                                    <span className="text-slate-800 text-xs">•</span>
                                    <p className="text-[11px] text-slate-600 font-black tracking-widest">{s.release_date}</p>
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-4 w-full sm:w-auto">
                            <button onClick={() => onEdit(s)} className="flex-1 sm:flex-none p-5 rounded-2xl bg-white/5 hover:bg-accent hover:text-white transition-all shadow-sm flex items-center justify-center"><Icons.Dashboard /></button>
                            <button onClick={() => onDelete(s.id)} className="flex-1 sm:flex-none p-5 rounded-2xl bg-white/5 hover:bg-red-500 hover:text-white transition-all shadow-sm flex items-center justify-center"><Icons.System /></button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

// --- 4. MEDIA VIEW ---
const AdminMediaView = ({ media, onUpload, onDelete }) => (
    <div className="space-y-12 animate-in fade-in duration-700">
        <div className="glass p-20 rounded-[4rem] border-2 border-white/5 border-dashed text-center group hover:border-accent/40 transition-all cursor-pointer relative overflow-hidden bg-white/[0.01] shadow-inner">
            <input type="file" multiple onChange={onUpload} className="absolute inset-0 opacity-0 cursor-pointer z-10" />
            <div className="relative z-0 pointer-events-none">
                <div className="w-24 h-24 bg-accent/5 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 group-hover:scale-110 group-hover:bg-accent group-hover:text-white transition-all duration-700 text-slate-600 shadow-inner">
                    <Icons.System />
                </div>
                <h3 className="font-black uppercase tracking-[0.4em] text-sm mb-3 group-hover:text-accent transition-colors">Media Core Upload</h3>
                <p className="text-[11px] text-slate-600 font-bold uppercase tracking-widest italic">Klicken oder Dateien per Drag & Drop hier ablegen</p>
            </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6">
            {media ? media.map(m => (
                <div key={m.id} className="glass group rounded-3xl border border-white/5 overflow-hidden aspect-square relative shadow-2xl">
                    {m.file_type && m.file_type.includes('image') ? (
                        <img src={m.file_path} className="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-all duration-1000" />
                    ) : (
                        <div className="w-full h-full flex flex-col items-center justify-center bg-slate-900/50 p-6">
                            <div className="text-slate-800 group-hover:text-accent transition-colors mb-4 transform scale-150"><Icons.Songs /></div>
                            <span className="text-[9px] font-black uppercase text-slate-600 truncate w-full text-center">{m.file_name}</span>
                        </div>
                    )}
                    <div className="absolute inset-0 bg-slate-950/90 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center p-6 gap-4">
                         <button onClick={() => { navigator.clipboard.writeText(window.location.origin + '/' + m.file_path); }} className="w-full py-4 bg-white/10 hover:bg-accent hover:text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">Copy URL</button>
                         <button onClick={() => onDelete(m.id)} className="w-full py-4 bg-red-500/20 hover:bg-red-500 hover:text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">Löschen</button>
                    </div>
                </div>
            )) : null}
        </div>
    </div>
);

// --- 5. SETTINGS VIEW ---
const AdminSettingsView = ({ settings, onSave }) => {
    const [form, setForm] = useState(settings || {});
    const [activeTab, setActiveTab] = useState('general');
    const [isUploading, setIsUploading] = useState(false);

    // Synchronisierung des lokalen States bei Datenankunft
    useEffect(() => {
        if (settings && Object.keys(settings).length > 0) {
            setForm(settings);
        }
    }, [settings]);

    const update = (k, v) => setForm(p => ({ ...p, [k]: v }));

    const handleProfilePhoto = async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setIsUploading(true);
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await fetch('api.php?action=uploadMedia', {
                method: 'POST',
                headers: { 'X-Admin-Code': SERVER_ADMIN_CODE },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                update('about_photo_url', data.url);
            }
        } catch (err) { console.error("Upload fehlgeschlagen"); }
        setIsUploading(false);
    };

    const tabs = [
        { id: 'general', label: 'Artist Profil' },
        { id: 'design', label: 'Look & Feel' },
        { id: 'seo', label: 'Global SEO' }
    ];

    return (
        <div className="glass rounded-[4rem] border border-white/5 overflow-hidden animate-in fade-in duration-700 shadow-2xl bg-white/[0.01]">
            <header className="p-14 md:p-16 border-b border-white/5 flex flex-col md:flex-row justify-between items-center gap-10 bg-white/[0.02]">
                <div className="flex items-center gap-8">
                    <div className="w-20 h-20 rounded-3xl bg-slate-900/50 flex items-center justify-center text-slate-600 shadow-inner">
                        <Icons.Settings />
                    </div>
                    <div>
                        <h2 className="text-4xl font-black uppercase tracking-tighter leading-none">System Konfig</h2>
                        <p className="text-[11px] text-slate-600 font-bold uppercase tracking-[0.4em] mt-4">Globale Einstellungen der Web-Präsenz</p>
                    </div>
                </div>
                <button onClick={() => onSave(form)} className="w-full md:w-auto bg-accent text-white px-14 py-6 rounded-3xl font-black uppercase text-[11px] tracking-widest shadow-2xl shadow-accent/20 hover:scale-105 active:scale-95 transition-all">Setup speichern</button>
            </header>

            <div className="flex flex-col md:flex-row min-h-[700px]">
                <aside className="w-full md:w-96 border-r border-white/5 bg-slate-950/30">
                    {tabs.map(t => (
                        <button 
                            key={t.id}
                            onClick={() => setActiveTab(t.id)}
                            className={`w-full text-left px-12 py-10 text-[12px] font-black uppercase tracking-[0.2em] transition-all flex items-center justify-between ${activeTab === t.id ? 'bg-accent/5 text-accent border-r-4 border-accent shadow-inner' : 'text-slate-500 hover:bg-white/5 hover:text-slate-300'}`}
                        >
                            {t.label}
                            {activeTab === t.id && <div className="w-2 h-2 rounded-full bg-accent shadow-[0_0_15px_#93d507]"></div>}
                        </button>
                    ))}
                </aside>

                <main className="flex-1 p-14 md:p-20 bg-slate-950/10">
                    {activeTab === 'general' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 animate-in slide-in-from-right-10 duration-500">
                            <AdminInput label="Artist Name" value={form.artist_name} onChange={e => update('artist_name', e.target.value)} />
                            <AdminInput label="Shop Domain" value={form.site_domain} onChange={e => update('site_domain', e.target.value)} />
                            <div className="md:col-span-2 pt-8">
                                <ToggleSwitch label="E-Commerce / Merch Shop aktivieren" checked={form.merch_enabled == 1} onChange={v => update('merch_enabled', v ? 1 : 0)} />
                            </div>
                            {form.merch_enabled == 1 && (
                                <div className="md:col-span-2 animate-in slide-in-from-top-4">
                                    <AdminInput label="Spreadshirt Shop ID" value={form.spreadshirt_shop_name} onChange={e => update('spreadshirt_shop_name', e.target.value)} />
                                </div>
                            )}

                            {/* --- BIOGRAFIE & PROFILBILD --- */}
                            <div className="md:col-span-2 pt-10 border-t border-white/5 space-y-10">
                                <h4 className="text-[11px] font-black uppercase text-accent tracking-[0.4em]">Biografie & Profil</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
                                    <div className="space-y-6">
                                        <label className="text-[10px] font-black uppercase text-slate-500 tracking-widest ml-1">Profilfoto</label>
                                        <div className="aspect-square w-full rounded-3xl overflow-hidden border border-white/10 bg-slate-900 relative group shadow-2xl">
                                            <img src={form.about_photo_url || 'https://via.placeholder.com/400?text=Artist+Photo'} className="w-full h-full object-cover" />
                                            {isUploading && <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center text-accent text-[9px] font-black">Lade...</div>}
                                            <input type="file" onChange={handleProfilePhoto} className="absolute inset-0 opacity-0 cursor-pointer z-10" accept="image/*" />
                                        </div>
                                        <p className="text-[9px] text-slate-600 font-bold uppercase text-center italic">Klicke zum Ändern</p>
                                    </div>
                                    <div className="md:col-span-2">
                                        <AdminInput label="Über den Artist (About Text)" type="textarea" value={form.about_text} onChange={e => update('about_text', e.target.value)} placeholder="Schreibe hier deine Biografie oder wichtige Infos..." />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {activeTab === 'design' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 animate-in slide-in-from-right-10 duration-500">
                            <div className="space-y-4">
                                <AdminInput label="Akzentfarbe (Hex)" value={form.accent_color} onChange={e => update('accent_color', e.target.value)} />
                                <div className="w-full h-4 rounded-full mt-2 shadow-[0_0_15px_rgba(0,0,0,0.5)]" style={{ backgroundColor: form.accent_color || '#93d507' }}></div>
                            </div>
                            <div className="space-y-4">
                                <AdminInput label="Hintergrundfarbe (Hex)" value={form.main_color} onChange={e => update('main_color', e.target.value)} />
                                <div className="w-full h-4 rounded-full mt-2 shadow-[0_0_15px_rgba(0,0,0,0.5)]" style={{ backgroundColor: form.main_color || '#020617' }}></div>
                            </div>
                            
                            {/* --- HEADER & FOOTER FARBE --- */}
                            <div className="space-y-4">
                                <AdminInput label="Header & Footer Farbe (Hex)" value={form.header_footer_color} onChange={e => update('header_footer_color', e.target.value)} />
                                <div className="w-full h-4 rounded-full mt-2 shadow-[0_0_15px_rgba(0,0,0,0.5)]" style={{ backgroundColor: form.header_footer_color || '#0a0f1d' }}></div>
                            </div>

                            <div className="md:col-span-2 pt-10">
                                <AdminInput label="Globale Social Embeds (TikTok / IG)" type="textarea" value={form.global_tiktok_embed} onChange={e => update('global_tiktok_embed', e.target.value)} placeholder="Füge hier Embed-Codes ein..." />
                            </div>
                        </div>
                    )}

                    {activeTab === 'seo' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 animate-in slide-in-from-right-10 duration-500">
                            <AdminInput label="Meta Beschreibung" type="textarea" value={form.seo_description} onChange={e => update('seo_description', e.target.value)} />
                            <AdminInput label="Sharing / OG Image URL" value={form.og_image_url} onChange={e => update('og_image_url', e.target.value)} />
                            <div className="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-8 pt-10 border-t border-white/5">
                                <AdminInput label="Instagram URL" value={form.instagram_url} onChange={e => update('instagram_url', e.target.value)} />
                                <AdminInput label="TikTok URL" value={form.tiktok_url} onChange={e => update('tiktok_url', e.target.value)} />
                            </div>
                        </div>
                    )}
                </main>
            </div>
        </div>
    );
};

// --- 6. SYSTEM VIEW (Vollständige Dev-Tools & Newsletter) ---
const AdminSystemView = ({ 
    sqlQuery, setSqlQuery, handleSqlRun, 
    systemFile, setSystemFile, targetFileName, setTargetFileName, handleSystemFileUpdate,
    newsletterSubject, setNewsletterSubject, newsletterBody, setNewsletterBody, handleSendNewsletter 
}) => {
    return (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 animate-in fade-in duration-700">
            
            <section className="glass p-12 md:p-16 rounded-[4rem] border border-white/5 space-y-12 bg-yellow-500/[0.01] shadow-2xl">
                <header className="flex items-center gap-8">
                    <div className="w-20 h-20 rounded-3xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 shadow-inner">
                        <Icons.Settings />
                    </div>
                    <div>
                        <h3 className="text-3xl font-black uppercase tracking-tighter leading-none">Fan Marketing</h3>
                        <p className="text-[12px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-4">Newsletter Broadcast an alle Abonnenten</p>
                    </div>
                </header>

                <div className="space-y-8 pt-12 border-t border-white/5">
                    <AdminInput label="E-Mail Betreff" value={newsletterSubject} onChange={e => setNewsletterSubject(e.target.value)} />
                    <AdminInput label="Nachricht (HTML erlaubt)" type="textarea" value={newsletterBody} onChange={e => setNewsletterBody(e.target.value)} />
                    <button onClick={handleSendNewsletter} className="w-full bg-accent text-white py-6 rounded-3xl font-black uppercase text-xs tracking-[0.3em] hover:scale-[1.02] active:scale-95 transition-all shadow-2xl shadow-accent/20 mt-8">Newsletter jetzt aussenden</button>
                    <p className="text-[9px] text-slate-600 font-bold uppercase tracking-widest text-center italic">Versand über System-Mailing-Engine</p>
                </div>
            </section>

            <section className="glass p-12 md:p-16 rounded-[4rem] border border-white/5 space-y-12 bg-red-500/[0.01] shadow-2xl">
                <header className="flex items-center gap-8">
                    <div className="w-20 h-20 rounded-3xl bg-red-500/10 flex items-center justify-center text-red-500 shadow-inner">
                        <Icons.System />
                    </div>
                    <div>
                        <h3 className="text-3xl font-black uppercase tracking-tighter leading-none">Core Engine</h3>
                        <p className="text-[12px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-4">Datenbank & System-Dateien Patching</p>
                    </div>
                </header>

                <div className="space-y-12 pt-12 border-t border-white/5">
                    <div className="space-y-4">
                        <label className="text-[10px] font-black uppercase text-slate-600 tracking-widest ml-2 flex justify-between">SQL Command <span className="text-red-500/40">Authorized Only</span></label>
                        <textarea 
                            className="w-full bg-slate-950/80 p-8 rounded-3xl border border-white/10 text-xs font-mono h-36 text-green-400 outline-none focus:border-red-500/30 transition-all shadow-inner placeholder:text-slate-900" 
                            placeholder="SELECT * FROM visits ORDER BY id DESC LIMIT 5;" 
                            value={sqlQuery} 
                            onChange={e => setSqlQuery(e.target.value)}
                        />
                        <button onClick={handleSqlRun} className="w-full bg-slate-900 border border-white/5 text-slate-500 py-5 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-800 hover:text-white transition-all">SQL Query Execute</button>
                    </div>

                    <div className="pt-12 border-t border-white/5">
                        <label className="text-[10px] font-black uppercase text-slate-600 tracking-widest mb-6 block ml-2">Kernel Hot-Patch (Live Update)</label>
                        <div className="flex flex-col gap-4">
                            <div className="flex flex-col md:flex-row gap-4">
                                <select className="flex-1 bg-slate-900 border border-white/10 rounded-2xl text-[10px] p-5 text-white font-black uppercase tracking-widest outline-none focus:border-accent" value={targetFileName} onChange={e => setTargetFileName(e.target.value)}>
                                    <option value="api.php">api.php (Core Logic)</option>
                                    <option value="admin_views.php">admin_views.php (UI Core)</option>
                                    <option value="index.php">index.php (Public Home)</option>
                                    <option value="views.php">views.php (Frontend Views)</option>
                                </select>
                                <div className="relative flex-1 group">
                                    <input type="file" onChange={e => setSystemFile(e.target.files[0])} className="absolute inset-0 opacity-0 cursor-pointer z-20" />
                                    <div className="h-full bg-slate-950/50 border border-white/10 rounded-2xl p-5 text-[10px] font-black uppercase text-slate-600 group-hover:text-white transition-all text-center flex items-center justify-center truncate px-6">
                                        {systemFile ? systemFile.name : 'Patch-Datei wählen...'}
                                    </div>
                                </div>
                            </div>
                            <button onClick={handleSystemFileUpdate} className="w-full bg-red-600/10 text-red-500 border border-red-500/20 py-6 rounded-3xl font-black uppercase text-[10px] tracking-widest hover:bg-red-600 hover:text-white transition-all shadow-lg">Hot-Patch einspielen & Kernel überschreiben</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
};